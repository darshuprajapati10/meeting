<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('survey_field_values')) {
            // Table already exists, just add index if it doesn't exist
            $indexes = DB::select("SHOW INDEXES FROM survey_field_values WHERE Key_name = 'sfv_survey_step_field_idx'");
            if (empty($indexes)) {
                Schema::table('survey_field_values', function (Blueprint $table) {
                    $table->index(['survey_id', 'survey_step_id', 'survey_field_id'], 'sfv_survey_step_field_idx');
                });
            }
            return;
        }
        
        Schema::create('survey_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('value')->nullable(); // Store the field value (can be string, JSON for arrays, etc.)
            $table->timestamps();
            
            // Index for faster lookups (using shorter name)
            $table->index(['survey_id', 'survey_step_id', 'survey_field_id'], 'sfv_survey_step_field_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_field_values');
    }
};
