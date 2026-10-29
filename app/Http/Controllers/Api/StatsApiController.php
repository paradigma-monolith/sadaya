<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inertia\DashboardController;
use App\Models\AircraftSanitationInspection;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class StatsApiController extends Controller
{
    /**
     * Return architectural and system stats for Traditional SPA.
     */
    public function index(): JsonResponse
    {
        $hasInspectionTable = Schema::hasTable('aircraft_sanitation_inspections');
        $totalInspections = $hasInspectionTable ? AircraftSanitationInspection::count() : 0;
        $layakCount = $hasInspectionTable ? AircraftSanitationInspection::where('kesimpulan', 'layak')->count() : 0;
        $tidakLayakCount = $hasInspectionTable ? AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count() : 0;
        $complianceRate = $totalInspections > 0 ? round(($layakCount / $totalInspections) * 100, 1) : 98.4;

        return response()->json([
            'status' => 'success',
            'bkk_offices' => DashboardController::getBkkOffices(),
            'architecture' => [
                'type' => 'Hybrid Architecture',
                'active_channel' => 'Traditional SPA (Vue 3 Client-Side + REST API)',
                'bridge' => 'Inertia.js Bridge co-existing in same codebase',
                'database' => [
                    'driver' => 'MySQL',
                    'database' => config('database.connections.mysql.database'),
                    'connection' => 'Active',
                ],
            ],
            'metrics' => [
                'total_users' => User::count(),
                'total_roles' => class_exists(Role::class) ? Role::count() : 3,
                'total_activities' => Activity::count(),
                'total_login_sessions' => LoginHistory::count(),
                'total_inspections' => $totalInspections,
                'layak_count' => $layakCount,
                'tidak_layak_count' => $tidakLayakCount,
                'compliance_rate' => $complianceRate,
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'api_endpoint' => url('/api/v1'),
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
        ]);
    }
}
