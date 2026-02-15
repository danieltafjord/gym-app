<?php

namespace App\Providers;

use App\Models\User;
use App\Services\StripeService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeService::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('widget', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }

    protected function configureAuthorization(): void
    {
        Gate::before(function (User $user, string $ability) {
            $currentTeamId = getPermissionsTeamId();
            setPermissionsTeamId(0);
            $isSuperAdmin = $user->hasRole('super-admin');
            setPermissionsTeamId($currentTeamId);

            return $isSuperAdmin ? true : null;
        });
    }
}
