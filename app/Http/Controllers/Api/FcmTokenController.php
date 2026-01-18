<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FcmService;

class FcmTokenController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Register or update FCM token
     * POST /api/fcm/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'platform' => 'required|in:ios,android,web',
            'device_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        if (!$user) {
            Log::error('FCM token registration failed: User not authenticated');
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $token = $request->input('token');
        $platform = $request->input('platform');
        $deviceId = $request->input('device_id');

        Log::info('FCM token registration attempt', [
            'user_id' => $user->id,
            'platform' => $platform,
            'token_length' => strlen($token),
            'device_id' => $deviceId
        ]);

        // Validate token format
        if (!$this->fcmService->validateToken($token)) {
            Log::warning('FCM token validation failed', [
                'user_id' => $user->id,
                'token_length' => strlen($token)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid FCM token format'
            ], 400);
        }

        try {
            if (!DB::getSchemaBuilder()->hasTable('fcm_tokens')) {
                Log::error('FCM tokens table does not exist');
                return response()->json([
                    'success' => false,
                    'message' => 'FCM tokens table does not exist. Please run migrations.'
                ], 500);
            }

            // Check if token already exists for this user
            $existingToken = DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->where('token', $token)
                ->first();

            if ($existingToken) {
                // Update existing token
                $updated = DB::table('fcm_tokens')
                    ->where('id', $existingToken->id)
                    ->update([
                        'platform' => $platform,
                        'device_id' => $deviceId,
                        'updated_at' => now(),
                    ]);
                
                Log::info('FCM token updated', [
                    'user_id' => $user->id,
                    'token_id' => $existingToken->id,
                    'updated' => $updated
                ]);
            } else {
                // Insert new token
                $inserted = DB::table('fcm_tokens')->insert([
                    'user_id' => $user->id,
                    'token' => $token,
                    'platform' => $platform,
                    'device_id' => $deviceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('FCM token inserted', [
                    'user_id' => $user->id,
                    'platform' => $platform,
                    'inserted' => $inserted
                ]);
                
                // Verify insertion
                $verifyCount = DB::table('fcm_tokens')
                    ->where('user_id', $user->id)
                    ->where('token', $token)
                    ->count();
                
                if ($verifyCount === 0) {
                    Log::error('FCM token insertion failed - token not found after insert', [
                        'user_id' => $user->id
                    ]);
                }
            }

            // Verify final state
            $finalCount = DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->count();
            
            Log::info('FCM token registration completed', [
                'user_id' => $user->id,
                'total_tokens_for_user' => $finalCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unregister FCM token
     * POST /api/fcm/unregister
     */
    public function unregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $token = $request->input('token');

        try {
            if (!DB::getSchemaBuilder()->hasTable('fcm_tokens')) {
                return response()->json([
                    'success' => false,
                    'message' => 'FCM tokens table does not exist. Please run migrations.'
                ], 500);
            }

            DB::table('fcm_tokens')
                ->where('user_id', $user->id)
                ->where('token', $token)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'FCM token unregistered successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister FCM token: ' . $e->getMessage()
            ], 500);
        }
    }
}


