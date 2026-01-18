<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');

            // Contact Information
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();

            // Professional Information
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();

            // Referrer (self reference)
            $table->foreignId('referrer_id')->nullable()->constrained('contacts')->nullOnDelete();

            // Contact Groups as JSON array of strings
            $table->json('groups')->nullable();

            // Additional
            $table->string('address')->nullable();
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};


