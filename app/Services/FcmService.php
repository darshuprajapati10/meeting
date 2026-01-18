<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $projectId = config('firebase.project_id');

            if (empty($projectId)) {
                Log::warning('Firebase project ID not configured');
                return;
            }

            // Option 1: Try environment variable (JSON content as string)
            $credentialsJson = config('firebase.credentials_json');
            
            // Option 2: Try file path
            $credentialsPath = config('firebase.credentials');
            
            $factory = new Factory();
            
            if (!empty($credentialsJson)) {
                // Use JSON content from environment variable
                $credentials = json_decode($credentialsJson, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $factory = $factory->withServiceAccount($credentials);
                    Log::info('Firebase initialized using environment variable');
                } else {
                    Log::warning('Invalid FIREBASE_CREDENTIALS_JSON format', [
                        'json_error' => json_last_error_msg()
                    ]);
                    return;
                }
            } elseif (file_exists($credentialsPath)) {
                // Use file path (existing method)
                $factory = $factory->withServiceAccount($credentialsPath);
                Log::info('Firebase initialized using service account file');
            } else {
                Log::warning('Firebase credentials not found', [
                    'path' => $credentialsPath,
                    'has_env_json' => !empty($credentialsJson)
                ]);
                return;
            }

            $factory = $factory->withProjectId($projectId);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notification to a single device
     *
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return bool
     */
    public function sendNotification(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return false;
        }

        try {
            $notification = Notification::create($title, $body);
            
            // Convert all data values to strings (FCM requirement)
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = (string) $value;
            }
            
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($stringData);

            $this->messaging->send($message);
            
            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title
            ]);

            return true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorClass = get_class($e);
            
            // Log full error details for debugging
            Log::debug('FCM exception caught', [
                'error_class' => $errorClass,
                'error_message' => $errorMessage,
                'error_code' => method_exists($e, 'getCode') ? $e->getCode() : null,
            ]);
            
            // Check if token is invalid and should be deleted
            // Check both error message and exception class name
            $isInvalidToken = str_contains($errorMessage, 'NotRegistered') || 
                             str_contains($errorMessage, 'Requested entity was not found') ||
                             str_contains($errorMessage, 'not found') ||
                             str_contains($errorClass, 'NotFound') ||
                             str_contains($errorClass, 'InvalidArgument');
            
            if ($isInvalidToken) {
                // Delete invalid token from database
                $deleted = DB::table('fcm_tokens')
                    ->where('token', $token)
                    ->delete();
                
                Log::info('Invalid FCM token deleted', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $errorMessage,
                    'error_class' => $errorClass,
                    'deleted_count' => $deleted
                ]);
            }
            
            Log::error('FCM notification failed', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $errorMessage,
                'error_class' => $errorClass
            ]);

            return false;
        }
    }

    /**
     * Send notification to multiple devices
     *
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results with success/failure for each token
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = [];
        
        foreach ($tokens as $token) {
            $results[$token] = $this->sendNotification($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * Send notification to all devices of a user
     *
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        if (!DB::getSchemaBuilder()->hasTable('fcm_tokens')) {
            Log::warning('fcm_tokens table does not exist');
            return [];
        }

        $tokens = DB::table('fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::warning('No FCM tokens found for user', ['user_id' => $userId]);
            return [];
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Validate FCM token
     *
     * @param string $token FCM device token
     * @return bool
     */
    public function validateToken(string $token): bool
    {
        // FCM tokens are typically 152+ characters long
        // Web tokens might not have ':' but are still valid
        // Android/iOS tokens often have ':' but web tokens might not
        // We accept if length is sufficient (minimum 100 characters)
        if (strlen($token) < 100) {
            return false;
        }
        
        return !empty($token);
    }
}

