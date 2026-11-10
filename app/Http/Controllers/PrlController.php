<?php

namespace App\Http\Controllers;

use App\Models\KantorWilker;
use App\Models\PrlHsbu;
use App\Models\PrlInspeksiHsbu;
use App\Models\PrlInspeksiSab;
use App\Models\PrlInspeksiTpp;
use App\Models\PrlSab;
use App\Models\PrlSiveraVektor;
use App\Models\PrlTpp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrlController extends Controller
{
    /**
     * Display the central Pengendalian Risiko Lingkungan (PRL) portal & analytics dashboard.
     */
    public function index(Request $request): Response
    {
        $activeTab = $request->query('tab', 'overview');
        $search = $request->query('search');
        $wilker = $request->query('wilker');
        $tingkatRisiko = $request->query('tingkat_risiko');
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

        // SIVERA Stats
        $siveraTotal = PrlSiveraVektor::count();
        $siveraMemenuhi = PrlSiveraVektor::where('baku_mutu_status', 'Memenuhi Syarat')->count();
        $siveraDiatasBaku = PrlSiveraVektor::where('baku_mutu_status', 'Di Atas Baku Mutu')->count();

        // 1. TPP Inspections Query
        $qTpp = PrlInspeksiTpp::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qTpp->where(function ($q) use ($search) {
                $q->where('nama_tpp', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab_tpp', 'like', "%{$search}%")
                  ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) $qTpp->where('wilker', $wilker);
        if ($tingkatRisiko) $qTpp->where('tingkat_risiko', $tingkatRisiko);
        if ($tahun) $qTpp->whereYear('tanggal_inspeksi', $tahun);
        if ($bulan) $qTpp->whereMonth('tanggal_inspeksi', $bulan);
        $inspeksiTpp = $qTpp->paginate(10, ['*'], 'page_tpp')->withQueryString();

        // 2. SAB Inspections Query
        $qSab = PrlInspeksiSab::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qSab->where(function ($q) use ($search) {
                $q->where('nama_sab', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab_sab', 'like', "%{$search}%")
                  ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) $qSab->where('wilker', $wilker);
        if ($tingkatRisiko) $qSab->where('tingkat_risiko', $tingkatRisiko);
        if ($tahun) $qSab->whereYear('tanggal_inspeksi', $tahun);
        if ($bulan) $qSab->whereMonth('tanggal_inspeksi', $bulan);
        $inspeksiSab = $qSab->paginate(10, ['*'], 'page_sab')->withQueryString();

        // 3. HSBU Inspections Query
        $qHsbu = PrlInspeksiHsbu::query()->latest('tanggal_inspeksi')->latest('id');
        if ($search) {
            $qHsbu->where(function ($q) use ($search) {
                $q->where('nama_gedung', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab_hsbu', 'like', "%{$search}%")
                  ->orWhere('nama_pemeriksa', 'like', "%{$search}%");
            });
        }
        if ($wilker) $qHsbu->where('wilker', $wilker);
        if ($tingkatRisiko) $qHsbu->where('tingkat_risiko', $tingkatRisiko);
        if ($tahun) $qHsbu->whereYear('tanggal_inspeksi', $tahun);
        if ($bulan) $qHsbu->whereMonth('tanggal_inspeksi', $bulan);
        $inspeksiHsbu = $qHsbu->paginate(10, ['*'], 'page_hsbu')->withQueryString();

        // 4. SIVERA Vektor Query
        $qSivera = PrlSiveraVektor::query()->latest('tanggal')->latest('id');
        if ($search) {
            $qSivera->where(function ($q) use ($search) {
                $q->where('jenis_vektor', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('parameter_utama', 'like', "%{$search}%");
            });
        }
        if ($wilker) $qSivera->where('wilker', $wilker);
        $siveraList = $qSivera->paginate(10, ['*'], 'page_sivera')->withQueryString();

        // Master Sarana Lists (for quick lookup / registers)
        $masterTpp = PrlTpp::latest()->take(50)->get();
        $masterSab = PrlSab::latest()->take(50)->get();
        $masterHsbu = PrlHsbu::latest()->take(50)->get();

        return Inertia::render('Prl/Index', [
            'active_tab' => $activeTab,
            'filters' => [
                'search' => $search,
                'wilker' => $wilker,
                'tingkat_risiko' => $tingkatRisiko,
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            'wilker_options' => $wilkerOptions,
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
                'sivera_total' => $siveraTotal,
                'sivera_memenuhi' => $siveraMemenuhi,
                'sivera_diatas_baku' => $siveraDiatasBaku,
                'wilker_stats' => $wilkerStats,
            ],
            'inspeksi_tpp' => $inspeksiTpp,
            'inspeksi_sab' => $inspeksiSab,
            'inspeksi_hsbu' => $inspeksiHsbu,
            'sivera_list' => $siveraList,
            'master_tpp' => $masterTpp,
            'master_sab' => $masterSab,
            'master_hsbu' => $masterHsbu,
        ]);
    }

    /**
     * Export PRL data to CSV/Excel format.
     */
    public function exportExcel(Request $request, string $type): StreamedResponse
    {
        $filename = "Export_PRL_" . strtoupper($type) . "_" . date('Ymd_His') . ".csv";

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
            } elseif ($type === 'sivera') {
                fputcsv($handle, ['No', 'Jenis Vektor', 'Wilayah Kerja', 'Lokasi', 'Tanggal', 'Parameter Utama', 'Nilai Indeks', 'Status Baku Mutu', 'Tindakan Pengendalian', 'Petugas']);
                $nomor = 1;
                foreach (PrlSiveraVektor::latest('tanggal')->cursor() as $r) {
                    fputcsv($handle, [
                        $nomor++,
                        $r->jenis_vektor,
                        $r->wilker,
                        $r->lokasi,
                        $r->tanggal?->format('d/m/Y') ?: '-',
                        $r->parameter_utama,
                        $r->nilai_indeks,
                        $r->baku_mutu_status,
                        $r->tindakan_pengendalian,
                        $r->created_by_name,
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
        if ($type === 'tpp') {
            $item = PrlInspeksiTpp::findOrFail($id);
            $title = "Laporan Inspeksi Higiene Sanitasi Pangan (TPP)";
        } elseif ($type === 'sab') {
            $item = PrlInspeksiSab::findOrFail($id);
            $title = "Laporan Inspeksi Sanitasi Sarana Air Bersih (SAB)";
        } elseif ($type === 'hsbu') {
            $item = PrlInspeksiHsbu::findOrFail($id);
            $title = "Laporan Inspeksi Higiene Sanitasi Bangunan dan Usaha (HSBU)";
        } else {
            abort(404, 'Jenis dokumen tidak dikenali.');
        }

        $pdf = Pdf::loadView('pdf.prl_inspeksi', [
            'type' => $type,
            'title' => $title,
            'item' => $item,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Inspeksi_PRL_{$type}_{$id}.pdf");
    }

    /**
     * Store new master TPP sarana.
     */
    public function storeTpp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'nama_tpp' => 'required|string|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
            'jml_karyawan' => 'nullable|string|max:255',
            'kapasitas_produksi' => 'nullable|string|max:255',
        ]);

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
    public function storeSab(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'nama_sab' => 'required|string|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
            'penanggung_jawab_no_hp' => 'nullable|string|max:30',
            'sumber_air' => 'nullable|string|max:255',
            'kapasitas_penampungan' => 'nullable|string|max:255',
        ]);

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
    public function storeHsbu(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'nama_gedung' => 'required|string|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
            'jml_karyawan' => 'nullable|string|max:255',
        ]);

        $validated['created_by_name'] = $request->user()?->name;
        $hsbu = PrlHsbu::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Gedung / Bangunan HSBU berhasil disimpan.',
            'data' => $hsbu,
        ]);
    }
}
