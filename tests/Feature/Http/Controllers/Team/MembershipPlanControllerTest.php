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
