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
                ->with(['team:id,name,slug', 'plan:id,name,price_cents,billing_period'])
                ->latest()
                ->get(),
        ]);
    }
}
