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
        // Update meetings table status enum to include 'Pending' and 'Rescheduled'
        // MySQL requires dropping and recreating the column to modify enum
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled', 'Pending', 'Rescheduled') DEFAULT 'Created'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        // Note: This will fail if any records have 'Pending' or 'Rescheduled' status
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled') DEFAULT 'Created'");
    }
};
