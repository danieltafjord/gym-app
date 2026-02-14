<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\MembershipController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->prefix('account')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('account.dashboard');

    Route::redirect('settings', '/account/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('account.profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('account.profile.update');
});

Route::middleware(['auth', 'verified'])->prefix('account')->group(function () {
    // Memberships
    Route::post('memberships', [MembershipController::class, 'store'])->name('account.memberships.store');
    Route::patch('memberships/{membership}/cancel', [MembershipController::class, 'cancel'])->name('account.memberships.cancel');
    Route::patch('memberships/{membership}/pause', [MembershipController::class, 'pause'])->name('account.memberships.pause');
    Route::patch('memberships/{membership}/resume', [MembershipController::class, 'resume'])->name('account.memberships.resume');

    // Settings
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('account.profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('account.password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('account/settings/appearance');
    })->name('account.appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('account.two-factor.show');
});
