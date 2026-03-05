<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->unsignedInteger('yearly_price_cents')->nullable()->after('price_cents');
            $table->string('stripe_yearly_price_id')->nullable()->after('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn(['yearly_price_cents', 'stripe_yearly_price_id']);
        });
    }
};
