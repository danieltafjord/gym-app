<?php

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();

    $this->owner = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->owner->id]);

    setPermissionsTeamId($this->team->id);
    $this->owner->assignRole('team-owner');
});

it('shows staff page with members and invitations', function () {
    TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'invited_by' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('team.settings.staff', $this->team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/settings/staff')
            ->has('staffMembers', 1)
            ->has('pendingInvitations', 1)
        );
});

it('sends an invitation', function () {
    $this->actingAs($this->owner)
        ->post(route('team.settings.staff.invite', $this->team), [
            'email' => 'newstaff@example.com',
            'role' => 'team-admin',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $this->team->id,
        'email' => 'newstaff@example.com',
        'role' => 'team-admin',
    ]);
});

it('cancels an invitation', function () {
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'invited_by' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->delete(route('team.settings.staff.invitations.destroy', [$this->team, $invitation]))
        ->assertRedirect();

    $this->assertDatabaseMissing('team_invitations', ['id' => $invitation->id]);
});

it('removes a staff member', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId($this->team->id);
    $admin->assignRole('team-admin');

    $this->actingAs($this->owner)
        ->delete(route('team.settings.staff.remove', [$this->team, $admin]))
        ->assertRedirect();

    setPermissionsTeamId($this->team->id);
    $admin->unsetRelation('roles');
    expect($admin->hasRole('team-admin'))->toBeFalse();
});

it('denies access to non-team members', function () {
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->get(route('team.settings.staff', $this->team))
        ->assertForbidden();
});
