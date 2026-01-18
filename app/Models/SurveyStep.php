<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id', 'step', 'tagline', 'order'
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function surveyFields(): HasMany
    {
        return $this->hasMany(SurveyField::class)->orderBy('order');
    }
}
