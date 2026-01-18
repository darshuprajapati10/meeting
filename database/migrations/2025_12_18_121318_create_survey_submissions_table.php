<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('meeting_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            
            // Prevent duplicate submissions - one submission per user per meeting per survey
            $table->unique(['user_id', 'meeting_id', 'survey_id'], 'unique_user_meeting_survey');
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('meeting_id');
            $table->index('survey_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_submissions');
    }
};
