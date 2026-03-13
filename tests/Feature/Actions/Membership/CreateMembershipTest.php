<?php

use App\Actions\Membership\CreateMembership;
use App\Enums\AccessCodeStrategy;
use App\Enums\AccessDurationUnit;
use App\Enums\ActivationMode;
use App\Enums\PlanType;
use App\Models\MembershipPlan;
use Carbon\CarbonImmutable;

it('creates purchase-activated one-time passes with exact 24-hour expiry windows', function () {
    CarbonImmutable::setTestNow('2026-03-05 10:00:00');

    $plan = MembershipPlan::factory()->create([
        'plan_type' => PlanType::OneTime,
        'access_duration_value' => 24,
        'access_duration_unit' => AccessDurationUnit::Hour,
        'activation_mode' => ActivationMode::Purchase,
        'access_code_strategy' => AccessCodeStrategy::Static,
    ]);

    $membership = app(CreateMembership::class)->handle(
        user: null,
        plan: $plan,
        email: 'pass@example.com',
        customerName: 'Pass Customer',
    );

    expect($membership->activated_at?->toDateTimeString())->toBe('2026-03-05 10:00:00')
        ->and($membership->starts_at?->toDateTimeString())->toBe('2026-03-05 10:00:00')
        ->and($membership->ends_at?->toDateTimeString())->toBe('2026-03-06 10:00:00');

    CarbonImmutable::setTestNow();
});

it('creates first-check-in passes without starting the access window immediately', function () {
    $plan = MembershipPlan::factory()->create([
        'plan_type' => PlanType::OneTime,
        'access_duration_value' => 24,
        'access_duration_unit' => AccessDurationUnit::Hour,
        'activation_mode' => ActivationMode::FirstCheckIn,
        'access_code_strategy' => AccessCodeStrategy::Static,
    ]);

    $membership = app(CreateMembership::class)->handle(
        user: null,
        plan: $plan,
        email: 'later@example.com',
        customerName: 'Later Customer',
    );

    expect($membership->activated_at)->toBeNull()
        ->and($membership->starts_at)->toBeNull()
        ->and($membership->ends_at)->toBeNull();
});
