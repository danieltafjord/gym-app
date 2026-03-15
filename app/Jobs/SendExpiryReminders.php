<?php

namespace App\Jobs;

use App\Mail\MembershipExpiringMail;
use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Membership::query()
            ->expiringWithin(7)
            ->whereNull('expiry_reminder_sent_at')
            ->with(['team', 'plan'])
            ->chunkById(100, function ($memberships) {
                foreach ($memberships as $membership) {
                    Mail::to($membership->email)->queue(new MembershipExpiringMail($membership));

                    $membership->update(['expiry_reminder_sent_at' => now()]);
                }
            });
    }
}
