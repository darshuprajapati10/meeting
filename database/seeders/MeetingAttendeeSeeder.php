<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Meeting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingAttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meetings = Meeting::all();
        $contacts = Contact::all();
        
        if ($meetings->isEmpty() || $contacts->isEmpty()) {
            $this->command->warn('No meetings or contacts found. Please run MeetingSeeder and ContactSeeder first.');
            return;
        }

        // Attach contacts to meetings (20 total attachments)
        $attachmentCount = 0;
        $usedCombinations = [];
        
        while ($attachmentCount < 20) {
            $meeting = $meetings->random();
            $contact = $contacts->random();
            
            // Ensure contact belongs to same organization as meeting
            if ($contact->organization_id !== $meeting->organization_id) {
                continue;
            }
            
            // Create unique combination key
            $key = $meeting->id . '_' . $contact->id;
            
            // Skip if this combination already exists
            if (in_array($key, $usedCombinations)) {
                continue;
            }
            
            // Check if already attached
            if (!$meeting->attendees()->where('contacts.id', $contact->id)->exists()) {
                $meeting->attendees()->attach($contact->id);
                $usedCombinations[] = $key;
                $attachmentCount++;
            }
        }

        $this->command->info('Created 20 meeting attendee attachments!');
    }
}


