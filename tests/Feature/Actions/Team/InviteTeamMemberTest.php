<?php

use App\Actions\Team\InviteTeamMember;
use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();

    $this->owner = User::factory()->create();
    $this->team = Team::factory()->create(['owner_id' => $this->owner->id]);

    setPermissionsTeamId($this->team->id);
    $this->owner->assignRole('team-owner');
});

it('creates an invitation and sends email', function () {
    $action = app(InviteTeamMember::class);

    $invitation = $action->handle($this->team, $this->owner, [
        'email' => 'staff@example.com',
        'role' => 'team-admin',
    ]);

    expect($invitation)->toBeInstanceOf(TeamInvitation::class)
        ->and($invitation->email)->toBe('staff@example.com')
        ->and($invitation->role)->toBe('team-admin')
        ->and($invitation->token)->toHaveLength(64);

    Mail::assertQueued(TeamInvitationMail::class, function ($mail) {
        return $mail->hasTo('staff@example.com');
    });
});

it('prevents duplicate pending invitations', function () {
    $action = app(InviteTeamMember::class);

    $action->handle($this->team, $this->owner, [
        'email' => 'staff@example.com',
        'role' => 'team-admin',
    ]);

    $action->handle($this->team, $this->owner, [
        'email' => 'staff@example.com',
        'role' => 'team-admin',
    ]);
})->throws(ValidationException::class);

it('prevents inviting existing staff', function () {
    $existingAdmin = User::factory()->create(['email' => 'admin@example.com']);
    setPermissionsTeamId($this->team->id);
    $existingAdmin->assignRole('team-admin');

    $action = app(InviteTeamMember::class);

    $action->handle($this->team, $this->owner, [
        'email' => 'admin@example.com',
        'role' => 'team-admin',
    ]);
})->throws(ValidationException::class);
