<?php

namespace App\Http\Controllers;

use App\Models\KantorWilker;
use App\Models\ShipHealthSurveillance;
use App\Services\ShipSurveillanceImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShipHealthSurveillanceController extends Controller
{
    /**
     * Display data management and analytics dashboard for Ship Health Surveillance (MDH).
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $pelabuhan = $request->input('pelabuhan');
        $kategori = $request->input('kategori');
        $kresiko = $request->input('kresiko');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedMonth = (int)$request->input('bulan', date('n'));
        $selectedQuarter = (int)$request->input('triwulan', ceil(date('n') / 3));
        $selectedYear = (int)$request->input('tahun', date('Y'));

        $query = ShipHealthSurveillance::with('verifier')
            ->latest('tanggal_kedatangan')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }

        if ($pelabuhan) {
            $query->filterPelabuhan($pelabuhan);
        }

        if ($kategori) {
            $query->filterKategori($kategori);
        }

        if ($kresiko) {
            $query->filterKresiko($kresiko);
        }

        if ($status) {
            $query->filterStatus($status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_kedatangan', [$startDate, $endDate]);
        }

        $surveillances = $query->paginate($request->input('per_page', 10))->withQueryString();

        // Base KPI Summaries
        $totalKapal = ShipHealthSurveillance::count();
        $totalGt = ShipHealthSurveillance::sum('gross_tonnage');
        $totalPnbp = ShipHealthSurveillance::sum('estimasi_pnbp');
        $kapalDalamNegeri = ShipHealthSurveillance::where('kategori', 'Dalam Negeri')->count();
        $kapalLuarNegeri = ShipHealthSurveillance::where('kategori', 'Luar Negeri')->count();

        // Risk Levels Breakdown
        $kresikoTinggi = ShipHealthSurveillance::where('kresiko', 'TINGGI')->count();
        $kresikoSedang = ShipHealthSurveillance::where('kresiko', 'SEDANG')->count();
        $kresikoRendah = ShipHealthSurveillance::where('kresiko', 'RENDAH')->count();

        // Operational Status Breakdown
        $statusSandar = ShipHealthSurveillance::where('hasil_status', 'SANDAR')->count();
        $statusKarantina = ShipHealthSurveillance::where('hasil_status', 'ZONA KARANTINA')->count();
        $statusPending = ShipHealthSurveillance::where('status_verifikasi', 'pending')->count();
        $statusVerified = ShipHealthSurveillance::where('status_verifikasi', 'verified')->count();

        // PNBP Breakdown per Pelabuhan
        $pnbpPerPelabuhan = ShipHealthSurveillance::selectRaw('pelabuhan, COUNT(*) as jumlah_kapal, SUM(estimasi_pnbp) as total_pnbp, SUM(gross_tonnage) as total_gt')
            ->groupBy('pelabuhan')
            ->orderByDesc('total_pnbp')
            ->get()
            ->map(function ($row) use ($totalPnbp) {
                $percentage = $totalPnbp > 0 ? round(($row->total_pnbp / $totalPnbp) * 100, 1) : 0;
                return [
                    'pelabuhan' => $row->pelabuhan,
                    'jumlah_kapal' => (int)$row->jumlah_kapal,
                    'total_pnbp' => (float)$row->total_pnbp,
                    'total_gt' => (int)$row->total_gt,
                    'persentase' => $percentage,
                ];
            });

        // Top Verifikator Leaderboard (Kecualikan MOHAMAD DAHLAN FAZHRY)
        $topVerifikators = ShipHealthSurveillance::whereNotNull('verifier_name')
            ->where('status_verifikasi', 'verified')
            ->whereRaw("UPPER(TRIM(verifier_name)) NOT LIKE '%MOHAMAD DAHLAN FAZHRY%'")
            ->selectRaw('verifier_name, COUNT(*) as jumlah_verifikasi')
            ->groupBy('verifier_name')
            ->orderByDesc('jumlah_verifikasi')
            ->take(5)
            ->get();

        // Target Kinerja Kapal Luar Negeri (PK)
        // Bulanan (Target default: 10 kapal LN per bulan)
        $monthStartDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $monthEndDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
        $realisasiBulanan = ShipHealthSurveillance::where('kategori', 'Luar Negeri')
            ->whereBetween('tanggal_kedatangan', [$monthStartDate, $monthEndDate])
            ->count();
        $targetBulanan = 10;
        $persenBulanan = $targetBulanan > 0 ? min(100, round(($realisasiBulanan / $targetBulanan) * 100, 1)) : 0;

        // Triwulanan (Target default: 30 kapal LN per triwulan)
        $quarterStartMonth = ($selectedQuarter - 1) * 3 + 1;
        $quarterEndMonth = $selectedQuarter * 3;
        $quarterStartDate = \Carbon\Carbon::create($selectedYear, $quarterStartMonth, 1)->startOfMonth()->toDateString();
        $quarterEndDate = \Carbon\Carbon::create($selectedYear, $quarterEndMonth, 1)->endOfMonth()->toDateString();
        $realisasiTriwulanan = ShipHealthSurveillance::where('kategori', 'Luar Negeri')
            ->whereBetween('tanggal_kedatangan', [$quarterStartDate, $quarterEndDate])
            ->count();
        $targetTriwulan = 30;
        $persenTriwulan = $targetTriwulan > 0 ? min(100, round(($realisasiTriwulanan / $targetTriwulan) * 100, 1)) : 0;

        // Tahunan (Target default: 120 kapal LN per tahun)
        $yearStartDate = \Carbon\Carbon::create($selectedYear, 1, 1)->startOfYear()->toDateString();
        $yearEndDate = \Carbon\Carbon::create($selectedYear, 12, 31)->endOfYear()->toDateString();
        $realisasiTahunan = ShipHealthSurveillance::where('kategori', 'Luar Negeri')
            ->whereBetween('tanggal_kedatangan', [$yearStartDate, $yearEndDate])
            ->count();
        $targetTahunan = 120;
        $persenTahunan = $targetTahunan > 0 ? min(100, round(($realisasiTahunan / $targetTahunan) * 100, 1)) : 0;

        // Available Ports List (Master Data & Observed DB)
        try {
            $officialPorts = KantorWilker::laut()->pluck('nama')->toArray();
        } catch (\Throwable $e) {
            $officialPorts = [
                'Wilayah Kerja Patimban',
                'Wilayah Kerja Cirebon',
                'Wilayah Kerja Indramayu',
                'Wilayah Kerja Palabuhanratu',
            ];
        }

        $dbPorts = ShipHealthSurveillance::whereNotNull('pelabuhan')
            ->distinct()
            ->pluck('pelabuhan')
            ->toArray();

        $ports = array_values(array_unique(array_filter(array_merge($officialPorts, $dbPorts))));

        return Inertia::render('ShipSurveillance/Index', [
            'surveillances' => $surveillances,
            'filters' => [
                'search' => $search,
                'pelabuhan' => $pelabuhan,
                'kategori' => $kategori,
                'kresiko' => $kresiko,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'bulan' => $selectedMonth,
                'triwulan' => $selectedQuarter,
                'tahun' => $selectedYear,
            ],
            'ports' => $ports,
            'summary' => [
                'total_kapal' => $totalKapal,
                'total_gt' => $totalGt,
                'total_pnbp' => $totalPnbp,
                'kapal_dalam_negeri' => $kapalDalamNegeri,
                'kapal_luar_negeri' => $kapalLuarNegeri,
                'kresiko_tinggi' => $kresikoTinggi,
                'kresiko_sedang' => $kresikoSedang,
                'kresiko_rendah' => $kresikoRendah,
                'status_sandar' => $statusSandar,
                'status_karantina' => $statusKarantina,
                'status_pending' => $statusPending,
                'status_verified' => $statusVerified,
            ],
            'pnbp_per_pelabuhan' => $pnbpPerPelabuhan,
            'top_verifikators' => $topVerifikators,
            'kpi_targets' => [
                'bulanan' => [
                    'bulan' => $selectedMonth,
                    'realisasi' => $realisasiBulanan,
                    'target' => $targetBulanan,
                    'persentase' => $persenBulanan,
                ],
                'triwulanan' => [
                    'triwulan' => $selectedQuarter,
                    'realisasi' => $realisasiTriwulanan,
                    'target' => $targetTriwulan,
                    'persentase' => $persenTriwulan,
                ],
                'tahunan' => [
                    'tahun' => $selectedYear,
                    'realisasi' => $realisasiTahunan,
                    'target' => $targetTahunan,
                    'persentase' => $persenTahunan,
                ],
            ],
            'quarantine_questions' => ShipHealthSurveillance::QUARANTINE_QUESTIONS,
        ]);
    }

    /**
     * Get detailed record data for modal.
     */
    public function show(int $id): JsonResponse
    {
        $item = ShipHealthSurveillance::with('verifier')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * AJAX endpoint to update Gross Tonnage inline and recalculate PNBP.
     */
    public function updateGrossTonnage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'gross_tonnage' => 'required|numeric|min:0',
        ]);

        $item = ShipHealthSurveillance::findOrFail($id);
        $gt = (int)$request->input('gross_tonnage');
        $pnbp = ShipHealthSurveillance::calculatePnbp($gt);

        $item->update([
            'gross_tonnage' => $gt,
            'estimasi_pnbp' => $pnbp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gross Tonnage dan estimasi PNBP berhasil diperbarui.',
            'data' => [
                'id' => $item->id,
                'gross_tonnage' => $item->gross_tonnage,
                'estimasi_pnbp' => $item->estimasi_pnbp,
                'formatted_pnbp' => 'Rp ' . number_format($item->estimasi_pnbp, 0, ',', '.'),
            ],
        ]);
    }

    /**
     * Verify or update quarantine status (SANDAR / ZONA KARANTINA).
     */
    public function verify(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'hasil_status' => 'required|in:SANDAR,ZONA KARANTINA',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $item = ShipHealthSurveillance::findOrFail($id);
        $user = $request->user();

        $item->update([
            'hasil_status' => $request->input('hasil_status'),
            'status_verifikasi' => 'verified',
            'catatan_verifikasi' => $request->input('catatan_verifikasi'),
            'verified_by' => $user?->id,
            'verifier_name' => $user?->name ?? 'Petugas Verifikator BKK',
            'verified_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengawasan kapal berhasil diverifikasi sebagai ' . $item->hasil_status . '.',
                'data' => $item,
            ]);
        }

        return back()->with('success', 'Status pengawasan kapal ' . $item->nama_kapal . ' berhasil diverifikasi sebagai ' . $item->hasil_status . '.');
    }

    /**
     * Delete a surveillance record.
     */
    public function destroy(int $id): RedirectResponse
    {
        $item = ShipHealthSurveillance::findOrFail($id);
        $nama = $item->nama_kapal;
        $item->delete();

        return back()->with('success', "Data pengawasan kapal {$nama} berhasil dihapus.");
    }

    /**
     * Export surveillance records to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'Laporan_Pengawasan_Kapal_' . date('Ymd_His') . '.csv';

        $query = ShipHealthSurveillance::latest('tanggal_kedatangan');

        if ($request->filled('pelabuhan')) {
            $query->filterPelabuhan($request->input('pelabuhan'));
        }
        if ($request->filled('kategori')) {
            $query->filterKategori($request->input('kategori'));
        }
        if ($request->filled('kresiko')) {
            $query->filterKresiko($request->input('kresiko'));
        }
        if ($request->filled('status')) {
            $query->filterStatus($request->input('status'));
        }

        $records = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'No',
                'No Registrasi',
                'Tanggal Kedatangan',
                'Nama Kapal',
                'Pelabuhan Asal',
                'Pelabuhan Tujuan',
                'Negara Asal',
                'Gross Tonnage (GT)',
                'Jumlah Crew',
                'Kategori',
                'Skor Risiko',
                'Tingkat Risiko',
                'Keputusan Karantina',
                'Estimasi PNBP (Rp)',
                'Status Verifikasi',
                'Petugas Verifikator',
                'Catatan Verifikasi',
            ]);

            $no = 1;
            foreach ($records as $row) {
                fputcsv($handle, [
                    $no++,
                    $row->registration_no,
                    $row->tanggal_kedatangan?->format('d/m/Y') ?? '-',
                    $row->nama_kapal,
                    $row->datang_dari,
                    $row->pelabuhan,
                    $row->origin_country ?? '-',
                    $row->gross_tonnage,
                    $row->jml_crew,
                    $row->kategori,
                    $row->skoring,
                    $row->kresiko,
                    $row->hasil_status,
                    $row->estimasi_pnbp,
                    $row->status_verifikasi,
                    $row->verifier_name ?? '-',
                    $row->catatan_verifikasi ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show public landing portal for Ship Health Quarantine Assessment (TANGKAP).
     */
    public function publicPortal()
    {
        return view('ship_surveillance.portal');
    }

    /**
     * Show public domestic ship health quarantine assessment form.
     */
    public function publicDomestic()
    {
        return view('ship_surveillance.form_domestic');
    }

    /**
     * Show public international (English) ship health quarantine assessment form.
     */
    public function publicInternational()
    {
        return view('ship_surveillance.form_international');
    }

    /**
     * Handle submission of ship health quarantine assessment form.
     */
    public function submitAssessment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kapal' => ['required', 'string', 'max:255'],
            'datang_dari' => ['required', 'string', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:100'],
            'pelabuhan' => ['required', 'string', 'max:255'],
            'tanggal_kedatangan' => ['required', 'date'],
            'gross_tonnage' => ['required', 'numeric', 'min:0'],
            'jml_crew' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'in:Dalam Negeri,Luar Negeri'],
            'skoring' => ['nullable', 'integer'],
            'kresiko' => ['nullable', 'string', 'max:50'],
            'hasil_status' => ['nullable', 'string', 'max:50'],
            'surat_pernyataan_nahkoda' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'checklist_dokumen' => ['nullable', 'array'],
        ]);

        // Upload Master's statement if present
        $suratPath = null;
        if ($request->hasFile('surat_pernyataan_nahkoda')) {
            $suratPath = $request->file('surat_pernyataan_nahkoda')->store('surveillance_docs', 'public');
        }

        // Collect question answers (Q1 through Q19)
        $questions = [];
        for ($i = 1; $i <= 19; $i++) {
            $key = "Q{$i}";
            if ($request->has($key)) {
                $questions[$key] = $request->input($key);
            }
        }

        // Calculate score, risk, and status dynamically
        $score = (int)$request->input('skoring', 0);
        $riskData = ShipHealthSurveillance::calculateRisk($score);
        $gt = (int)$validated['gross_tonnage'];
        $pnbp = ShipHealthSurveillance::calculatePnbp($gt);

        $regNo = 'MDH-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));

        $record = ShipHealthSurveillance::create([
            'registration_no' => $regNo,
            'nama_kapal' => strtoupper(trim($validated['nama_kapal'])),
            'datang_dari' => strtoupper(trim($validated['datang_dari'])),
            'origin_country' => $validated['origin_country'] ?? null,
            'pelabuhan' => $validated['pelabuhan'],
            'tanggal_kedatangan' => $validated['tanggal_kedatangan'],
            'gross_tonnage' => $gt,
            'jml_crew' => (int)$validated['jml_crew'],
            'kategori' => $validated['kategori'],
            'questions' => $questions,
            'skoring' => $score,
            'kresiko' => $riskData['kresiko'],
            'hasil_status' => $riskData['hasil_status'],
            'estimasi_pnbp' => $pnbp,
            'surat_pernyataan_nahkoda' => $suratPath,
            'dokumen_pendukung' => $request->input('checklist_dokumen', []),
            'status_verifikasi' => 'pending',
        ]);

        return redirect()->route('ship-surveillance.receipt', $record->registration_no)
            ->with('success', 'Formulir Karantina Kesehatan Kapal berhasil dikirim. Nomor Registrasi: ' . $record->registration_no);
    }

    /**
     * Show official receipt page for ship health surveillance assessment.
     */
    public function receipt(string $registration_no)
    {
        $surveillance = ShipHealthSurveillance::where('registration_no', $registration_no)->firstOrFail();

        return view('ship_surveillance.receipt', [
            'surveillance' => $surveillance,
        ]);
    }

    /**
     * Test connection to Simply MySQL database and return statistics.
     */
    public function testImportConnection(Request $request, ShipSurveillanceImportService $service): JsonResponse
    {
        $validated = $request->validate([
            'host' => 'nullable|string',
            'port' => 'nullable|numeric',
            'database' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'table' => 'nullable|string',
        ]);

        $result = $service->testConnection($validated);

        return response()->json($result);
    }

    /**
     * Execute direct database migration from Simply MySQL to Sadaya.
     */
    public function importDirect(Request $request, ShipSurveillanceImportService $service): JsonResponse
    {
        $config = $request->only(['host', 'port', 'database', 'username', 'password', 'table']);
        $options = [
            'skip_duplicate' => (bool)$request->input('skip_duplicate', true),
            'recalculate_pnbp' => (bool)$request->input('recalculate_pnbp', true),
            'normalize_ports' => (bool)$request->input('normalize_ports', true),
            'truncate_first' => (bool)$request->input('truncate_first', false),
        ];

        $result = $service->importFromMysql($config, $options);

        return response()->json($result);
    }

    /**
     * Import records from an uploaded backup file (.sql, .json, .csv).
     */
    public function importFile(Request $request, ShipSurveillanceImportService $service): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $options = [
            'skip_duplicate' => (bool)$request->input('skip_duplicate', true),
            'recalculate_pnbp' => (bool)$request->input('recalculate_pnbp', true),
            'normalize_ports' => (bool)$request->input('normalize_ports', true),
            'truncate_first' => (bool)$request->input('truncate_first', false),
        ];

        $result = $service->importFromFile($request->file('file'), $options);

        return response()->json($result);
    }
}

