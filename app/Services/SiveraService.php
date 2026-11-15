<?php

namespace App\Services;

use App\Models\KantorWilker;
use App\Models\PrlSiveraVektor;
use App\Models\User;
use App\Support\DashboardCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiveraService
{
    /**
     * Resolve combined list of official and observed wilkers for SIVERA.
     *
     * @return array<int, string>
     */
    public function getWilkerOptions(): array
    {
        try {
            $officialWilkers = KantorWilker::pluck('nama')->toArray();
        } catch (\Throwable $e) {
            $officialWilkers = [];
        }

        $observedWilkers = PrlSiveraVektor::distinct()->pluck('wilker')->toArray();

        return array_values(array_unique(array_filter(array_merge($officialWilkers, $observedWilkers))));
    }

    /**
     * Build sorted inspector name options from users and historical SIVERA entries.
     *
     * @param  array<int, string>  $extraContributors
     * @return array<int, string>
     */
    public function getPemeriksaOptions(array $extraContributors = []): array
    {
        $usersList = User::orderBy('name')->pluck('name')->toArray();
        $inspList = PrlSiveraVektor::distinct()->whereNotNull('created_by_name')->pluck('created_by_name')->toArray();

        $pemeriksaAgg = [];
        foreach (array_merge($usersList, $inspList, $extraContributors) as $raw) {
            foreach (preg_split('/[,|]/', (string) $raw) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '' && ! is_numeric($trimmed)) {
                    $pemeriksaAgg[$trimmed] = true;
                }
            }
        }

        $pemeriksaOptions = array_keys($pemeriksaAgg);
        sort($pemeriksaOptions);

        return $pemeriksaOptions;
    }

    /**
     * Apply search, wilker, status, date, and vector-type filters to a SIVERA query.
     *
     * @param  array<string, mixed>  $filters
     */
    public function applyFilters(Builder $query, array $filters): void
    {
        $search = $filters['search'] ?? null;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('jenis_vektor', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('wilker', 'like', "%{$search}%")
                    ->orWhere('parameter_utama', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['wilker'])) {
            $query->where('wilker', $filters['wilker']);
        }

        $tingkatRisiko = $filters['tingkat_risiko'] ?? null;
        if ($tingkatRisiko) {
            $normalizedRisk = strtolower((string) $tingkatRisiko);
            if (in_array($normalizedRisk, ['rendah', 'ms', 'memenuhi syarat', 'aman'], true)) {
                $query->where('baku_mutu_status', 'Memenuhi Syarat');
            } elseif (in_array($normalizedRisk, ['tinggi', 'tms', 'di atas baku mutu', 'sedang', 'bahaya'], true)) {
                $query->where('baku_mutu_status', '!=', 'Memenuhi Syarat');
            } else {
                $query->where('baku_mutu_status', 'like', "%{$tingkatRisiko}%");
            }
        }

        if (! empty($filters['tgl_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tgl_mulai']);
        }
        if (! empty($filters['tgl_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tgl_selesai']);
        }
        if (! empty($filters['tahun'])) {
            $query->whereYear('tanggal', $filters['tahun']);
        }
        if (! empty($filters['bulan'])) {
            $query->whereMonth('tanggal', $filters['bulan']);
        }

        $vektor = (string) ($filters['sivera_vektor'] ?? '');
        if ($vektor === '' || in_array(strtolower($vektor), ['dashboard', 'semua data', 'semua', 'all'], true)) {
            return;
        }

        $normalized = strtolower($vektor);
        if ($normalized === 'jentik' || $normalized === 'jentik aedes') {
            $query->where('jenis_vektor', 'like', '%Jentik Aedes%');
        } elseif ($normalized === 'lalat') {
            $query->where('jenis_vektor', 'like', '%Lalat%');
        } elseif ($normalized === 'kecoa') {
            $query->where('jenis_vektor', 'like', '%Kecoa%');
        } elseif ($normalized === 'tikus') {
            $query->where('jenis_vektor', 'like', '%Tikus%');
        } elseif ($normalized === 'pinjal') {
            $query->where('jenis_vektor', 'like', '%Pinjal%');
        } elseif (str_contains($normalized, 'jentik anopheles') || str_contains($normalized, 'anopheles jentik')) {
            $this->applyAnophelesFilter($query, 'jentik');
        } elseif (str_contains($normalized, 'nyamuk anopheles') || str_contains($normalized, 'anopheles nyamuk')) {
            $this->applyAnophelesFilter($query, 'nyamuk');
        } else {
            $query->where('jenis_vektor', 'like', "%{$vektor}%");
        }
    }

    protected function applyAnophelesFilter(Builder $query, string $stage): void
    {
        if ($stage === 'jentik') {
            $query->where(function ($q) {
                $q->where('jenis_vektor', 'like', '%anopheles%jentik%')
                    ->orWhere('jenis_vektor', 'like', '%jentik%anopheles%')
                    ->orWhere('jenis_vektor', 'Anopheles (Jentik)')
                    ->orWhere('jenis_vektor', 'Jentik Anopheles');
            });

            return;
        }

        $query->where(function ($q) {
            $q->where('jenis_vektor', 'like', '%anopheles%nyamuk%')
                ->orWhere('jenis_vektor', 'like', '%nyamuk%anopheles%')
                ->orWhere('jenis_vektor', 'Anopheles (Nyamuk)')
                ->orWhere('jenis_vektor', 'Nyamuk Anopheles');
        });
    }

    /**
     * Compute aggregate SIVERA KPI metrics (cached via DashboardCache).
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        return DashboardCache::remember('sivera:metrics', 120, function (): array {
            $records = PrlSiveraVektor::query()
                ->get(['jenis_vektor', 'baku_mutu_status', 'nilai_indeks']);

            $totalSurveilans = $records->count();
            $totalMs = $records->where('baku_mutu_status', 'Memenuhi Syarat')->count();
            $totalTms = $totalSurveilans - $totalMs;
            $kepatuhanPct = $totalSurveilans > 0 ? round(($totalMs / $totalSurveilans) * 100, 1) : 0;

            $isAnophelesJentik = static fn (string $jv): bool => (str_contains($jv, 'anopheles') && str_contains($jv, 'jentik'))
                || $jv === 'anopheles (jentik)'
                || $jv === 'jentik anopheles';
            $isAnophelesNyamuk = static fn (string $jv): bool => (str_contains($jv, 'anopheles') && str_contains($jv, 'nyamuk'))
                || $jv === 'anopheles (nyamuk)'
                || $jv === 'nyamuk anopheles';

            $groups = [
                'jentik' => $records->filter(fn ($r) => str_contains(strtolower((string) $r->jenis_vektor), 'jentik aedes')),
                'lalat' => $records->filter(fn ($r) => str_contains(strtolower((string) $r->jenis_vektor), 'lalat')),
                'kecoa' => $records->filter(fn ($r) => str_contains(strtolower((string) $r->jenis_vektor), 'kecoa')),
                'tikus' => $records->filter(fn ($r) => str_contains(strtolower((string) $r->jenis_vektor), 'tikus')),
                'pinjal' => $records->filter(fn ($r) => str_contains(strtolower((string) $r->jenis_vektor), 'pinjal')),
                'anophelesJentik' => $records->filter(fn ($r) => $isAnophelesJentik(strtolower((string) $r->jenis_vektor))),
                'anophelesNyamuk' => $records->filter(fn ($r) => $isAnophelesNyamuk(strtolower((string) $r->jenis_vektor))),
            ];

            return [
                'totalSurveilans' => $totalSurveilans,
                'totalMs' => $totalMs,
                'totalTms' => $totalTms,
                'kepatuhanPct' => $kepatuhanPct,
                'jentikCount' => $groups['jentik']->count(),
                'jentikMs' => $groups['jentik']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'jentikAbjAvg' => round((float) ($groups['jentik']->avg('nilai_indeks') ?? 0), 1),
                'lalatCount' => $groups['lalat']->count(),
                'lalatMs' => $groups['lalat']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'lalatFiAvg' => round((float) ($groups['lalat']->avg('nilai_indeks') ?? 0), 1),
                'kecoaCount' => $groups['kecoa']->count(),
                'kecoaMs' => $groups['kecoa']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'kecoaIdxAvg' => round((float) ($groups['kecoa']->avg('nilai_indeks') ?? 0), 1),
                'tikusCount' => $groups['tikus']->count(),
                'tikusMs' => $groups['tikus']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'tikusTrapAvg' => round((float) ($groups['tikus']->avg('nilai_indeks') ?? 0), 1),
                'pinjalCount' => $groups['pinjal']->count(),
                'pinjalMs' => $groups['pinjal']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'anophelesJentikCount' => $groups['anophelesJentik']->count(),
                'anophelesJentikMs' => $groups['anophelesJentik']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
                'anophelesNyamukCount' => $groups['anophelesNyamuk']->count(),
                'anophelesNyamukMs' => $groups['anophelesNyamuk']->where('baku_mutu_status', 'Memenuhi Syarat')->count(),
            ];
        });
    }

    /**
     * Compute multi-dimensional SIVERA charts and per-wilker breakdown (cached via DashboardCache).
     *
     * @param  array<int, string>  $wilkerOptions
     * @return array<string, mixed>
     */
    public function computeDashboardData(array $wilkerOptions): array
    {
        return DashboardCache::remember(
            'sivera:dashboard:'.md5(implode('|', $wilkerOptions)),
            120,
            function () use ($wilkerOptions): array {
                $records = PrlSiveraVektor::all();

                $wilkerSet = [];
                foreach ($wilkerOptions as $wo) {
                    $wo = trim((string) $wo);
                    if ($wo !== '') {
                        $wilkerSet[$wo] = true;
                    }
                }
                foreach ($records as $r) {
                    $w = trim((string) $r->wilker);
                    if ($w !== '') {
                        $wilkerSet[$w] = true;
                    }
                }
                $labels = array_keys($wilkerSet);
                sort($labels);
                if (empty($labels)) {
                    $labels = ['Wilayah Kerja Patimban', 'Wilayah Kerja Cirebon', 'Wilayah Kerja Indramayu', 'Wilayah Kerja Pelabuharatu'];
                }

                $summary = [
                    'total_surveilans' => $records->count(),
                    'jentik' => 0,
                    'kontainer_positif' => 0,
                    'kontainer_diperiksa' => 0,
                    'rumah_diperiksa' => 0,
                    'rumah_positif' => 0,
                    'abj_avg' => 100,
                    'lalat' => 0,
                    'avg_lalat' => 0,
                    'lalat_ms' => 0,
                    'lalat_tms' => 0,
                    'kecoa' => 0,
                    'kecoa_perangkap' => 0,
                    'kecoa_tertangkap' => 0,
                    'avg_kecoa' => 0,
                    'tikus' => 0,
                    'tikus_perangkap' => 0,
                    'tikus_tertangkap' => 0,
                    'avg_success_trap' => 0,
                    'pinjal' => 0,
                    'pinjal_umum' => 0,
                    'pinjal_khusus' => 0,
                    'anopheles_jentik' => 0,
                    'anopheles_nyamuk' => 0,
                    'avg_indeks_habitat' => 0,
                    'avg_mbr' => 0,
                    'total_ms' => 0,
                    'total_tms' => 0,
                    'kepatuhan_pct' => 100,
                ];

                $kecoaSpeciesList = [
                    'Periplatena americana' => 'P. americana',
                    'Blatella germanica' => 'B. germanica',
                    'Supella longipalpa' => 'S. longipalpa',
                    'Neostylopiga rhombifolia' => 'N. rhombifolia',
                    'Lain-Lain' => 'Lain-Lain',
                ];

                $kecoaChart = [];
                foreach ($kecoaSpeciesList as $short) {
                    $kecoaChart[$short] = array_fill(0, count($labels), 0);
                }

                $lalatStatusChart = [
                    'MS' => array_fill(0, count($labels), 0),
                    'TMS' => array_fill(0, count($labels), 0),
                ];

                $jenisKontainerChart = [];

                $tikusSpeciesList = [
                    'Rattus norvegicus' => 'R. norvegicus',
                    'Rattus tanezumi' => 'R. tanezumi',
                    'Mus musculus' => 'M. musculus',
                    'Suncus murinus' => 'S. murinus',
                    'Lain-Lain' => 'Lain-Lain',
                ];

                $spesiesTikusChart = [];
                foreach ($tikusSpeciesList as $short) {
                    $spesiesTikusChart[$short] = array_fill(0, count($labels), 0);
                }

                $wilkerDetail = [];
                foreach ($labels as $w) {
                    $wilkerDetail[$w] = [
                        'wilker' => $w,
                        'total_surveilans' => 0,
                        'jentik_survei' => 0,
                        'jentik_kontainer_diperiksa' => 0,
                        'jentik_kontainer_positif' => 0,
                        'jentik_rumah_diperiksa' => 0,
                        'jentik_rumah_positif' => 0,
                        'abj' => 100,
                        'lalat_survei' => 0,
                        'lalat_rata_sum' => 0,
                        'lalat_avg' => 0,
                        'lalat_ms' => 0,
                        'lalat_tms' => 0,
                        'kecoa_survei' => 0,
                        'kecoa_perangkap' => 0,
                        'kecoa_tertangkap' => 0,
                        'kecoa_kepadatan' => 0,
                        'tikus_survei' => 0,
                        'tikus_perangkap' => 0,
                        'tikus_tertangkap' => 0,
                        'tikus_success_trap_sum' => 0,
                        'tikus_success_trap_avg' => 0,
                        'pinjal_survei' => 0,
                        'pinjal_umum' => 0,
                        'pinjal_khusus' => 0,
                        'anopheles_jentik_survei' => 0,
                        'anopheles_habitat_sum' => 0,
                        'anopheles_habitat_avg' => 0,
                        'anopheles_nyamuk_survei' => 0,
                        'anopheles_mbr_sum' => 0,
                        'anopheles_mbr_avg' => 0,
                    ];
                }

                $lalatValues = [];
                $tikusTrapValues = [];
                $abjValues = [];
                $anophelesHabitatValues = [];
                $anophelesMbrValues = [];

                foreach ($records as $r) {
                    $w = trim((string) ($r->wilker ?: $labels[0]));
                    $wIdx = array_search($w, $labels);
                    if ($wIdx === false) {
                        $w = $labels[0];
                        $wIdx = 0;
                    }

                    $jv = strtolower((string) ($r->jenis_vektor ?: ''));
                    $dt = is_array($r->detail_data) ? $r->detail_data : (json_decode((string) $r->detail_data, true) ?: []);

                    $wilkerDetail[$w]['total_surveilans']++;

                    if (str_contains($jv, 'jentik') && ! str_contains($jv, 'anopheles')) {
                        $summary['jentik']++;
                        $kp = (int) ($dt['jml_kontainer_positif'] ?? 0);
                        $kd = (int) ($dt['jml_kontainer_diperiksa'] ?? 0);
                        $rp = (int) ($dt['jml_rumah_positif'] ?? 0);
                        $rd = (int) ($dt['jml_rumah_diperiksa'] ?? 0);

                        $summary['kontainer_positif'] += $kp;
                        $summary['kontainer_diperiksa'] += $kd;
                        $summary['rumah_positif'] += $rp;
                        $summary['rumah_diperiksa'] += $rd;

                        $abj = $r->nilai_indeks > 0 ? (float) $r->nilai_indeks : ($rd > 0 ? round((($rd - $rp) / $rd) * 100, 1) : 100);
                        $abjValues[] = $abj;

                        $wilkerDetail[$w]['jentik_survei']++;
                        $wilkerDetail[$w]['jentik_kontainer_positif'] += $kp;
                        $wilkerDetail[$w]['jentik_kontainer_diperiksa'] += $kd;
                        $wilkerDetail[$w]['jentik_rumah_positif'] += $rp;
                        $wilkerDetail[$w]['jentik_rumah_diperiksa'] += $rd;

                        $rawK = (string) ($dt['jenis_kontainer'] ?? '');
                        $parts = array_filter(array_map('trim', explode(',', $rawK)));
                        foreach ($parts as $p) {
                            $jenisKontainerChart[$p] = ($jenisKontainerChart[$p] ?? 0) + 1;
                        }
                    } elseif (str_contains($jv, 'lalat')) {
                        $summary['lalat']++;
                        $fi = (float) ($r->nilai_indeks ?: ($dt['rata_rata'] ?? 0));
                        $lalatValues[] = $fi;
                        $isMs = $fi < 2.0;
                        if ($isMs) {
                            $summary['lalat_ms']++;
                            $lalatStatusChart['MS'][$wIdx]++;
                            $wilkerDetail[$w]['lalat_ms']++;
                        } else {
                            $summary['lalat_tms']++;
                            $lalatStatusChart['TMS'][$wIdx]++;
                            $wilkerDetail[$w]['lalat_tms']++;
                        }

                        $wilkerDetail[$w]['lalat_survei']++;
                        $wilkerDetail[$w]['lalat_rata_sum'] += $fi;
                    } elseif (str_contains($jv, 'kecoa')) {
                        $summary['kecoa']++;
                        $kt = (int) ($dt['jml_kecoa_tertangkap'] ?? 0);
                        $kp = (int) ($dt['perangkap_terpasang'] ?? 0);
                        $summary['kecoa_tertangkap'] += $kt;
                        $summary['kecoa_perangkap'] += $kp;

                        $wilkerDetail[$w]['kecoa_survei']++;
                        $wilkerDetail[$w]['kecoa_tertangkap'] += $kt;
                        $wilkerDetail[$w]['kecoa_perangkap'] += $kp;

                        $spesiesRaw = $dt['spesies_tertangkap'] ?? null;
                        if (is_string($spesiesRaw)) {
                            $spesiesRaw = json_decode($spesiesRaw, true);
                        }
                        if (is_array($spesiesRaw)) {
                            foreach ($spesiesRaw as $spName => $count) {
                                $lbl = $kecoaSpeciesList[$spName] ?? 'Lain-Lain';
                                $kecoaChart[$lbl][$wIdx] += (int) $count;
                            }
                        }
                    } elseif (str_contains($jv, 'tikus')) {
                        $summary['tikus']++;
                        $tt = (int) ($dt['jml_tikus_tertangkap'] ?? 0);
                        $tp = (int) ($dt['jml_perangkap_dipasang'] ?? 0);
                        $trapPct = (float) ($r->nilai_indeks ?: ($dt['success_trap'] ?? 0));
                        $tikusTrapValues[] = $trapPct;
                        $summary['tikus_tertangkap'] += $tt;
                        $summary['tikus_perangkap'] += $tp;

                        $wilkerDetail[$w]['tikus_survei']++;
                        $wilkerDetail[$w]['tikus_tertangkap'] += $tt;
                        $wilkerDetail[$w]['tikus_perangkap'] += $tp;
                        $wilkerDetail[$w]['tikus_success_trap_sum'] += $trapPct;

                        $rawSp = $dt['spesies'] ?? $dt['spesies_tikus'] ?? null;
                        if (is_string($rawSp) && str_starts_with(trim($rawSp), '{')) {
                            $rawSp = json_decode($rawSp, true);
                        }
                        if (is_array($rawSp)) {
                            foreach ($rawSp as $spName => $count) {
                                $lbl = $tikusSpeciesList[$spName] ?? 'Lain-Lain';
                                $spesiesTikusChart[$lbl][$wIdx] += (int) $count;
                            }
                        } elseif (is_string($rawSp) && trim($rawSp) !== '') {
                            $lbl = $tikusSpeciesList[trim($rawSp)] ?? 'Lain-Lain';
                            $spesiesTikusChart[$lbl][$wIdx] += 1;
                        }
                    } elseif (str_contains($jv, 'pinjal')) {
                        $summary['pinjal']++;
                        $pu = (int) ($dt['pinjal_umum'] ?? 0);
                        $pk = (int) ($dt['pinjal_khusus'] ?? 0);
                        $summary['pinjal_umum'] += $pu;
                        $summary['pinjal_khusus'] += $pk;

                        $wilkerDetail[$w]['pinjal_survei']++;
                        $wilkerDetail[$w]['pinjal_umum'] += $pu;
                        $wilkerDetail[$w]['pinjal_khusus'] += $pk;
                    } elseif (str_contains($jv, 'anopheles')) {
                        if (str_contains($jv, 'jentik')) {
                            $summary['anopheles_jentik']++;
                            $hi = (float) ($r->nilai_indeks ?: ($dt['indeks_habitat'] ?? 0));
                            $anophelesHabitatValues[] = $hi;
                            $wilkerDetail[$w]['anopheles_jentik_survei']++;
                            $wilkerDetail[$w]['anopheles_habitat_sum'] += $hi;
                        } else {
                            $summary['anopheles_nyamuk']++;
                            $mbr = (float) ($r->nilai_indeks ?: ($dt['mbr'] ?? 0));
                            $anophelesMbrValues[] = $mbr;
                            $wilkerDetail[$w]['anopheles_nyamuk_survei']++;
                            $wilkerDetail[$w]['anopheles_mbr_sum'] += $mbr;
                        }
                    }

                    if ($r->baku_mutu_status === 'Memenuhi Syarat') {
                        $summary['total_ms']++;
                    } else {
                        $summary['total_tms']++;
                    }
                }

                $summary['avg_lalat'] = count($lalatValues) > 0 ? round(array_sum($lalatValues) / count($lalatValues), 2) : 0;
                $summary['avg_success_trap'] = count($tikusTrapValues) > 0 ? round(array_sum($tikusTrapValues) / count($tikusTrapValues), 2) : 0;
                $summary['abj_avg'] = count($abjValues) > 0 ? round(array_sum($abjValues) / count($abjValues), 1) : 100;
                $summary['avg_kecoa'] = $summary['kecoa_perangkap'] > 0 ? round($summary['kecoa_tertangkap'] / $summary['kecoa_perangkap'], 2) : 0;
                $summary['avg_indeks_habitat'] = count($anophelesHabitatValues) > 0 ? round(array_sum($anophelesHabitatValues) / count($anophelesHabitatValues), 1) : 0;
                $summary['avg_mbr'] = count($anophelesMbrValues) > 0 ? round(array_sum($anophelesMbrValues) / count($anophelesMbrValues), 2) : 0;
                $summary['kepatuhan_pct'] = $summary['total_surveilans'] > 0 ? round(($summary['total_ms'] / $summary['total_surveilans']) * 100, 1) : 100;

                arsort($jenisKontainerChart);
                $formattedKontainer = [];
                $totalKontainerTemuan = array_sum($jenisKontainerChart);
                foreach ($jenisKontainerChart as $name => $count) {
                    $formattedKontainer[] = [
                        'name' => $name,
                        'count' => $count,
                        'pct' => $totalKontainerTemuan > 0 ? round(($count / $totalKontainerTemuan) * 100, 1) : 0,
                    ];
                }

                foreach ($wilkerDetail as $w => &$item) {
                    $item['lalat_avg'] = $item['lalat_survei'] > 0 ? round($item['lalat_rata_sum'] / $item['lalat_survei'], 2) : 0;
                    $item['tikus_success_trap_avg'] = $item['tikus_survei'] > 0 ? round($item['tikus_success_trap_sum'] / $item['tikus_survei'], 2) : 0;
                    $item['kecoa_kepadatan'] = $item['kecoa_perangkap'] > 0 ? round($item['kecoa_tertangkap'] / $item['kecoa_perangkap'], 2) : 0;
                    if ($item['jentik_rumah_diperiksa'] > 0) {
                        $item['abj'] = round((($item['jentik_rumah_diperiksa'] - $item['jentik_rumah_positif']) / $item['jentik_rumah_diperiksa']) * 100, 1);
                    } elseif ($item['jentik_kontainer_diperiksa'] > 0) {
                        $item['abj'] = round((($item['jentik_kontainer_diperiksa'] - $item['jentik_kontainer_positif']) / $item['jentik_kontainer_diperiksa']) * 100, 1);
                    } else {
                        $item['abj'] = 100;
                    }
                    $item['anopheles_habitat_avg'] = $item['anopheles_jentik_survei'] > 0 ? round($item['anopheles_habitat_sum'] / $item['anopheles_jentik_survei'], 1) : 0;
                    $item['anopheles_mbr_avg'] = $item['anopheles_nyamuk_survei'] > 0 ? round($item['anopheles_mbr_sum'] / $item['anopheles_nyamuk_survei'], 2) : 0;
                }
                unset($item);

                return [
                    'labels' => $labels,
                    'summary' => $summary,
                    'charts' => [
                        'kecoa' => $kecoaChart,
                        'lalat_status' => $lalatStatusChart,
                        'jentik_kontainer' => $formattedKontainer,
                        'jentik_kontainer_map' => $jenisKontainerChart,
                        'spesies_tikus' => $spesiesTikusChart,
                    ],
                    'wilker_detail' => array_values($wilkerDetail),
                ];
            }
        );
    }

    /**
     * Build the view payload needed to render SIVERA official surveillance & control PDFs.
     *
     * @return array<string, mixed>
     */
    public function buildPdfData(PrlSiveraVektor $row, ?string $tipe, ?string $kopBase64, string $fallbackActor): array
    {
        $jv = $row->jenis_vektor ?? '';
        $detailData = is_array($row->detail_data) ? $row->detail_data : (json_decode((string) $row->detail_data, true) ?: []);
        $pengendalian = $detailData['pengendalian'] ?? [];

        $tglTarget = ! empty($pengendalian['tanggal'])
            ? $pengendalian['tanggal']
            : (! empty($detailData['tanggal_selesai']) ? $detailData['tanggal_selesai'] : ($row->tanggal ? $row->tanggal->format('Y-m-d') : date('Y-m-d')));

        $hariTanggal = Carbon::parse($tglTarget)->locale('id')->isoFormat('dddd, D MMMM Y');
        $wilker = $row->wilker ?: 'Wilayah Kerja';
        $namaPegawai = $row->created_by_name ?: $fallbackActor;
        $persiapan = $pengendalian['persiapan'] ?? 'Persiapan alat perlindungan diri (APD), bahan pengendali, dan koordinasi lapangan.';
        $catatanPengendalian = $row->tindakan_pengendalian ?: ($pengendalian['catatan'] ?? '');

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
                if (! empty($pengendalian['nyamuk_fogging'])) {
                    $metode[] = 'Fogging / Pengasapan Termal';
                }
                if (! empty($pengendalian['nyamuk_fisik'])) {
                    $metode[] = 'Fisik (Raket nyamuk/Light trap)';
                }
                $metodePengendalian = ! empty($metode) ? implode(', ', $metode) : 'Kimia (Thermal Fogging)';

                if (! empty($pengendalian['nyamuk_fogging_ml'])) {
                    $ins = $pengendalian['nyamuk_fogging_ml'].' ml';
                    $sub = [];
                    if (! empty($pengendalian['nyamuk_fogging_bahan'])) {
                        $sub[] = 'Bahan: '.$pengendalian['nyamuk_fogging_bahan'];
                    }
                    if (! empty($pengendalian['nyamuk_fogging_merk'])) {
                        $sub[] = 'Merk: '.$pengendalian['nyamuk_fogging_merk'];
                    }
                    if (! empty($sub)) {
                        $ins .= ' ('.implode(', ', $sub).')';
                    }
                    $jumlahInsektisida = $ins;
                }
                $penggunaanSolar = ! empty($pengendalian['nyamuk_fogging_solar']) ? $pengendalian['nyamuk_fogging_solar'].' Liter' : '5 Liter';
                $penggunaanBensin = ! empty($pengendalian['nyamuk_fogging_bensin']) ? $pengendalian['nyamuk_fogging_bensin'].' Liter' : '2 Liter';
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
                if (! empty($pengendalian['jentik_kimia'])) {
                    $metode[] = 'Kimia (Larvasidasi / Abatisasi)';
                }
                if (! empty($pengendalian['jentik_fisik'])) {
                    $metode[] = 'Fisik (3M Plus: Menguras & Menutup)';
                }
                if (! empty($pengendalian['jentik_biologi'])) {
                    $metode[] = 'Biologi (BTI / Ikan Pemakan Jentik)';
                }
                $metodePengendalian = ! empty($metode) ? implode(', ', $metode) : 'Kimia (Larvasidasi selektif) & Fisik (PSN)';

                $ins = [];
                if (! empty($pengendalian['jentik_kimia_gram'])) {
                    $ins[] = $pengendalian['jentik_kimia_gram'].' gram';
                }
                if (! empty($pengendalian['jentik_kimia_sachet'])) {
                    $ins[] = $pengendalian['jentik_kimia_sachet'].' sachet';
                }
                $jumlahInsektisida = ! empty($ins) ? implode(' / ', $ins) : ($row->tindakan_pengendalian ?: '-');
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
            if (! empty($pengendalian['spraying'])) {
                $metode[] = 'Residual Spraying';
                if (! empty($pengendalian['spraying_ml'])) {
                    $jumlahInsektisida = $pengendalian['spraying_ml'].' ml (Spraying)';
                }
            }
            if (! empty($pengendalian['granule'])) {
                $metode[] = 'Umpan Granule';
                if (! empty($pengendalian['granule_gram'])) {
                    $jumlahInsektisida = $pengendalian['granule_gram'].' gram (Granule)';
                }
            }
            $metodePengendalian = ! empty($metode) ? 'Kimia ('.implode(', ', $metode).')' : 'Kimia / Fisika (Sanitasi & Fly Trap)';

            if (! empty($detailData['detail_lokasi']) && is_array($detailData['detail_lokasi'])) {
                $tabelLokasiLalat = $detailData['detail_lokasi'];
            } elseif (! empty($detailData['lokasi_items']) && is_array($detailData['lokasi_items'])) {
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
            if (! empty($pengendalian['retrapping'])) {
                $metode[] = 'Pemasangan Retrapping';
                $jumlahInsektisida = ($pengendalian['retrapping_trap'] ?? '10').' unit trap';
            }
            if (! empty($pengendalian['gel_bait'])) {
                $metode[] = 'Aplikasi Gel Baiting';
                $jumlahInsektisida = ($pengendalian['gel_bait_gram'] ?? '20').' gram gel';
            }
            $metodePengendalian = ! empty($metode) ? implode(', ', $metode) : 'Fisik (Trapping) & Kimia (Gel Baiting)';
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
            $jumlahInsektisida = ($detailData['jml_perangkap_dipasang'] ?? 50).' trap terpasang';
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

            if (! empty($detailData['tangkapan_dalam']) && ! empty($detailData['tangkapan_luar'])) {
                $slots = [
                    '18_19' => '18.00 - 19.00', '19_20' => '19.00 - 20.00', '20_21' => '20.00 - 21.00',
                    '21_22' => '21.00 - 22.00', '22_23' => '22.00 - 23.00', '23_24' => '23.00 - 24.00',
                    '24_01' => '24.00 - 01.00', '01_02' => '01.00 - 02.00', '02_03' => '02.00 - 03.00',
                    '03_04' => '03.00 - 04.00', '04_05' => '04.00 - 05.00', '05_06' => '05.00 - 06.00',
                ];
                $tabelAnopheles12jam = [];
                foreach ($slots as $k => $lbl) {
                    $d = (int) ($detailData['tangkapan_dalam'][$k] ?? 0);
                    $l = (int) ($detailData['tangkapan_luar'][$k] ?? 0);
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

        return [
            'title' => 'Laporan '.ucwords(strtolower($judulKegiatan)),
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
            'kop' => $kopBase64,
        ];
    }

    /**
     * Stream all SIVERA vector records as UTF-8 BOM CSV.
     */
    public function streamCsvExport(): StreamedResponse
    {
        $filename = 'Export_PRL_SIVERA_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
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
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Load the Kop Surat PNG encoded as data URI for PDF templates.
     */
    public static function getKopSuratBase64(): ?string
    {
        $possibleKopPaths = [
            public_path('dist/img/Kop_BKK_bandung.png'),
            public_path('images/Kop_BKK_bandung.png'),
            public_path('storage/images/Kop_BKK_bandung.png'),
        ];

        foreach ($possibleKopPaths as $kp) {
            if (file_exists($kp)) {
                return 'data:image/png;base64,'.base64_encode((string) file_get_contents($kp));
            }
        }

        return null;
    }
}
