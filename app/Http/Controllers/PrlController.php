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
use App\Services\SimplyDataMigrationService;
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

        // Master Sarana Lists (for quick lookup / registers / forms)
        $masterTpp = PrlTpp::orderBy('nama_tpp')->get(['id', 'nama_tpp', 'wilker', 'penanggung_jawab', 'jml_karyawan', 'kapasitas_produksi']);
        $masterSab = PrlSab::orderBy('nama_sab')->get(['id', 'nama_sab', 'wilker', 'penanggung_jawab', 'penanggung_jawab_no_hp', 'sumber_air', 'kapasitas_penampungan']);
        $masterHsbu = PrlHsbu::orderBy('nama_gedung')->get(['id', 'nama_gedung', 'wilker', 'penanggung_jawab', 'jml_karyawan']);

        // Pemeriksa / Inspector Options
        $usersList = \App\Models\User::orderBy('name')->pluck('name')->toArray();
        $inspList = array_merge(
            PrlInspeksiTpp::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray(),
            PrlInspeksiSab::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray(),
            PrlInspeksiHsbu::distinct()->whereNotNull('nama_pemeriksa')->pluck('nama_pemeriksa')->toArray()
        );
        $pemeriksaAgg = [];
        foreach (array_merge($usersList, $inspList) as $raw) {
            foreach (preg_split('/[,|]/', (string)$raw) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '' && !is_numeric($trimmed)) {
                    $pemeriksaAgg[$trimmed] = true;
                }
            }
        }
        $pemeriksaOptions = array_keys($pemeriksaAgg);
        sort($pemeriksaOptions);

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
     * Get Kop Surat Base64.
     */
    private function getKopSuratBase64(): ?string
    {
        $possibleKopPaths = [
            public_path('dist/img/Kop_BKK_bandung.png'),
            public_path('images/Kop_BKK_bandung.png'),
            public_path('storage/images/Kop_BKK_bandung.png'),
        ];
        foreach ($possibleKopPaths as $kp) {
            if (file_exists($kp)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents($kp));
            }
        }
        return null;
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
            'kop' => $this->getKopSuratBase64(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Inspeksi_PRL_{$type}_{$id}.pdf");
    }

    /**
     * Print SIVERA Official Surveillance & Control PDF report.
     * Matches Simply CI4 Export/Pdf methods.
     */
    public function printSiveraPdf(Request $request, int $id, ?string $tipe = null)
    {
        $row = PrlSiveraVektor::findOrFail($id);
        $jv = $row->jenis_vektor ?? '';
        $detailData = is_array($row->detail_data) ? $row->detail_data : (json_decode($row->detail_data, true) ?: []);
        $pengendalian = $detailData['pengendalian'] ?? [];

        // Determine target date
        $tglTarget = !empty($pengendalian['tanggal']) 
            ? $pengendalian['tanggal'] 
            : (!empty($detailData['tanggal_selesai']) ? $detailData['tanggal_selesai'] : ($row->tanggal ? $row->tanggal->format('Y-m-d') : date('Y-m-d')));
        
        $carbonDate = \Carbon\Carbon::parse($tglTarget)->locale('id');
        $hariTanggal = $carbonDate->isoFormat('dddd, D MMMM Y');

        $wilker = $row->wilker ?: 'Wilayah Kerja';
        $namaPegawai = $row->created_by_name ?: (auth()->user()?->name ?: 'Petugas Pengendalian');
        $persiapan = $pengendalian['persiapan'] ?? 'Persiapan alat perlindungan diri (APD), bahan pengendali, dan koordinasi lapangan.';
        $catatanPengendalian = $row->tindakan_pengendalian ?: ($pengendalian['catatan'] ?? '');

        // Determine specific report format
        $judulKegiatan = 'PENGENDALIAN VEKTOR';
        $alatDanBahan = [];
        $alatDanBahanSplit = null;
        $metodePengendalian = 'Kimia / Fisika / Biologi';
        $jumlahInsektisida = '-';
        $penggunaanSolar = '';
        $penggunaanBensin = '';
        $hasilSurvei = $row->parameter_utama ?: ($row->baku_mutu_status ?? '-');
        $tabelLokasiLalat = null;
        $tabelAnopheles12jam = null;
        $anophelesTotalDalam = 0;
        $anophelesTotalLuar = 0;
        $anophelesTotalSemua = 0;

        if (stripos($jv, 'Jentik Aedes') !== false || stripos($jv, 'Aedes') !== false) {
            if ($tipe === 'fogging') {
                $judulKegiatan = 'PENGENDALIAN VEKTOR DBD (FOGGING)';
                $alatDanBahanSplit = [
                    ['Mesin Fogging', 'Jerigen Solar', 'Jerigen Bensin', 'Bensin', 'Solar', 'Corong Besar (Ukuran 25)', 'Corong Kecil (Ukuran 9)', 'Batang Pengaduk'],
                    ['Insektisida', 'Masker', 'Handscoon', 'Sarung Tangan Anti Panas', 'Gelas Ukur', 'Kunci Kunci Mesin Fogging', 'Ear Muff', 'Lap Fiber'],
                ];
                $metode = [];
                if (!empty($pengendalian['nyamuk_fogging'])) $metode[] = 'Fogging / Pengasapan Termal';
                if (!empty($pengendalian['nyamuk_fisik'])) $metode[] = 'Fisik (Raket nyamuk/Light trap)';
                $metodePengendalian = !empty($metode) ? implode(', ', $metode) : 'Kimia (Thermal Fogging)';
                
                if (!empty($pengendalian['nyamuk_fogging_ml'])) {
                    $ins = $pengendalian['nyamuk_fogging_ml'] . ' ml';
                    $sub = [];
                    if (!empty($pengendalian['nyamuk_fogging_bahan'])) $sub[] = 'Bahan: ' . $pengendalian['nyamuk_fogging_bahan'];
                    if (!empty($pengendalian['nyamuk_fogging_merk'])) $sub[] = 'Merk: ' . $pengendalian['nyamuk_fogging_merk'];
                    if (!empty($sub)) $ins .= ' (' . implode(', ', $sub) . ')';
                    $jumlahInsektisida = $ins;
                }
                $penggunaanSolar = !empty($pengendalian['nyamuk_fogging_solar']) ? $pengendalian['nyamuk_fogging_solar'] . ' Liter' : '5 Liter';
                $penggunaanBensin = !empty($pengendalian['nyamuk_fogging_bensin']) ? $pengendalian['nyamuk_fogging_bensin'] . ' Liter' : '2 Liter';
            } else {
                $judulKegiatan = 'PENGENDALIAN VEKTOR DBD (JENTIK)';
                $alatDanBahan = [
                    'Batang Pengaduk',
                    'Larvasida (Abate / Temephos)',
                    'Masker Medis',
                    'Sarung Tangan (Handscoon)',
                    'Alat Tulis & Senter',
                    'Dokumentasi Kegiatan',
                ];
                $metode = [];
                if (!empty($pengendalian['jentik_kimia'])) $metode[] = 'Kimia (Larvasidasi / Abatisasi)';
                if (!empty($pengendalian['jentik_fisik'])) $metode[] = 'Fisik (3M Plus: Menguras & Menutup)';
                if (!empty($pengendalian['jentik_biologi'])) $metode[] = 'Biologi (BTI / Ikan Pemakan Jentik)';
                $metodePengendalian = !empty($metode) ? implode(', ', $metode) : 'Kimia (Larvasidasi selektif) & Fisik (PSN)';

                $ins = [];
                if (!empty($pengendalian['jentik_kimia_gram'])) $ins[] = $pengendalian['jentik_kimia_gram'] . ' gram';
                if (!empty($pengendalian['jentik_kimia_sachet'])) $ins[] = $pengendalian['jentik_kimia_sachet'] . ' sachet';
                $jumlahInsektisida = !empty($ins) ? implode(' / ', $ins) : ($row->tindakan_pengendalian ?: '-');
            }
        } elseif (stripos($jv, 'Lalat') !== false) {
            $judulKegiatan = 'PENGENDALIAN VEKTOR LALAT';
            $alatDanBahan = [
                'Spray Can (Alat Semprot)',
                'Insektisida Lalat / Fly Bait Granule',
                'Fly Grill Standard Kemenkes',
                'Hand Counter & Stopwatch',
                'Masker & Sarung Tangan',
                'Alat Tulis & Dokumentasi',
            ];
            $metode = [];
            if (!empty($pengendalian['spraying'])) {
                $metode[] = 'Residual Spraying';
                if (!empty($pengendalian['spraying_ml'])) {
                    $jumlahInsektisida = $pengendalian['spraying_ml'] . ' ml (Spraying)';
                }
            }
            if (!empty($pengendalian['granule'])) {
                $metode[] = 'Umpan Granule';
                if (!empty($pengendalian['granule_gram'])) {
                    $jumlahInsektisida = $pengendalian['granule_gram'] . ' gram (Granule)';
                }
            }
            $metodePengendalian = !empty($metode) ? 'Kimia (' . implode(', ', $metode) . ')' : 'Kimia / Fisika (Sanitasi & Fly Trap)';

            if (!empty($detailData['detail_lokasi']) && is_array($detailData['detail_lokasi'])) {
                $tabelLokasiLalat = $detailData['detail_lokasi'];
            } elseif (!empty($detailData['lokasi_items']) && is_array($detailData['lokasi_items'])) {
                $tabelLokasiLalat = $detailData['lokasi_items'];
            }
        } elseif (stripos($jv, 'Kecoa') !== false) {
            $judulKegiatan = 'PENGENDALIAN VEKTOR KECOA';
            $alatDanBahan = [
                'Perangkap Kecoa (Sticky Trap)',
                'Gel Baiting / Umpan Racun Kecoa',
                'Senter Penerangan Celah',
                'Masker & Sarung Tangan',
                'Alat Tulis & Dokumentasi',
            ];
            $metode = [];
            if (!empty($pengendalian['retrapping'])) {
                $metode[] = 'Pemasangan Retrapping';
                $jumlahInsektisida = ($pengendalian['retrapping_trap'] ?? '10') . ' unit trap';
            }
            if (!empty($pengendalian['gel_bait'])) {
                $metode[] = 'Aplikasi Gel Baiting';
                $jumlahInsektisida = ($pengendalian['gel_bait_gram'] ?? '20') . ' gram gel';
            }
            $metodePengendalian = !empty($metode) ? implode(', ', $metode) : 'Fisik (Trapping) & Kimia (Gel Baiting)';
        } elseif (stripos($jv, 'Tikus') !== false) {
            $judulKegiatan = 'PENGENDALIAN DAN SURVEILANS TIKUS';
            $alatDanBahan = [
                'Perangkap Tikus (Live Trap / Snap Trap)',
                'Umpan Menarik (Kelapa Bakar, Ikan Asin)',
                'Kantong Kain Pengambil Tikus',
                'Kloroform Pembius & Sisir Kutu',
                'Timbangan Digital & Penggaris Ukur',
                'Formulir Identifikasi Spesies Tikus',
            ];
            $metodePengendalian = $catatanPengendalian ?: 'Rat Proofing, Pemasangan Perangkap Permanen, dan Sanitasi Habitat';
            $jumlahInsektisida = ($detailData['jml_perangkap_dipasang'] ?? 50) . ' trap terpasang';
        } elseif (stripos($jv, 'Pinjal') !== false) {
            $judulKegiatan = 'SURVEILANS DAN PENGENDALIAN PINJAL TIKUS';
            $alatDanBahan = [
                'Nampan Putih Berisi Air / Kloroform',
                'Sisir Kutu Khusus Rodensia',
                'Petridish & Kaca Pembesar / Mikroskop',
                'Dusting Gun Insektisida',
                'Alat Tulis & Lembar Surveilans Pinjal',
            ];
            $metodePengendalian = $catatanPengendalian ?: 'Dusting insektisida pada sarang tikus & perimeter pelabuhan';
        } elseif (stripos($jv, 'Jentik Anopheles') !== false) {
            $judulKegiatan = 'SURVEILANS HABITAT & PENGENDALIAN JENTIK ANOPHELES';
            $alatDanBahan = [
                'Dipper / Cidukan Jentik Standard',
                'Pipet Tetes & Botol Sampel Air',
                'pH Meter & Salinometer Pesisir',
                'Larvasida Biologi (BTI / Bacillus thuringiensis)',
                'Alat Tulis & GPS Tracking',
            ];
            $metodePengendalian = $catatanPengendalian ?: 'Pembersihan lumut air, sanitasi perindukan payau, dan larvasida biologi';
        } elseif (stripos($jv, 'Nyamuk Anopheles') !== false) {
            $judulKegiatan = 'SURVEILANS MALARIA (PENANGKAPAN NYAMUK ANOPHELES 12 JAM)';
            $alatDanBahan = [
                'Aspirator Penangkapan Nyamuk',
                'Paper Cup Bertutup Kasa & Karet Gelang',
                'Senter Kepala / Headlamp',
                'Termohigrometer & Anemometer Portable',
                'Kapas Beralkohol & Larutan Glukosa',
                'Formulir Pencatatan 12 Jam (18.00-06.00 WIB)',
            ];
            $metodePengendalian = $catatanPengendalian ?: 'Indoor Residual Spraying (IRS) & Pembagian Kelambu Berinsektisida';

            if (!empty($detailData['tangkapan_dalam']) && !empty($detailData['tangkapan_luar'])) {
                $slots = [
                    '18_19' => '18.00 - 19.00', '19_20' => '19.00 - 20.00', '20_21' => '20.00 - 21.00',
                    '21_22' => '21.00 - 22.00', '22_23' => '22.00 - 23.00', '23_24' => '23.00 - 24.00',
                    '24_01' => '24.00 - 01.00', '01_02' => '01.00 - 02.00', '02_03' => '02.00 - 03.00',
                    '03_04' => '03.00 - 04.00', '04_05' => '04.00 - 05.00', '05_06' => '05.00 - 06.00',
                ];
                $tabelAnopheles12jam = [];
                foreach ($slots as $k => $lbl) {
                    $d = (int)($detailData['tangkapan_dalam'][$k] ?? 0);
                    $l = (int)($detailData['tangkapan_luar'][$k] ?? 0);
                    $anophelesTotalDalam += $d;
                    $anophelesTotalLuar += $l;
                    $tabelAnopheles12jam[] = [
                        'label' => $lbl,
                        'dalam' => $d,
                        'luar' => $l,
                        'total' => $d + $l,
                    ];
                }
                $anophelesTotalSemua = $anophelesTotalDalam + $anophelesTotalLuar;
            }
        }

        $pdf = Pdf::loadView('pdf.sivera_pdf', [
            'title' => "Laporan " . ucwords(strtolower($judulKegiatan)),
            'judul_kegiatan' => $judulKegiatan,
            'wilker' => $wilker,
            'hari_tanggal' => $hariTanggal,
            'lokasi' => $row->lokasi ?: '-',
            'persiapan' => $persiapan,
            'alat_dan_bahan' => $alatDanBahan,
            'alat_dan_bahan_split' => $alatDanBahanSplit,
            'hasil_survei' => $hasilSurvei,
            'metode_pengendalian' => $metodePengendalian,
            'jumlah_insektisida' => $jumlahInsektisida,
            'penggunaan_solar' => $penggunaanSolar,
            'penggunaan_bensin' => $penggunaanBensin,
            'catatan_pengendalian' => $catatanPengendalian,
            'nama_pegawai' => $namaPegawai,
            'tabel_lokasi_lalat' => $tabelLokasiLalat,
            'tabel_anopheles_12jam' => $tabelAnopheles12jam,
            'anopheles_total_dalam' => $anophelesTotalDalam,
            'anopheles_total_luar' => $anophelesTotalLuar,
            'anopheles_total_semua' => $anophelesTotalSemua,
            'kop' => $this->getKopSuratBase64(),
        ])->setPaper('a4', 'portrait');

        $cleanFilename = str_replace([' ', '/', '\\'], '_', "Laporan_{$judulKegiatan}_{$row->id}");
        return $pdf->stream("{$cleanFilename}.pdf");
    }

    public function printSiveraJentikPdf(Request $request, int $id, ?string $tipe = 'jentik')
    {
        return $this->printSiveraPdf($request, $id, $tipe);
    }

    public function printSiveraLalatPdf(Request $request, int $id)
    {
        return $this->printSiveraPdf($request, $id, 'lalat');
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

    /**
     * Store new Inspeksi TPP record.
     */
    public function storeInspeksiTpp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'tanggal_inspeksi' => 'required|date',
            'nama_tpp' => 'required|string|max:255',
            'prl_tpp_id' => 'nullable|integer',
            'penanggung_jawab_tpp' => 'nullable|string|max:255',
            'jml_karyawan_tpp' => 'nullable|string|max:255',
            'kapasitas_produksi_tpp' => 'nullable|string|max:255',
            'nama_pemeriksa' => 'nullable|string|max:255',
            'sec_a_satu' => 'nullable|string|max:10',
            'sec_a_dua' => 'nullable|string|max:10',
            'sec_a_tiga' => 'nullable|string|max:10',
            'sec_a_skor' => 'nullable|integer',
            'sec_b_satu' => 'nullable|string|max:10',
            'sec_b_dua' => 'nullable|string|max:10',
            'sec_b_tiga' => 'nullable|string|max:10',
            'sec_b_skor' => 'nullable|integer',
            'sec_c_skor' => 'nullable|integer',
            'sec_c_keterangan' => 'nullable|string|max:255',
            'sec_d_nilai_inspeksi_sebelumnya' => 'nullable|integer',
            'sec_d_skor' => 'nullable|integer',
            'hasil' => 'required|integer',
            'tingkat_risiko' => 'required|string|in:Rendah,Sedang,Tinggi',
            'kesimpulan' => 'nullable|string',
            'makanan' => 'nullable|string',
            'saran_rencana_tindakan' => 'nullable|string',
        ]);

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
    public function storeInspeksiSab(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'tanggal_inspeksi' => 'required|date',
            'nama_sab' => 'required|string|max:255',
            'prl_sab_id' => 'nullable|integer',
            'penanggung_jawab_sab' => 'nullable|string|max:255',
            'penanggung_jawab_no_hp_sab' => 'nullable|string|max:30',
            'sumber_air_sab' => 'nullable|string|max:255',
            'kapasitas_penampungan_sab' => 'nullable|string|max:255',
            'nama_pemeriksa' => 'nullable|string|max:255',
            'sec_b_satu' => 'nullable|string|max:10',
            'sec_b_dua' => 'nullable|string|max:10',
            'sec_b_tiga' => 'nullable|string|max:10',
            'sec_b_empat' => 'nullable|string|max:10',
            'sec_c_satu' => 'nullable|string|max:10',
            'sec_c_dua' => 'nullable|string|max:10',
            'sec_c_tiga' => 'nullable|string|max:10',
            'sec_c_empat' => 'nullable|string|max:10',
            'sec_c_lima' => 'nullable|string|max:10',
            'sec_c_enam' => 'nullable|string|max:10',
            'sec_c_tujuh' => 'nullable|string|max:10',
            'sec_c_delapan' => 'nullable|string|max:10',
            'sec_c_sembilan' => 'nullable|string|max:10',
            'sec_c_sepuluh' => 'nullable|string|max:10',
            'hasil' => 'required|integer',
            'tingkat_risiko' => 'required|string',
            'saran_rencana_tindakan' => 'nullable|string',
        ]);

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
    public function storeInspeksiHsbu(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:255',
            'tanggal_inspeksi' => 'required|date',
            'nama_gedung' => 'required|string|max:255',
            'prl_hsbu_id' => 'nullable|integer',
            'penanggung_jawab_hsbu' => 'nullable|string|max:255',
            'jml_karyawan_hsbu' => 'nullable|string|max:255',
            'nama_pemeriksa' => 'nullable|string|max:255',
            'komponen_penilaian' => 'nullable|array',
            'total' => 'required|numeric',
            'tingkat_risiko' => 'required|string|in:Rendah,Sedang,Tinggi',
            'saran_rencana_tindakan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

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
        return $this->index($request);
    }

    public function sabTab(Request $request): Response
    {
        $request->merge(['tab' => 'sab']);
        return $this->index($request);
    }

    public function hsbuTab(Request $request): Response
    {
        $request->merge(['tab' => 'hsbu']);
        return $this->index($request);
    }

    public function siveraTab(Request $request): Response
    {
        $request->merge(['tab' => 'sivera']);
        return $this->index($request);
    }

    /**
     * Store new SIVERA Vektor record.
     */
    public function storeSivera(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jenis_vektor' => 'required|string|max:50',
            'wilker' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'parameter_utama' => 'required|string|max:255',
            'nilai_indeks' => 'required|numeric',
            'baku_mutu_status' => 'required|string|max:50',
            'tindakan_pengendalian' => 'nullable|string',
            'detail_data' => 'nullable|array',
        ]);

        $validated['created_by_name'] = $request->user()?->name;
        $sivera = PrlSiveraVektor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Surveilans Vektor (SIVERA) berhasil disimpan.',
            'data' => $sivera,
        ]);
    }

    /**
     * Update an existing SIVERA record.
     */
    public function updateSivera(Request $request, int $id): JsonResponse
    {
        $sivera = PrlSiveraVektor::findOrFail($id);

        $validated = $request->validate([
            'jenis_vektor' => 'required|string|max:50',
            'wilker' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'parameter_utama' => 'required|string|max:255',
            'nilai_indeks' => 'required|numeric',
            'baku_mutu_status' => 'required|string|max:50',
            'tindakan_pengendalian' => 'nullable|string',
            'detail_data' => 'nullable|array',
        ]);

        $sivera->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Surveilans Vektor (SIVERA) berhasil diperbarui.',
            'data' => $sivera,
        ]);
    }

    /**
     * Update tindakan pengendalian for SIVERA record.
     */
    public function updatePengendalianSivera(Request $request, int $id): JsonResponse
    {
        $sivera = PrlSiveraVektor::findOrFail($id);

        $validated = $request->validate([
            'tindakan_pengendalian' => 'required|string',
            'pengendalian_detail' => 'nullable|array',
        ]);

        $detail = $sivera->detail_data ?? [];
        if (!empty($validated['pengendalian_detail'])) {
            $detail['pengendalian'] = $validated['pengendalian_detail'];
        }

        $sivera->update([
            'tindakan_pengendalian' => $validated['tindakan_pengendalian'],
            'detail_data' => $detail,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tindakan pengendalian berhasil disimpan.',
            'data' => $sivera,
        ]);
    }

    /**
     * Delete an inspection record.
     */
    public function destroyInspeksi(Request $request, string $type, int $id): JsonResponse
    {
        $deleted = false;
        if ($type === 'tpp') {
            $deleted = PrlInspeksiTpp::findOrFail($id)->delete();
        } elseif ($type === 'sab') {
            $deleted = PrlInspeksiSab::findOrFail($id)->delete();
        } elseif ($type === 'hsbu') {
            $deleted = PrlInspeksiHsbu::findOrFail($id)->delete();
        } elseif ($type === 'sivera') {
            $deleted = PrlSiveraVektor::findOrFail($id)->delete();
        }

        return response()->json([
            'success' => (bool)$deleted,
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
            'success' => (bool)$deleted,
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
            'skip_duplicate' => (bool)$request->input('skip_duplicate', true),
            'truncate_first' => (bool)$request->input('truncate_first', false),
            'dry_run' => (bool)$request->input('dry_run', false),
        ];

        // Privilege check: Truncate requires Super Admin
        if ($options['truncate_first'] && !$request->user()->hasRole('Super Admin')) {
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

