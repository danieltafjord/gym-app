<?php

use App\Enums\MembershipStatus;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use Inertia\Testing\AssertableInertia as Assert;

it('shows kiosk page for active team and gym', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/check-in-kiosk')
            ->has('team')
            ->has('gym')
            ->has('settings')
        );
});

it('returns 404 for inactive team', function () {
    $team = Team::factory()->create(['is_active' => false]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertNotFound();
});

it('returns 404 for inactive gym', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => false]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertNotFound();
});

it('returns 404 for gym belonging to another team', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $otherTeam = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $otherTeam->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertNotFound();
});

it('does not require authentication', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertOk();
});

it('processes a check-in and sets gym from URL', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'KIOSKCHECKINKIOSKCHECKI1',
        'status' => MembershipStatus::Active,
    ]);

    $this->post(route('public.kiosk.store', [$team, $gym]), [
        'access_code' => 'KIOSKCHECKINKIOSKCHECKI1',
        'method' => 'qr_scan',
    ])
        ->assertRedirect()
        ->assertSessionHas('checkInResult.success', true);

    $this->assertDatabaseHas('check_ins', [
        'membership_id' => $membership->id,
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'checked_in_by' => null,
    ]);
});

it('does not expose sensitive team data', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.name')
            ->has('team.slug')
            ->missing('team.owner_id')
            ->missing('team.stripe_account_id')
        );
});

it('includes kiosk_mode in settings', function () {
    $team = Team::factory()->create([
        'is_active' => true,
        'check_in_settings' => ['kiosk_mode' => 'barcode_scanner'],
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.kiosk', [$team, $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.kiosk_mode', 'barcode_scanner')
        );
});
