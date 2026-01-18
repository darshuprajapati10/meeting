<?php

namespace App\Mail;

use App\Models\User;
use App\Services\ZeptoMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class EmailVerification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Send the email using ZeptoMail service
     */
    public function send(): bool
    {
        $zeptomailService = new ZeptoMailService();

        return $zeptomailService->sendView(
            $this->user->email,
            'Verify Your Email Address - Ongoing Forge',
            'emails.email-verification',
            [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]
        );
    }
}
