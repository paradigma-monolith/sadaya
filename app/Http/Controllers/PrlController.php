<?php

namespace App\Http\Controllers;

use App\Http\Requests\Prl\StoreHsbuRequest;
use App\Http\Requests\Prl\StoreInspeksiHsbuRequest;
use App\Http\Requests\Prl\StoreInspeksiSabRequest;
use App\Http\Requests\Prl\StoreInspeksiTppRequest;
use App\Http\Requests\Prl\StoreSabRequest;
use App\Http\Requests\Prl\StoreTppRequest;
use App\Http\Requests\Sivera\StoreSiveraRequest;
use App\Http\Requests\Sivera\UpdatePengendalianSiveraRequest;
use App\Models\KantorWilker;
use App\Models\PrlHsbu;
use App\Models\PrlInspeksiHsbu;
use App\Models\PrlInspeksiSab;
use App\Models\PrlInspeksiTpp;
use App\Models\PrlSab;
use App\Models\PrlTpp;
use App\Models\User;
use App\Services\SimplyDataMigrationService;
use App\Services\SiveraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrlController extends Controller
{
    /**
     * Entrypoint for backward compatibility. Dispatches to Sanitasi Lingkungan or SIVERA.
     */
    public function index(Request $request): Response
    {
        $tab = $request->query('tab');
        if ($tab === 'sivera' || $request->is('*/sivera*')) {
            return $this->siveraIndex($request);
        }

        return $this->sanitasiIndex($request);
    }

    /**
     * Display the Sanitasi Lingkungan portal (TPP, SAB, HSBU).
     */
    public function sanitasiIndex(Request $request): Response
    {
        $activeTab = $request->query('tab', 'tpp');
        if ($activeTab === 'overview' || $activeTab === 'sivera') {
            $activeTab = 'tpp';
        }
        $search = $request->query('search');
        $wilker = $request->query('wilker');
        $tingkatRisiko = $request->query('tingkat_risiko');
        $tglMulai = $request->query('tgl_mulai', $request->query('tanggal_mulai'));
        $tglSelesai = $request->query('tgl_selesai', $request->query('tanggal_selesai'));
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        // Available Wilker List
        try {
            $officialWilkers = KantorWilker::pluck('nama')->toArray();
        } catch (\Throwable $e) {
            $officialWilkers = [];
        }
        $observedWilkers = array_merge(
            PrlTpp::distinct()->pluck('wilker')->toArray(),
            PrlSab::distinct()->pluck('wilker')->toArray(),
            PrlHsbu::distinct()->pluck('wilker')->toArray()
        );
        $wilkerOptions = array_values(array_unique(array_filter(array_merge($officialWilkers, $observedWilkers))));

        // Global Statistics
        $totalTpp = PrlTpp::count();
        $totalSab = PrlSab::count();
        $totalHsbu = PrlHsbu::count();
        $totalSarana = $totalTpp + $totalSab + $totalHsbu;

        $totalInspTpp = PrlInspeksiTpp::count();
        $totalInspSab = PrlInspeksiSab::count();
        $totalInspHsbu = PrlInspeksiHsbu::count();
        $totalInspeksi = $totalInspTpp + $totalInspSab + $totalInspHsbu;

        $risikoRendah = PrlInspeksiTpp::where('tingkat_risiko', 'Rendah')->count()
            + PrlInspeksiSab::where('tingkat_risiko', 'Rendah')->count()
            + PrlInspeksiHsbu::where('tingkat_risiko', 'Rendah')->count();

        $risikoSedang = PrlInspeksiTpp::where('tingkat_risiko', 'Sedang')->count()
            + PrlInspeksiSab::where('tingkat_risiko', 'Sedang')->count()
            + PrlInspeksiHsbu::where('tingkat_risiko', 'Sedang')->count();

        $risikoTinggi = PrlInspeksiTpp::where('tingkat_risiko', 'Tinggi')->count()
            + PrlInspeksiSab::where('tingkat_risiko', 'Tinggi')->count()
            + PrlInspeksiHsbu::where('tingkat_risiko', 'Tinggi')->count();

        $kepatuhanPersen = $totalInspeksi > 0 ? round(($risikoRendah / $totalInspeksi) * 100, 1) : 0;

        // Wilker breakdown
        $wilkerStats = [];
        foreach ($wilkerOptions as $wk) {
            $cntTpp = PrlTpp::where('wilker', $wk)->count();
            $cntSab = PrlSab::where('wilker', $wk)->count();
            $cntHsbu = PrlHsbu::where('wilker', $wk)->count();
            $cntInsp = PrlInspeksiTpp::where('wilker', $wk)->count()
                + PrlInspeksiSab::where('wilker', $wk)->count()
                + PrlInspeksiHsbu::where('wilker', $wk)->count();

            if (($cntTpp + $cntSab + $cntHsbu + $cntInsp) > 0) {
                $wilkerStats[] = [
                    'wilker' => $wk,
                    'total_sarana' => $cntTpp + $cntSab + $cntHsbu,
                    'total_inspeksi' => $cntInsp,
                    'tpp' => $cntTpp,
                    'sab' => $cntSab,
                    'hsbu' => $cntHsbu,
                ];
            }
        }

        // Per-page settings from request (default 25)
        $perPageTpp = min(1000, max(5, (int) $request->input('per_page_tpp', 25)));
        $perPageSab = min(1000, max(5, (int) $request->input('per_page_sab', 25)));
        $perPageHsbu = min(1000, max(5, (int) $request->input('per_page_hsbu', 25)));

        // 1. TPP Inspections Query
        $qTpp = PrlInspeksiTpp::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qTpp->where(function ($q) use ($search) {
                $q->where('nama_tpp', 'like', "%{$search}%")
                    ->orWhere('penanggung_jawab_tpp', 'like', "%{$search}%")
                    ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) {
            $qTpp->where('wilker', $wilker);
        }
        if ($tingkatRisiko) {
            $qTpp->where('tingkat_risiko', $tingkatRisiko);
        }
        if ($tglMulai) {
            $qTpp->whereDate('tanggal_inspeksi', '>=', $tglMulai);
        }
        if ($tglSelesai) {
            $qTpp->whereDate('tanggal_inspeksi', '<=', $tglSelesai);
        }
        if ($tahun) {
            $qTpp->whereYear('tanggal_inspeksi', $tahun);
        }
        if ($bulan) {
            $qTpp->whereMonth('tanggal_inspeksi', $bulan);
        }
        $inspeksiTpp = $qTpp->paginate($perPageTpp, ['*'], 'page_tpp')->withQueryString();

        // 2. SAB Inspections Query
        $qSab = PrlInspeksiSab::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qSab->where(function ($q) use ($search) {
                $q->where('nama_sab', 'like', "%{$search}%")
                    ->orWhere('penanggung_jawab_sab', 'like', "%{$search}%")
                    ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) {
            $qSab->where('wilker', $wilker);
        }
        if ($tingkatRisiko) {
            $qSab->where('tingkat_risiko', $tingkatRisiko);
        }
        if ($tglMulai) {
            $qSab->whereDate('tanggal_inspeksi', '>=', $tglMulai);
        }
        if ($tglSelesai) {
            $qSab->whereDate('tanggal_inspeksi', '<=', $tglSelesai);
        }
        if ($tahun) {
            $qSab->whereYear('tanggal_inspeksi', $tahun);
        }
        if ($bulan) {
            $qSab->whereMonth('tanggal_inspeksi', $bulan);
        }
        $inspeksiSab = $qSab->paginate($perPageSab, ['*'], 'page_sab')->withQueryString();

        // 3. HSBU Inspections Query
        $qHsbu = PrlInspeksiHsbu::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qHsbu->where(function ($q) use ($search) {
                $q->where('nama_gedung', 'like', "%{$search}%")
                    ->orWhere('penanggung_jawab_hsbu', 'like', "%{$search}%")
                    ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) {
            $qHsbu->where('wilker', $wilker);
        }
        if ($tingkatRisiko) {
            $qHsbu->where('tingkat_risiko', $tingkatRisiko);
        }
        if ($tglMulai) {
            $qHsbu->whereDate('tanggal_inspeksi', '>=', $tglMulai);
        }
        if ($tglSelesai) {
            $qHsbu->whereDate('tanggal_inspeksi', '<=', $tglSelesai);
        }
        if ($tahun) {
            $qHsbu->whereYear('tanggal_inspeksi', $tahun);
        }
        if ($bulan) {
            $qHsbu->whereMonth('tanggal_inspeksi', $bulan);
        }
        $inspeksiHsbu = $qHsbu->paginate($perPageHsbu, ['*'], 'page_hsbu')->withQueryString();

        // Master Sarana Lists (for quick lookup / registers / forms)
        $masterTpp = PrlTpp::orderBy('nama_tpp')->get(['id', 'nama_tpp', 'wilker', 'penanggung_jawab', 'jml_karyawan', 'kapasitas_produksi']);
        $masterSab = PrlSab::orderBy('nama_sab')->get(['id', 'nama_sab', 'wilker', 'penanggung_jawab', 'penanggung_jawab_no_hp', 'sumber_air', 'kapasitas_penampungan']);
        $masterHsbu = PrlHsbu::orderBy('nama_gedung')->get(['id', 'nama_gedung', 'wilker', 'penanggung_jawab', 'jml_karyawan']);

        // Pemeriksa / Inspector Options
        $usersList = User::orderBy('name')->pluck('name')->toArray();
        $inspList = array_merge(
            PrlInspeksiTpp::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray(),
            PrlInspeksiSab::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray(),
            PrlInspeksiHsbu::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray()
        );
        $pemeriksaAgg = [];
        foreach (array_merge($usersList, $inspList) as $raw) {
            foreach (preg_split('/[,|]/', (string) $raw) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '' && ! is_numeric($trimmed)) {
                    $pemeriksaAgg[$trimmed] = true;
                }
            }
        }
        $pemeriksaOptions = array_keys($pemeriksaAgg);
        sort($pemeriksaOptions);

        return Inertia::render('SanitasiLingkungan/Index', [
            'active_tab' => $activeTab,
            'filters' => [
                'search' => $search,
                'wilker' => $wilker,
                'tingkat_risiko' => $tingkatRisiko,
                'tgl_mulai' => $tglMulai,
                'tgl_selesai' => $tglSelesai,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'per_page_tpp' => $perPageTpp,
                'per_page_sab' => $perPageSab,
                'per_page_hsbu' => $perPageHsbu,
            ],
            'wilker_options' => $wilkerOptions,
            'pemeriksa_options' => $pemeriksaOptions,
            'stats' => [
                'total_sarana' => $totalSarana,
                'total_inspeksi' => $totalInspeksi,
                'total_tpp' => $totalTpp,
                'total_sab' => $totalSab,
                'total_hsbu' => $totalHsbu,
                'total_insp_tpp' => $totalInspTpp,
                'total_insp_sab' => $totalInspSab,
                'total_insp_hsbu' => $totalInspHsbu,
                'risiko_rendah' => $risikoRendah,
                'risiko_sedang' => $risikoSedang,
                'risiko_tinggi' => $risikoTinggi,
                'kepatuhan_persen' => $kepatuhanPersen,
                'wilker_stats' => $wilkerStats,
            ],
            'inspeksi_tpp' => $inspeksiTpp,
            'inspeksi_sab' => $inspeksiSab,
            'inspeksi_hsbu' => $inspeksiHsbu,
            'master_tpp' => $masterTpp,
            'master_sab' => $masterSab,
            'master_hsbu' => $masterHsbu,
        ]);
    }

    /**
     * Display the dedicated SIVERA (Surveilans Vektor dan Reservoar) portal.
     */
    public function siveraIndex(Request $request): Response
    {
        return app(SiveraController::class)->index($request);
    }

    public function exportSiveraExcel(Request $request): StreamedResponse
    {
        return app(SiveraController::class)->exportExcel();
    }

    public function destroySiveraRecord(Request $request, int $id): JsonResponse
    {
        return app(SiveraController::class)->destroy($id);
    }

    /**
     * Export PRL data to CSV/Excel format.
     */
    public function exportExcel(Request $request, string $type): StreamedResponse
    {
        if ($type === 'sivera') {
            return app(SiveraService::class)->streamCsvExport();
        }

        $filename = 'Export_PRL_'.strtoupper($type).'_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'tpp') {
                fputcsv($handle, ['No', 'Wilayah Kerja', 'Tanggal Inspeksi', 'Nama TPP', 'Penanggung Jawab', 'Jml Karyawan', 'Kapasitas Produksi', 'Petugas Pemeriksa', 'Total Skor', 'Tingkat Risiko', 'Kesimpulan', 'Saran / Tindakan']);
                $nomor = 1;
                foreach (PrlInspeksiTpp::latest('tanggal_inspeksi')->cursor() as $r) {
                    fputcsv($handle, [
                        $nomor++,
                        $r->wilker,
                        $r->tanggal_inspeksi?->format('d/m/Y') ?: '-',
                        $r->nama_tpp,
                        $r->penanggung_jawab_tpp,
                        $r->jml_karyawan_tpp,
                        $r->kapasitas_produksi_tpp,
                        $r->nama_pemeriksa,
                        $r->hasil,
                        $r->tingkat_risiko,
                        $r->kesimpulan,
                        $r->saran_rencana_tindakan,
                    ]);
                }
            } elseif ($type === 'sab') {
                fputcsv($handle, ['No', 'Wilayah Kerja', 'Tanggal Inspeksi', 'Nama SAB', 'Penanggung Jawab', 'No HP', 'Sumber Air', 'Kapasitas Penampungan', 'Petugas Pemeriksa', 'Total Skor', 'Tingkat Risiko', 'Saran / Tindakan']);
                $nomor = 1;
                foreach (PrlInspeksiSab::latest('tanggal_inspeksi')->cursor() as $r) {
                    fputcsv($handle, [
                        $nomor++,
                        $r->wilker,
                        $r->tanggal_inspeksi?->format('d/m/Y') ?: '-',
                        $r->nama_sab,
                        $r->penanggung_jawab_sab,
                        $r->penanggung_jawab_no_hp_sab,
                        $r->sumber_air_sab,
                        $r->kapasitas_penampungan_sab,
                        $r->nama_pemeriksa,
                        $r->hasil,
                        $r->tingkat_risiko,
                        $r->saran_rencana_tindakan,
                    ]);
                }
            } elseif ($type === 'hsbu') {
                fputcsv($handle, ['No', 'Wilayah Kerja', 'Tanggal Inspeksi', 'Nama Gedung/Bangunan', 'Penanggung Jawab', 'Jml Karyawan', 'Petugas Pemeriksa', 'Total Skor', 'Tingkat Risiko', 'Saran / Tindakan', 'Catatan']);
                $nomor = 1;
                foreach (PrlInspeksiHsbu::latest('tanggal_inspeksi')->cursor() as $r) {
                    fputcsv($handle, [
                        $nomor++,
                        $r->wilker,
                        $r->tanggal_inspeksi?->format('d/m/Y') ?: '-',
                        $r->nama_gedung,
                        $r->penanggung_jawab_hsbu,
                        $r->jml_karyawan_hsbu,
                        $r->nama_pemeriksa,
                        $r->total,
                        $r->tingkat_risiko,
                        $r->saran_rencana_tindakan,
                        $r->catatan,
                    ]);
                }
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Print official inspection certificate / report in PDF format.
     */
    public function printPdf(Request $request, string $type, int $id)
    {
        if ($type === 'sivera') {
            return $this->printSiveraPdf($request, $id);
        }

        if ($type === 'tpp') {
            $item = PrlInspeksiTpp::findOrFail($id);
            $title = 'Laporan Inspeksi Higiene Sanitasi Pangan (TPP)';
        } elseif ($type === 'sab') {
            $item = PrlInspeksiSab::findOrFail($id);
            $title = 'Laporan Inspeksi Sanitasi Sarana Air Bersih (SAB)';
        } elseif ($type === 'hsbu') {
            $item = PrlInspeksiHsbu::findOrFail($id);
            $title = 'Laporan Inspeksi Higiene Sanitasi Bangunan dan Usaha (HSBU)';
        } else {
            abort(404, 'Jenis dokumen tidak dikenali.');
        }

        $pdf = Pdf::loadView('pdf.prl_inspeksi', [
            'type' => $type,
            'title' => $title,
            'item' => $item,
            'kop' => SiveraService::getKopSuratBase64(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Inspeksi_PRL_{$type}_{$id}.pdf");
    }

    /**
     * Backward-compatibility delegates for SIVERA PDF endpoints.
     */
    public function printSiveraPdf(Request $request, int $id, ?string $tipe = null)
    {
        return app(SiveraController::class)->printPdf($request, $id, $tipe);
    }

    public function printSiveraJentikPdf(Request $request, int $id, ?string $tipe = 'jentik')
    {
        return app(SiveraController::class)->printJentikPdf($request, $id, $tipe);
    }

    public function printSiveraLalatPdf(Request $request, int $id)
    {
        return app(SiveraController::class)->printLalatPdf($request, $id);
    }

    /**
     * Store new master TPP sarana.
     */
    public function storeTpp(StoreTppRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['created_by_name'] = $request->user()?->name;
        $tpp = PrlTpp::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Sarana TPP berhasil disimpan.',
            'data' => $tpp,
        ]);
    }

    /**
     * Store new master SAB sarana.
     */
    public function storeSab(StoreSabRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['created_by_name'] = $request->user()?->name;
        $sab = PrlSab::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Sarana Air Bersih (SAB) berhasil disimpan.',
            'data' => $sab,
        ]);
    }

    /**
     * Store new master HSBU sarana.
     */
    public function storeHsbu(StoreHsbuRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['created_by_name'] = $request->user()?->name;
        $hsbu = PrlHsbu::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Gedung / Bangunan HSBU berhasil disimpan.',
            'data' => $hsbu,
        ]);
    }

    /**
     * Store new Inspeksi TPP record.
     */
    public function storeInspeksiTpp(StoreInspeksiTppRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['prl_tpp_id'])) {
            $tpp = PrlTpp::firstOrCreate(
                ['nama_tpp' => $validated['nama_tpp'], 'wilker' => $validated['wilker']],
                [
                    'penanggung_jawab' => $validated['penanggung_jawab_tpp'] ?? null,
                    'jml_karyawan' => $validated['jml_karyawan_tpp'] ?? null,
                    'kapasitas_produksi' => $validated['kapasitas_produksi_tpp'] ?? null,
                    'created_by_name' => $request->user()?->name,
                ]
            );
            $validated['prl_tpp_id'] = $tpp->id;
        }

        $inspeksi = PrlInspeksiTpp::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Inspeksi Higiene Sanitasi TPP berhasil disimpan.',
            'data' => $inspeksi,
        ]);
    }

    /**
     * Store new Inspeksi SAB record.
     */
    public function storeInspeksiSab(StoreInspeksiSabRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['prl_sab_id'])) {
            $sab = PrlSab::firstOrCreate(
                ['nama_sab' => $validated['nama_sab'], 'wilker' => $validated['wilker']],
                [
                    'penanggung_jawab' => $validated['penanggung_jawab_sab'] ?? null,
                    'penanggung_jawab_no_hp' => $validated['penanggung_jawab_no_hp_sab'] ?? null,
                    'sumber_air' => $validated['sumber_air_sab'] ?? null,
                    'kapasitas_penampungan' => $validated['kapasitas_penampungan_sab'] ?? null,
                    'created_by_name' => $request->user()?->name,
                ]
            );
            $validated['prl_sab_id'] = $sab->id;
        }

        $inspeksi = PrlInspeksiSab::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Inspeksi Kualitas Sanitasi SAB berhasil disimpan.',
            'data' => $inspeksi,
        ]);
    }

    /**
     * Store new Inspeksi HSBU record.
     */
    public function storeInspeksiHsbu(StoreInspeksiHsbuRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['prl_hsbu_id'])) {
            $hsbu = PrlHsbu::firstOrCreate(
                ['nama_gedung' => $validated['nama_gedung'], 'wilker' => $validated['wilker']],
                [
                    'penanggung_jawab' => $validated['penanggung_jawab_hsbu'] ?? null,
                    'jml_karyawan' => $validated['jml_karyawan_hsbu'] ?? null,
                    'created_by_name' => $request->user()?->name,
                ]
            );
            $validated['prl_hsbu_id'] = $hsbu->id;
        }

        $inspeksi = PrlInspeksiHsbu::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Inspeksi Higiene Sanitasi Gedung HSBU berhasil disimpan.',
            'data' => $inspeksi,
        ]);
    }

    public function tppTab(Request $request): Response
    {
        $request->merge(['tab' => 'tpp']);

        return $this->sanitasiIndex($request);
    }

    public function sabTab(Request $request): Response
    {
        $request->merge(['tab' => 'sab']);

        return $this->sanitasiIndex($request);
    }

    public function hsbuTab(Request $request): Response
    {
        $request->merge(['tab' => 'hsbu']);

        return $this->sanitasiIndex($request);
    }

    public function siveraTab(Request $request): Response
    {
        return $this->siveraIndex($request);
    }

    /**
     * Store new SIVERA Vektor record (backward-compatible delegate).
     */
    public function storeSivera(StoreSiveraRequest $request): JsonResponse
    {
        return app(SiveraController::class)->store($request);
    }

    /**
     * Update an existing SIVERA record (backward-compatible delegate).
     */
    public function updateSivera(StoreSiveraRequest $request, int $id): JsonResponse
    {
        return app(SiveraController::class)->update($request, $id);
    }

    /**
     * Update tindakan pengendalian for SIVERA record (backward-compatible delegate).
     */
    public function updatePengendalianSivera(UpdatePengendalianSiveraRequest $request, int $id): JsonResponse
    {
        return app(SiveraController::class)->updatePengendalian($request, $id);
    }

    /**
     * Delete an inspection record.
     */
    public function destroyInspeksi(Request $request, string $type, int $id): JsonResponse
    {
        if ($type === 'sivera') {
            return app(SiveraController::class)->destroy($id);
        }

        $deleted = false;
        if ($type === 'tpp') {
            $deleted = PrlInspeksiTpp::findOrFail($id)->delete();
        } elseif ($type === 'sab') {
            $deleted = PrlInspeksiSab::findOrFail($id)->delete();
        } elseif ($type === 'hsbu') {
            $deleted = PrlInspeksiHsbu::findOrFail($id)->delete();
        }

        return response()->json([
            'success' => (bool) $deleted,
            'message' => 'Data riwayat inspeksi berhasil dihapus.',
        ]);
    }

    /**
     * Delete a master sarana record.
     */
    public function destroySarana(Request $request, string $type, int $id): JsonResponse
    {
        $deleted = false;
        if ($type === 'tpp') {
            $deleted = PrlTpp::findOrFail($id)->delete();
        } elseif ($type === 'sab') {
            $deleted = PrlSab::findOrFail($id)->delete();
        } elseif ($type === 'hsbu') {
            $deleted = PrlHsbu::findOrFail($id)->delete();
        }

        return response()->json([
            'success' => (bool) $deleted,
            'message' => 'Data master sarana berhasil dihapus.',
        ]);
    }

    /**
     * Test connection to MySQL Simply for PRL module.
     */
    public function testSimplyConnection(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $payload = $request->all();
        $targetModule = $request->input('target_module', 'prl_sivera');
        $res = $service->testModule($targetModule, $payload);

        return response()->json([
            'success' => ($res['connected'] && $res['table_exists']),
            'message' => ($res['connected'] && $res['table_exists'])
                ? "Koneksi ke modul {$res['name']} berhasil ({$res['count']} baris)."
                : ($res['error'] ?? 'Gagal terhubung ke basis data Simply MySQL'),
            'modules' => [$targetModule => $res],
        ]);
    }

    /**
     * Run PRL data migration from Simply CI4.
     */
    public function syncFromSimply(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $modules = $request->input('modules', ['prl_tpp', 'prl_sab', 'prl_hsbu', 'prl_sivera']);
        if (is_string($modules)) {
            $modules = [$modules];
        }

        $credentials = $request->all();
        $options = [
            'skip_duplicate' => (bool) $request->input('skip_duplicate', true),
            'truncate_first' => (bool) $request->input('truncate_first', false),
            'dry_run' => (bool) $request->input('dry_run', false),
        ];

        // Privilege check: Truncate requires Super Admin
        if ($options['truncate_first'] && ! $request->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Opsi mengosongkan tabel (Truncate) hanya diizinkan untuk Super Admin.',
            ], 403);
        }

        $results = [];
        $totalImported = 0;
        $totalSkipped = 0;
        $totalProcessed = 0;

        foreach ($modules as $mod) {
            $res = $service->migrateModule($mod, $credentials, $options);
            $results[$mod] = $res;
            if ($res['success']) {
                $totalImported += $res['imported'] ?? 0;
                $totalSkipped += $res['skipped'] ?? 0;
                $totalProcessed += $res['total_processed'] ?? 0;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sinkronisasi data PRL dari Simply selesai ({$totalImported} data baru diimpor, {$totalSkipped} data duplikat dilewati).",
            'total_processed' => $totalProcessed,
            'total_imported' => $totalImported,
            'total_skipped' => $totalSkipped,
            'details' => $results,
        ]);
    }
}
