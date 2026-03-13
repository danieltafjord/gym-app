<?php

use App\Enums\PlanType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('access_duration_value')->nullable()->after('plan_type');
            $table->string('access_duration_unit', 20)->nullable()->after('access_duration_value');
            $table->string('activation_mode', 20)->default('purchase')->after('access_duration_unit');
            $table->boolean('requires_account')->default(false)->after('activation_mode');
            $table->string('access_code_strategy', 30)->default('rotate_on_check_in')->after('requires_account');
            $table->unsignedInteger('max_entries')->nullable()->after('access_code_strategy');
        });

        DB::table('membership_plans')
            ->where('plan_type', PlanType::OneTime->value)
            ->orderBy('id')
            ->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $accessDuration = match ($plan->billing_period) {
                        'weekly' => ['value' => 1, 'unit' => 'week'],
                        'quarterly' => ['value' => 3, 'unit' => 'month'],
                        'yearly' => ['value' => 1, 'unit' => 'year'],
                        default => ['value' => 1, 'unit' => 'month'],
                    };

                    DB::table('membership_plans')
                        ->where('id', $plan->id)
                        ->update([
                            'access_duration_value' => $accessDuration['value'],
                            'access_duration_unit' => $accessDuration['unit'],
                            'activation_mode' => 'purchase',
                            'requires_account' => false,
                            'access_code_strategy' => 'rotate_on_check_in',
                        ]);
                }
            });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->change();
            $table->dateTime('ends_at')->nullable()->change();
            $table->timestamp('activated_at')->nullable()->after('ends_at');
            $table->unsignedInteger('entries_used')->default(0)->after('activated_at');
            $table->index(['status', 'ends_at']);
        });

        DB::table('memberships')
            ->orderBy('id')
            ->chunkById(100, function ($memberships): void {
                foreach ($memberships as $membership) {
                    $startsAt = $membership->starts_at
                        ? CarbonImmutable::parse((string) $membership->starts_at)->startOfDay()
                        : null;
                    $endsAt = $membership->ends_at
                        ? CarbonImmutable::parse((string) $membership->ends_at)->endOfDay()
                        : null;

                    DB::table('memberships')
                        ->where('id', $membership->id)
                        ->update([
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                            'activated_at' => $startsAt,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['status', 'ends_at']);
            $table->dropColumn(['activated_at', 'entries_used']);
            $table->date('starts_at')->nullable(false)->change();
            $table->date('ends_at')->nullable()->change();
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn([
                'access_duration_value',
                'access_duration_unit',
                'activation_mode',
                'requires_account',
                'access_code_strategy',
                'max_entries',
            ]);
        });
    }
};
