<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\KantorWilker;
use App\Models\LoginHistory;
use App\Models\ShipSanitationInspection;
use App\Models\User;
use Illuminate\Support\Carbon;
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
     * Keyword dictionary used to map free-text locations (geotag / embarkasi / pelabuhan)
     * onto the official KantorWilker records, keyed by a fragment of the office code.
     */
    protected const OFFICE_KEYWORDS = [
        'BDO' => ['husen', 'husein', 'bdo', 'pajajaran'],
        'KJT' => ['kertajati', 'kjt', 'bijb', 'majalengka'],
        'CBN' => ['cirebon', 'muara jati'],
        'IDM' => ['indramayu'],
        'PTB' => ['patimban', 'subang'],
        'PLR' => ['pelabuhanratu', 'pelabuharatu', 'sukabumi'],
        'BDG' => ['induk', 'cikapayang', 'kelas i bandung'],
    ];

    protected const STATUS_LABELS = [
        'submitted' => 'Diajukan',
        'verified' => 'Diverifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    protected const MODULE_META = [
        'aircraft' => ['label' => 'Sanitasi Pesawat', 'moda' => 'udara', 'url' => '/aircraft-inspections'],
        'ship' => ['label' => 'Sanitasi Kapal', 'moda' => 'laut', 'url' => '/ship-inspections'],
        'alat' => ['label' => 'Alkes Pesawat', 'moda' => 'udara', 'url' => '/flight-medical-inspections/alat'],
        'obat' => ['label' => 'Obat P3K Pesawat', 'moda' => 'udara', 'url' => '/flight-medical-inspections/obat'],
    ];

    /**
     * Resolve a free-text location to an office "no" (or null when unknown).
     */
    protected function resolveOfficeNo(?string $text, array $offices): ?int
    {
        $haystack = strtolower(trim((string) $text));
        if ($haystack === '') {
            return null;
        }

        foreach ($offices as $o) {
            if ($haystack === strtolower($o['name']) || str_contains($haystack, strtolower($o['name']))) {
                return $o['no'];
            }
        }

        foreach ($offices as $o) {
            foreach (self::OFFICE_KEYWORDS as $codeFragment => $keywords) {
                if (!str_contains(strtoupper((string) $o['code']), $codeFragment)) {
                    continue;
                }
                foreach ($keywords as $kw) {
                    if (str_contains($haystack, $kw)) {
                        return $o['no'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Display the executive monitoring dashboard built from live inspection data.
     */
    public function dashboard(): Response
    {
        Carbon::setLocale('id');
        $offices = self::getBkkOffices();
        $today = Carbon::today();
        $periodDays = 30;
        $periodStart = $today->copy()->subDays($periodDays - 1);
        $prevStart = $periodStart->copy()->subDays($periodDays);
        $prevEnd = $periodStart->copy()->subDay();

        // ------------------------------------------------------------------
        // 1. Normalise every inspection module into a single record stream
        // ------------------------------------------------------------------
        $records = collect();
        $aircraftRows = collect();
        $shipRows = collect();
        $medicalRows = collect();

        if (Schema::hasTable('aircraft_sanitation_inspections')) {
            $aircraftRows = AircraftSanitationInspection::query()
                ->select(['id', 'registration_no', 'tanda_kebangsaan', 'pemilik', 'tanggal', 'kesimpulan', 'tindakan', 'status', 'lokasi_geotag', 'petugas_bkk_nama', 'created_at'])
                ->get();

            foreach ($aircraftRows as $r) {
                $records->push([
                    'module' => 'aircraft',
                    'id' => $r->id,
                    'registration_no' => $r->registration_no,
                    'subject' => trim(($r->tanda_kebangsaan ?: '-') . ($r->pemilik ? " · {$r->pemilik}" : '')),
                    'officer' => $r->petugas_bkk_nama,
                    'date' => $r->tanggal ? Carbon::parse($r->tanggal) : Carbon::parse($r->created_at),
                    'created_at' => Carbon::parse($r->created_at),
                    'status' => $r->status ?: 'submitted',
                    'result' => $r->kesimpulan === 'tidak_layak' ? 'Tidak Layak' : 'Layak',
                    'result_ok' => $r->kesimpulan !== 'tidak_layak',
                    'office_no' => $this->resolveOfficeNo($r->lokasi_geotag, $offices),
                ]);
            }
        }

        if (Schema::hasTable('ship_sanitation_inspections')) {
            $shipRows = ShipSanitationInspection::query()
                ->select(['id', 'registration_no', 'nama_kapal', 'pelabuhan', 'tanggal', 'sertifikat_mencatat', 'jenis_kapal', 'status', 'areas', 'nama_pemeriksa', 'created_at'])
                ->get();

            foreach ($shipRows as $r) {
                $records->push([
                    'module' => 'ship',
                    'id' => $r->id,
                    'registration_no' => $r->registration_no,
                    'subject' => $r->nama_kapal ?: '-',
                    'officer' => $r->nama_pemeriksa,
                    'date' => $r->tanggal ? Carbon::parse($r->tanggal) : Carbon::parse($r->created_at),
                    'created_at' => Carbon::parse($r->created_at),
                    'status' => $r->status ?: 'submitted',
                    'result' => $r->sertifikat_mencatat === 'Pengendalian' ? 'SSCC' : 'SSCEC',
                    'result_ok' => $r->sertifikat_mencatat !== 'Pengendalian',
                    'office_no' => $this->resolveOfficeNo($r->pelabuhan, $offices),
                ]);
            }
        }

        if (Schema::hasTable('flight_medical_inspections')) {
            $medicalRows = FlightMedicalInspection::query()
                ->select(['id', 'registration_no', 'type', 'embarkasi', 'nomor_kloter', 'status', 'crew_nama', 'pelapor_nama', 'created_at'])
                ->get();

            foreach ($medicalRows as $r) {
                $module = $r->type === 'obat' ? 'obat' : 'alat';
                $records->push([
                    'module' => $module,
                    'id' => $r->id,
                    'registration_no' => $r->registration_no,
                    'subject' => $r->nomor_kloter ? "Kloter {$r->nomor_kloter}" : ($r->embarkasi ?: '-'),
                    'officer' => $r->pelapor_nama ?: $r->crew_nama,
                    'date' => Carbon::parse($r->created_at),
                    'created_at' => Carbon::parse($r->created_at),
                    'status' => $r->status ?: 'submitted',
                    'result' => self::STATUS_LABELS[$r->status] ?? 'Diajukan',
                    'result_ok' => $r->status !== 'rejected',
                    'office_no' => $this->resolveOfficeNo($r->embarkasi, $offices),
                ]);
            }
        }

        // ------------------------------------------------------------------
        // 2. Headline KPIs
        // ------------------------------------------------------------------
        $inPeriod = fn ($rec) => $rec['date']->betweenIncluded($periodStart, $today->copy()->endOfDay());
        $inPrev = fn ($rec) => $rec['date']->betweenIncluded($prevStart, $prevEnd->copy()->endOfDay());

        $periodCount = $records->filter($inPeriod)->count();
        $prevCount = $records->filter($inPrev)->count();
        $growth = $prevCount > 0
            ? round((($periodCount - $prevCount) / $prevCount) * 100, 1)
            : ($periodCount > 0 ? 100.0 : 0.0);

        $statusBreakdown = [];
        foreach (self::STATUS_LABELS as $key => $label) {
            $statusBreakdown[] = [
                'key' => $key,
                'label' => $label,
                'count' => $records->where('status', $key)->count(),
            ];
        }

        $aircraftTotal = $aircraftRows->count();
        $layak = $aircraftRows->where('kesimpulan', 'layak')->count();
        $tidakLayak = $aircraftRows->where('kesimpulan', 'tidak_layak')->count();

        // Ship sanitary risk findings across the 32 standard areas
        $riskAreaCounter = [];
        $shipsWithRisk = 0;
        foreach ($shipRows as $ship) {
            $hasRisk = false;
            foreach ((array) ($ship->areas ?? []) as $idx => $area) {
                $status = is_array($area) ? ($area['status'] ?? $area['result'] ?? '') : '';
                if (stripos((string) $status, 'Ditemukan Faktor Risiko') !== false && stripos((string) $status, 'Tidak') === false) {
                    $hasRisk = true;
                    $name = $area['nama'] ?? $area['area_name'] ?? ('Area #' . (is_numeric($idx) ? $idx + 1 : $idx));
                    $riskAreaCounter[$name] = ($riskAreaCounter[$name] ?? 0) + 1;
                }
            }
            if ($hasRisk) {
                $shipsWithRisk++;
            }
        }
        arsort($riskAreaCounter);
        $topRiskAreas = collect($riskAreaCounter)->take(5)->map(fn ($c, $n) => ['name' => $n, 'count' => $c])->values();

        $kpi = [
            'total' => $records->count(),
            'period_count' => $periodCount,
            'prev_count' => $prevCount,
            'growth' => $growth,
            'today' => $records->filter(fn ($r) => $r['date']->isSameDay($today))->count(),
            'pending' => $records->where('status', 'submitted')->count(),
            'approved' => $records->where('status', 'approved')->count(),
            'verified' => $records->where('status', 'verified')->count(),
            'rejected' => $records->where('status', 'rejected')->count(),
            'compliance_rate' => $aircraftTotal > 0 ? round(($layak / $aircraftTotal) * 100, 1) : 0,
            'ships_with_risk' => $shipsWithRisk,
            'ship_total' => $shipRows->count(),
            'completion_rate' => $records->count() > 0
                ? round((($records->where('status', 'approved')->count() + $records->where('status', 'verified')->count()) / $records->count()) * 100, 1)
                : 0,
        ];

        // ------------------------------------------------------------------
        // 3. Per-module breakdown
        // ------------------------------------------------------------------
        $modules = [];
        foreach (self::MODULE_META as $key => $meta) {
            $set = $records->where('module', $key);
            $modules[] = array_merge($meta, [
                'key' => $key,
                'total' => $set->count(),
                'period' => $set->filter($inPeriod)->count(),
                'pending' => $set->where('status', 'submitted')->count(),
                'approved' => $set->where('status', 'approved')->count(),
                'verified' => $set->where('status', 'verified')->count(),
                'rejected' => $set->where('status', 'rejected')->count(),
            ]);
        }

        $tindakanLabels = [
            'disinseksi' => 'Disinseksi',
            'fumigasi' => 'Fumigasi',
            'disinfeksi' => 'Disinfeksi',
            'general_cleaning' => 'General Cleaning',
        ];
        $aircraftStats = [
            'total' => $aircraftTotal,
            'layak' => $layak,
            'tidak_layak' => $tidakLayak,
            'tindakan' => collect($tindakanLabels)->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $aircraftRows->where('tindakan', $key)->count(),
            ])->values(),
        ];

        $shipStats = [
            'total' => $shipRows->count(),
            'sscec' => $shipRows->where('sertifikat_mencatat', 'Pembebasan')->count(),
            'sscc' => $shipRows->where('sertifikat_mencatat', 'Pengendalian')->count(),
            'ships_with_risk' => $shipsWithRisk,
            'top_risk_areas' => $topRiskAreas,
            'jenis_kapal' => $shipRows->groupBy(fn ($s) => $s->jenis_kapal ?: 'Lainnya')
                ->map(fn ($g, $name) => ['name' => $name, 'count' => $g->count()])
                ->sortByDesc('count')->values(),
        ];

        $medicalStats = [
            'total' => $medicalRows->count(),
            'alat' => $medicalRows->where('type', 'alat')->count(),
            'obat' => $medicalRows->where('type', 'obat')->count(),
            'embarkasi' => $medicalRows->groupBy(fn ($m) => $m->embarkasi ?: 'Lainnya')
                ->map(fn ($g, $name) => ['name' => $name, 'count' => $g->count()])
                ->sortByDesc('count')->values(),
        ];

        // ------------------------------------------------------------------
        // 4. Daily trend (last 30 days) per module
        // ------------------------------------------------------------------
        $trend = ['dates' => [], 'iso' => [], 'aircraft' => [], 'ship' => [], 'alat' => [], 'obat' => [], 'total' => []];
        $byDay = $records->groupBy(fn ($r) => $r['date']->toDateString());
        for ($i = $periodDays - 1; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $iso = $day->toDateString();
            $dayRecords = $byDay->get($iso, collect());
            $trend['dates'][] = $day->translatedFormat('d M');
            $trend['iso'][] = $iso;
            foreach (['aircraft', 'ship', 'alat', 'obat'] as $m) {
                $trend[$m][] = $dayRecords->where('module', $m)->count();
            }
            $trend['total'][] = $dayRecords->count();
        }

        // ------------------------------------------------------------------
        // 5. Per-office operational statistics
        // ------------------------------------------------------------------
        $officeStats = collect($offices)->map(function ($o) use ($records, $inPeriod) {
            $set = $records->where('office_no', $o['no']);
            $last = $set->sortByDesc(fn ($r) => $r['date']->timestamp)->first();
            $modaLower = strtolower($o['moda'] . ' ' . $o['type']);

            return [
                'no' => $o['no'],
                'code' => $o['code'],
                'name' => $o['name'],
                'short_name' => $o['short_name'],
                'zone' => $o['zone'],
                'type' => $o['type'],
                'moda' => $o['moda'],
                'address' => $o['address'],
                'lat' => $o['lat'],
                'lng' => $o['lng'],
                'is_hq' => $o['is_hq'],
                'category' => $o['is_hq'] ? 'induk' : ((str_contains($modaLower, 'udara') || str_contains($modaLower, 'bandara')) ? 'udara' : 'laut'),
                'total' => $set->count(),
                'period' => $set->filter($inPeriod)->count(),
                'aircraft' => $set->where('module', 'aircraft')->count(),
                'ship' => $set->where('module', 'ship')->count(),
                'alat' => $set->where('module', 'alat')->count(),
                'obat' => $set->where('module', 'obat')->count(),
                'pending' => $set->where('status', 'submitted')->count(),
                'approved' => $set->where('status', 'approved')->count(),
                'last_date' => $last ? $last['date']->translatedFormat('d M Y') : null,
                'last_human' => $last ? $last['date']->diffForHumans() : null,
            ];
        })->values();

        $unassigned = $records->whereNull('office_no')->count();

        // ------------------------------------------------------------------
        // 6. Latest inspections feed (all modules)
        // ------------------------------------------------------------------
        $officeNames = collect($offices)->pluck('short_name', 'no');
        $recentInspections = $records
            ->sortByDesc(fn ($r) => $r['created_at']->format('YmdHis') . $r['date']->format('Ymd'))
            ->take(12)
            ->map(fn ($r) => [
                'module' => $r['module'],
                'module_label' => self::MODULE_META[$r['module']]['label'],
                'registration_no' => $r['registration_no'],
                'subject' => $r['subject'],
                'officer' => $r['officer'] ?: '-',
                'office' => $r['office_no'] ? ($officeNames[$r['office_no']] ?? '-') : 'Lainnya',
                'date' => $r['date']->translatedFormat('d M Y'),
                'date_human' => $r['created_at']->diffForHumans(),
                'status' => $r['status'],
                'status_label' => self::STATUS_LABELS[$r['status']] ?? ucfirst($r['status']),
                'result' => $r['result'],
                'result_ok' => $r['result_ok'],
                'url' => self::MODULE_META[$r['module']]['url'] . '?search=' . urlencode((string) $r['registration_no']),
            ])
            ->values();

        // ------------------------------------------------------------------
        // 7. Audit trail & login history
        // ------------------------------------------------------------------
        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($act) => [
                'id' => $act->id,
                'description' => $act->description,
                'event' => $act->event,
                'subject_type' => class_basename((string) $act->subject_type),
                'causer_name' => $act->causer?->name ?? 'System',
                'created_at' => $act->created_at?->diffForHumans(),
            ]);

        $recentLogins = LoginHistory::with('user')
            ->latest('login_at')
            ->take(8)
            ->get()
            ->map(fn ($lh) => [
                'id' => $lh->id,
                'user_name' => $lh->user?->name ?? 'Pengguna',
                'email' => $lh->user?->email ?? '-',
                'ip_address' => $lh->ip_address,
                'device' => $lh->device,
                'status' => $lh->status,
                'login_at' => $lh->login_at ? Carbon::parse($lh->login_at)->diffForHumans() : '-',
            ]);

        return Inertia::render('Inertia/Dashboard', [
            'generated_at' => now('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
            'period' => [
                'days' => $periodDays,
                'start' => $periodStart->translatedFormat('d M Y'),
                'end' => $today->translatedFormat('d M Y'),
            ],
            'kpi' => $kpi,
            'modules' => $modules,
            'status_breakdown' => $statusBreakdown,
            'aircraft_stats' => $aircraftStats,
            'ship_stats' => $shipStats,
            'medical_stats' => $medicalStats,
            'trend' => $trend,
            'offices' => $officeStats,
            'unassigned' => $unassigned,
            'recent_inspections' => $recentInspections,
            'recent_activities' => $recentActivities,
            'recent_logins' => $recentLogins,
            'bkk_offices' => $offices,
            'metrics' => array_merge($kpi, [
                'total_users' => User::count(),
                'total_inspections' => $kpi['total'],
                'compliance_rate' => $kpi['compliance_rate'],
            ]),
            'users' => [
                'total' => User::count(),
                'roles' => class_exists(Role::class) ? Role::count() : 0,
                'activities' => Activity::count(),
            ],
        ]);
    }
}
