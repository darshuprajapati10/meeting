<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated This controller is deprecated. Privacy settings feature has been removed from the frontend.
 * These endpoints will return 410 Gone status. Consider removing this controller after monitoring for 30 days.
 * 
 * Frontend removal date: 2025-01-XX
 * Deprecation date: 2025-01-XX
 * Planned removal: After 30 days of monitoring (if no usage detected)
 */
class PrivacySettingsController extends Controller
{
    /**
     * Get the current authenticated user's privacy settings.
     * 
     * @deprecated This endpoint has been deprecated. Privacy features have been removed from the frontend.
     * Returns 410 Gone status.
     * 
     * GET /api/privacy/settings
     */
    public function index(Request $request)
    {
        // Log deprecated endpoint access for monitoring
        Log::warning('Deprecated privacy settings endpoint accessed', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => '/api/privacy/settings',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Privacy settings endpoint has been deprecated',
            'errors' => ['This feature has been removed from the application'],
        ], 410); // 410 Gone - resource is no longer available
    }

    /**
     * Update the current authenticated user's privacy settings.
     * 
     * @deprecated This endpoint has been deprecated. Privacy features have been removed from the frontend.
     * Returns 410 Gone status.
     * 
     * POST /api/privacy/settings
     */
    public function store(Request $request)
    {
        // Log deprecated endpoint access for monitoring
        Log::warning('Deprecated privacy settings endpoint accessed', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => '/api/privacy/settings',
            'request_data' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Privacy settings endpoint has been deprecated',
            'errors' => ['This feature has been removed from the application'],
        ], 410); // 410 Gone - resource is no longer available
    }
}

