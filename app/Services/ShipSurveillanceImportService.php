<?php

namespace App\Services;

use App\Models\KantorWilker;
use App\Models\ShipHealthSurveillance;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

class ShipSurveillanceImportService
{
    /**
     * Map old raw port names from Simply to official Sadaya Wilker names.
     */
    protected array $portMapping = [
        'patimban' => 'Wilayah Kerja Patimban',
        'cirebon' => 'Wilayah Kerja Cirebon',
        'indramayu' => 'Wilayah Kerja Indramayu',
        'pelabuharatu' => 'Wilayah Kerja Pelabuharatu',
        'palabuhanratu' => 'Wilayah Kerja Pelabuharatu',
        'pelabuhanratu' => 'Wilayah Kerja Pelabuharatu',
    ];

    /**
     * Test PDO MySQL Connection to Simply database and return summary statistics.
     */
    public function testConnection(array $config): array
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int)($config['port'] ?? 3306);
        $database = $config['database'] ?? 'bkkr3927_cop';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? 'mysql';
        $table = $config['table'] ?? 'tbl_pasien';

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );

            // Check if table exists
            $tableCheck = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetchAll();
            if (empty($tableCheck)) {
                return [
                    'success' => false,
                    'message' => "Tabel '{$table}' tidak ditemukan di database '{$database}'. Pastikan nama tabel benar.",
                ];
            }

            // Gather statistics
            $stats = $pdo->query("SELECT 
                COUNT(*) as total, 
                MIN(date_updated) as min_date, 
                MAX(date_updated) as max_date,
                COUNT(CASE WHEN (origin_country IS NOT NULL AND origin_country != '' AND UPPER(origin_country) NOT IN ('INDONESIA', '-', 'IDN')) OR datang_dari LIKE '%singapore%' OR datang_dari LIKE '%singapur%' OR datang_dari LIKE '%malaysia%' THEN 1 END) as ln_count,
                COUNT(CASE WHEN kresiko = 'TINGGI' THEN 1 END) as high_risk_count,
                COUNT(CASE WHEN status_verifikasi = 'verified' THEN 1 END) as verified_count
            FROM {$table}")->fetch(PDO::FETCH_ASSOC);

            // Ports break-down
            $portsRaw = $pdo->query("SELECT pelabuhan, COUNT(*) as c FROM {$table} GROUP BY pelabuhan ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC);
            $ports = [];
            foreach ($portsRaw as $p) {
                $rawName = trim($p['pelabuhan']);
                $normalized = $this->normalizePort($rawName);
                $ports[] = [
                    'raw' => $rawName ?: '(Kosong / Tidak Tercatat)',
                    'normalized' => $normalized,
                    'count' => (int)$p['c'],
                ];
            }

            return [
                'success' => true,
                'message' => "Koneksi ke database '{$database}' berhasil!",
                'stats' => [
                    'total' => (int)($stats['total'] ?? 0),
                    'luar_negeri' => (int)($stats['ln_count'] ?? 0),
                    'dalam_negeri' => (int)($stats['total'] ?? 0) - (int)($stats['ln_count'] ?? 0),
                    'tinggi_risiko' => (int)($stats['high_risk_count'] ?? 0),
                    'terverifikasi' => (int)($stats['verified_count'] ?? 0),
                    'min_date' => $stats['min_date'] ? date('d/m/Y', strtotime($stats['min_date'])) : '-',
                    'max_date' => $stats['max_date'] ? date('d/m/Y', strtotime($stats['max_date'])) : '-',
                    'ports' => $ports,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke MySQL Simply: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import records directly from Simply MySQL database into Sadaya.
     */
    public function importFromMysql(array $config, array $options = []): array
    {
        $startTime = microtime(true);

        $host = $config['host'] ?? '127.0.0.1';
        $port = (int)($config['port'] ?? 3306);
        $database = $config['database'] ?? 'bkkr3927_cop';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? 'mysql';
        $table = $config['table'] ?? 'tbl_pasien';

        $skipDuplicate = $options['skip_duplicate'] ?? true;
        $recalculatePnbp = $options['recalculate_pnbp'] ?? true;
        $normalizePorts = $options['normalize_ports'] ?? true;
        $truncateFirst = $options['truncate_first'] ?? false;
        $dryRun = $options['dry_run'] ?? false;

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi database gagal: ' . $e->getMessage(),
            ];
        }

        if ($truncateFirst && !$dryRun) {
            ShipHealthSurveillance::truncate();
        }

        // Cache existing users to map verifiers
        $usersMap = User::pluck('id', 'name')->toArray();
        $emailMap = User::pluck('id', 'email')->toArray();

        // Query all rows
        $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY id ASC");

        $totalProcessed = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $totalPnbp = 0;
        $errors = [];

        $batch = [];
        $batchSize = 250;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalProcessed++;

            try {
                $mapped = $this->transformRow($row, [
                    'normalize_ports' => $normalizePorts,
                    'recalculate_pnbp' => $recalculatePnbp,
                    'users_map' => $usersMap,
                    'email_map' => $emailMap,
                ]);

                if ($skipDuplicate) {
                    $exists = ShipHealthSurveillance::where('registration_no', $mapped['registration_no'])
                        ->orWhere(function ($q) use ($mapped) {
                            $q->where('nama_kapal', $mapped['nama_kapal'])
                              ->where('tanggal_kedatangan', $mapped['tanggal_kedatangan'])
                              ->where('datang_dari', $mapped['datang_dari']);
                        })->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }
                }

                $totalPnbp += $mapped['estimasi_pnbp'];

                if (!$dryRun) {
                    $batch[] = $mapped;
                    if (count($batch) >= $batchSize) {
                        $this->insertBatch($batch);
                        $importedCount += count($batch);
                        $batch = [];
                    }
                } else {
                    $importedCount++;
                }
            } catch (Throwable $e) {
                $failedCount++;
                if (count($errors) < 10) {
                    $errors[] = "Baris ID {$row['id']}: " . $e->getMessage();
                }
            }
        }

        // Flush remaining batch
        if (!empty($batch) && !$dryRun) {
            $this->insertBatch($batch);
            $importedCount += count($batch);
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'message' => "Proses migrasi selesai dalam {$duration} detik.",
            'total_processed' => $totalProcessed,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'total_pnbp' => $totalPnbp,
            'duration' => $duration,
            'errors' => $errors,
        ];
    }

    /**
     * Import records from an uploaded file (.sql, .json, or .csv).
     */
    public function importFromFile(UploadedFile $file, array $options = []): array
    {
        $startTime = microtime(true);
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        if ($extension === 'json') {
            $jsonContent = file_get_contents($path);
            $rows = json_decode($jsonContent, true);
            if (!is_array($rows)) {
                return ['success' => false, 'message' => 'Format file JSON tidak valid.'];
            }
        } elseif ($extension === 'csv') {
            $handle = fopen($path, 'r');
            if ($handle !== false) {
                $headers = fgetcsv($handle, 4096, ',');
                if ($headers) {
                    $headers = array_map(fn($h) => trim(str_replace(["\xEF\xBB\xBF", '"', "'"], '', $h)), $headers);
                    while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                        if (count($data) === count($headers)) {
                            $rows[] = array_combine($headers, $data);
                        }
                    }
                }
                fclose($handle);
            }
        } elseif ($extension === 'sql') {
            // Parse SQL INSERT INTO statements
            $sqlContent = file_get_contents($path);
            $rows = $this->parseSqlInserts($sqlContent);
            if (empty($rows)) {
                return ['success' => false, 'message' => 'Tidak ditemukan baris data INSERT INTO yang valid pada file SQL.'];
            }
        } else {
            return ['success' => false, 'message' => 'Format file tidak didukung. Harap upload file .sql, .json, atau .csv.'];
        }

        $skipDuplicate = $options['skip_duplicate'] ?? true;
        $recalculatePnbp = $options['recalculate_pnbp'] ?? true;
        $normalizePorts = $options['normalize_ports'] ?? true;
        $truncateFirst = $options['truncate_first'] ?? false;

        if ($truncateFirst) {
            ShipHealthSurveillance::truncate();
        }

        $usersMap = User::pluck('id', 'name')->toArray();
        $emailMap = User::pluck('id', 'email')->toArray();

        $totalProcessed = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $totalPnbp = 0;
        $errors = [];
        $batch = [];
        $batchSize = 250;

        foreach ($rows as $row) {
            $totalProcessed++;
            try {
                $mapped = $this->transformRow($row, [
                    'normalize_ports' => $normalizePorts,
                    'recalculate_pnbp' => $recalculatePnbp,
                    'users_map' => $usersMap,
                    'email_map' => $emailMap,
                ]);

                if ($skipDuplicate) {
                    $exists = ShipHealthSurveillance::where('registration_no', $mapped['registration_no'])
                        ->orWhere(function ($q) use ($mapped) {
                            $q->where('nama_kapal', $mapped['nama_kapal'])
                              ->where('tanggal_kedatangan', $mapped['tanggal_kedatangan'])
                              ->where('datang_dari', $mapped['datang_dari']);
                        })->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }
                }

                $totalPnbp += $mapped['estimasi_pnbp'];
                $batch[] = $mapped;

                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch);
                    $importedCount += count($batch);
                    $batch = [];
                }
            } catch (Throwable $e) {
                $failedCount++;
                if (count($errors) < 10) {
                    $errors[] = "Data ke-{$totalProcessed}: " . $e->getMessage();
                }
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
            $importedCount += count($batch);
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'message' => "Import file selesai dalam {$duration} detik.",
            'total_processed' => $totalProcessed,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'total_pnbp' => $totalPnbp,
            'duration' => $duration,
            'errors' => $errors,
        ];
    }

    /**
     * Transform a single tbl_pasien row into Sadaya ShipHealthSurveillance structure.
     */
    public function transformRow(array $row, array $options = []): array
    {
        $id = (int)($row['id'] ?? 0);
        $dateUpdated = !empty($row['date_updated']) ? $row['date_updated'] : date('Y-m-d H:i:s');
        $timestamp = strtotime($dateUpdated);
        $dateOnly = date('Y-m-d', $timestamp ?: time());

        // Unique Registration Number
        $registrationNo = sprintf('MDH-%s-%05d', date('Ymd', $timestamp ?: time()), $id);

        $namaKapal = strtoupper(trim($row['nama'] ?? 'KAPAL TANPA NAMA'));
        $datangDari = trim($row['datang_dari'] ?? '-');
        $originCountry = trim($row['origin_country'] ?? '');
        $negaraSinggah = trim($row['negara'] ?? '-');

        // Determine category: Luar Negeri vs Dalam Negeri
        $upperOrigin = strtoupper($originCountry);
        $upperDari = strtoupper($datangDari);
        $isLuarNegeri = false;

        if (!empty($originCountry) && !in_array($upperOrigin, ['INDONESIA', '-', 'IDN', 'INA'])) {
            $isLuarNegeri = true;
        } elseif (
            str_contains($upperDari, 'SINGAPORE') || 
            str_contains($upperDari, 'SINGAPUR') || 
            str_contains($upperDari, 'MALAYSIA') || 
            str_contains($upperDari, 'JAPAN') || 
            str_contains($upperDari, 'CHINA') ||
            str_contains($upperDari, 'KOREA') ||
            str_contains($upperDari, 'THAILAND') ||
            str_contains($upperDari, 'VIETNAM') ||
            str_contains($upperDari, 'TAIWAN') ||
            str_contains($upperDari, 'ANGOLA') ||
            str_contains($upperDari, 'AUSTRALIA')
        ) {
            $isLuarNegeri = true;
            if (empty($originCountry)) {
                $originCountry = 'Luar Negeri';
            }
        }

        $kategori = $isLuarNegeri ? 'Luar Negeri' : 'Dalam Negeri';

        // Gross Tonnage
        $gt = isset($row['gross_tonnage']) && is_numeric($row['gross_tonnage']) ? (int)$row['gross_tonnage'] : 0;
        $jmlCrew = isset($row['jml_crew']) && is_numeric($row['jml_crew']) ? (int)$row['jml_crew'] : 0;

        // Port Normalization
        $rawPort = trim($row['pelabuhan'] ?? '');
        if ($options['normalize_ports'] ?? true) {
            $pelabuhan = $this->normalizePort($rawPort);
        } else {
            $pelabuhan = $rawPort ?: 'Wilayah Kerja Patimban';
        }

        // Questions Pack
        $questions = [];
        for ($i = 1; $i <= 19; $i++) {
            $key = "q{$i}";
            if (isset($row[$key]) && is_numeric($row[$key])) {
                $questions[$key] = (int)$row[$key];
            }
        }
        for ($i = 1; $i <= 7; $i++) {
            $key = "x{$i}";
            if (isset($row[$key]) && is_numeric($row[$key])) {
                $questions[$key] = (int)$row[$key];
            }
        }

        // Skoring & Risk Level
        $skoring = isset($row['skoring']) && is_numeric($row['skoring']) ? (int)$row['skoring'] : 0;
        $kresiko = strtoupper(trim($row['kresiko'] ?? ''));
        if (!in_array($kresiko, ['RENDAH', 'SEDANG', 'TINGGI'])) {
            if ($skoring < 10) {
                $kresiko = 'RENDAH';
            } elseif ($skoring < 20) {
                $kresiko = 'SEDANG';
            } else {
                $kresiko = 'TINGGI';
            }
        }

        // Status Keputusan Sandar / Karantina
        $rawHasil = strtoupper(trim($row['hasilStatus'] ?? ''));
        if (in_array($rawHasil, ['ZONA KARANTINA', 'KARANTINA'])) {
            $hasilStatus = 'ZONA KARANTINA';
        } elseif ($rawHasil === 'SANDAR') {
            $hasilStatus = 'SANDAR';
        } else {
            $hasilStatus = ($kresiko === 'TINGGI') ? 'ZONA KARANTINA' : 'SANDAR';
        }

        // PNBP Calculation
        if ($options['recalculate_pnbp'] ?? true) {
            $estimasiPnbp = ShipHealthSurveillance::calculatePnbp($gt, $kategori);
        } else {
            $estimasiPnbp = 0;
        }

        // Verification Mapping
        $rawVerifStatus = strtolower(trim($row['status_verifikasi'] ?? 'pending'));
        $statusVerifikasi = in_array($rawVerifStatus, ['pending', 'verified', 'rejected']) ? $rawVerifStatus : 'pending';
        $catatanVerifikasi = !empty($row['catatan_verifikasi']) ? trim($row['catatan_verifikasi']) : null;
        $rawVerifiedBy = !empty($row['verified_by']) ? trim($row['verified_by']) : null;
        $verifiedAt = !empty($row['verified_at']) ? date('Y-m-d H:i:s', strtotime($row['verified_at'])) : null;

        $verifiedById = null;
        if ($rawVerifiedBy) {
            $usersMap = $options['users_map'] ?? [];
            $emailMap = $options['email_map'] ?? [];
            if (isset($usersMap[$rawVerifiedBy])) {
                $verifiedById = $usersMap[$rawVerifiedBy];
            } elseif (isset($emailMap[$rawVerifiedBy])) {
                $verifiedById = $emailMap[$rawVerifiedBy];
            }
        }

        return [
            'registration_no' => $registrationNo,
            'nama_kapal' => $namaKapal,
            'datang_dari' => $datangDari,
            'origin_country' => $originCountry ?: null,
            'pelabuhan' => $pelabuhan,
            'gross_tonnage' => $gt,
            'jml_crew' => $jmlCrew,
            'negara_singgah_14_hari' => $negaraSinggah ?: null,
            'kategori' => $kategori,
            'questions' => json_encode($questions),
            'skoring' => $skoring,
            'kresiko' => $kresiko,
            'hasil_status' => $hasilStatus,
            'surat_pernyataan_nahkoda' => !empty($row['surat_pernyataan_nahkoda']) ? trim($row['surat_pernyataan_nahkoda']) : null,
            'dokumen_mdh' => null,
            'dokumen_pendukung' => null,
            'estimasi_pnbp' => $estimasiPnbp,
            'status_verifikasi' => $statusVerifikasi,
            'catatan_verifikasi' => $catatanVerifikasi,
            'verified_by' => $verifiedById,
            'verifier_name' => $rawVerifiedBy,
            'verified_at' => $verifiedAt,
            'tanggal_kedatangan' => $dateOnly,
            'created_at' => date('Y-m-d H:i:s', $timestamp ?: time()),
            'updated_at' => date('Y-m-d H:i:s', $timestamp ?: time()),
        ];
    }

    /**
     * Normalize port names to official Wilker names.
     */
    public function normalizePort(string $raw): string
    {
        $clean = strtolower(trim($raw));
        if (isset($this->portMapping[$clean])) {
            return $this->portMapping[$clean];
        }

        foreach ($this->portMapping as $key => $official) {
            if (str_contains($clean, $key)) {
                return $official;
            }
        }

        if (empty($clean) || $clean === '-') {
            return 'Wilayah Kerja Patimban';
        }

        return ucwords($clean);
    }

    /**
     * Batch insert mapped records into ship_health_surveillances.
     */
    protected function insertBatch(array $records): void
    {
        DB::table('ship_health_surveillances')->insert($records);
    }

    /**
     * Simple SQL insert statement parser for backup files.
     */
    protected function parseSqlInserts(string $sql): array
    {
        $rows = [];
        preg_match_all("/INSERT\s+INTO\s+[`'\"]?(?:tbl_pasien|ship_health_surveillances)[`'\"]?\s*\(([^)]+)\)\s*VALUES\s*([^;]+);/is", $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $columns = array_map(fn($c) => trim(str_replace(['`', '"', "'"], '', $c)), explode(',', $match[1]));
            $valuesBlock = trim($match[2]);

            // Split tuples: (val1, val2), (val3, val4)
            preg_match_all('/\((.*?)\)(?:,|$)/s', $valuesBlock, $tupleMatches);
            foreach ($tupleMatches[1] as $tuple) {
                $rawVals = str_getcsv($tuple, ',', "'");
                $cleanedVals = array_map(function ($v) {
                    $v = trim($v);
                    if (strtoupper($v) === 'NULL') return null;
                    return trim($v, "'\"");
                }, $rawVals);

                if (count($columns) === count($cleanedVals)) {
                    $rows[] = array_combine($columns, $cleanedVals);
                }
            }
        }

        return $rows;
    }
}
