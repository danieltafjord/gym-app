<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('cancelled_at');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_subscription_id');
            $table->string('stripe_status', 30)->nullable()->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_payment_intent_id', 'stripe_status']);
        });
    }
};
