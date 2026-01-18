<?php

namespace App\Services;

use App\Models\Meeting;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Build month array for the given organization IDs (empty => empty month only)
     *
     * @param array<int> $organizationIds
     * @param callable|null $queryModifier Optional closure to modify the query (for filters)
     */
    public static function buildMonth(array $organizationIds, Carbon $target, ?callable $queryModifier = null): array
    {
        $start = (clone $target)->startOfMonth();
        $end = (clone $target)->endOfMonth();

        $days = [];
        for ($d = (clone $start); $d->lte($end); $d->addDay()) {
            $days[$d->toDateString()] = [
                'date' => $d->toDateString(),
                'weekday' => $d->dayOfWeekIso,
                'count' => 0,
                'meetings' => [],
            ];
        }

        if (!empty($organizationIds)) {
            $query = Meeting::whereIn('organization_id', $organizationIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            
            // Apply query modifier if provided (for filters)
            if ($queryModifier) {
                $query = $queryModifier($query);
            }
            
            $meetings = $query->orderBy('date')
                ->orderBy('time')
                ->get(['id', 'meeting_title', 'status', 'date', 'time', 'duration', 'meeting_type']);

            foreach ($meetings as $m) {
                $key = Carbon::parse($m->date)->toDateString();
                if (isset($days[$key])) {
                    $days[$key]['count'] += 1;
                    $days[$key]['meetings'][] = [
                        'id' => $m->id,
                        'meeting_title' => $m->meeting_title,
                        'status' => $m->status,
                        'time' => $m->time,
                        'duration' => (int) $m->duration,
                        'meeting_type' => $m->meeting_type,
                    ];
                }
            }
        }

        // Only return days that actually have meetings in the current month
        $nonEmptyDays = array_values(array_filter($days, static function (array $dayRow): bool {
            return ($dayRow['count'] ?? 0) > 0;
        }));

        return [
            'year' => (int) $target->year,
            'month' => (int) $target->month,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'days' => $nonEmptyDays,
        ];
    }

    /**
     * Build a 7-day week grid (Mon–Sun) for the given organization IDs.
     * Returns only days that actually have meetings, consistent with month API.
     *
     * @param array<int> $organizationIds
     * @param callable|null $queryModifier Optional closure to modify the query (for filters)
     */
    public static function buildWeek(array $organizationIds, Carbon $target, ?callable $queryModifier = null): array
    {
        $start = (clone $target)->startOfWeek(Carbon::MONDAY);
        $end = (clone $target)->endOfWeek(Carbon::SUNDAY);

        $days = [];
        for ($d = (clone $start); $d->lte($end); $d->addDay()) {
            $days[$d->toDateString()] = [
                'date' => $d->toDateString(),
                'weekday' => $d->dayOfWeekIso,
                'count' => 0,
                'meetings' => [],
            ];
        }

        if (!empty($organizationIds)) {
            $query = Meeting::whereIn('organization_id', $organizationIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            
            // Apply query modifier if provided (for filters)
            if ($queryModifier) {
                $query = $queryModifier($query);
            }
            
            $meetings = $query->orderBy('date')
                ->orderBy('time')
                ->get(['id', 'meeting_title', 'status', 'date', 'time', 'duration', 'meeting_type']);

            foreach ($meetings as $m) {
                $key = Carbon::parse($m->date)->toDateString();
                if (isset($days[$key])) {
                    $days[$key]['count'] += 1;
                    $days[$key]['meetings'][] = [
                        'id' => $m->id,
                        'meeting_title' => $m->meeting_title,
                        'status' => $m->status,
                        'time' => $m->time,
                        'duration' => (int) $m->duration,
                        'meeting_type' => $m->meeting_type,
                    ];
                }
            }
        }

        $nonEmptyDays = array_values(array_filter($days, static function (array $dayRow): bool {
            return ($dayRow['count'] ?? 0) > 0;
        }));

        return [
            'year' => (int) $start->year,
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'days' => $nonEmptyDays,
        ];
    }

    /**
     * Build day array with meetings for a specific date.
     * Returns meetings ordered by time.
     *
     * @param array<int> $organizationIds
     * @param callable|null $queryModifier Optional closure to modify the query (for filters)
     */
    public static function buildDay(array $organizationIds, Carbon $target, ?callable $queryModifier = null): array
    {
        $dateStr = $target->toDateString();

        $meetings = [];
        if (!empty($organizationIds)) {
            $query = Meeting::whereIn('organization_id', $organizationIds)
                ->whereDate('date', $dateStr);
            
            // Apply query modifier if provided (for filters)
            if ($queryModifier) {
                $query = $queryModifier($query);
            }
            
            $meetings = $query->orderBy('time')
                ->get(['id', 'meeting_title', 'status', 'date', 'time', 'duration', 'meeting_type', 'custom_location', 'agenda_notes']);
        }

        return [
            'date' => $dateStr,
            'weekday' => $target->dayOfWeekIso,
            'year' => (int) $target->year,
            'month' => (int) $target->month,
            'day' => (int) $target->day,
            'count' => $meetings->count(),
            'meetings' => $meetings->map(function ($m) {
                return [
                    'id' => $m->id,
                    'meeting_title' => $m->meeting_title,
                    'status' => $m->status,
                    'time' => $m->time,
                    'duration' => (int) $m->duration,
                    'meeting_type' => $m->meeting_type,
                    'custom_location' => $m->custom_location,
                    'agenda_notes' => $m->agenda_notes,
                ];
            })->values()->all(),
        ];
    }
}


