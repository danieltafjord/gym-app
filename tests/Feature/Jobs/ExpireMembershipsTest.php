<?php

use App\Enums\MembershipStatus;
use App\Jobs\ExpireMemberships;
use App\Mail\MembershipExpiredMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->plan = MembershipPlan::factory()->create(['team_id' => $this->team->id]);
});

it('expires active memberships past their end date', function () {
    Mail::fake();

    $expired = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
        'ends_at' => now()->subDay(),
    ]);

    $active = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
        'ends_at' => now()->addWeek(),
    ]);

    (new ExpireMemberships)->handle();

    expect($expired->fresh()->status)->toBe(MembershipStatus::Expired)
        ->and($active->fresh()->status)->toBe(MembershipStatus::Active);

    Mail::assertQueued(MembershipExpiredMail::class, 1);
});

it('does not expire already cancelled memberships', function () {
    Mail::fake();

    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Cancelled,
        'ends_at' => now()->subDay(),
    ]);

    (new ExpireMemberships)->handle();

    Mail::assertNothingQueued();
});
