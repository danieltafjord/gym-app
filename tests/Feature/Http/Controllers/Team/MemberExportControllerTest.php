<?php

use App\Enums\MembershipStatus;
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

it('exports members as CSV', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Export Test',
        'email' => 'export@example.com',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('team.members.export', $this->team));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)->toContain('Name,Email,Phone,Plan,Status');
    expect($content)->toContain('Export Test');
    expect($content)->toContain('export@example.com');
});

it('respects search filter in export', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Included Member',
    ]);
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Excluded Person',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('team.members.export', ['team' => $this->team, 'search' => 'Included']));

    $content = $response->streamedContent();
    expect($content)->toContain('Included Member');
    expect($content)->not->toContain('Excluded Person');
});

it('respects status filter in export', function () {
    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Active Member',
        'status' => MembershipStatus::Active,
    ]);
    Membership::factory()->cancelled()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'Cancelled Member',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('team.members.export', ['team' => $this->team, 'status' => 'active']));

    $content = $response->streamedContent();
    expect($content)->toContain('Active Member');
    expect($content)->not->toContain('Cancelled Member');
});
