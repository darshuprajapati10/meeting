<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireTrialSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-trials';
    protected $description = 'Expire trial subscriptions and downgrade to Free plan';

    public function handle(SubscriptionService $subscriptionService)
    {
        Log::info('Scheduler: subscriptions:expire-trials command started', [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone')
        ]);

        // Find all trial subscriptions that have expired
        $expiredTrials = Subscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->with(['organization', 'plan'])
            ->get();

        $expiredCount = $expiredTrials->count();
        
        if ($expiredCount === 0) {
            Log::info('No expired trial subscriptions found');
            $this->info('No expired trial subscriptions found');
            return 0;
        }

        Log::info('Found expired trial subscriptions', [
            'count' => $expiredCount
        ]);

        $downgradedCount = 0;
        $errorCount = 0;

        foreach ($expiredTrials as $trialSubscription) {
            try {
                // Cancel the expired trial subscription
                $trialSubscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                // Create a new Free plan subscription for the organization
                $subscriptionService->createFreeSubscription($trialSubscription->organization);

                Log::info('Expired trial subscription downgraded to Free', [
                    'subscription_id' => $trialSubscription->id,
                    'organization_id' => $trialSubscription->organization_id,
                    'trial_ended_at' => $trialSubscription->trial_ends_at,
                ]);

                $downgradedCount++;
            } catch (\Exception $e) {
                Log::error('Error downgrading expired trial subscription', [
                    'subscription_id' => $trialSubscription->id,
                    'organization_id' => $trialSubscription->organization_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $errorCount++;
            }
        }

        Log::info('Scheduler: subscriptions:expire-trials command completed', [
            'expired_found' => $expiredCount,
            'downgraded' => $downgradedCount,
            'errors' => $errorCount,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        $this->info("Processed {$expiredCount} expired trials: {$downgradedCount} downgraded, {$errorCount} errors");

        return 0;
    }
}

