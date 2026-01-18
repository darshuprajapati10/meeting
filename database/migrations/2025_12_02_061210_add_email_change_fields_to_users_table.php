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
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_change_new_email')->nullable()->after('email_verified_at');
            $table->string('email_change_token')->nullable()->after('email_change_new_email');
            $table->timestamp('email_change_token_expires_at')->nullable()->after('email_change_token');
            $table->timestamp('email_change_requested_at')->nullable()->after('email_change_token_expires_at');
            
            // Create indexes for faster lookups
            $table->index('email_change_token');
            $table->index('email_change_new_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_change_token']);
            $table->dropIndex(['email_change_new_email']);
            $table->dropColumn([
                'email_change_new_email',
                'email_change_token',
                'email_change_token_expires_at',
                'email_change_requested_at',
            ]);
        });
    }
};
