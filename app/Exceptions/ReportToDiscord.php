<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ReportToDiscord
{
    public function __invoke(Throwable $exception): void
    {
        if (!app()->environment('production')) {
            return;
        }
        // Skip certain exception types that are not critical
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return; // Don't report validation errors

        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return; // Don't report 404s
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return; // Don't report method not allowed
        }
        
        try {
            $webhook = config('services.discord.webhook_url');
            if (!$webhook) {
                return;
            }

            // Get logged in user email
            $user = Auth::user();
            $userEmail = $user ? $user->email : 'Guest';
            $userId = $user ? $user->id : null;

            // Check if it's a POST request and extract input
            $request = Request::instance();
            $requestMethod = $request->method();
            $requestUrl = $request->fullUrl();
            
            // Get POST data but exclude sensitive fields
            $sensitiveFields = ['password', 'token', 'api_key', 'secret', 'credit_card', 'cvv', 'ssn'];
            $postData = $requestMethod === 'POST' 
                ? json_encode($request->except($sensitiveFields), JSON_PRETTY_PRINT) 
                : null;

            // Truncate large POST data to prevent Discord message limits
            if ($postData && strlen($postData) > 2000) {
                $postData = substr($postData, 0, 2000) . "\n... (truncated)";
            }

            // Build exception trace (first 10 lines)
            $trace = $exception->getTraceAsString();
            $traceLines = explode("\n", $trace);
            $tracePreview = implode("\n", array_slice($traceLines, 0, 10));
            if (count($traceLines) > 10) {
                $tracePreview .= "\n... (more lines)";
            }

            $message = "**🚨 Laravel Exception**\n";
            $message .= "**Message:** `" . $exception->getMessage() . "`\n";
            $message .= "**File:** `" . $exception->getFile() . ':' . $exception->getLine() . "`\n";
            $message .= "**Exception:** `" . get_class($exception) . "`\n";
            $message .= "**Env:** `" . app()->environment() . "`\n";
            $message .= "**User:** `" . $userEmail . "` (ID: " . ($userId ?? 'N/A') . ")\n";
            $message .= "**URL:** `" . $requestUrl . "`\n";
            $message .= "**Method:** `" . $requestMethod . "`\n";

            if ($postData) {
                $message .= "**POST Data:**\n```json\n" . $postData . "\n```\n";
            }

            $message .= "**Trace:**\n```\n" . $tracePreview . "\n```\n";

            Http::timeout(5)->post($webhook, [
                'content' => $message
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send Discord alert: ' . $e->getMessage());
        }
    }
}

