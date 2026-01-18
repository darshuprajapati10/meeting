<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionFeature
{
    public function __construct(private SubscriptionService $subscriptionService)
    {}

    public function handle(Request $request, Closure $next, string $feature)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
                'data' => [
                    'upgrade_required' => true,
                ]
            ], 403);
        }

        if (!$this->subscriptionService->hasFeature($organization, $feature)) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription.',
                'data' => [
                    'upgrade_required' => true,
                    'feature' => $feature,
                ]
            ], 403);
        }

        return $next($request);
    }
}
