<?php

use App\Http\Controllers\Team\AnalyticsController;
use App\Http\Controllers\Team\CheckInController;
use App\Http\Controllers\Team\CheckInSettingsController;
use App\Http\Controllers\Team\GymController;
use App\Http\Controllers\Team\MemberController;
use App\Http\Controllers\Team\MemberExportController;
use App\Http\Controllers\Team\MembershipNoteController;
use App\Http\Controllers\Team\MembershipPlanController;
use App\Http\Controllers\Team\StaffController;
use App\Http\Controllers\Team\StripeConnectController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Team\TeamGeneralSettingsController;
use App\Http\Controllers\Team\TeamWidgetDefaultsController;
use App\Http\Controllers\Team\WidgetSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('team', [TeamController::class, 'store'])->name('team.store');

    Route::middleware(['team.access', 'team.active'])->prefix('team/{team}')->group(function () {
        Route::get('/', [TeamController::class, 'show'])->name('team.show');
        Route::get('analytics', [AnalyticsController::class, 'show'])->name('team.analytics');
        Route::get('edit', [TeamController::class, 'edit'])->name('team.edit');
        Route::patch('/', [TeamController::class, 'update'])->name('team.update');

        // Gyms
        Route::resource('gyms', GymController::class)->except('edit')->names('team.gyms');

        // Gym Settings
        Route::prefix('gyms/{gym}/settings')->group(function () {
            Route::get('/', fn () => redirect()->route('team.gyms.settings.general', [
                'team' => request()->route('team'),
                'gym' => request()->route('gym'),
            ]))->name('team.gyms.settings');
            Route::get('general', [GymController::class, 'edit'])->name('team.gyms.settings.general');
            Route::get('occupancy', [GymController::class, 'occupancy'])->name('team.gyms.settings.occupancy');
            Route::get('widget', [WidgetSettingsController::class, 'edit'])->name('team.gyms.settings.widget');
            Route::patch('widget', [WidgetSettingsController::class, 'update'])->name('team.gyms.settings.widget.update');
            Route::delete('widget', [WidgetSettingsController::class, 'destroy'])->name('team.gyms.settings.widget.destroy');
        });

        // Legacy redirects
        Route::get('gyms/{gym}/edit', fn () => redirect()->route('team.gyms.settings.general', [
            'team' => request()->route('team'),
            'gym' => request()->route('gym'),
        ]))->name('team.gyms.edit');
        Route::get('gyms/{gym}/widget', fn () => redirect()->route('team.gyms.settings.widget', [
            'team' => request()->route('team'),
            'gym' => request()->route('gym'),
        ]))->name('team.gyms.widget');

        // Plans
        Route::resource('plans', MembershipPlanController::class)->names('team.plans');

        // Check-In
        Route::get('check-in', [CheckInController::class, 'scanner'])->name('team.check-in.scanner');
        Route::post('check-in', [CheckInController::class, 'store'])->name('team.check-in.store');
        Route::get('check-ins', [CheckInController::class, 'index'])->name('team.check-ins.index');

        // Members
        Route::get('members/create', [MemberController::class, 'create'])->name('team.members.create');
        Route::get('members/export', MemberExportController::class)->name('team.members.export');
        Route::post('members', [MemberController::class, 'store'])->name('team.members.store');
        Route::get('members', [MemberController::class, 'index'])->name('team.members.index');
        Route::get('members/{membership}', [MemberController::class, 'show'])->name('team.members.show');
        Route::patch('members/{membership}', [MemberController::class, 'update'])->name('team.members.update');
        Route::patch('members/{membership}/details', [MemberController::class, 'updateDetails'])->name('team.members.update-details');
        Route::patch('members/{membership}/extend', [MemberController::class, 'extend'])->name('team.members.extend');
        Route::post('members/{membership}/notes', [MembershipNoteController::class, 'store'])->name('team.members.notes.store');
        Route::delete('members/{membership}', [MemberController::class, 'destroy'])->name('team.members.destroy');

        // Settings
        Route::get('settings', fn () => redirect()->route('team.settings.general', ['team' => request()->route('team')]))->name('team.settings');
        Route::get('settings/general', [TeamGeneralSettingsController::class, 'edit'])->name('team.settings.general');
        Route::patch('settings/general', [TeamGeneralSettingsController::class, 'update'])->name('team.settings.general.update');
        Route::get('settings/widget-defaults', [TeamWidgetDefaultsController::class, 'edit'])->name('team.settings.widget-defaults');
        Route::patch('settings/widget-defaults', [TeamWidgetDefaultsController::class, 'update'])->name('team.settings.widget-defaults.update');
        Route::get('settings/check-in', [CheckInSettingsController::class, 'edit'])->name('team.settings.check-in');
        Route::patch('settings/check-in', [CheckInSettingsController::class, 'update'])->name('team.settings.check-in.update');
        Route::get('settings/staff', [StaffController::class, 'index'])->name('team.settings.staff');
        Route::post('settings/staff/invite', [StaffController::class, 'store'])->name('team.settings.staff.invite');
        Route::delete('settings/staff/invitations/{invitation}', [StaffController::class, 'destroyInvitation'])->name('team.settings.staff.invitations.destroy');
        Route::delete('settings/staff/{user}', [StaffController::class, 'removeStaff'])->name('team.settings.staff.remove');

        // Stripe Connect
        Route::get('stripe/onboard', [StripeConnectController::class, 'onboard'])->name('team.stripe.onboard');
        Route::get('stripe/return', [StripeConnectController::class, 'returnMethod'])->name('team.stripe.return');
        Route::get('stripe/refresh', [StripeConnectController::class, 'refresh'])->name('team.stripe.refresh');
        Route::get('stripe/dashboard', [StripeConnectController::class, 'dashboard'])->name('team.stripe.dashboard');
    });
});
