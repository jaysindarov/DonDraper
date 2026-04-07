<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    /**
     * Show the billing / upgrade page.
     */
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Billing/Index', [
            'currentPlan'  => $user->plan,
            'credits'      => $user->credits,
            'plans'        => config('billing.plans'),
            'creditPacks'  => config('billing.credit_packs'),
            'subscription' => $user->subscription('default')?->only([
                'stripe_status', 'ends_at', 'trial_ends_at',
            ]),
            'onGracePeriod' => $user->subscription('default')?->onGracePeriod() ?? false,
        ]);
    }

    /**
     * Start a Stripe Checkout session for a plan subscription.
     */
    public function checkout(Request $request, string $plan): RedirectResponse
    {
        $planConfig = config("billing.plans.{$plan}");

        abort_if(
            !$planConfig || !$planConfig['stripe_price_id'],
            400,
            'Invalid plan or Stripe price not configured.'
        );

        $user = $request->user();

        // Already on this plan
        if ($user->plan === $plan && $user->subscribed('default')) {
            return redirect()->route('billing')
                ->with('error', "You are already on the {$planConfig['name']} plan.");
        }

        // If the user already has a subscription, use Stripe's swap to upgrade/downgrade
        if ($user->subscribed('default')) {
            $user->subscription('default')->swap($planConfig['stripe_price_id']);

            $this->applyPlan($user, $plan);

            return redirect()->route('billing')
                ->with('success', "Your plan has been updated to {$planConfig['name']}.");
        }

        // New subscription — redirect to Stripe Checkout
        return $user->newSubscription('default', $planConfig['stripe_price_id'])
            ->checkout([
                'success_url' => route('billing.success') . '?plan=' . $plan,
                'cancel_url'  => route('billing'),
            ]);
    }

    /**
     * Handle a successful Stripe Checkout redirect.
     * Stripe Checkout already creates the subscription — we sync the plan here
     * as an immediate update; the webhook will also fire and confirm it.
     */
    public function success(Request $request): RedirectResponse
    {
        $plan = $request->query('plan');
        $planConfig = config("billing.plans.{$plan}");

        if ($planConfig && $request->user()) {
            $this->applyPlan($request->user(), $plan);
        }

        return redirect()->route('billing')
            ->with('success', 'Subscription activated! Your credits have been topped up.');
    }

    /**
     * Redirect to the Stripe Customer Portal for subscription management.
     * From there users can update payment method, cancel, view invoices.
     */
    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('billing'));
    }

    /**
     * One-time credit top-up via Stripe Checkout (no subscription).
     */
    public function buyCredits(Request $request, int $pack): RedirectResponse
    {
        $packs = config('billing.credit_packs');

        abort_unless(isset($packs[$pack]) && !empty($packs[$pack]['stripe_price_id']), 400);

        $packConfig = $packs[$pack];

        return $request->user()
            ->checkoutCharge(
                $packConfig['price'] * 100, // amount in cents
                $packConfig['name'],
                1,
                [
                    'success_url' => route('billing.credits.success') . '?pack=' . $pack,
                    'cancel_url'  => route('billing'),
                ]
            );
    }

    /**
     * Handle a successful credit pack purchase redirect.
     */
    public function creditsSuccess(Request $request): RedirectResponse
    {
        $packIndex = (int) $request->query('pack', -1);
        $packs     = config('billing.credit_packs');

        if (isset($packs[$packIndex])) {
            $credits = $packs[$packIndex]['credits'];
            $request->user()->increment('credits', $credits);
        }

        return redirect()->route('billing')
            ->with('success', 'Credits added to your account!');
    }

    /**
     * Update the user's plan and refresh their credits.
     * Called both on immediate swap and on checkout success.
     */
    private function applyPlan(\App\Models\User $user, string $plan): void
    {
        $credits = config("billing.plans.{$plan}.credits_per_month", 0);

        $user->update([
            'plan'    => $plan,
            'credits' => $credits,
        ]);
    }
}
