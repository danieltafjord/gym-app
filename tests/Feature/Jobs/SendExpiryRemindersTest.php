<?php

use App\Enums\MembershipStatus;
use App\Jobs\SendExpiryReminders;
use App\Mail\MembershipExpiringMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->plan = MembershipPlan::factory()->create(['team_id' => $this->team->id]);
});

it('sends expiry reminders for memberships expiring within 7 days', function () {
    Mail::fake();

    $expiringSoon = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
        'ends_at' => now()->addDays(5),
        'expiry_reminder_sent_at' => null,
    ]);

    $expiringLater = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
        'ends_at' => now()->addDays(14),
        'expiry_reminder_sent_at' => null,
    ]);

    (new SendExpiryReminders)->handle();

    Mail::assertQueued(MembershipExpiringMail::class, 1);
    expect($expiringSoon->fresh()->expiry_reminder_sent_at)->not->toBeNull()
        ->and($expiringLater->fresh()->expiry_reminder_sent_at)->toBeNull();
});

it('does not send duplicate reminders', function () {
    Mail::fake();

    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
        'ends_at' => now()->addDays(5),
        'expiry_reminder_sent_at' => now()->subDay(),
    ]);

    (new SendExpiryReminders)->handle();

    Mail::assertNothingQueued();
});
