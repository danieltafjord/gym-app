<?php

use App\Http\Controllers\Team\GymController;
use App\Http\Controllers\Team\MemberController;
use App\Http\Controllers\Team\MembershipPlanController;
use App\Http\Controllers\Team\StripeConnectController;
use App\Http\Controllers\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('team', [TeamController::class, 'store'])->name('team.store');

    Route::middleware(['team.access', 'team.active'])->prefix('team/{team}')->group(function () {
        Route::get('/', [TeamController::class, 'show'])->name('team.show');
        Route::get('edit', [TeamController::class, 'edit'])->name('team.edit');
        Route::patch('/', [TeamController::class, 'update'])->name('team.update');

        // Gyms
        Route::resource('gyms', GymController::class)->names('team.gyms');

        // Plans
        Route::resource('plans', MembershipPlanController::class)->names('team.plans');

        // Members
        Route::get('members', [MemberController::class, 'index'])->name('team.members.index');
        Route::get('members/{membership}', [MemberController::class, 'show'])->name('team.members.show');
        Route::patch('members/{membership}', [MemberController::class, 'update'])->name('team.members.update');
        Route::delete('members/{membership}', [MemberController::class, 'destroy'])->name('team.members.destroy');

        // Stripe Connect
        Route::get('stripe/onboard', [StripeConnectController::class, 'onboard'])->name('team.stripe.onboard');
        Route::get('stripe/return', [StripeConnectController::class, 'returnMethod'])->name('team.stripe.return');
        Route::get('stripe/refresh', [StripeConnectController::class, 'refresh'])->name('team.stripe.refresh');
        Route::get('stripe/dashboard', [StripeConnectController::class, 'dashboard'])->name('team.stripe.dashboard');
    });
});
