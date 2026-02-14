<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Controllers\Api\V1\MembershipPlanController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('user', [UserController::class, 'show']);

    // Browse teams
    Route::get('teams', [TeamController::class, 'index']);
    Route::get('teams/{team}', [TeamController::class, 'show']);
    Route::get('teams/{team}/plans', [MembershipPlanController::class, 'index']);

    // My memberships
    Route::get('memberships', [MembershipController::class, 'index']);
    Route::post('memberships', [MembershipController::class, 'store']);
    Route::patch('memberships/{membership}/cancel', [MembershipController::class, 'cancel']);
    Route::patch('memberships/{membership}/pause', [MembershipController::class, 'pause']);
    Route::patch('memberships/{membership}/resume', [MembershipController::class, 'resume']);

    // Team admin routes
    Route::middleware(['team.access'])->prefix('team/{team}')->group(function () {
        Route::apiResource('gyms', \App\Http\Controllers\Api\V1\Team\GymController::class);
        Route::apiResource('plans', \App\Http\Controllers\Api\V1\Team\MembershipPlanController::class);
        Route::apiResource('members', \App\Http\Controllers\Api\V1\Team\MemberController::class)->only(['index', 'show', 'destroy']);
    });
});
