<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'meeting_title',
        'status',
        'date',
        'time',
        'duration',
        'meeting_type',
        'custom_location',
        'survey_id',
        'agenda_notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'duration' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'meeting_attendees')
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(MeetingNotification::class);
    }

    public function surveySubmissions(): HasMany
    {
        return $this->hasMany(SurveySubmission::class);
    }

    /**
     * Check if a user has submitted survey for this meeting
     * Checks survey_submissions and survey_responses tables
     */
    public function hasUserSubmittedSurvey($userId): bool
    {
        if (!$this->survey_id) {
            return false;
        }
        
        try {
            // First check survey_submissions table (primary source)
            $hasSubmission = SurveySubmission::where('user_id', $userId)
                ->where('meeting_id', $this->id)
                ->where('survey_id', $this->survey_id)
                ->exists();
            
            if ($hasSubmission) {
                return true;
            }
            
            // Fallback: Check survey_responses table (backward compatibility)
            $hasResponse = \App\Models\SurveyResponse::where('user_id', $userId)
                ->where('meeting_id', $this->id)
                ->where('survey_id', $this->survey_id)
                ->exists();
            
            if ($hasResponse) {
                return true;
            }
        } catch (\Exception $e) {
            // Log error but don't fail - return false if query fails
            \Log::warning('Error checking survey submission', [
                'user_id' => $userId,
                'meeting_id' => $this->id,
                'survey_id' => $this->survey_id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return false;
    }
}

