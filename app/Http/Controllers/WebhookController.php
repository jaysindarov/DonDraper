<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Stripe\Subscription;

/**
 * Extends Cashier's webhook controller to handle plan/credit sync.
 *
 * Cashier handles signature verification and event routing automatically.
 * We override specific event handlers to keep our plan and credit fields
 * in sync with the source of truth in Stripe.
 *
 * Events handled:
 *   - customer.subscription.updated   → sync plan on upgrade/downgrade
 *   - customer.subscription.deleted   → downgrade to free
 *   - invoice.payment_succeeded       → refresh credits each billing cycle
 */
class WebhookController extends CashierWebhookController
{
    /**
     * Subscription was created or updated (plan change, reactivation, etc.)
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $subscription = $payload['data']['object'];

        if ($subscription['status'] === Subscription::STATUS_ACTIVE) {
            $this->syncPlanFromSubscription($subscription);
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Subscription cancelled — downgrade to free immediately (or at period end
     * if cancel_at_period_end is true; Cashier's grace period handles that case).
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $stripeCustomerId = $payload['data']['object']['customer'];
        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if ($user) {
            $user->update([
                'plan'    => 'free',
                'credits' => config('billing.plans.free.credits_per_month', 10),
            ]);
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    /**
     * Invoice paid — refresh credits for the new billing period.
     * Fires on every successful recurring charge, not just the first one.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $invoice = $payload['data']['object'];

        // Only process subscription invoices, not one-off charges
        if (empty($invoice['subscription'])) {
            return $this->successMethod();
        }

        $stripeCustomerId = $invoice['customer'];
        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if ($user && $user->plan !== 'free') {
            $credits = config("billing.plans.{$user->plan}.credits_per_month", 0);
            $user->update(['credits' => $credits]);
        }

        return $this->successMethod();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function syncPlanFromSubscription(array $subscription): void
    {
        $stripeCustomerId = $subscription['customer'];
        $stripePriceId    = $subscription['items']['data'][0]['price']['id'] ?? null;

        if (!$stripePriceId) {
            return;
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();

        if (!$user) {
            return;
        }

        // Find which plan matches this Stripe Price ID
        foreach (config('billing.plans') as $planKey => $planConfig) {
            if (($planConfig['stripe_price_id'] ?? null) === $stripePriceId) {
                $user->update(['plan' => $planKey]);
                return;
            }
        }
    }
}
