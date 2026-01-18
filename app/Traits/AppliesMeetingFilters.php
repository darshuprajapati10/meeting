<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait AppliesMeetingFilters
{
    /**
     * Apply filters to a meeting query
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function applyMeetingFilters(Builder $query, array $filters): Builder
    {
        // Meeting Type filter
        if (!empty($filters['meeting_type'])) {
            $query->where('meeting_type', $filters['meeting_type']);
        }

        // Attendees filter
        if (!empty($filters['attendees'])) {
            // Use subquery to count attendees for range filtering
            $attendeeCountSubquery = '(SELECT COUNT(*) FROM meeting_attendees WHERE meeting_attendees.meeting_id = meetings.id)';
            
            switch ($filters['attendees']) {
                case '1-on-1':
                    $query->has('attendees', '=', 1);
                    break;
                case 'small':
                    // 2-5 attendees
                    $query->whereRaw("{$attendeeCountSubquery} >= 2")
                          ->whereRaw("{$attendeeCountSubquery} <= 5");
                    break;
                case 'medium':
                    // 6-15 attendees
                    $query->whereRaw("{$attendeeCountSubquery} >= 6")
                          ->whereRaw("{$attendeeCountSubquery} <= 15");
                    break;
                case 'large':
                    // 16+ attendees
                    $query->whereRaw("{$attendeeCountSubquery} >= 16");
                    break;
            }
        }

        // Duration filter
        if (!empty($filters['duration'])) {
            $duration = (int) $filters['duration'];
            if ($duration === 120) {
                // 2+ hours
                $query->where('duration', '>=', 120);
            } else {
                $query->where('duration', '=', $duration);
            }
        }

        // Status filter
        if (!empty($filters['status'])) {
            // Map frontend status to database status
            // Database: 'Created', 'Scheduled', 'Completed', 'Cancelled'
            // Frontend: 'upcoming', 'completed', 'cancelled'
            $statusMap = [
                'upcoming' => ['Created', 'Scheduled'], // Both are upcoming
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
            
            $dbStatus = $statusMap[$filters['status']] ?? $filters['status'];
            if (is_array($dbStatus)) {
                $query->whereIn('status', $dbStatus);
            } else {
                $query->where('status', $dbStatus);
            }
        }

        return $query;
    }
}

