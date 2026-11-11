<?php

namespace App\Http\Controllers;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\KantorWilker;
use App\Models\ShipSanitationInspection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TravelConsentHubController extends Controller
{
    public const VALID_TABS = ['portal', 'aircraft', 'ship', 'alat', 'obat'];

    /**
     * Unified Multi-Tab Travel Consent Hub entrypoint.
     */
    public function index(Request $request): Response
    {
        return $this->renderTab($request);
    }

    public function portalTab(Request $request): Response
    {
        return $this->renderTab($request, 'portal');
    }

    public function aircraftTab(Request $request): Response
    {
        return $this->renderTab($request, 'aircraft');
    }

    public function shipTab(Request $request): Response
    {
        return $this->renderTab($request, 'ship');
    }

    public function alatTab(Request $request): Response
    {
        return $this->renderTab($request, 'alat');
    }

    public function obatTab(Request $request): Response
    {
        return $this->renderTab($request, 'obat');
    }

    /**
     * Render the unified Travel Consent view opened to the resolved tab.
     */
    public function renderTab(Request $request, ?string $forcedTab = null): Response
    {
        $activeTab = $forcedTab ?: (string) $request->query('tab', 'portal');
        if (!in_array($activeTab, self::VALID_TABS, true)) {
            $activeTab = 'portal';
        }

        $aircraftData = $this->buildAircraftData($request, $activeTab);
        $shipData = $this->buildShipData($request, $activeTab);
        $alatData = $this->buildFlightMedicalData($request, 'alat', $activeTab);
        $obatData = $this->buildFlightMedicalData($request, 'obat', $activeTab);

        return Inertia::render('TravelConsent/Index', [
            'active_tab' => $activeTab,
            'aircraft' => $aircraftData,
            'ship' => $shipData,
            'alat' => $alatData,
            'obat' => $obatData,
            'summary' => [
                'total_records' => $aircraftData['stats']['total']
                    + $shipData['stats']['total']
                    + $alatData['stats']['total']
                    + $obatData['stats']['total'],
                'pending_verification' => $aircraftData['stats']['pending_count']
                    + $shipData['stats']['pending_count']
                    + $alatData['stats']['submitted_count']
                    + $obatData['stats']['submitted_count'],
            ],
        ]);
    }

    /**
     * Resolve tab-scoped query param with unprefixed fallback for legacy routes.
     */
    protected function resolveFilter(Request $request, string $activeTab, string $tabName, string $key, mixed $default = null): mixed
    {
        $prefixed = $request->input("{$tabName}_{$key}");
        if ($prefixed !== null && $prefixed !== '') {
            return $prefixed;
        }

        if ($activeTab === $tabName) {
            return $request->input($key, $default);
        }

        return $default;
    }

    /**
     * Build Aircraft Sanitation Inspection tab dataset.
     */
    protected function buildAircraftData(Request $request, string $activeTab): array
    {
        $search = $this->resolveFilter($request, $activeTab, 'aircraft', 'search');
        $kesimpulan = $this->resolveFilter($request, $activeTab, 'aircraft', 'kesimpulan');
        $status = $this->resolveFilter($request, $activeTab, 'aircraft', 'status');
        $perPage = min(100, max(5, (int) $this->resolveFilter($request, $activeTab, 'aircraft', 'per_page', 10)));

        $query = AircraftSanitationInspection::with('verifier')
            ->latest('tanggal')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }
        if ($kesimpulan) {
            $query->filterKesimpulan($kesimpulan);
        }
        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($perPage, ['*'], 'page_aircraft')->withQueryString();

        $total = AircraftSanitationInspection::count();
        $layakCount = AircraftSanitationInspection::where('kesimpulan', 'layak')->count();
        $tidakLayakCount = AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count();
        $needActionCount = AircraftSanitationInspection::whereNotNull('tindakan')->count();
        $pendingCount = AircraftSanitationInspection::whereIn('status', ['submitted', 'verified'])->count();
        $todayCount = AircraftSanitationInspection::whereDate('tanggal', now()->toDateString())->count();
        $complianceRate = $total > 0 ? round(($layakCount / $total) * 100, 1) : 100;

        return [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'kesimpulan' => $kesimpulan,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'layak_count' => $layakCount,
                'tidak_layak_count' => $tidakLayakCount,
                'need_action_count' => $needActionCount,
                'pending_count' => $pendingCount,
                'today_count' => $todayCount,
                'compliance_rate' => $complianceRate,
            ],
        ];
    }

    /**
     * Build Ship Sanitation Inspection (SSCC / SSCEC) tab dataset.
     */
    protected function buildShipData(Request $request, string $activeTab): array
    {
        $search = $this->resolveFilter($request, $activeTab, 'ship', 'search');
        $pelabuhan = $this->resolveFilter($request, $activeTab, 'ship', 'pelabuhan');
        $sertifikat = $this->resolveFilter($request, $activeTab, 'ship', 'sertifikat');
        $status = $this->resolveFilter($request, $activeTab, 'ship', 'status');
        $perPage = min(100, max(5, (int) $this->resolveFilter($request, $activeTab, 'ship', 'per_page', 10)));

        $query = ShipSanitationInspection::with('verifier')
            ->latest('tanggal')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }
        if ($pelabuhan) {
            $query->filterPelabuhan($pelabuhan);
        }
        if ($sertifikat) {
            $query->filterSertifikat($sertifikat);
        }
        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($perPage, ['*'], 'page_ship')->withQueryString();

        $total = ShipSanitationInspection::count();
        $pembebasanCount = ShipSanitationInspection::where('sertifikat_mencatat', 'Pembebasan')->count();
        $pengendalianCount = ShipSanitationInspection::where('sertifikat_mencatat', 'Pengendalian')->count();
        $approvedCount = ShipSanitationInspection::where('status', 'approved')->count();
        $pendingCount = ShipSanitationInspection::whereIn('status', ['submitted', 'verified'])->count();
        $todayCount = ShipSanitationInspection::whereDate('tanggal', now()->toDateString())->count();

        try {
            $officialPorts = KantorWilker::laut()->pluck('nama')->toArray();
        } catch (\Throwable $e) {
            $officialPorts = collect(KantorWilker::DEFAULT_OFFICES)
                ->filter(fn ($o) => in_array($o['kode'], ['WLK-PTB-06', 'WLK-CBN-03', 'WLK-IDM-04', 'WLK-PLR-07', 'BKK-BDG-01'], true))
                ->pluck('nama')
                ->toArray();
        }

        $dbPorts = ShipSanitationInspection::whereNotNull('pelabuhan')
            ->distinct()
            ->pluck('pelabuhan')
            ->toArray();

        $ports = array_values(array_unique(array_filter(array_merge($officialPorts, $dbPorts))));

        return [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'pelabuhan' => $pelabuhan,
                'sertifikat' => $sertifikat,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'pembebasan_count' => $pembebasanCount,
                'pengendalian_count' => $pengendalianCount,
                'approved_count' => $approvedCount,
                'pending_count' => $pendingCount,
                'today_count' => $todayCount,
            ],
            'ports' => $ports,
        ];
    }

    /**
     * Build Flight Medical Inspection tab dataset parameterized by type ('alat' | 'obat').
     */
    protected function buildFlightMedicalData(Request $request, string $type, string $activeTab): array
    {
        $search = $this->resolveFilter($request, $activeTab, $type, 'search');
        $embarkasi = $this->resolveFilter($request, $activeTab, $type, 'embarkasi');
        $status = $this->resolveFilter($request, $activeTab, $type, 'status');
        $perPage = min(100, max(5, (int) $this->resolveFilter($request, $activeTab, $type, 'per_page', 10)));

        $query = FlightMedicalInspection::where('type', $type)
            ->with('verifier')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }
        if ($embarkasi) {
            $query->filterEmbarkasi($embarkasi);
        }
        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($perPage, ['*'], "page_{$type}")->withQueryString();

        $allRows = FlightMedicalInspection::where('type', $type)->get();
        $total = $allRows->count();
        $approvedCount = $allRows->where('status', 'approved')->count();
        $submittedCount = $allRows->where('status', 'submitted')->count();
        $distinctKloter = $allRows->pluck('nomor_kloter')->filter()->unique()->count();

        $totalItemsUsed = 0;
        foreach ($allRows as $row) {
            foreach (['items_p3k', 'items_emergensi'] as $field) {
                $items = $row->{$field};
                if (!is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    $totalItemsUsed += (int) ($item['terpakai'] ?? 0);
                }
            }
        }

        try {
            $officialEmbarkasi = KantorWilker::udara()->pluck('nama')->toArray();
        } catch (\Throwable $e) {
            $officialEmbarkasi = collect(KantorWilker::DEFAULT_OFFICES)
                ->filter(fn ($o) => in_array($o['kode'], ['POS-BDO-02', 'WLK-KJT-05', 'BKK-BDG-01'], true))
                ->pluck('nama')
                ->toArray();
        }

        $dbEmbarkasi = FlightMedicalInspection::where('type', $type)
            ->distinct()
            ->pluck('embarkasi')
            ->toArray();

        $embarkasiList = array_values(array_unique(array_filter(array_merge($officialEmbarkasi, $dbEmbarkasi))));

        return [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'embarkasi' => $embarkasi,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'approved_count' => $approvedCount,
                'submitted_count' => $submittedCount,
                'total_kloter' => $distinctKloter,
                'total_items_used' => $totalItemsUsed,
            ],
            'embarkasiList' => $embarkasiList,
        ];
    }
}

