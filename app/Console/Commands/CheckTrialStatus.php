<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckTrialStatus extends Command
{
    protected $signature = 'subscriptions:check-trials {--organization-id= : Check specific organization ID}';
    protected $description = 'Check trial subscription status';

    public function handle()
    {
        $organizationId = $this->option('organization-id');
        
        $query = Subscription::where('status', 'trial')
            ->with(['organization', 'plan']);
            
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }
        
        $trials = $query->orderBy('trial_ends_at', 'asc')->get();
        
        if ($trials->isEmpty()) {
            $this->info('No trial subscriptions found.');
            return 0;
        }
        
        $this->info("Found {$trials->count()} trial subscription(s):\n");
        
        $headers = ['ID', 'Organization', 'Plan', 'Status', 'Trial Ends At', 'Days Remaining', 'Expired?'];
        $rows = [];
        
        foreach ($trials as $trial) {
            $trialEndsAt = $trial->trial_ends_at;
            $daysRemaining = null;
            
            if ($trialEndsAt) {
                if ($trialEndsAt->isFuture()) {
                    $daysRemaining = now()->diffInDays($trialEndsAt, false);
                } else {
                    $daysRemaining = 0;
                }
            } else {
                $daysRemaining = 'N/A';
            }
            
            $isExpired = $trial->isTrialExpired() ? 'YES' : 'NO';
            
            $rows[] = [
                $trial->id,
                $trial->organization->name ?? 'N/A',
                $trial->plan->name ?? 'N/A',
                $trial->status,
                $trialEndsAt ? $trialEndsAt->format('Y-m-d H:i:s') : 'N/A',
                $daysRemaining !== 'N/A' ? (string) $daysRemaining : 'N/A',
                $isExpired,
            ];
        }
        
        $this->table($headers, $rows);
        
        // Summary
        $expiredCount = $trials->filter(fn($trial) => $trial->isTrialExpired())->count();
        $activeCount = $trials->filter(fn($trial) => $trial->isTrialActive())->count();
        
        $this->info("\nSummary:");
        $this->info("  Active trials: {$activeCount}");
        $this->info("  Expired trials: {$expiredCount}");
        
        return 0;
    }
}



