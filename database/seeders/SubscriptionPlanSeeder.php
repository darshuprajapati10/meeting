<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionAddOn;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $freePlan = [
            'name' => 'free',
            'display_name' => 'Free',
            'description' => 'Perfect for individuals getting started',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'limits' => [
                'meetings_per_month' => 20,
                'attendees_per_meeting' => 10,
                'contacts' => 50,
                'active_surveys' => 1,
                'survey_responses_per_month' => 50,
                'users' => 1,
                'storage_mb' => 100,
                'contact_favorites' => 5,
                'contact_groups' => 1,
                'survey_steps' => 2,
                'questions_per_survey' => 10,
                'data_retention_days' => 365,
            ],
            'features' => [
                'calendar_views' => ['month'],
                'meeting_types' => ['video_call', 'phone_call', 'in_person', 'online'],
                'reminder_options' => ['24h', '1h'],
                'csv_import' => false,
                'csv_export' => false,
                'survey_analytics' => 'basic',
                'survey_question_types' => ['text', 'paragraph', 'number', 'date', 'radio', 'checkbox'],
                'skip_logic' => false,
                'branching' => false,
                'remove_branding' => false,
                'api_access' => false,
                'two_factor_auth' => false,
                'email_reminders' => false,
                'advanced_filters' => false,
                'support_priority' => 'standard',
                'support_response_hours' => 48,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ];

        $proPlan = [
            'name' => 'pro',
            'display_name' => 'Pro',
            'description' => 'For growing teams and businesses',
            'price_monthly' => 99900,  // ₹999 in paise
            'price_yearly' => 999900,  // ₹9,999 in paise
            'limits' => [
                'meetings_per_month' => -1,  // -1 = unlimited
                'attendees_per_meeting' => -1,
                'contacts' => -1,
                'active_surveys' => -1,
                'survey_responses_per_month' => -1,
                'users' => 5,
                'storage_mb' => 10240,  // 10 GB
                'contact_favorites' => -1,
                'contact_groups' => -1,
                'survey_steps' => -1,
                'questions_per_survey' => -1,
                'data_retention_days' => -1,
            ],
            'features' => [
                'calendar_views' => ['month', 'week', 'day'],
                'meeting_types' => ['video_call', 'phone_call', 'in_person', 'online', 'custom'],
                'reminder_options' => ['5m', '15m', '30m', '1h', '24h', '1w', 'custom'],
                'csv_import' => true,
                'csv_export' => true,
                'survey_analytics' => 'advanced',
                'survey_question_types' => ['text', 'paragraph', 'number', 'date', 'radio', 'checkbox', 'dropdown', 'rating', 'nps', 'file_upload'],
                'skip_logic' => true,
                'branching' => true,
                'remove_branding' => true,
                'api_access' => false,  // Add-on
                'two_factor_auth' => true,
                'email_reminders' => true,
                'advanced_filters' => true,
                'support_priority' => 'priority',
                'support_response_hours' => 24,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ];

        SubscriptionPlan::updateOrCreate(
            ['name' => 'free'],
            $freePlan
        );

        SubscriptionPlan::updateOrCreate(
            ['name' => 'pro'],
            $proPlan
        );

        // Create Additional Users add-on
        SubscriptionAddOn::updateOrCreate(
            ['name' => 'extra_users'],
            [
                'name' => 'extra_users',
                'display_name' => 'Additional Users',
                'description' => 'Add more team members to your Pro plan',
                'price_monthly' => 14900,  // ₹149 in paise
                'unit' => 'user',
                'is_active' => true,
            ]
        );
    }
}
