<?php

/**
 * Billing & Plan Configuration
 *
 * Stripe Price IDs are set via environment variables so they can differ
 * between test and production without touching code.
 *
 * credits_per_month: credits granted when a subscription starts or renews.
 * For 'enterprise', set a very high number — there is no hard "unlimited"
 * in the credit system, but 999999 is effectively unlimited.
 */

return [

    'plans' => [

        'free' => [
            'name'               => 'Starter',
            'price'              => 0,
            'credits_per_month'  => 10,
            'stripe_price_id'    => null, // No Stripe product for free tier
            'features'           => [
                '10 image generations',
                'Standard quality',
                'Basic styles',
                'Community gallery',
            ],
        ],

        'pro' => [
            'name'               => 'Pro',
            'price'              => 19,
            'credits_per_month'  => 500,
            'stripe_price_id'    => env('STRIPE_PRICE_PRO', ''),
            'features'           => [
                '500 image generations/month',
                'HD & Ultra HD quality',
                'All styles & models',
                'Priority queue',
                'Private gallery',
                'API access',
            ],
        ],

        'enterprise' => [
            'name'               => 'Enterprise',
            'price'              => 79,
            'credits_per_month'  => 999999,
            'stripe_price_id'    => env('STRIPE_PRICE_ENTERPRISE', ''),
            'features'           => [
                'Unlimited generations',
                'All Pro features',
                'Custom models',
                'Dedicated support',
                'Team workspace',
                'SLA guarantee',
            ],
        ],

    ],

    // One-time credit top-up packs (no subscription required)
    'credit_packs' => [
        [
            'name'            => 'Starter Pack',
            'credits'         => 20,
            'price'           => 4,
            'stripe_price_id' => env('STRIPE_PRICE_CREDITS_20', ''),
        ],
        [
            'name'            => 'Power Pack',
            'credits'         => 100,
            'price'           => 15,
            'stripe_price_id' => env('STRIPE_PRICE_CREDITS_100', ''),
            'popular'         => true,
        ],
        [
            'name'            => 'Pro Pack',
            'credits'         => 500,
            'price'           => 60,
            'stripe_price_id' => env('STRIPE_PRICE_CREDITS_500', ''),
        ],
    ],

];
