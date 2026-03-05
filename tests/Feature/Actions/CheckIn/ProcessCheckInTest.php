<?php

use App\Actions\CheckIn\ProcessCheckIn;
use App\Enums\CheckInMethod;
use App\Enums\MembershipStatus;
use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

it('processes a valid check-in', function () {
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'ABCDEFGHIJKLMNOPQRSTUVWX',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;
    $result = $action->handle($team, [
        'access_code' => 'ABCDEFGHIJKLMNOPQRSTUVWX',
        'gym_id' => $gym->id,
        'method' => CheckInMethod::QrScan->value,
    ]);

    expect($result['success'])->toBeTrue()
        ->and($result['check_in'])->toBeInstanceOf(CheckIn::class)
        ->and($result['message'])->toContain($membership->customer_name);

    $this->assertDatabaseHas('check_ins', [
        'membership_id' => $membership->id,
        'team_id' => $team->id,
        'gym_id' => $gym->id,
    ]);
});

it('regenerates the access code after a successful check-in', function () {
    $team = Team::factory()->create();
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'ORIGINALCODEORIGINALCODE',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;
    $action->handle($team, [
        'access_code' => 'ORIGINALCODEORIGINALCODE',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    $membership->refresh();
    expect($membership->access_code)
        ->not->toBe('ORIGINALCODEORIGINALCODE')
        ->toHaveLength(24);
});

it('assigns the only active gym when none is provided', function () {
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'SINGLEGYMAUTOASSIGNCHECK',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;
    $result = $action->handle($team, [
        'access_code' => 'SINGLEGYMAUTOASSIGNCHECK',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    expect($result['success'])->toBeTrue();

    $this->assertDatabaseHas('check_ins', [
        'team_id' => $team->id,
        'gym_id' => $gym->id,
    ]);
});

it('rejects an unknown access code', function () {
    $team = Team::factory()->create();

    $action = new ProcessCheckIn;
    $result = $action->handle($team, [
        'access_code' => 'NOTFOUNDNOTFOUNDNOTFOUND',
        'gym_id' => null,
        'method' => CheckInMethod::ManualEntry->value,
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['check_in'])->toBeNull()
        ->and($result['message'])->toContain('No membership found');
});

it('rejects an inactive membership', function () {
    $team = Team::factory()->create();
    Membership::factory()->cancelled()->create([
        'team_id' => $team->id,
        'access_code' => 'CANCELLEDMEMBERSHIPCODE1',
    ]);

    $action = new ProcessCheckIn;
    $result = $action->handle($team, [
        'access_code' => 'CANCELLEDMEMBERSHIPCODE1',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('cancelled');
});

it('prevents duplicate check-ins within the configured window', function () {
    $team = Team::factory()->create([
        'check_in_settings' => ['prevent_duplicate_minutes' => 5],
    ]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'DUPETESTDUPETESTDUPETEST',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;

    $first = $action->handle($team, [
        'access_code' => 'DUPETESTDUPETESTDUPETEST',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);
    expect($first['success'])->toBeTrue();

    // Code was regenerated — use the new one
    $membership->refresh();
    $second = $action->handle($team, [
        'access_code' => $membership->access_code,
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);
    expect($second['success'])->toBeFalse()
        ->and($second['message'])->toContain('Already checked in');
});

it('allows duplicate check-ins when window is zero', function () {
    $team = Team::factory()->create([
        'check_in_settings' => ['prevent_duplicate_minutes' => 0],
    ]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'ZEROWINDOWZEROWINDOWZERO',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;

    $first = $action->handle($team, [
        'access_code' => 'ZEROWINDOWZEROWINDOWZERO',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    // Code was regenerated — use the new one
    $membership->refresh();
    $second = $action->handle($team, [
        'access_code' => $membership->access_code,
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    expect($first['success'])->toBeTrue()
        ->and($second['success'])->toBeTrue();
});

it('scopes access code lookup to the team', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    Membership::factory()->create([
        'team_id' => $team1->id,
        'access_code' => 'TEAMSCOPETEAMSCOPETEAMSC',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;
    $result = $action->handle($team2, [
        'access_code' => 'TEAMSCOPETEAMSCOPETEAMSC',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('No membership found');
});

it('records staff user id', function () {
    $team = Team::factory()->create();
    $staff = User::factory()->create();
    Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'STAFFIDTESTSTAFFIDTESTST',
        'status' => MembershipStatus::Active,
    ]);

    $action = new ProcessCheckIn;
    $result = $action->handle($team, [
        'access_code' => 'STAFFIDTESTSTAFFIDTESTST',
        'gym_id' => null,
        'method' => CheckInMethod::QrScan->value,
    ], $staff->id);

    expect($result['success'])->toBeTrue();
    $this->assertDatabaseHas('check_ins', [
        'checked_in_by' => $staff->id,
    ]);
});
