<?php

use App\Http\Controllers\Public\WidgetCheckoutController;
use App\Http\Controllers\Public\WidgetController;
use App\Http\Middleware\EnsureTeamAccess;
use App\Http\Middleware\EnsureTeamIsActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function (): void {
            Route::middleware(['throttle:widget', \Illuminate\Routing\Middleware\SubstituteBindings::class])
                ->prefix('widget')
                ->group(function (): void {
                    Route::get('embed.js', [WidgetController::class, 'script'])->name('widget.script');
                    Route::get('{team}/{gym}', [WidgetController::class, 'data'])->name('widget.data');
                    Route::post('{team}/{gym}/checkout/{membershipPlan}/intent', [WidgetCheckoutController::class, 'createIntent'])->name('widget.checkout.intent');
                    Route::post('{team}/{gym}/checkout/confirm', [WidgetCheckoutController::class, 'confirm'])->name('widget.checkout.confirm');
                });

            Route::middleware('web')
                ->group(base_path('routes/public.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'stripe/connect-webhook',
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->statefulApi();

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'team.access' => EnsureTeamAccess::class,
            'team.active' => EnsureTeamIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
