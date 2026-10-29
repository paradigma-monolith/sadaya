<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\AircraftSanitationInspection;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display the primary production-ready dashboard.
     */
    public function dashboard(): Response
    {
        // 1. Gather User, Security & Inspection Metrics
        $totalUsers = User::count();
        $totalRoles = class_exists(Role::class) ? Role::count() : 3;
        $totalActivities = Activity::count();
        $totalInspections = class_exists(AircraftSanitationInspection::class) ? AircraftSanitationInspection::count() : 0;
        $layakInspections = class_exists(AircraftSanitationInspection::class) ? AircraftSanitationInspection::where('kesimpulan', 'layak')->count() : 0;
        $tidakLayakInspections = class_exists(AircraftSanitationInspection::class) ? AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count() : 0;

        // 2. Recent Audit Trail Activities
        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($act) {
                return [
                    'id' => $act->id,
                    'description' => $act->description,
                    'event' => $act->event,
                    'subject_type' => class_basename($act->subject_type),
                    'causer_name' => $act->causer?->name ?? 'System',
                    'causer_avatar' => $act->causer?->avatar_url ?? 'https://ui-avatars.com/api/?name=System&background=6366f1&color=fff',
                    'created_at' => $act->created_at->diffForHumans(),
                ];
            });

        // 3. Recent Login Histories
        $recentLogins = LoginHistory::with('user')
            ->latest('login_at')
            ->take(6)
            ->get()
            ->map(function ($lh) {
                return [
                    'id' => $lh->id,
                    'user_name' => $lh->user?->name ?? 'Pengguna',
                    'email' => $lh->user?->email ?? '-',
                    'ip_address' => $lh->ip_address,
                    'device' => $lh->device,
                    'status' => $lh->status,
                    'login_at' => Carbon::parse($lh->login_at)->diffForHumans(),
                ];
            });

        // 4. Activity Trend Analytics (Last 7 Days) for BaseChart ECharts
        $trendDates = [];
        $trendCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $trendDates[] = $day->format('d M');
            $trendCounts[] = Activity::whereDate('created_at', $day)->count() + LoginHistory::whereDate('login_at', $day)->count();
        }

        // 5. System, Database & Infrastructure Telemetry
        $systemInfo = [
            'database' => [
                'driver' => config('database.default'),
                'database' => config('database.connections.mysql.database'),
                'host' => config('database.connections.mysql.host'),
                'status' => 'Connected',
            ],
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'app_env' => config('app.env'),
            ],
            'infrastructure' => [
                'octane' => [
                    'installed' => class_exists(\Laravel\Octane\Octane::class),
                    'server' => config('octane.server', 'roadrunner'),
                    'in_octane' => isset($_SERVER['LARAVEL_OCTANE']),
                ],
                'redis' => \App\Services\RedisService::getTelemetry(),
                'sentry' => [
                    'installed' => class_exists(\Sentry\Laravel\Integration::class),
                    'configured' => !empty(config('sentry.dsn')),
                ],
                'pulse' => [
                    'installed' => class_exists(\Laravel\Pulse\Pulse::class),
                    'enabled' => config('pulse.enabled', true),
                    'path' => config('pulse.path', 'pulse'),
                ],
                'reverb' => [
                    'installed' => class_exists(\Laravel\Reverb\ReverbServiceProvider::class),
                    'broadcaster' => config('broadcasting.default'),
                    'port' => config('reverb.servers.reverb.port', 8080),
                ],
            ],
        ];

        return Inertia::render('Inertia/Dashboard', [
            'metrics' => [
                'total_users' => $totalUsers,
                'total_roles' => $totalRoles,
                'total_activities' => $totalActivities,
                'total_inspections' => $totalInspections,
                'layak_inspections' => $layakInspections,
                'tidak_layak_inspections' => $tidakLayakInspections,
            ],
            'recent_activities' => $recentActivities,
            'recent_logins' => $recentLogins,
            'chart_data' => [
                'dates' => $trendDates,
                'counts' => $trendCounts,
            ],
            'system' => $systemInfo,
        ]);
    }
}
