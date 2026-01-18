<?php

namespace App\Console\Commands;

use App\Services\ZeptoMailService;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Console\Command;

class TestZeptoMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:zeptomail {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ZeptoMail email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter email address to send test email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Testing ZeptoMail email sending...');
        $this->newLine();

        // Check configuration
        $this->info('Checking configuration...');
        $apiKey = config('mail.mailers.zeptomail.api_key') ?? env('ZEPTO_MAIL_API_KEY');
        $fromAddress = config('mail.mailers.zeptomail.from_address') ?? env('ZEPTO_MAIL_FROM_ADDRESS');
        $fromName = config('mail.mailers.zeptomail.from_name') ?? env('ZEPTO_MAIL_FROM_NAME');

        if (!$apiKey) {
            $this->error('ZEPTO_MAIL_API_KEY is not set in .env file!');
            return 1;
        }

        $this->info("✓ API Key: " . substr($apiKey, 0, 20) . '...');
        $this->info("✓ From Address: {$fromAddress}");
        $this->info("✓ From Name: {$fromName}");
        $this->newLine();

        // Test 1: Simple email via ZeptoMailService
        $this->info('Test 1: Sending simple test email...');
        try {
            $zeptomailService = new ZeptoMailService();
            $htmlBody = '<h1>Test Email from ZeptoMail</h1><p>This is a test email to verify ZeptoMail integration is working correctly.</p>';
            
            $result = $zeptomailService->sendEmail(
                $email,
                'ZeptoMail Test Email',
                $htmlBody
            );

            if ($result) {
                $this->info('✓ Simple email sent successfully!');
            } else {
                $this->error('✗ Failed to send simple email. Check logs for details.');
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
        }

        $this->newLine();

        // Test 2: Email verification email
        $this->info('Test 2: Sending email verification email...');
        try {
            // Create or get a test user
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->warn('User not found. Creating test user...');
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $email,
                    'password' => bcrypt('password'),
                ]);
            }

            // Generate verification token
            $token = $user->generateEmailVerificationToken();
            $appUrl = config('app.url', 'http://localhost');
            $verificationUrl = $appUrl . '/api/email/verify/' . $token;

            $emailVerification = new EmailVerification($user, $verificationUrl);
            $result = $emailVerification->send();

            if ($result) {
                $this->info('✓ Verification email sent successfully!');
                $this->info("  Verification URL: {$verificationUrl}");
            } else {
                $this->error('✗ Failed to send verification email. Check logs for details.');
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error('  Stack trace: ' . $e->getTraceAsString());
        }

        $this->newLine();
        $this->info('Testing complete! Check your email inbox: ' . $email);
        $this->info('Also check storage/logs/laravel.log for detailed logs.');

        return 0;
    }
}
