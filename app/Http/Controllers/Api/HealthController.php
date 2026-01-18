<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    /**
     * Comprehensive health check for deployment verification
     */
    public function check()
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ];

        // Database connectivity check
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'connected';
        } catch (\Exception $e) {
            $checks['database'] = 'failed';
            $checks['status'] = 'unhealthy';
        }

        // Cache system check
        try {
            Cache::has('health_check_test');
            $checks['cache'] = 'accessible';
        } catch (\Exception $e) {
            $checks['cache'] = 'failed';
            $checks['status'] = 'degraded';
        }

        // Queue system check
        try {
            $queueSize = DB::table('jobs')->count();
            $checks['queue'] = [
                'status' => 'running',
                'pending_jobs' => $queueSize,
            ];
        } catch (\Exception $e) {
            $checks['queue'] = ['status' => 'failed'];
        }

        // Release information (useful for atomic deployments)
        $checks['release'] = [
            'path' => base_path(),
            'env' => app()->environment(),
        ];

        $httpCode = $checks['status'] === 'healthy' ? 200 : 503;

        return response()->json($checks, $httpCode);
    }

    /**
     * Simple ping endpoint for basic uptime checks
     */
    public function ping()
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
