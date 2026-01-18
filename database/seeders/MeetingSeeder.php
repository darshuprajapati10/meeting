<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();
        $users = User::all();
        $surveys = Survey::all();
        
        if ($organizations->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No organizations or users found. Please run OrganizationSeeder and UserSeeder first.');
            return;
        }

        $statuses = ['Created', 'Scheduled', 'Completed', 'Cancelled'];
        $meetingTypes = ['Video Call', 'In-Person Meeting', 'Phone Call', 'Online Meeting'];
        $meetingTitles = [
            'Project Kickoff Meeting',
            'Team Standup',
            'Client Review',
            'Strategy Planning',
            'Quarterly Review',
            'Product Demo',
            'Training Session',
            'One-on-One',
            'Board Meeting',
            'Sales Presentation',
        ];
        $durations = [15, 30, 45, 60, 90, 120];

        // Get first organization (Dhaval's Organization) or create one
        $targetOrganization = Organization::first();
        if (!$targetOrganization) {
            $targetOrganization = Organization::create([
                'name' => 'Dhaval\'s Organization',
                'slug' => 'dhavals-organization',
                'description' => 'Main organization',
                'status' => 'active',
            ]);
        }
        
        // Get first user or use random
        $targetUser = $users->first();
        
        // Create 100 meetings for the target organization
        for ($i = 1; $i <= 100; $i++) {
            $date = Carbon::now()->addDays(fake()->numberBetween(-30, 60));
            $time = fake()->time('H:i');
            
            $meeting = Meeting::create([
                'organization_id' => $targetOrganization->id,
                'meeting_title' => fake()->randomElement($meetingTitles) . ' ' . $i,
                'status' => fake()->randomElement($statuses),
                'date' => $date,
                'time' => $time,
                'duration' => fake()->randomElement($durations),
                'meeting_type' => fake()->randomElement($meetingTypes),
                'custom_location' => fake()->boolean(40) ? fake()->address() : null,
                'survey_id' => $surveys->isNotEmpty() && fake()->boolean(30) ? $surveys->random()->id : null,
                'agenda_notes' => fake()->boolean(60) ? fake()->paragraph(3) : null,
                'created_by' => $targetUser->id,
            ]);
        }

        $this->command->info('Created 100 meeting records!');
    }
}


