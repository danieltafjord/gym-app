<?php

namespace App\Http\Controllers\Team;

use App\Actions\CheckIn\ProcessCheckIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckIn\ProcessCheckInRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    public function scanner(Team $team): Response
    {
        return Inertia::render('team/check-in/scanner', [
            'team' => $team,
            'gyms' => $team->gyms()->active()->get(),
            'settings' => $team->check_in_settings_with_defaults,
        ]);
    }

    public function store(
        ProcessCheckInRequest $request,
        Team $team,
        ProcessCheckIn $action,
    ): RedirectResponse {
        $result = $action->handle($team, $request->validated(), $request->user()->id);

        return back()->with('checkInResult', [
            'success' => $result['success'],
            'message' => $result['message'],
            'membership' => $result['membership'] ? [
                'id' => $result['membership']->id,
                'customer_name' => $result['membership']->customer_name,
                'plan_name' => $result['membership']->plan?->name,
                'status' => $result['membership']->status->value,
            ] : null,
            'check_in' => $result['check_in'] ? [
                'id' => $result['check_in']->id,
                'created_at' => $result['check_in']->created_at->toISOString(),
                'gym_name' => $result['check_in']->gym?->name,
            ] : null,
        ]);
    }

    public function index(Team $team): Response
    {
        return Inertia::render('team/check-in/index', [
            'team' => $team,
            'checkIns' => $team->checkIns()
                ->with(['membership.user', 'membership.plan', 'gym', 'checkedInBy'])
                ->recent()
                ->paginate(15),
            'gyms' => $team->gyms()->active()->get(),
        ]);
    }
}
