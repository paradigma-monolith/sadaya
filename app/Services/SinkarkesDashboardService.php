<?php

namespace App\Services;

use App\Models\KantorWilker;
use App\Models\SinkarkesReport;

class SinkarkesDashboardService
{
    protected array $schema;

    public function __construct()
    {
        $schemaPath = app_path('Data/sinkarkes_schema.json');
        if (!file_exists($schemaPath)) {
            $schemaPath = storage_path('app/sinkarkes_schema.json');
        }
        $this->schema = file_exists($schemaPath) ? json_decode(file_get_contents($schemaPath), true) : ['sections' => []];
    }

    /**
     * Compute and compile complete dashboard statistics, matrix, and chart series.
     */
    public function getDashboardData(int $year, ?string $wilker = null): array
    {
        // 1. Get Canonical Wilayah Kerja list
        try {
            $dbWilkers = KantorWilker::where('is_active', true)->orderBy('no')->pluck('nama_pendek')->toArray();
        } catch (\Throwable $e) {
            $dbWilkers = [];
        }

        $wilkerOptions = !empty($dbWilkers) ? array_values($dbWilkers) : [
            'Induk BKK Bandung',
            'POS Bandara Husen',
            'Wilker Cirebon',
            'Wilker Indramayu',
            'Wilker Kertajati',
            'Wilker Patimban',
            'Wilker Pelabuharatu',
        ];

        // 2. Fetch all reports for this year
        $query = SinkarkesReport::with(['updater:id,name'])->where('year', $year);

        $isFilteredWilker = ($wilker && $wilker !== 'all' && !empty($wilker));
        if ($isFilteredWilker) {
            $query->where(function ($q) use ($wilker) {
                $q->where('wilker', $wilker);
                if (stripos($wilker, 'Husen') !== false || stripos($wilker, 'Husein') !== false) {
                    $q->orWhere('wilker', 'BANDARA HUSEIN')
                      ->orWhere('wilker', 'Bandara Husein')
                      ->orWhere('wilker', 'POS Bandara Husen');
                }
            });
        }

        $allReports = $query->get();

        // Also fetch all year reports across all wilkers for cross-wilker comparison
        $crossWilkerReports = $isFilteredWilker
            ? SinkarkesReport::where('year', $year)->get()
            : $allReports;

        // Group reports by canonical wilker and month
        $wilkerMonthData = [];
        $wilkerMonthMeta = [];

        foreach ($crossWilkerReports as $rep) {
            $canonical = $this->canonicalizeWilker($rep->wilker, $wilkerOptions);
            $month = (int) $rep->month;
            $raw = $rep->data ?? [];
            $computed = $this->resolveFormulas($raw);

            $wilkerMonthData[$canonical][$month] = $computed;
            $wilkerMonthMeta[$canonical][$month] = [
                'has_data' => !empty($raw),
                'filled_count' => count(array_filter($raw, fn($v) => $v !== null && $v !== '')),
                'status' => $rep->status ?? 'submitted',
                'updated_at' => $rep->updated_at ? $rep->updated_at->format('d M Y H:i') : null,
                'updater' => $rep->updater?->name ?? 'Petugas BKK',
            ];
        }

        // 3. Build Wilker x 12 Month Reporting Matrix
        $matrix = [];
        $totalPossibleReports = count($wilkerOptions) * 12;
        $totalSubmittedReports = 0;

        foreach ($wilkerOptions as $w) {
            $rowMonths = [];
            $filledMonthsCount = 0;
            for ($m = 1; $m <= 12; $m++) {
                $meta = $wilkerMonthMeta[$w][$m] ?? [
                    'has_data' => false,
                    'filled_count' => 0,
                    'status' => 'empty',
                    'updated_at' => null,
                    'updater' => null,
                ];
                if ($meta['has_data']) {
                    $filledMonthsCount++;
                    $totalSubmittedReports++;
                }
                $rowMonths[$m] = $meta;
            }
            $matrix[] = [
                'wilker' => $w,
                'months' => $rowMonths,
                'filled_months' => $filledMonthsCount,
                'compliance_percent' => round(($filledMonthsCount / 12) * 100),
            ];
        }

        // 4. Determine Active Data Pool (Filtered Wilker or Consolidated All Wilkers)
        $activeWilkerData = [];
        if ($isFilteredWilker) {
            $activeCanonical = $this->canonicalizeWilker($wilker, $wilkerOptions);
            $activeWilkerData = $wilkerMonthData[$activeCanonical] ?? [];
        } else {
            // Aggregate all wilkers into a single monthly sum per row
            for ($m = 1; $m <= 12; $m++) {
                $activeWilkerData[$m] = [];
                foreach ($wilkerOptions as $w) {
                    $mData = $wilkerMonthData[$w][$m] ?? [];
                    foreach ($mData as $rowNum => $val) {
                        if (is_numeric($val)) {
                            $activeWilkerData[$m][$rowNum] = ($activeWilkerData[$m][$rowNum] ?? 0) + (float)$val;
                        }
                    }
                }
            }
        }

        // Helper to sum row values across months
        $sumRow = function (int $rowNum, array $monthsRange = null) use ($activeWilkerData): float {
            $range = $monthsRange ?? range(1, 12);
            $total = 0;
            foreach ($range as $m) {
                $v = $activeWilkerData[$m][$rowNum] ?? 0;
                if (is_numeric($v)) {
                    $total += (float)$v;
                }
            }
            return $total;
        };

        // Monthly breakdown helper
        $getMonthlyRow = function (int $rowNum) use ($activeWilkerData): array {
            $res = [];
            for ($m = 1; $m <= 12; $m++) {
                $res[] = (float)($activeWilkerData[$m][$rowNum] ?? 0);
            }
            return $res;
        };

        // 5. Compute Executive KPI Summary Cards
        // Alat Angkut
        $kapalDatang = $sumRow(7);
        $kapalBerangkat = $sumRow(14);
        $totalKapal = $kapalDatang + $kapalBerangkat;

        $pesawatDatang = $sumRow(21);
        $pesawatBerangkat = $sumRow(30);
        $totalPesawat = $pesawatDatang + $pesawatBerangkat;
        $totalAlatAngkut = $totalKapal + $totalPesawat;

        // Orang (Penumpang & Kru)
        $penumpangKapal = $sumRow(37) + $sumRow(44);
        $penumpangPesawat = $sumRow(51) + $sumRow(58);
        $totalPenumpang = $penumpangKapal + $penumpangPesawat;

        $abkKapal = $sumRow(65) + $sumRow(72);
        $crewPesawat = $sumRow(79) + $sumRow(86);
        $totalCrew = $abkKapal + $crewPesawat;
        $totalOrang = $totalPenumpang + $totalCrew;

        // Dokumen Kekarantinaan
        $sscec = $sumRow(93);
        $sscc = $sumRow(94);
        $p3kKapal = $sumRow(95);
        $p3kPesawat = $sumRow(187);
        $hac = $sumRow(96);
        $desinseksi = $sumRow(99);
        $desinfeksi = $sumRow(102);
        $fumigasi = $sumRow(105);
        $sailingPermit = $sumRow(107);
        $bukuKesehatan = $sumRow(108);
        $ijinJenazah = $sumRow(198) + $sumRow(201);
        $totalDokumen = $sscec + $sscc + $p3kKapal + $p3kPesawat + $hac + $desinseksi + $desinfeksi + $fumigasi + $sailingPermit + $bukuKesehatan + $ijinJenazah;

        // Upaya Kesehatan & Layanan Medis
        $poliklinikKKP = $sumRow(160);
        $suratSehat = $sumRow(184);
        $labDasar = $sumRow(188);
        $gawatDarurat = $sumRow(193);
        $laikTerbang = $sumRow(196);
        $ambulans = $sumRow(206);
        $totalLayananKesehatan = $poliklinikKKP + $suratSehat + $labDasar + $gawatDarurat + $laikTerbang + $ambulans;

        // Vaksinasi ICV
        $vaksinYellowFever = $sumRow(210) + $sumRow(215) + $sumRow(220);
        $vaksinCholera = $sumRow(211) + $sumRow(216) + $sumRow(221);
        $vaksinMeningitis = $sumRow(212) + $sumRow(217) + $sumRow(222);
        $vaksinTyphoid = $sumRow(213) + $sumRow(218) + $sumRow(223);
        $totalVaksinasi = $sumRow(207) + $sumRow(219);
        if ($totalVaksinasi == 0) {
            $totalVaksinasi = $vaksinYellowFever + $vaksinCholera + $vaksinMeningitis + $vaksinTyphoid;
        }

        // Pengendalian Risiko Lingkungan
        $tpmSehat = $sumRow(286);
        $tpmTidakSehat = $sumRow(287);
        $totalTPM = $sumRow(285);
        if ($totalTPM == 0) $totalTPM = $tpmSehat + $tpmTidakSehat;

        $airBaik = $sumRow(275) + $sumRow(278) + $sumRow(281);
        $airTidakBaik = $sumRow(276) + $sumRow(279) + $sumRow(282);
        $sanitasiKapalBaik = $sumRow(326);
        $sanitasiPesawatBaik = $sumRow(332);
        $totalSanitasiAlatAngkut = $sumRow(325) + $sumRow(331);

        $kpi = [
            'total_alat_angkut' => $totalAlatAngkut,
            'total_pesawat' => $totalPesawat,
            'total_kapal' => $totalKapal,
            'total_orang' => $totalOrang,
            'total_penumpang' => $totalPenumpang,
            'total_crew' => $totalCrew,
            'total_dokumen' => $totalDokumen,
            'total_layanan_kesehatan' => $totalLayananKesehatan,
            'total_vaksinasi' => $totalVaksinasi,
            'total_tpm_diinspeksi' => $totalTPM,
            'tpm_sehat' => $tpmSehat,
            'tpm_tidak_sehat' => $tpmTidakSehat,
            'air_memenuhi_syarat' => $airBaik,
            'compliance_rate' => $totalPossibleReports > 0 ? round(($totalSubmittedReports / $totalPossibleReports) * 100) : 0,
            'submitted_reports_count' => $totalSubmittedReports,
            'total_possible_reports' => $totalPossibleReports,
        ];

        // 6. Chart 1: Monthly Traffic Trend (12 Months)
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $planeMonthly = [];
        $shipMonthly = [];
        $passengerMonthly = [];
        $crewMonthly = [];

        for ($m = 1; $m <= 12; $m++) {
            $planeMonthly[] = (float)(($activeWilkerData[$m][21] ?? 0) + ($activeWilkerData[$m][30] ?? 0));
            $shipMonthly[] = (float)(($activeWilkerData[$m][7] ?? 0) + ($activeWilkerData[$m][14] ?? 0));
            $passengerMonthly[] = (float)(($activeWilkerData[$m][37] ?? 0) + ($activeWilkerData[$m][44] ?? 0) + ($activeWilkerData[$m][51] ?? 0) + ($activeWilkerData[$m][58] ?? 0));
            $crewMonthly[] = (float)(($activeWilkerData[$m][65] ?? 0) + ($activeWilkerData[$m][72] ?? 0) + ($activeWilkerData[$m][79] ?? 0) + ($activeWilkerData[$m][86] ?? 0));
        }

        $trafficChart = [
            'months' => $monthLabels,
            'plane' => $planeMonthly,
            'ship' => $shipMonthly,
            'passenger' => $passengerMonthly,
            'crew' => $crewMonthly,
        ];

        // 7. Chart 2: Document Composition (Donut / Pie)
        $docData = [
            ['name' => 'SSCEC (Bebas Sanitasi)', 'value' => $sscec],
            ['name' => 'SSCC (Tindakan Sanitasi)', 'value' => $sscc],
            ['name' => 'P3K Kapal & Pesawat', 'value' => $p3kKapal + $p3kPesawat],
            ['name' => 'Health Alert Card (HAC)', 'value' => $hac],
            ['name' => 'Sertifikat Desinseksi', 'value' => $desinseksi],
            ['name' => 'Sertifikat Desinfeksi', 'value' => $desinfeksi],
            ['name' => 'Sailing Permit & DC/DEC', 'value' => $sailingPermit + $sumRow(106)],
            ['name' => 'Surat Izin Jenazah', 'value' => $ijinJenazah],
        ];
        // Filter non-zero, or keep top items
        $filteredDocData = array_values(array_filter($docData, fn($d) => $d['value'] > 0));
        if (empty($filteredDocData)) {
            $filteredDocData = $docData; // fallback structure for clean chart rendering
        }

        // 8. Chart 3: Cross-Wilker Comparison Bar Chart
        $wilkerComparison = [];
        foreach ($wilkerOptions as $w) {
            $wPlanes = 0;
            $wShips = 0;
            $wPass = 0;
            $wDocs = 0;
            $wHealth = 0;

            for ($m = 1; $m <= 12; $m++) {
                $d = $wilkerMonthData[$w][$m] ?? [];
                $wPlanes += (float)(($d[21] ?? 0) + ($d[30] ?? 0));
                $wShips += (float)(($d[7] ?? 0) + ($d[14] ?? 0));
                $wPass += (float)(($d[37] ?? 0) + ($d[44] ?? 0) + ($d[51] ?? 0) + ($d[58] ?? 0));
                $wDocs += (float)(($d[93] ?? 0) + ($d[94] ?? 0) + ($d[95] ?? 0) + ($d[96] ?? 0) + ($d[99] ?? 0) + ($d[102] ?? 0) + ($d[107] ?? 0));
                $wHealth += (float)(($d[160] ?? 0) + ($d[184] ?? 0) + ($d[207] ?? 0));
            }

            $wilkerComparison[] = [
                'name' => $w,
                'short_name' => str_replace(['Wilker ', 'POS Bandara '], ['', ''], $w),
                'total_alat_angkut' => $wPlanes + $wShips,
                'total_orang' => $wPass,
                'total_dokumen' => $wDocs,
                'total_layanan' => $wHealth,
            ];
        }

        // 9. Chart 4: Health Services & ICV Vaccines Monthly
        $healthMonthly = [];
        $poliklinikMonthly = [];
        $sehatMonthly = [];
        $icvMonthly = [];

        for ($m = 1; $m <= 12; $m++) {
            $poliklinikMonthly[] = (float)($activeWilkerData[$m][160] ?? 0);
            $sehatMonthly[] = (float)($activeWilkerData[$m][184] ?? 0);
            $icvSum = (float)($activeWilkerData[$m][207] ?? 0);
            if ($icvSum == 0) {
                $icvSum = (float)(($activeWilkerData[$m][210] ?? 0) + ($activeWilkerData[$m][211] ?? 0) + ($activeWilkerData[$m][212] ?? 0) + ($activeWilkerData[$m][213] ?? 0) + ($activeWilkerData[$m][220] ?? 0) + ($activeWilkerData[$m][221] ?? 0) + ($activeWilkerData[$m][222] ?? 0) + ($activeWilkerData[$m][223] ?? 0));
            }
            $icvMonthly[] = $icvSum;
        }

        $healthChart = [
            'months' => $monthLabels,
            'poliklinik' => $poliklinikMonthly,
            'surat_sehat' => $sehatMonthly,
            'vaksin_icv' => $icvMonthly,
        ];

        // 10. Chart 5: Environmental Risk & Sanitation
        $environmentChart = [
            'tpm' => [
                ['name' => 'TPM Sehat', 'value' => $tpmSehat],
                ['name' => 'TPM Tidak Sehat', 'value' => $tpmTidakSehat],
            ],
            'air' => [
                ['name' => 'Air Baik (Fisik/Kimia/Bakteri)', 'value' => $airBaik],
                ['name' => 'Air Tidak Memenuhi', 'value' => $airTidakBaik],
            ],
            'alat_angkut' => [
                ['name' => 'Sanitasi Kapal Baik', 'value' => $sanitasiKapalBaik],
                ['name' => 'Sanitasi Pesawat Baik', 'value' => $sanitasiPesawatBaik],
            ],
        ];

        // 11. Comprehensive Monthly Total Summary Table (Jan - Des + Total Tahunan)
        $summaryDefinitions = [
            // KSE Section
            ['code' => 'KSE.1', 'label' => 'Kedatangan Kapal', 'unit' => 'Kpl', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 7],
            ['code' => 'KSE.2', 'label' => 'Keberangkatan Kapal (PHC)', 'unit' => 'Kpl', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 14],
            ['code' => 'KSE.3', 'label' => 'Kedatangan Pesawat Udara', 'unit' => 'Pswt', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 21],
            ['code' => 'KSE.4', 'label' => 'Keberangkatan Pesawat Udara', 'unit' => 'Pswt', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 30],
            ['code' => 'KSE.5.1', 'label' => 'Penumpang Kapal Datang', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 37],
            ['code' => 'KSE.5.2', 'label' => 'Penumpang Kapal Berangkat', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 44],
            ['code' => 'KSE.6.1', 'label' => 'Penumpang Pesawat Datang', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 51],
            ['code' => 'KSE.6.2', 'label' => 'Penumpang Pesawat Berangkat', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 58],
            ['code' => 'KSE.7', 'label' => 'Anak Buah Kapal (ABK)', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'rows_sum' => [65, 72]],
            ['code' => 'KSE.8', 'label' => 'Kru / Awak Pesawat', 'unit' => 'Org', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'rows_sum' => [79, 86]],
            ['code' => 'KSE.9.A', 'label' => 'Penerbitan SSCEC (Bebas Sanitasi)', 'unit' => 'Sertifikat', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 93],
            ['code' => 'KSE.9.B', 'label' => 'Penerbitan SSCC (Tindakan Sanitasi)', 'unit' => 'Sertifikat', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 94],
            ['code' => 'KSE.10', 'label' => 'Pemberian Health Alert Card (HAC)', 'unit' => 'Lembar', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 96],
            ['code' => 'KSE.11', 'label' => 'Sertifikat Desinseksi Alat Angkut', 'unit' => 'Lembar', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 99],
            ['code' => 'KSE.12', 'label' => 'Sertifikat Desinfeksi Alat Angkut', 'unit' => 'Lembar', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 102],
            ['code' => 'KSE.15', 'label' => 'Pemberian Sailing Permit', 'unit' => 'Kpl', 'section' => 'KSE', 'section_name' => 'Karantina & Surveilans Epidemiologi', 'row' => 107],

            // UKP Section
            ['code' => 'UKP.1', 'label' => 'Kunjungan Poliklinik BKK', 'unit' => 'Pasien', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 160],
            ['code' => 'UKP.2', 'label' => 'Surat Keterangan Berbadan Sehat', 'unit' => 'Org', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 184],
            ['code' => 'UKP.3', 'label' => 'Pemeriksaan Obat & P3K Kapal/Pesawat', 'unit' => 'Unit', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 185],
            ['code' => 'UKP.4', 'label' => 'Pemeriksaan Lab Klinik Dasar', 'unit' => 'Sampel', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 188],
            ['code' => 'UKP.5', 'label' => 'Penanggulangan Gawat Darurat Medik', 'unit' => 'Kasus', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 193],
            ['code' => 'UKP.6', 'label' => 'Sertifikat Keterangan Laik Terbang', 'unit' => 'Org', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'row' => 196],
            ['code' => 'UKP.7', 'label' => 'Surat Izin Angkut / Masuk Jenazah', 'unit' => 'Lembar', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'rows_sum' => [198, 201]],
            ['code' => 'UKP.10', 'label' => 'Pelayanan Vaksinasi Internasional (ICV)', 'unit' => 'Dosis', 'section' => 'UKP', 'section_name' => 'Upaya Kesehatan Pelabuhan', 'special' => 'icv'],

            // PRL Section
            ['code' => 'PRL.1', 'label' => 'Pengamatan Jentik Bangunan Diperiksa', 'unit' => 'Bangunan', 'section' => 'PRL', 'section_name' => 'Pengendalian Risiko Lingkungan', 'row' => 227],
            ['code' => 'PRL.2', 'label' => 'Pengendalian Tikus Tertangkap', 'unit' => 'Ekor', 'section' => 'PRL', 'section_name' => 'Pengendalian Risiko Lingkungan', 'row' => 257],
            ['code' => 'PRL.5', 'label' => 'Pemeriksaan Kualitas Air Bersih (Fisik)', 'unit' => 'Sampel', 'section' => 'PRL', 'section_name' => 'Pengendalian Risiko Lingkungan', 'rows_sum' => [274, 277, 280]],
            ['code' => 'PRL.6', 'label' => 'Pengawasan Tempat Pengolahan Makanan (TPM)', 'unit' => 'Sarana', 'section' => 'PRL', 'section_name' => 'Pengendalian Risiko Lingkungan', 'row' => 285],
            ['code' => 'PRL.9', 'label' => 'Hygiene Sanitasi Alat Angkut (Kapal & Pesawat)', 'unit' => 'Unit', 'section' => 'PRL', 'section_name' => 'Pengendalian Risiko Lingkungan', 'rows_sum' => [324, 325, 331]],
        ];

        $summaryTable = [];
        $monthlyTotalsAll = array_fill(1, 12, 0);
        $sectionTotals = [
            'KSE' => array_fill(1, 12, 0),
            'UKP' => array_fill(1, 12, 0),
            'PRL' => array_fill(1, 12, 0),
        ];

        foreach ($summaryDefinitions as $def) {
            $months = [];
            $totalYear = 0;

            for ($m = 1; $m <= 12; $m++) {
                $val = 0;
                if (isset($def['row'])) {
                    $val = (float)($activeWilkerData[$m][$def['row']] ?? 0);
                } elseif (isset($def['rows_sum'])) {
                    foreach ($def['rows_sum'] as $r) {
                        $val += (float)($activeWilkerData[$m][$r] ?? 0);
                    }
                } elseif (($def['special'] ?? '') === 'icv') {
                    $val = (float)($activeWilkerData[$m][207] ?? 0);
                    if ($val == 0) {
                        $val = (float)(
                            ($activeWilkerData[$m][210] ?? 0) +
                            ($activeWilkerData[$m][211] ?? 0) +
                            ($activeWilkerData[$m][212] ?? 0) +
                            ($activeWilkerData[$m][213] ?? 0) +
                            ($activeWilkerData[$m][220] ?? 0) +
                            ($activeWilkerData[$m][221] ?? 0) +
                            ($activeWilkerData[$m][222] ?? 0) +
                            ($activeWilkerData[$m][223] ?? 0)
                        );
                    }
                }

                $months[$m] = $val;
                $totalYear += $val;
                $monthlyTotalsAll[$m] += $val;
                $sec = $def['section'];
                if (isset($sectionTotals[$sec])) {
                    $sectionTotals[$sec][$m] += $val;
                }
            }

            $summaryTable[] = [
                'code' => $def['code'],
                'label' => $def['label'],
                'unit' => $def['unit'],
                'section' => $def['section'],
                'section_name' => $def['section_name'],
                'months' => $months,
                'total_year' => $totalYear,
            ];
        }

        // 12. Cross-Wilker Monthly Total Summary
        $wilkerMonthlyTotals = [];
        $wilkerGrandMonthly = array_fill(1, 12, 0);

        foreach ($wilkerOptions as $w) {
            $wMonths = [];
            $wTotalYear = 0;
            $wFilledCount = 0;

            for ($m = 1; $m <= 12; $m++) {
                $d = $wilkerMonthData[$w][$m] ?? [];
                // Sum all key operational activity in this wilker this month
                $mSum = (float)(
                    ($d[7] ?? 0) + ($d[14] ?? 0) + ($d[21] ?? 0) + ($d[30] ?? 0) +
                    ($d[37] ?? 0) + ($d[44] ?? 0) + ($d[51] ?? 0) + ($d[58] ?? 0) +
                    ($d[65] ?? 0) + ($d[72] ?? 0) + ($d[79] ?? 0) + ($d[86] ?? 0) +
                    ($d[93] ?? 0) + ($d[94] ?? 0) + ($d[96] ?? 0) + ($d[99] ?? 0) + ($d[102] ?? 0) + ($d[107] ?? 0) +
                    ($d[160] ?? 0) + ($d[184] ?? 0) + ($d[185] ?? 0) + ($d[188] ?? 0) + ($d[193] ?? 0) + ($d[196] ?? 0) + ($d[198] ?? 0) + ($d[201] ?? 0) + ($d[207] ?? 0)
                );

                $wMonths[$m] = $mSum;
                $wTotalYear += $mSum;
                $wilkerGrandMonthly[$m] += $mSum;

                if (!empty($d)) {
                    $wFilledCount++;
                }
            }

            $wilkerMonthlyTotals[] = [
                'wilker' => $w,
                'months' => $wMonths,
                'total_year' => $wTotalYear,
                'filled_months' => $wFilledCount,
                'compliance_percent' => round(($wFilledCount / 12) * 100),
            ];
        }

        $grandTotalYearAll = array_sum($monthlyTotalsAll);
        $wilkerGrandTotalYear = array_sum($wilkerGrandMonthly);

        return [
            'year' => $year,
            'wilker' => $wilker ?? 'all',
            'is_consolidated' => !$isFilteredWilker,
            'wilker_options' => $wilkerOptions,
            'kpi' => $kpi,
            'matrix' => $matrix,
            'traffic_chart' => $trafficChart,
            'document_chart' => $filteredDocData,
            'wilker_comparison' => $wilkerComparison,
            'health_chart' => $healthChart,
            'environment_chart' => $environmentChart,
            'summary_table' => [
                'items' => $summaryTable,
                'monthly_totals' => $monthlyTotalsAll,
                'total_year' => $grandTotalYearAll,
                'section_totals' => $sectionTotals,
            ],
            'wilker_summary' => [
                'items' => $wilkerMonthlyTotals,
                'monthly_totals' => $wilkerGrandMonthly,
                'total_year' => $wilkerGrandTotalYear,
            ],
        ];
    }

    /**
     * Compute formula_rows sums iteratively until stable.
     */
    protected function resolveFormulas(array $rawVals): array
    {
        $vals = [];
        foreach ($rawVals as $k => $v) {
            if ($v !== null && $v !== '') {
                $vals[$k] = is_numeric($v) ? (float)$v : $v;
            }
        }

        $changed = true;
        $passes = 0;
        while ($changed && $passes < 10) {
            $changed = false;
            $passes++;
            foreach ($this->schema['sections'] ?? [] as $sec) {
                foreach ($sec['items'] ?? [] as $item) {
                    if (($item['type'] ?? '') === 'summary' && !empty($item['formula_rows'])) {
                        $sum = 0;
                        foreach ($item['formula_rows'] as $r) {
                            $sum += (isset($vals[$r]) && is_numeric($vals[$r])) ? (float)$vals[$r] : 0;
                        }
                        $cur = $vals[$item['excel_row']] ?? null;
                        if ($cur !== $sum) {
                            $vals[$item['excel_row']] = $sum;
                            $changed = true;
                        }
                    }
                }
            }
        }

        return $vals;
    }

    /**
     * Normalize wilker name to canonical standard.
     */
    protected function canonicalizeWilker(?string $wilker, array $options): string
    {
        if (!$wilker) return $options[0] ?? 'Induk BKK Bandung';

        foreach ($options as $opt) {
            if (strcasecmp($opt, $wilker) === 0) return $opt;
        }

        if (stripos($wilker, 'Husen') !== false || stripos($wilker, 'Husein') !== false) {
            return 'POS Bandara Husen';
        }
        if (stripos($wilker, 'Kertajati') !== false) return 'Wilker Kertajati';
        if (stripos($wilker, 'Cirebon') !== false) return 'Wilker Cirebon';
        if (stripos($wilker, 'Indramayu') !== false || stripos($wilker, 'Balongan') !== false) return 'Wilker Indramayu';
        if (stripos($wilker, 'Patimban') !== false) return 'Wilker Patimban';
        if (stripos($wilker, 'Ratu') !== false) return 'Wilker Pelabuharatu';
        if (stripos($wilker, 'Induk') !== false || stripos($wilker, 'Bandung') !== false) return 'Induk BKK Bandung';

        return $wilker;
    }
}

