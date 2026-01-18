<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZeptoMailService
{
    protected string $apiKey;
    protected string $bounceAddress;
    protected string $fromAddress;
    protected string $fromName;
    protected string $apiUrl = 'https://api.zeptomail.com/v1.1/email';

    public function __construct()
    {
        $this->apiKey = config('mail.mailers.zeptomail.api_key', env('ZEPTO_MAIL_API_KEY'));
        $this->bounceAddress = config('mail.mailers.zeptomail.bounce_address', env('ZEPTO_MAIL_BOUNCE_ADDRESS'));
        $this->fromAddress = config('mail.mailers.zeptomail.from_address', env('ZEPTO_MAIL_FROM_ADDRESS'));
        $this->fromName = config('mail.mailers.zeptomail.from_name', env('ZEPTO_MAIL_FROM_NAME', 'Ongoing Forge'));
    }

    /**
     * Send email via ZeptoMail API
     *
     * @param string $to Email address of recipient
     * @param string $subject Email subject
     * @param string $htmlBody HTML content of the email
     * @param string|null $textBody Plain text content (optional)
     * @param array $headers Additional headers (optional)
     * @return bool
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $headers = []
    ): bool {
        try {
            $payload = [
                'from' => [
                    'address' => $this->fromAddress,
                    'name' => $this->fromName,
                ],
                'to' => [
                    [
                        'email_address' => [
                            'address' => $to,
                            'name' => '',
                        ],
                    ],
                ],
                'subject' => $subject,
                'htmlbody' => $htmlBody,
            ];

            // Add text body if provided
            if ($textBody) {
                $payload['textbody'] = $textBody;
            }

            // Add bounce address to headers
            if ($this->bounceAddress) {
                $payload['bounce_address'] = $this->bounceAddress;
            }

            // Add custom headers if provided
            if (!empty($headers)) {
                $payload['headers'] = $headers;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Zoho-encryptionapikey ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('ZeptoMail email sent successfully', [
                    'to' => $to,
                    'subject' => $subject,
                ]);
                return true;
            }

            Log::error('ZeptoMail API error', [
                'status' => $response->status(),
                'response' => $response->json(),
                'to' => $to,
                'subject' => $subject,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ZeptoMail service exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $to,
                'subject' => $subject,
            ]);

            return false;
        }
    }

    /**
     * Send email using a view template
     *
     * @param string $to Email address of recipient
     * @param string $subject Email subject
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return bool
     */
    public function sendView(
        string $to,
        string $subject,
        string $view,
        array $data = []
    ): bool {
        try {
            $htmlBody = view($view, $data)->render();
            return $this->sendEmail($to, $subject, $htmlBody);
        } catch (\Exception $e) {
            Log::error('ZeptoMail sendView exception', [
                'error' => $e->getMessage(),
                'view' => $view,
                'to' => $to,
            ]);
            return false;
        }
    }
}
