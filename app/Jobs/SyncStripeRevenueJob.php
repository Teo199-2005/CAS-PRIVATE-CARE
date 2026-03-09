<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sync Stripe revenue data to cache.
 * Run every 5-10 minutes via scheduler to avoid Stripe API rate limits in hot paths.
 */
class SyncStripeRevenueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (!config('services.stripe.secret')) {
            return;
        }
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeTotal = 0;
            $charges = \Stripe\Charge::all(['limit' => 100]);
            foreach ($charges->data as $charge) {
                if ($charge->status === 'succeeded') {
                    $stripeTotal += $charge->amount / 100;
                }
            }
            Cache::put('stripe_revenue_total', $stripeTotal, now()->addMinutes(10));
        } catch (\Throwable $e) {
            Log::warning('SyncStripeRevenueJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
