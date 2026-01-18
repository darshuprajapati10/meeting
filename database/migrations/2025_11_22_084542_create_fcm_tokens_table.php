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
        // Check if table already exists (idempotent migration)
        if (Schema::hasTable('fcm_tokens')) {
            // Table exists - check if token column needs to be fixed
            $columns = DB::select("DESCRIBE fcm_tokens");
            $tokenColumn = collect($columns)->firstWhere('Field', 'token');
            
            if ($tokenColumn && (stripos($tokenColumn->Type, 'text') !== false || stripos($tokenColumn->Type, 'blob') !== false)) {
                // Fix existing table: change TEXT to VARCHAR(500)
                try {
                    DB::statement("ALTER TABLE fcm_tokens DROP INDEX fcm_tokens_user_id_token_unique");
                } catch (\Exception $e) {
                    // Index may not exist, continue
                }
                DB::statement("ALTER TABLE fcm_tokens MODIFY COLUMN token VARCHAR(500) NOT NULL");
                try {
                    DB::statement("ALTER TABLE fcm_tokens ADD UNIQUE KEY fcm_tokens_user_id_token_unique (user_id, token)");
                } catch (\Exception $e) {
                    // Index may already exist, continue
                }
            }
            return; // Table already exists, skip creation
        }

        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 500); // FCM device token (FCM tokens are typically ~163 chars, using 500 for safety)
            $table->enum('platform', ['ios', 'android', 'web'])->default('android');
            $table->string('device_id')->nullable(); // Optional device identifier
            $table->timestamps();
            
            // Ensure one token per user per device
            $table->unique(['user_id', 'token']);
            $table->index('user_id');
            $table->index(['user_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
