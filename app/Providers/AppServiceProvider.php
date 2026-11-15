<?php

namespace App\Providers;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\KantorWilker;
use App\Models\LoginHistory;
use App\Models\PrlSiveraVektor;
use App\Models\ShipSanitationInspection;
use App\Models\SinkarkesDailyReport;
use App\Models\SinkarkesReport;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\UserPolicy;
use App\Support\DashboardCache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());

        Gate::policy(Activity::class, AuditLogPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AircraftSanitationInspection::class, InspectionPolicy::class);
        Gate::policy(ShipSanitationInspection::class, InspectionPolicy::class);
        Gate::policy(FlightMedicalInspection::class, InspectionPolicy::class);

        // Restrict OpenAPI docs in production to Admin / Super Admin users
        Gate::define('viewApiDocs', function (?User $user = null) {
            return $this->app->environment(['local', 'testing'])
                || ($user !== null && $user->hasAnyRole(['Super Admin', 'Admin']));
        });

        RateLimiter::for('login', function (Request $request) {
            $login = strtolower(trim((string) ($request->input('login') ?? $request->input('email', ''))));

            return Limit::perMinute(5)->by("{$login}|{$request->ip()}");
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-submit', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('exports', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        $invalidateDashboard = static function (): void {
            DashboardCache::invalidate();
        };

        foreach ([
            AircraftSanitationInspection::class,
            ShipSanitationInspection::class,
            FlightMedicalInspection::class,
            KantorWilker::class,
            LoginHistory::class,
            PrlSiveraVektor::class,
            SinkarkesReport::class,
            SinkarkesDailyReport::class,
            User::class,
        ] as $modelClass) {
            $modelClass::saved($invalidateDashboard);
            $modelClass::deleted($invalidateDashboard);
        }
    }
}
