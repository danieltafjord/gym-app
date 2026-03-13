<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('account/dashboard', [
            'memberships' => $user->memberships()
                ->with(['team:id,name,slug', 'plan:id,name,price_cents,billing_period,plan_type,access_duration_value,access_duration_unit,activation_mode,requires_account,access_code_strategy,max_entries'])
                ->latest()
                ->get(),
        ]);
    }
}
