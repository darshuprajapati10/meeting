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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // 'free', 'pro'
            $table->string('display_name');            // 'Free', 'Pro'
            $table->text('description')->nullable();
            $table->integer('price_monthly');          // 0, 999 (in paise: 0, 99900)
            $table->integer('price_yearly');           // 0, 9999 (in paise: 0, 999900)
            $table->json('limits');                    // JSON of all limits
            $table->json('features');                  // JSON of feature flags
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
