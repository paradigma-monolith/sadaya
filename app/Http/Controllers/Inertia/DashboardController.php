<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\AircraftSanitationInspection;
use App\Models\KantorWilker;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Master Data: Induk BKK Kelas I Bandung & 6 Wilayah Kerja / Pos.
     */
    public static function getBkkOffices(): array
    {
        try {
            if (Schema::hasTable('kantor_wilkers')) {
                $dbOffices = KantorWilker::orderBy('no')->get();
                if ($dbOffices->isNotEmpty()) {
                    return $dbOffices->map(function ($o) {
                        return [
                            'no' => $o->no,
                            'code' => $o->kode,
                            'name' => $o->nama,
                            'short_name' => $o->nama_pendek ?: $o->nama,
                            'zone' => $o->zona ?: 'Jawa Barat',
                            'type' => $o->tipe,
                            'moda' => $o->moda ?: 'Operasional',
                            'address' => $o->alamat,
                            'lat' => (float) $o->latitude,
                            'lng' => (float) $o->longitude,
                            'is_hq' => (bool) $o->is_induk,
                            'highlight' => $o->is_induk ? 'orange' : 'white',
                            'status' => $o->is_active ? 'ACTIVE' : 'INACTIVE',
                            'personnel' => $o->is_induk ? 68 : 24,
                            'readiness' => 97,
                            'daily_ops' => $o->is_induk ? 42 : 28,
                        ];
                    })->toArray();
                }
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return [
            [
                'no' => 1,
                'code' => 'BKK-BDG-01',
                'name' => 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)',
                'short_name' => 'Induk BKK Bandung',
                'zone' => 'Kota Bandung',
                'type' => 'Kantor Induk',
                'moda' => 'Pusat Komando',
                'address' => 'Jl. Cikapayang No.5, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40116',
                'lat' => -6.8989,
                'lng' => 107.6108,
                'is_hq' => true,
                'highlight' => 'orange',
                'status' => 'ACTIVE',
                'personnel' => 68,
                'readiness' => 98,
                'daily_ops' => 42,
            ],
            [
                'no' => 2,
                'code' => 'POS-BDO-02',
                'name' => 'POS Bandara Husen Sastranegara',
                'short_name' => 'POS Husen Sastranegara',
                'zone' => 'Kota Bandung',
                'type' => 'Pos Bandara',
                'moda' => 'Udara',
                'address' => 'Jl. Pajajaran Dalam, Husen Sastranegara, Kec. Cicendo, Kota Bandung, Jawa Barat 40174',
                'lat' => -6.9006,
                'lng' => 107.5763,
                'is_hq' => false,
                'highlight' => 'orange',
                'status' => 'ACTIVE',
                'personnel' => 24,
                'readiness' => 96,
                'daily_ops' => 31,
            ],
            [
                'no' => 3,
                'code' => 'WLK-CBN-03',
                'name' => 'Wilayah Kerja Cirebon',
                'short_name' => 'Wilker Cirebon',
                'zone' => 'Kota Cirebon',
                'type' => 'Wilayah Kerja',
                'moda' => 'Laut & Pesisir',
                'address' => 'Jl. Terusan Jl. Sekar Kemuning II No. 7, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131',
                'lat' => -6.7320,
                'lng' => 108.5523,
                'is_hq' => false,
                'highlight' => 'orange',
                'status' => 'ACTIVE',
                'personnel' => 22,
                'readiness' => 94,
                'daily_ops' => 28,
            ],
            [
                'no' => 4,
                'code' => 'WLK-IDM-04',
                'name' => 'Wilayah Kerja Indramayu',
                'short_name' => 'Wilker Indramayu',
                'zone' => 'Kab. Indramayu',
                'type' => 'Wilayah Kerja',
                'moda' => 'Laut & Pesisir',
                'address' => 'Jl. Jend. Sudirman, Lemahmekar, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45213',
                'lat' => -6.3276,
                'lng' => 108.3249,
                'is_hq' => false,
                'highlight' => 'white',
                'status' => 'ACTIVE',
                'personnel' => 18,
                'readiness' => 91,
                'daily_ops' => 19,
            ],
            [
                'no' => 5,
                'code' => 'WLK-KJT-05',
                'name' => 'Wilayah Kerja Kertajati',
                'short_name' => 'Wilker Kertajati (BIJB)',
                'zone' => 'Kab. Majalengka',
                'type' => 'Wilayah Kerja',
                'moda' => 'Udara Internasional',
                'address' => 'Kertajati, Majalengka Regency, West Java 45457',
                'lat' => -6.6561,
                'lng' => 108.1578,
                'is_hq' => false,
                'highlight' => 'orange',
                'status' => 'ACTIVE',
                'personnel' => 30,
                'readiness' => 97,
                'daily_ops' => 36,
            ],
            [
                'no' => 6,
                'code' => 'WLK-PTB-06',
                'name' => 'Wilayah Kerja Patimban',
                'short_name' => 'Wilker Patimban',
                'zone' => 'Kab. Subang',
                'type' => 'Wilayah Kerja',
                'moda' => 'Pelabuhan Laut',
                'address' => 'Pelabuhan Patimban, Pusakanagara, Subang, Jawa Barat 41255',
                'lat' => -6.2415,
                'lng' => 107.9048,
                'is_hq' => false,
                'highlight' => 'orange',
                'status' => 'ACTIVE',
                'personnel' => 26,
                'readiness' => 95,
                'daily_ops' => 34,
            ],
            [
                'no' => 7,
                'code' => 'WLK-PLR-07',
                'name' => 'Wilayah Kerja Pelabuharatu',
                'short_name' => 'Wilker Pelabuharatu',
                'zone' => 'Kab. Sukabumi',
                'type' => 'Wilayah Kerja',
                'moda' => 'Pelabuhan Samudera',
                'address' => 'Jl. Sirgalih, Pelabuhanratu, Kec. Pelabuhanratu, Kabupaten Sukabumi, Jawa Barat 43364',
                'lat' => -6.9875,
                'lng' => 106.5514,
                'is_hq' => false,
                'highlight' => 'white',
                'status' => 'ACTIVE',
                'personnel' => 16,
                'readiness' => 92,
                'daily_ops' => 21,
            ],
        ];
    }

    /**
     * Display the primary tactical command center dashboard.
     */
    public function dashboard(): Response
    {
        // 1. Gather User & Security Metrics
        $totalUsers = User::count();
        $totalRoles = class_exists(Role::class) ? Role::count() : 3;
        $totalActivities = Activity::count();

        // 2. Gather Aircraft Sanitation Inspection Telemetry
        $hasInspectionTable = Schema::hasTable('aircraft_sanitation_inspections');
        $totalInspections = $hasInspectionTable ? AircraftSanitationInspection::count() : 0;
        $layakCount = $hasInspectionTable ? AircraftSanitationInspection::where('kesimpulan', 'layak')->count() : 0;
        $tidakLayakCount = $hasInspectionTable ? AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count() : 0;
        $needActionCount = $hasInspectionTable ? AircraftSanitationInspection::whereNotNull('tindakan')->count() : 0;
        $todayInspections = $hasInspectionTable ? AircraftSanitationInspection::whereDate('tanggal', now()->toDateString())->count() : 0;
        $complianceRate = $totalInspections > 0 ? round(($layakCount / $totalInspections) * 100, 1) : 98.4;

        $actionBreakdown = [
            'disinseksi' => 0,
            'fumigasi' => 0,
            'disinfeksi' => 0,
            'general_cleaning' => 0,
        ];

        if ($hasInspectionTable) {
            $actionsRaw = AircraftSanitationInspection::whereNotNull('tindakan')
                ->select('tindakan', DB::raw('COUNT(*) as count'))
                ->groupBy('tindakan')
                ->pluck('count', 'tindakan');
            $actionBreakdown = [
                'disinseksi' => (int) ($actionsRaw['disinseksi'] ?? 0),
                'fumigasi' => (int) ($actionsRaw['fumigasi'] ?? 0),
                'disinfeksi' => (int) ($actionsRaw['disinfeksi'] ?? 0),
                'general_cleaning' => (int) ($actionsRaw['general_cleaning'] ?? 0),
            ];
        }

        // 3. Recent Audit Trail Activities
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
                    'causer_avatar' => $act->causer?->avatar_url ?? 'https://ui-avatars.com/api/?name=System&background=f97316&color=fff',
                    'created_at' => $act->created_at->diffForHumans(),
                ];
            });

        // 4. Recent Login Histories
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

        // 5. Activity Trend Analytics (Last 14 Days) for ECharts
        $trendDates = [];
        $trendCounts = [];
        $inspectionTrendCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $trendDates[] = $day->format('d M');
            $actCount = Activity::whereDate('created_at', $day)->count() + LoginHistory::whereDate('login_at', $day)->count();
            $inspCount = $hasInspectionTable ? AircraftSanitationInspection::whereDate('tanggal', $day)->count() : 0;
            $trendCounts[] = $actCount;
            $inspectionTrendCounts[] = $inspCount;
        }

        // 6. System, Database & Infrastructure Telemetry
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
            'bkk_offices' => self::getBkkOffices(),
            'metrics' => [
                'total_users' => $totalUsers,
                'total_roles' => $totalRoles,
                'total_activities' => $totalActivities,
                'total_inspections' => $totalInspections,
                'layak_count' => $layakCount,
                'tidak_layak_count' => $tidakLayakCount,
                'need_action_count' => $needActionCount,
                'today_inspections' => $todayInspections,
                'compliance_rate' => $complianceRate,
                'actions' => $actionBreakdown,
            ],
            'recent_activities' => $recentActivities,
            'recent_logins' => $recentLogins,
            'chart_data' => [
                'dates' => $trendDates,
                'counts' => $trendCounts,
                'inspections' => $inspectionTrendCounts,
            ],
            'system' => $systemInfo,
        ]);
    }
}
