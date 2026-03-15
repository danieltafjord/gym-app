<?php

use App\Actions\Team\AcceptTeamInvitation;
use App\Models\TeamInvitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('accepts a pending invitation and assigns the role', function () {
    $invitation = TeamInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => 'team-admin',
    ]);

    $user = User::factory()->create(['email' => 'staff@example.com']);

    $action = app(AcceptTeamInvitation::class);
    $action->handle($invitation, $user);

    expect($invitation->fresh()->accepted_at)->not->toBeNull();

    setPermissionsTeamId($invitation->team_id);
    expect($user->hasRole('team-admin'))->toBeTrue();
});

it('rejects expired invitations', function () {
    $invitation = TeamInvitation::factory()->expired()->create([
        'email' => 'staff@example.com',
    ]);

    $user = User::factory()->create(['email' => 'staff@example.com']);

    $action = app(AcceptTeamInvitation::class);
    $action->handle($invitation, $user);
})->throws(ValidationException::class);

it('rejects invitations for wrong email', function () {
    $invitation = TeamInvitation::factory()->create([
        'email' => 'staff@example.com',
    ]);

    $user = User::factory()->create(['email' => 'other@example.com']);

    $action = app(AcceptTeamInvitation::class);
    $action->handle($invitation, $user);
})->throws(ValidationException::class);
