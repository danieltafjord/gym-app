<?php

namespace App\Http\Controllers\Team;

use App\Actions\Membership\CreateMembershipNote;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreMembershipNoteRequest;
use App\Models\Membership;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class MembershipNoteController extends Controller
{
    public function store(
        StoreMembershipNoteRequest $request,
        Team $team,
        Membership $membership,
        CreateMembershipNote $action,
    ): RedirectResponse {
        $action->handle($membership, $request->user(), $request->validated()['content']);

        return back();
    }
}
