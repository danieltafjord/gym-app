<?php

namespace App\Http\Controllers\Public;

use App\Actions\CheckIn\ProcessCheckIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckIn\ProcessCheckInRequest;
use App\Models\Gym;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CheckInKioskController extends Controller
{
    public function show(Team $team, Gym $gym): Response
    {
        abort_unless($team->is_active, 404);
        abort_unless($gym->team_id === $team->id && $gym->is_active, 404);

        return Inertia::render('public/check-in-kiosk', [
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'gym' => [
                'id' => $gym->id,
                'name' => $gym->name,
                'slug' => $gym->slug,
            ],
            'settings' => $team->check_in_settings_with_defaults,
        ]);
    }

    public function store(ProcessCheckInRequest $request, Team $team, Gym $gym, ProcessCheckIn $action): RedirectResponse
    {
        abort_unless($team->is_active, 404);
        abort_unless($gym->team_id === $team->id && $gym->is_active, 404);

        $data = $request->validated();
        $data['gym_id'] = $gym->id;

        $result = $action->handle($team, $data);

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
                'gym_name' => $gym->name,
            ] : null,
        ]);
    }
}
