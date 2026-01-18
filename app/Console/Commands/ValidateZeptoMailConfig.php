<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateZeptoMailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:validate-zeptomail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate ZeptoMail configuration in .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Validating ZeptoMail Configuration...');
        $this->newLine();

        $errors = [];
        $warnings = [];
        $success = [];

        // Required variables
        $requiredVars = [
            'ZEPTO_MAIL_API_KEY' => 'ZeptoMail API Key (required)',
            'ZEPTO_MAIL_FROM_ADDRESS' => 'From Email Address (required)',
            'ZEPTO_MAIL_FROM_NAME' => 'From Name (required)',
            'ZEPTO_MAIL_BOUNCE_ADDRESS' => 'Bounce Address (required)',
            'MAIL_MAILER' => 'Mail Driver (should be "zeptomail")',
            'APP_URL' => 'Application URL (required for verification links)',
        ];

        // Check each required variable
        foreach ($requiredVars as $var => $description) {
            $value = env($var);
            
            if (empty($value)) {
                $errors[] = "❌ {$var} - NOT SET ({$description})";
            } else {
                // Validate specific values
                switch ($var) {
                    case 'MAIL_MAILER':
                        if ($value !== 'zeptomail') {
                            $warnings[] = "⚠️  {$var} = '{$value}' (should be 'zeptomail')";
                        } else {
                            $success[] = "✅ {$var} = '{$value}'";
                        }
                        break;
                    
                    case 'ZEPTO_MAIL_FROM_ADDRESS':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "❌ {$var} - Invalid email format: '{$value}'";
                        } else {
                            $success[] = "✅ {$var} = '{$value}'";
                        }
                        break;
                    
                    case 'ZEPTO_MAIL_BOUNCE_ADDRESS':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $warnings[] = "⚠️  {$var} - Invalid email format: '{$value}'";
                        } else {
                            $success[] = "✅ {$var} = '{$value}'";
                        }
                        break;
                    
                    case 'ZEPTO_MAIL_API_KEY':
                        if (strlen($value) < 20) {
                            $warnings[] = "⚠️  {$var} - Seems too short (should be long API key)";
                        } else {
                            $success[] = "✅ {$var} = '" . substr($value, 0, 20) . '...' . "' (length: " . strlen($value) . ")";
                        }
                        break;
                    
                    case 'APP_URL':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $warnings[] = "⚠️  {$var} - Invalid URL format: '{$value}'";
                        } else {
                            $success[] = "✅ {$var} = '{$value}'";
                        }
                        break;
                    
                    default:
                        $success[] = "✅ {$var} = '{$value}'";
                        break;
                }
            }
        }

        // Display results
        if (!empty($success)) {
            $this->info('✅ Correctly Configured:');
            foreach ($success as $msg) {
                $this->line("  {$msg}");
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Warnings:');
            foreach ($warnings as $msg) {
                $this->line("  {$msg}");
            }
            $this->newLine();
        }

        if (!empty($errors)) {
            $this->error('❌ Errors (Must Fix):');
            foreach ($errors as $msg) {
                $this->line("  {$msg}");
            }
            $this->newLine();
        }

        // Summary
        $this->newLine();
        if (empty($errors) && empty($warnings)) {
            $this->info('🎉 All configuration is correct!');
            $this->info('You can now test email sending with: php artisan test:zeptomail your-email@example.com');
            return 0;
        } elseif (empty($errors)) {
            $this->warn('⚠️  Configuration has warnings but should work.');
            $this->info('You can test email sending with: php artisan test:zeptomail your-email@example.com');
            return 0;
        } else {
            $this->error('❌ Configuration has errors. Please fix them before testing.');
            $this->newLine();
            $this->info('Required .env variables:');
            $this->line('  MAIL_MAILER=zeptomail');
            $this->line('  ZEPTO_MAIL_API_KEY=your-api-key-here');
            $this->line('  ZEPTO_MAIL_FROM_ADDRESS=noreply@ongoingforge.com');
            $this->line('  ZEPTO_MAIL_FROM_NAME="Ongoing Forge"');
            $this->line('  ZEPTO_MAIL_BOUNCE_ADDRESS=bounce@ongoingforge.zeptomail.in');
            $this->line('  APP_URL=http://localhost:8000');
            return 1;
        }
    }
}
