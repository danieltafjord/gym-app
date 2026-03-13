<?php

use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows plans index with price_formatted', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'price_cents' => 4999,
        'yearly_price_cents' => 49990,
    ]);

    $this->actingAs($user)
        ->get(route('team.plans.index', $team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/plans/index')
            ->has('plans.data', 1)
            ->where('plans.data.0.price_formatted', '49.99')
            ->where('plans.data.0.yearly_price_formatted', '499.90')
            ->where('publicPlansUrl', route('public.gym', ['team' => $team->slug, 'gym' => $gym->slug]))
        );
});

it('shares team currency on the create plan page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'default_currency' => 'NOK',
    ]);

    $this->actingAs($user)
        ->get(route('team.plans.create', $team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/plans/create')
            ->where('team.default_currency', 'NOK')
        );
});

it('stores a plan when features are provided as comma-separated text', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('team.plans.store', $team), [
            'name' => 'Premium',
            'description' => 'Best plan',
            'price_cents' => 4999,
            'yearly_price_cents' => 49990,
            'billing_period' => 'monthly',
            'features' => ' Tilgang til Gym, Åpent 24/7, Alle dager, Ingen bindingstid',
        ]);

    $response->assertRedirect(route('team.plans.index', $team));

    $plan = MembershipPlan::query()->where('team_id', $team->id)->first();

    expect($plan)->not->toBeNull()
        ->and($plan?->features)->toBe([
            'Tilgang til Gym',
            'Åpent 24/7',
            'Alle dager',
            'Ingen bindingstid',
        ])
        ->and($plan?->yearly_price_cents)->toBe(49990);
});

it('stores one-time access settings for day-pass products', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('team.plans.store', $team), [
            'name' => '24-Hour Pass',
            'description' => 'Single-day access',
            'plan_type' => 'one_time',
            'price_cents' => 1999,
            'billing_period' => 'monthly',
            'access_duration_value' => 24,
            'access_duration_unit' => 'hour',
            'activation_mode' => 'purchase',
            'requires_account' => true,
            'access_code_strategy' => 'static',
            'features' => 'Gym floor, Locker room',
        ]);

    $response->assertRedirect(route('team.plans.index', $team));

    $plan = MembershipPlan::query()
        ->where('team_id', $team->id)
        ->where('name', '24-Hour Pass')
        ->first();

    expect($plan)->not->toBeNull()
        ->and($plan?->plan_type)->toBe(\App\Enums\PlanType::OneTime)
        ->and($plan?->access_duration_value)->toBe(24)
        ->and($plan?->access_duration_unit)->toBe(\App\Enums\AccessDurationUnit::Hour)
        ->and($plan?->requires_account)->toBeTrue()
        ->and($plan?->access_code_strategy)->toBe(\App\Enums\AccessCodeStrategy::Static);
});

it('updates a plan when features are provided as comma-separated text', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'features' => ['Old feature'],
    ]);

    $response = $this->actingAs($user)
        ->patch(route('team.plans.update', [$team, $plan]), [
            'features' => ' Gruppetime, Fri vekter, Kaffe ',
        ]);

    $response->assertSessionHasNoErrors();

    expect($plan->fresh()?->features)->toBe([
        'Gruppetime',
        'Fri vekter',
        'Kaffe',
    ]);
});

it('updates recurring pricing when the edit form submits display prices', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'billing_period' => \App\Enums\BillingPeriod::Monthly,
        'price_cents' => 4999,
        'yearly_price_cents' => 49990,
    ]);

    $this->actingAs($user)
        ->from(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->patch(route('team.plans.update', [$team, $plan]), [
            'name' => $plan->name,
            'description' => $plan->description,
            'plan_type' => 'recurring',
            'price' => '59.99',
            'yearly_price' => '599.90',
            'billing_period' => 'monthly',
            'access_code_strategy' => 'rotate_on_check_in',
            'requires_account' => false,
            'features' => 'Gym access',
        ])
        ->assertRedirect(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->assertSessionHasNoErrors();

    $plan->refresh();

    expect($plan->price_cents)->toBe(5999)
        ->and($plan->yearly_price_cents)->toBe(59990);
});

it('updates one-time access settings for an existing plan', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
    ]);

    $this->actingAs($user)
        ->from(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->patch(route('team.plans.update', [$team, $plan]), [
            'name' => 'Day Pass',
            'description' => '24-hour access',
            'plan_type' => 'one_time',
            'price_cents' => 1999,
            'billing_period' => 'monthly',
            'access_duration_value' => 24,
            'access_duration_unit' => 'hour',
            'activation_mode' => 'first_check_in',
            'requires_account' => true,
            'access_code_strategy' => 'static',
            'max_entries' => 3,
            'features' => 'Gym floor, Showers',
        ])
        ->assertRedirect(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->assertSessionHasNoErrors();

    $plan->refresh();

    expect($plan->name)->toBe('Day Pass')
        ->and($plan->plan_type)->toBe(\App\Enums\PlanType::OneTime)
        ->and($plan->price_cents)->toBe(1999)
        ->and($plan->access_duration_value)->toBe(24)
        ->and($plan->access_duration_unit)->toBe(\App\Enums\AccessDurationUnit::Hour)
        ->and($plan->activation_mode)->toBe(\App\Enums\ActivationMode::FirstCheckIn)
        ->and($plan->requires_account)->toBeTrue()
        ->and($plan->access_code_strategy)->toBe(\App\Enums\AccessCodeStrategy::Static)
        ->and($plan->max_entries)->toBe(3)
        ->and($plan->features)->toBe(['Gym floor', 'Showers']);
});

it('stores a plan without syncing to stripe when stripe key is missing', function () {
    config()->set('stripe.secret', null);
    config()->set('stripe.dev_mode', false);

    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'stripe_account_id' => 'acct_local_test',
        'stripe_onboarding_complete' => true,
    ]);

    $response = $this->actingAs($user)
        ->post(route('team.plans.store', $team), [
            'name' => 'Local Plan',
            'description' => 'Should not call Stripe',
            'price_cents' => 2500,
            'yearly_price_cents' => 25000,
            'billing_period' => 'monthly',
            'features' => 'Gym access',
        ]);

    $response->assertRedirect(route('team.plans.index', $team));

    $plan = MembershipPlan::query()
        ->where('team_id', $team->id)
        ->where('name', 'Local Plan')
        ->first();

    expect($plan)->not->toBeNull()
        ->and($plan?->stripe_product_id)->toBeNull()
        ->and($plan?->stripe_price_id)->toBeNull()
        ->and($plan?->stripe_yearly_price_id)->toBeNull();
});

it('deletes a plan', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->delete(route('team.plans.destroy', [
            'team' => $team,
            'plan' => $plan,
        ]))
        ->assertRedirect(route('team.plans.index', $team));

    $this->assertDatabaseMissing('membership_plans', [
        'id' => $plan->id,
    ]);
});

it('does not delete a plan that has memberships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);

    Membership::factory()->create([
        'user_id' => $member->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);

    $this->actingAs($owner)
        ->from(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->delete(route('team.plans.destroy', [
            'team' => $team,
            'plan' => $plan,
        ]))
        ->assertRedirect(route('team.plans.edit', ['team' => $team, 'plan' => $plan]))
        ->assertSessionHasErrors('delete_plan');

    $this->assertDatabaseHas('membership_plans', [
        'id' => $plan->id,
    ]);
});
