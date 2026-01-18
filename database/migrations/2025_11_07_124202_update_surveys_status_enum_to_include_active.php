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
            // For SQLite, just update existing records
            DB::table('surveys')
                ->where('status', 'Published')
                ->update(['status' => 'Active']);
            return;
        }
        
        // Step 1: First add 'Active' to the enum (keeping 'Published' temporarily)
        DB::statement("ALTER TABLE surveys MODIFY COLUMN status ENUM('Draft', 'Published', 'Active', 'Archived') DEFAULT 'Draft'");
        
        // Step 2: Update any existing 'Published' records to 'Active'
        DB::table('surveys')
            ->where('status', 'Published')
            ->update(['status' => 'Active']);
        
        // Step 3: Now remove 'Published' from the enum, keeping only 'Active'
        DB::statement("ALTER TABLE surveys MODIFY COLUMN status ENUM('Draft', 'Active', 'Archived') DEFAULT 'Draft'");
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
        
        // Revert back to 'Published' if needed
        DB::statement("ALTER TABLE surveys MODIFY COLUMN status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft'");
    }
};
