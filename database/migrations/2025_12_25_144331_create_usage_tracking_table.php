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
        Schema::create('usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('metric');                  // 'meetings', 'contacts', 'surveys', 'responses', 'storage'
            $table->integer('count')->default(0);
            $table->date('period_start');              // First day of billing period
            $table->date('period_end');                // Last day of billing period
            $table->timestamps();

            $table->unique(['organization_id', 'metric', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_tracking');
    }
};
