<?php

use App\Http\Controllers\Public\CheckInKioskController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\PublicGymController;
use Illuminate\Support\Facades\Route;

// Check-in kiosk (public, no auth)
Route::get('kiosk/{team}/{gym}', [CheckInKioskController::class, 'show'])->name('public.kiosk');
Route::post('kiosk/{team}/{gym}', [CheckInKioskController::class, 'store'])->name('public.kiosk.store');

Route::get('{team}', [PublicGymController::class, 'showTeam'])->name('public.team');
Route::get('{team}/{gym}', [PublicGymController::class, 'showGym'])->name('public.gym');
Route::get('{team}/{gym}/widget', [PublicGymController::class, 'widget'])->name('public.widget');

Route::get('{team}/{gym}/checkout/success', [CheckoutController::class, 'success'])->name('public.checkout.success');
Route::get('{team}/{gym}/checkout/{membershipPlan}', [CheckoutController::class, 'show'])->name('public.checkout');
Route::post('{team}/{gym}/checkout/{membershipPlan}/intent', [CheckoutController::class, 'createIntent'])->name('public.checkout.intent');
