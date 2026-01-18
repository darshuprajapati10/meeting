<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\MeetingNotification;
use Illuminate\Database\Seeder;

class MeetingNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meetings = Meeting::all();
        
        if ($meetings->isEmpty()) {
            $this->command->warn('No meetings found. Please run MeetingSeeder first.');
            return;
        }

        $units = ['minutes', 'hours', 'days'];
        $triggers = ['before', 'after'];
        $minutesOptions = [5, 10, 15, 30, 60, 120, 1440]; // 5 min to 1 day

        // Create 20 meeting notifications (distributed across meetings)
        $notificationCount = 0;
        foreach ($meetings as $meeting) {
            // Each meeting gets 1-2 notifications
            $numNotifications = fake()->numberBetween(1, 2);
            
            for ($j = 1; $j <= $numNotifications && $notificationCount < 20; $j++) {
                MeetingNotification::create([
                    'meeting_id' => $meeting->id,
                    'minutes' => fake()->randomElement($minutesOptions),
                    'unit' => fake()->randomElement($units),
                    'trigger' => fake()->randomElement($triggers),
                    'is_enabled' => fake()->boolean(80), // 80% are enabled
                ]);
                $notificationCount++;
            }
            
            if ($notificationCount >= 20) {
                break;
            }
        }

        $this->command->info('Created 20 meeting notification records!');
    }
}


