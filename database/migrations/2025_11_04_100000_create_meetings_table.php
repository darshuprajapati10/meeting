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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('meeting_title');
            $table->enum('status', ['Created', 'Scheduled', 'Completed', 'Cancelled'])->default('Created');
            $table->date('date');
            $table->time('time');
            $table->integer('duration')->default(30); // in minutes
            $table->enum('meeting_type', ['Video Call', 'In-Person Meeting', 'Phone Call', 'Online Meeting'])->default('Video Call');
            $table->string('custom_location')->nullable();
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->nullOnDelete();
            $table->text('agenda_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};

