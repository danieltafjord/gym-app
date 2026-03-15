<?php

namespace App\Jobs;

use App\Mail\MembershipExpiredMail;
use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ExpireMemberships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Membership::query()
            ->expired()
            ->with(['team', 'plan'])
            ->chunkById(100, function ($memberships) {
                foreach ($memberships as $membership) {
                    $membership->syncExpiredStatus();

                    Mail::to($membership->email)->queue(new MembershipExpiredMail($membership));
                }
            });
    }
}
