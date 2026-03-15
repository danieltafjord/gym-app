<?php

use App\Mail\MembershipCancelledMail;
use App\Mail\MembershipExpiredMail;
use App\Mail\MembershipExpiringMail;
use App\Mail\MembershipPausedMail;
use App\Mail\MembershipResumedMail;
use App\Mail\PaymentFailedMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->plan = MembershipPlan::factory()->create(['team_id' => $this->team->id, 'name' => 'Premium Plan']);
    $this->membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'customer_name' => 'John Doe',
        'email' => 'john@example.com',
        'ends_at' => now()->addDays(7),
    ]);
    $this->membership->load('team', 'plan');
});

it('renders membership expiring mail with default body', function () {
    $mail = new MembershipExpiringMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('expiring');
});

it('renders membership expired mail with default body', function () {
    $mail = new MembershipExpiredMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('expired');
});

it('renders membership cancelled mail with default body', function () {
    $mail = new MembershipCancelledMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('cancelled');
});

it('renders membership paused mail with default body', function () {
    $mail = new MembershipPausedMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('paused');
});

it('renders membership resumed mail with default body', function () {
    $mail = new MembershipResumedMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('resumed');
});

it('renders payment failed mail with default body', function () {
    $mail = new PaymentFailedMail($this->membership);

    $rendered = $mail->render();

    expect($rendered)->toContain('John Doe')
        ->and($rendered)->toContain('Premium Plan')
        ->and($rendered)->toContain('unable to process');
});

it('uses custom email template when available', function () {
    \App\Models\EmailTemplate::factory()->create([
        'team_id' => $this->team->id,
        'trigger' => 'membership_expiring',
        'subject' => 'Custom: {customer_name} expiring',
        'body' => 'Hey {customer_name}, your {plan_name} is ending on {ends_at}!',
    ]);

    $mail = new MembershipExpiringMail($this->membership);

    expect($mail->envelope()->subject)->toContain('Custom: John Doe expiring');

    $rendered = $mail->render();
    expect($rendered)->toContain('Hey John Doe');
});
