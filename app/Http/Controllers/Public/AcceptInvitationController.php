<?php

namespace App\Http\Controllers\Public;

use App\Actions\Team\AcceptTeamInvitation;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcceptInvitationController extends Controller
{
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)
            ->with('team:id,name,slug')
            ->first();

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('home')->with('error', 'This invitation is no longer valid.');
        }

        return Inertia::render('invitation/show', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'team_name' => $invitation->team->name,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function accept(Request $request, string $token, AcceptTeamInvitation $action): RedirectResponse
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        $action->handle($invitation, $request->user());

        return redirect()->route('team.show', $invitation->team)->with('success', 'You have joined the team.');
    }
}
