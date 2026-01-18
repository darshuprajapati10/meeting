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
        Schema::table('organizations', function (Blueprint $table) {
            $table->enum('type', ['business', 'individual'])->default('business')->after('name');
            $table->enum('gst_status', ['registered', 'unregistered'])->nullable()->after('email');
            $table->string('gst_in')->nullable()->after('gst_status');
            $table->string('place_of_supply')->nullable()->after('gst_in');
            
            // Shipping address fields
            $table->text('shipping_address')->nullable()->after('address');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_zip')->nullable()->after('shipping_city');
            $table->string('shipping_phone')->nullable()->after('shipping_zip');
            
            // Billing address fields
            $table->text('billing_address')->nullable()->after('shipping_phone');
            $table->string('billing_city')->nullable()->after('billing_address');
            $table->string('billing_zip')->nullable()->after('billing_city');
            $table->string('billing_phone')->nullable()->after('billing_zip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'gst_status',
                'gst_in',
                'place_of_supply',
                'shipping_address',
                'shipping_city',
                'shipping_zip',
                'shipping_phone',
                'billing_address',
                'billing_city',
                'billing_zip',
                'billing_phone',
            ]);
        });
    }
};
