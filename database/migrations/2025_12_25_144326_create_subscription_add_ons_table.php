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
        Schema::create('subscription_add_ons', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // 'extra_users', 'extra_storage', 'api_access'
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->integer('price_monthly');          // in paise
            $table->string('unit')->nullable();        // 'user', 'gb', null for flat
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_add_ons');
    }
};
