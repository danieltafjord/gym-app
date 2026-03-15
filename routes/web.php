<?php

use App\Http\Controllers\Public\AcceptInvitationController;
use App\Http\Controllers\Public\GymOccupancyController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('invitation/{token}', [AcceptInvitationController::class, 'show'])->name('invitation.show');

Route::middleware(['auth'])->group(function () {
    Route::redirect('dashboard', '/account')->name('dashboard');
    Route::get('gym-occupancy/{team}/{gym}', [GymOccupancyController::class, 'show'])->name('gym.occupancy');
    Route::post('invitation/{token}/accept', [AcceptInvitationController::class, 'accept'])->name('invitation.accept');
});

// Stripe Webhooks (CSRF excluded in bootstrap/app.php)
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');
Route::post('stripe/connect-webhook', [StripeWebhookController::class, 'handleConnectWebhook'])->name('stripe.connect-webhook');

require __DIR__.'/account.php';
require __DIR__.'/team.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
