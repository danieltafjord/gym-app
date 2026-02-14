<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('sort_order');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
            $table->string('plan_type', 20)->default('recurring')->after('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn(['stripe_product_id', 'stripe_price_id', 'plan_type']);
        });
    }
};
