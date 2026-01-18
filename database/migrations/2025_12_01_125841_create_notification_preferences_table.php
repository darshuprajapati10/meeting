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
        if (Schema::hasTable('notification_preferences')) {
            // Table already exists, skip creation
            return;
        }

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('push_notifications_enabled')->default(true);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('email_meeting_reminders')->default(true);
            $table->boolean('email_meeting_updates')->default(true);
            $table->boolean('email_meeting_cancellations')->default(true);
            // JSON field - default handled in application layer for cross-database compatibility
            $table->json('meeting_reminders')->nullable();
            $table->boolean('notification_sound')->default(true);
            $table->boolean('notification_badge')->default(true);
            $table->timestamps();
            
            // Create unique index on user_id (already enforced by unique() but explicit for clarity)
            $table->index('user_id');
        });

        // Set default value for meeting_reminders using raw SQL for PostgreSQL compatibility
        // For other databases, the application layer will handle the default
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE notification_preferences ALTER COLUMN meeting_reminders SET DEFAULT '[15]'::jsonb");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
