<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'survey_name', 'description', 'status', 'created_by'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function surveySteps(): HasMany
    {
        return $this->hasMany(SurveyStep::class)->orderBy('order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function surveySubmissions(): HasMany
    {
        return $this->hasMany(SurveySubmission::class);
    }

    /**
     * Get total response count for this survey
     * Counts unique submissions per meeting from survey_submissions table
     * Also counts responses without meeting_id from survey_responses table (for backward compatibility)
     */
    public function getResponseCount(): int
    {
        // Count unique submissions per meeting
        $submissionCount = $this->surveySubmissions()
            ->distinct('meeting_id')
            ->whereNotNull('meeting_id')
            ->count('meeting_id');
        
        // Count responses without meeting_id (for backward compatibility)
        $responseCountWithoutMeeting = $this->surveyResponses()
            ->whereNull('meeting_id')
            ->distinct('user_id')
            ->count('user_id');
        
        return $submissionCount + $responseCountWithoutMeeting;
    }
}
