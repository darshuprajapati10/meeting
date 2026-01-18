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
        // SQLite doesn't support MODIFY COLUMN, skip for SQLite (used in tests)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // Add 'Pending' and 'Rescheduled' to the existing enum
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled', 'Pending', 'Rescheduled') DEFAULT 'Created'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support MODIFY COLUMN, skip for SQLite (used in tests)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // Revert back to original enum values
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('Created', 'Scheduled', 'Completed', 'Cancelled') DEFAULT 'Created'");
    }
};
