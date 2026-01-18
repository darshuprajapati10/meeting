<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class, // Add subscription plans first
            UserSeeder::class,
            // OrganizationSeeder::class, // Commented out as requested
            // ContactSeeder::class, // Commented out as requested
            // SurveySeeder::class, // Commented out as requested
            // SurveyStepSeeder::class, // Also commented out as it depends on surveys
            // SurveyFieldSeeder::class, // Also commented out as it depends on survey steps
            // MeetingSeeder::class, // Commented out as requested
            // MeetingNotificationSeeder::class, // Also commented out as it depends on meetings
            // ContactFavouriteSeeder::class, // Also commented out as it depends on contacts
            // MeetingAttendeeSeeder::class, // Also commented out as it depends on meetings
        ]);
    }
}
