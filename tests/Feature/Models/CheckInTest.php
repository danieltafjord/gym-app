<?php

use App\Enums\CheckInMethod;
use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

it('can be created with valid attributes', function () {
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create(['team_id' => $team->id]);
    $staff = User::factory()->create();

    $checkIn = CheckIn::factory()->create([
        'membership_id' => $membership->id,
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'checked_in_by' => $staff->id,
        'method' => CheckInMethod::QrScan,
    ]);

    expect($checkIn)->toBeInstanceOf(CheckIn::class)
        ->and($checkIn->method)->toBe(CheckInMethod::QrScan);
});

it('belongs to a membership', function () {
    $membership = Membership::factory()->create();
    $checkIn = CheckIn::factory()->create(['membership_id' => $membership->id, 'team_id' => $membership->team_id]);

    expect($checkIn->membership->id)->toBe($membership->id);
});

it('belongs to a team', function () {
    $team = Team::factory()->create();
    $checkIn = CheckIn::factory()->create(['team_id' => $team->id]);

    expect($checkIn->team->id)->toBe($team->id);
});

it('belongs to a gym', function () {
    $gym = Gym::factory()->create();
    $checkIn = CheckIn::factory()->create(['gym_id' => $gym->id]);

    expect($checkIn->gym->id)->toBe($gym->id);
});

it('can have a null gym', function () {
    $checkIn = CheckIn::factory()->withoutGym()->create();

    expect($checkIn->gym)->toBeNull();
});

it('belongs to a staff user via checked_in_by', function () {
    $staff = User::factory()->create();
    $checkIn = CheckIn::factory()->create(['checked_in_by' => $staff->id]);

    expect($checkIn->checkedInBy->id)->toBe($staff->id);
});

it('can have a null checked_in_by', function () {
    $checkIn = CheckIn::factory()->withoutStaff()->create();

    expect($checkIn->checkedInBy)->toBeNull();
});

it('scopes to a team', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();

    CheckIn::factory()->create(['team_id' => $team1->id]);
    CheckIn::factory()->create(['team_id' => $team1->id]);
    CheckIn::factory()->create(['team_id' => $team2->id]);

    expect(CheckIn::forTeam($team1->id)->count())->toBe(2);
});

it('scopes recent orders by created_at descending', function () {
    $team = Team::factory()->create();

    $older = CheckIn::factory()->create(['team_id' => $team->id, 'created_at' => now()->subHour()]);
    $newer = CheckIn::factory()->create(['team_id' => $team->id, 'created_at' => now()]);

    $results = CheckIn::recent()->get();

    expect($results->first()->id)->toBe($newer->id);
});

it('casts method to CheckInMethod enum', function () {
    $checkIn = CheckIn::factory()->barcodeScan()->create();

    expect($checkIn->method)->toBe(CheckInMethod::BarcodeScanner);
});
