<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/account/settings/profile');
    Route::redirect('settings/profile', '/account/settings/profile');
    Route::redirect('settings/password', '/account/settings/password');
    Route::redirect('settings/appearance', '/account/settings/appearance');
    Route::redirect('settings/two-factor', '/account/settings/two-factor');
});
