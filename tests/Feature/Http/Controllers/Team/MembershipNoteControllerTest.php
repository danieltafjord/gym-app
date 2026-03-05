<?php

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
    $this->membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);
});

it('creates a note for a membership', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.notes.store', [
            'team' => $this->team,
            'membership' => $this->membership,
        ]), [
            'content' => 'This is a test note.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('membership_notes', [
        'membership_id' => $this->membership->id,
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'content' => 'This is a test note.',
    ]);
});

it('validates note content is required', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.notes.store', [
            'team' => $this->team,
            'membership' => $this->membership,
        ]), [
            'content' => '',
        ])
        ->assertSessionHasErrors(['content']);
});

it('validates note content max length', function () {
    $this->actingAs($this->user)
        ->post(route('team.members.notes.store', [
            'team' => $this->team,
            'membership' => $this->membership,
        ]), [
            'content' => str_repeat('a', 5001),
        ])
        ->assertSessionHasErrors(['content']);
});

it('shows notes on member detail page', function () {
    $this->membership->notes()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'content' => 'Existing note',
    ]);

    $this->actingAs($this->user)
        ->get(route('team.members.show', [
            'team' => $this->team,
            'membership' => $this->membership,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('notes', 1)
            ->where('notes.0.content', 'Existing note')
        );
});
