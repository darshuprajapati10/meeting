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
        Schema::create('survey_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_step_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', [
                'Short Answer',
                'Paragraph',
                'Email',
                'Multiple Choice',
                'Checkboxes',
                'Dropdown',
                'Rating Scale',
                'Date',
                'Number',
                'File Upload'
            ])->default('Short Answer');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_fields');
    }
};
