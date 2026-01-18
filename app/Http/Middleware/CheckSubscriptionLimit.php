<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionLimit
{
    public function __construct(private SubscriptionService $subscriptionService)
    {}

    public function handle(Request $request, Closure $next, string $action)
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

        $result = $this->subscriptionService->checkLimit($organization, $action);

        if (!$result['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => [
                    'upgrade_required' => true,
                    'current' => $result['current'],
                    'limit' => $result['limit'],
                ]
            ], 403);
        }

        return $next($request);
    }
}
