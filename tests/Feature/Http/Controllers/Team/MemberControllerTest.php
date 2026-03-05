<?php

use App\Enums\MembershipStatus;
use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->user->id]);
    $this->plan = MembershipPlan::factory()->create(['team_id' => $this->team->id]);
});

// --- Index / Search / Filter ---

it('shows members index', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'John Doe',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', $this->team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/members/index')
            ->has('members.data', 1)
            ->where('members.data.0.customer_name', 'John Doe')
        );
});

it('searches members by name', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Alice Smith',
    ]);
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Bob Jones',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', ['team' => $this->team, 'search' => 'Alice']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('members.data', 1)
            ->where('members.data.0.customer_name', 'Alice Smith')
        );
});

it('searches members by email', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'email' => 'unique-test@example.com',
    ]);
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'email' => 'other@example.com',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', ['team' => $this->team, 'search' => 'unique-test']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('members.data', 1)
            ->where('members.data.0.email', 'unique-test@example.com')
        );
});

it('filters members by status', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
    ]);
    Membership::factory()->cancelled()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', ['team' => $this->team, 'status' => 'active']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('members.data', 1)
            ->where('members.data.0.status', 'active')
        );
});

it('filters members by plan', function () {
    $otherPlan = MembershipPlan::factory()->create(['team_id' => $this->team->id]);

    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $otherPlan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', ['team' => $this->team, 'plan' => $this->plan->id]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('members.data', 1)
        );
});

it('combines search and filter', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Active Alice',
        'status' => MembershipStatus::Active,
    ]);
    Membership::factory()->cancelled()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Cancelled Alice',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.index', [
            'team' => $this->team,
            'search' => 'Alice',
            'status' => 'active',
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('members.data', 1)
            ->where('members.data.0.customer_name', 'Active Alice')
        );
});

// --- Create / Store ---

it('renders create member form', function () {
    $this->actingAs($this->user)
        ->get(route('team.members.create', $this->team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/members/create')
            ->has('plans')
        );
});

it('stores a new member', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.store', $this->team), [
            'customer_name' => 'New Member',
            'email' => 'new@example.com',
            'customer_phone' => '1234567890',
            'membership_plan_id' => $this->plan->id,
        ])
        ->assertRedirect(route('team.members.index', $this->team));

    $this->assertDatabaseHas('memberships', [
        'team_id' => $this->team->id,
        'customer_name' => 'New Member',
        'email' => 'new@example.com',
        'customer_phone' => '1234567890',
        'membership_plan_id' => $this->plan->id,
        'status' => 'active',
    ]);
});

it('stores a new member with custom start date', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.store', $this->team), [
            'customer_name' => 'Future Member',
            'email' => 'future@example.com',
            'membership_plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
        ])
        ->assertRedirect(route('team.members.index', $this->team));

    $membership = Membership::where('customer_name', 'Future Member')->first();
    expect($membership)->not->toBeNull();
    expect($membership->starts_at->toDateString())->toBe('2026-06-01');
});

it('validates store member request', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.store', $this->team), [])
        ->assertSessionHasErrors(['customer_name', 'email', 'membership_plan_id']);
});

it('validates plan belongs to team on store', function () {
    $otherTeam = Team::factory()->create();
    $otherPlan = MembershipPlan::factory()->create(['team_id' => $otherTeam->id]);

    $this->actingAs($this->user)
        ->post(route('team.members.store', $this->team), [
            'customer_name' => 'Test',
            'email' => 'test@example.com',
            'membership_plan_id' => $otherPlan->id,
        ])
        ->assertSessionHasErrors(['membership_plan_id']);
});

// --- Show ---

it('shows member details', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Show Test',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.show', ['team' => $this->team, 'membership' => $membership]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/members/show')
            ->where('membership.customer_name', 'Show Test')
            ->has('plans')
            ->has('checkIns')
            ->has('notes')
        );
});

// --- Update Details ---

it('updates member details', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.update-details', ['team' => $this->team, 'membership' => $membership]), [
            'customer_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])
        ->assertRedirect();

    expect($membership->fresh())
        ->customer_name->toBe('Updated Name')
        ->email->toBe('updated@example.com');
});

it('updates member plan', function () {
    $newPlan = MembershipPlan::factory()->create(['team_id' => $this->team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.update-details', ['team' => $this->team, 'membership' => $membership]), [
            'membership_plan_id' => $newPlan->id,
        ])
        ->assertRedirect();

    expect($membership->fresh()->membership_plan_id)->toBe($newPlan->id);
});

it('validates update details request', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.update-details', ['team' => $this->team, 'membership' => $membership]), [
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors(['email']);
});

// --- Update Status ---

it('updates member status', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.update', ['team' => $this->team, 'membership' => $membership]), [
            'status' => 'paused',
        ])
        ->assertRedirect();

    expect($membership->fresh()->status)->toBe(MembershipStatus::Paused);
});

// --- Extend Membership ---

it('extends membership end date', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'ends_at' => now()->addDays(5),
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.extend', ['team' => $this->team, 'membership' => $membership]), [
            'ends_at' => '2027-01-01',
        ])
        ->assertRedirect();

    expect($membership->fresh()->ends_at->toDateString())->toBe('2027-01-01');
});

it('reactivates expired membership on extend', function () {
    $membership = Membership::factory()->expired()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.extend', ['team' => $this->team, 'membership' => $membership]), [
            'ends_at' => '2027-06-01',
            'reactivate' => true,
        ])
        ->assertRedirect();

    $fresh = $membership->fresh();
    expect($fresh->status)->toBe(MembershipStatus::Active);
    expect($fresh->ends_at->toDateString())->toBe('2027-06-01');
});

it('validates extend request', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->patch(route('team.members.extend', ['team' => $this->team, 'membership' => $membership]), [
            'ends_at' => 'not-a-date',
        ])
        ->assertSessionHasErrors(['ends_at']);
});

// --- Check-In History ---

it('shows check-in history on member detail', function () {
    $gym = Gym::factory()->create(['team_id' => $this->team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);
    CheckIn::factory()->create([
        'membership_id' => $membership->id,
        'team_id' => $this->team->id,
        'gym_id' => $gym->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.show', ['team' => $this->team, 'membership' => $membership]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('checkIns.data', 1)
        );
});

// --- Destroy ---

it('removes a member', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('team.members.destroy', ['team' => $this->team, 'membership' => $membership]))
        ->assertRedirect(route('team.members.index', $this->team));

    $this->assertDatabaseMissing('memberships', ['id' => $membership->id]);
});
