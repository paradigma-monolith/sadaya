<?php

namespace App\Services;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\KantorWilker;
use App\Models\ShipHealthSurveillance;
use App\Models\ShipSanitationInspection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use PDO;
use Throwable;

class SimplyDataMigrationService
{
    protected array $portMapping = [
        'patimban' => 'Wilayah Kerja Patimban',
        'cirebon' => 'Wilayah Kerja Cirebon',
        'indramayu' => 'Wilayah Kerja Indramayu',
        'pelabuharatu' => 'Wilayah Kerja Pelabuharatu',
        'palabuhanratu' => 'Wilayah Kerja Pelabuharatu',
    ];

    /**
     * Module definitions with default database, table, and metadata.
     */
    public function getModuleConfigs(): array
    {
        return [
            'ship_surveillance' => [
                'id' => 'ship_surveillance',
                'name' => 'Pengawasan Kedatangan Kapal (TANGKAP)',
                'source_db' => 'bkkr3927_cop',
                'source_table' => 'tbl_pasien',
                'target_model' => ShipHealthSurveillance::class,
                'target_table' => 'ship_health_surveillances',
                'description' => 'Maritime Declaration of Health, faktor risiko, skoring, dan penetapan izin sandar/karantina.',
                'icon' => 'Ship',
                'color' => 'text-[#00A99D]',
                'bg_color' => 'bg-emerald-50 dark:bg-emerald-950/40',
            ],
            'aircraft_sanitation' => [
                'id' => 'aircraft_sanitation',
                'name' => 'Inspeksi Sanitasi Pesawat Udara',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'tc_aircraft_sanitation',
                'target_model' => AircraftSanitationInspection::class,
                'target_table' => 'aircraft_sanitation_inspections',
                'description' => 'Travel Consent & Aircraft Sanitation Checklist (kabin, toilet, galley, dokumen kesehatan).',
                'icon' => 'Plane',
                'color' => 'text-amber-600',
                'bg_color' => 'bg-amber-50 dark:bg-amber-950/40',
            ],
            'ship_sanitation' => [
                'id' => 'ship_sanitation',
                'name' => 'Inspeksi Sanitasi Kapal (SSCC / SSCEC)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'tc_sanitasi_kapal',
                'target_model' => ShipSanitationInspection::class,
                'target_table' => 'ship_sanitation_inspections',
                'description' => 'Sertifikat Sanitasi Kapal WHO IHR 2005 (Penerbitan / Pembebasan 32 area sanitasi).',
                'icon' => 'Anchor',
                'color' => 'text-cyan-600',
                'bg_color' => 'bg-cyan-50 dark:bg-cyan-950/40',
            ],
            'medical_alat' => [
                'id' => 'medical_alat',
                'name' => 'Pemakaian Alkes Medis Haji (P3K & Emergensi)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'tc_alat_kesehatan',
                'target_model' => FlightMedicalInspection::class,
                'target_table' => 'flight_medical_inspections',
                'description' => 'Log pemakaian kit alat kesehatan kotak P3K dan emergensi penerbangan haji.',
                'icon' => 'PackageCheck',
                'color' => 'text-rose-600',
                'bg_color' => 'bg-rose-50 dark:bg-rose-950/40',
            ],
            'medical_obat' => [
                'id' => 'medical_obat',
                'name' => 'Pemakaian Obat Medis Haji (P3K & Emergensi)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'tc_obat',
                'target_model' => FlightMedicalInspection::class,
                'target_table' => 'flight_medical_inspections',
                'description' => 'Log pemakaian obat-obatan kotak P3K dan emergensi penerbangan haji.',
                'icon' => 'Pill',
                'color' => 'text-purple-600',
                'bg_color' => 'bg-purple-50 dark:bg-purple-950/40',
            ],
            'users' => [
                'id' => 'users',
                'name' => 'Akun Pengguna & Petugas (Simply User)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'user',
                'target_model' => User::class,
                'target_table' => 'users',
                'description' => 'Sinkronisasi seluruh akun pengguna, username/NIP, password, dan hak akses dari Simply.',
                'icon' => 'Users',
                'color' => 'text-amber-600',
                'bg_color' => 'bg-amber-50 dark:bg-amber-950/40',
            ],
        ];
    }

    /**
     * Get live row counts in Sadaya for all target models.
     */
    public function getSadayaStats(): array
    {
        return [
            'ship_surveillance' => ShipHealthSurveillance::count(),
            'aircraft_sanitation' => AircraftSanitationInspection::count(),
            'ship_sanitation' => ShipSanitationInspection::count(),
            'medical_alat' => FlightMedicalInspection::where('type', 'alat')->count(),
            'medical_obat' => FlightMedicalInspection::where('type', 'obat')->count(),
            'users' => User::count(),
        ];
    }

    /**
     * Resolve effective credentials and target DB/table for a module.
     * Checks per-module credentials first, falls back to global credentials, then module default config.
     */
    public function resolveModuleCredentials(string $module, array $credentials): array
    {
        $configs = $this->getModuleConfigs();
        $cfg = $configs[$module] ?? [];

        // Check if credentials are nested under modules[module]
        $modCreds = $credentials['modules'][$module] ?? [];

        return [
            'host' => !empty($modCreds['host']) ? $modCreds['host'] : ($credentials['host'] ?? '127.0.0.1'),
            'port' => (int)(!empty($modCreds['port']) ? $modCreds['port'] : ($credentials['port'] ?? 3306)),
            'username' => isset($modCreds['username']) ? $modCreds['username'] : ($credentials['username'] ?? 'root'),
            'password' => isset($modCreds['password']) ? $modCreds['password'] : ($credentials['password'] ?? 'mysql'),
            'database' => !empty($modCreds['database']) ? $modCreds['database'] : (!empty($credentials['database']) ? $credentials['database'] : ($cfg['source_db'] ?? '')),
            'table' => !empty($modCreds['table']) ? $modCreds['table'] : (!empty($credentials['table']) ? $credentials['table'] : ($cfg['source_table'] ?? '')),
        ];
    }

    /**
     * Test connection to MySQL and probe a single module.
     */
    public function testModule(string $module, array $credentials): array
    {
        $configs = $this->getModuleConfigs();
        if (!isset($configs[$module])) {
            return [
                'module' => $module,
                'name' => $module,
                'connected' => false,
                'table_exists' => false,
                'count' => 0,
                'error' => "Modul '{$module}' tidak ditemukan.",
            ];
        }

        $cfg = $configs[$module];
        $cred = $this->resolveModuleCredentials($module, $credentials);
        $host = $cred['host'];
        $port = $cred['port'];
        $username = $cred['username'];
        $password = $cred['password'];
        $dbName = $cred['database'];
        $tblName = $cred['table'];

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 4,
                ]
            );
        } catch (Throwable $e) {
            return [
                'module' => $module,
                'name' => $cfg['name'],
                'host' => $host,
                'port' => $port,
                'database' => $dbName,
                'table' => $tblName,
                'connected' => false,
                'table_exists' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }

        try {
            $check = $pdo->query("SHOW TABLES LIKE '{$tblName}'")->fetchAll();
            if (empty($check)) {
                return [
                    'module' => $module,
                    'name' => $cfg['name'],
                    'host' => $host,
                    'port' => $port,
                    'database' => $dbName,
                    'table' => $tblName,
                    'connected' => true,
                    'table_exists' => false,
                    'count' => 0,
                    'error' => "Tabel '{$tblName}' belum ada di database {$dbName}.",
                ];
            }

            $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `{$tblName}`")->fetchColumn();
            $minDate = null;
            $maxDate = null;
            try {
                $dateCol = ($tblName === 'tbl_pasien') ? 'date_updated' : (($tblName === 'tc_aircraft_sanitation' || $tblName === 'tc_sanitasi_kapal') ? 'tanggal' : 'created_at');
                $dRow = $pdo->query("SELECT MIN({$dateCol}) as min_d, MAX({$dateCol}) as max_d FROM `{$tblName}`")->fetch(PDO::FETCH_ASSOC);
                $minDate = $dRow['min_d'] ? date('d/m/Y', strtotime($dRow['min_d'])) : null;
                $maxDate = $dRow['max_d'] ? date('d/m/Y', strtotime($dRow['max_d'])) : null;
            } catch (Throwable) {}

            return [
                'module' => $module,
                'name' => $cfg['name'],
                'host' => $host,
                'port' => $port,
                'database' => $dbName,
                'table' => $tblName,
                'connected' => true,
                'table_exists' => true,
                'count' => $cnt,
                'min_date' => $minDate,
                'max_date' => $maxDate,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'module' => $module,
                'name' => $cfg['name'],
                'host' => $host,
                'port' => $port,
                'database' => $dbName,
                'table' => $tblName,
                'connected' => true,
                'table_exists' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection to MySQL and probe all modules with individual credentials.
     */
    public function testAll(array $credentials): array
    {
        $modules = $this->getModuleConfigs();
        $results = [];
        $overallSuccess = true;

        foreach ($modules as $modKey => $cfg) {
            $modRes = $this->testModule($modKey, $credentials);
            if (!$modRes['connected'] || !$modRes['table_exists']) {
                $overallSuccess = false;
            }
            $results[$modKey] = $modRes;
        }

        return [
            'success' => $overallSuccess,
            'message' => $overallSuccess ? 'Koneksi ke semua database modul Simply berhasil diverifikasi.' : 'Sebagian database/tabel modul tidak dapat dihubungi.',
            'modules' => $results,
            'sadaya_current' => $this->getSadayaStats(),
        ];
    }

    /**
     * Migrate a single module from MySQL.
     */
    public function migrateModule(string $module, array $credentials, array $options = []): array
    {
        $startTime = microtime(true);
        $configs = $this->getModuleConfigs();
        if (!isset($configs[$module])) {
            return ['success' => false, 'message' => "Modul '{$module}' tidak dikenali."];
        }

        $cfg = $configs[$module];
        $cred = $this->resolveModuleCredentials($module, $credentials);
        $host = $cred['host'];
        $port = $cred['port'];
        $username = $cred['username'];
        $password = $cred['password'];
        $dbName = $cred['database'];
        $tblName = $cred['table'];

        $skipDuplicate = $options['skip_duplicate'] ?? true;
        $truncateFirst = $options['truncate_first'] ?? false;
        $dryRun = $options['dry_run'] ?? false;

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );
        } catch (Throwable $e) {
            return ['success' => false, 'message' => "Gagal terhubung ke {$dbName} ({$host}:{$port}): " . $e->getMessage()];
        }

        if ($truncateFirst && !$dryRun) {
            $this->truncateTarget($module);
        }

        $usersMap = User::pluck('id', 'name')->toArray();
        $emailMap = User::pluck('id', 'email')->toArray();

        $stmt = $pdo->query("SELECT * FROM `{$tblName}` ORDER BY 1 ASC");
        $totalProcessed = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];
        $batch = [];
        $batchSize = 200;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalProcessed++;
            try {
                $mapped = $this->transformRow($module, $row, [
                    'users_map' => $usersMap,
                    'email_map' => $emailMap,
                ]);

                if ($skipDuplicate && $this->isDuplicate($module, $mapped)) {
                    $skippedCount++;
                    continue;
                }

                if (!$dryRun) {
                    $batch[] = $mapped;
                    if (count($batch) >= $batchSize) {
                        $this->insertBatch($module, $batch);
                        $importedCount += count($batch);
                        $batch = [];
                    }
                } else {
                    $importedCount++;
                }
            } catch (Throwable $e) {
                $failedCount++;
                $rowId = $row['id'] ?? $row['id_user'] ?? $row['id_pasien'] ?? $totalProcessed;
                if (count($errors) < 5) {
                    $errors[] = "ID {$rowId}: " . $e->getMessage();
                }
            }
        }

        if (!empty($batch) && !$dryRun) {
            $this->insertBatch($module, $batch);
            $importedCount += count($batch);
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'module' => $module,
            'module_name' => $cfg['name'],
            'total_processed' => $totalProcessed,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'duration' => $duration,
            'errors' => $errors,
        ];
    }

    /**
     * Migrate all modules in sequence.
     */
    public function migrateAll(array $credentials, array $options = []): array
    {
        $startTime = microtime(true);
        $modules = array_keys($this->getModuleConfigs());
        $results = [];
        $totalImported = 0;
        $totalSkipped = 0;
        $totalProcessed = 0;

        foreach ($modules as $mod) {
            $res = $this->migrateModule($mod, $credentials, $options);
            $results[$mod] = $res;
            if ($res['success']) {
                $totalImported += $res['imported'];
                $totalSkipped += $res['skipped'];
                $totalProcessed += $res['total_processed'];
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'message' => "Migrasi seluruh data selesai dalam {$duration} detik.",
            'total_processed' => $totalProcessed,
            'total_imported' => $totalImported,
            'total_skipped' => $totalSkipped,
            'duration' => $duration,
            'details' => $results,
            'sadaya_current' => $this->getSadayaStats(),
        ];
    }

    /**
     * Import specific module from uploaded file (.sql, .json, .csv).
     */
    public function importFromFile(string $module, UploadedFile $file, array $options = []): array
    {
        $startTime = microtime(true);
        $configs = $this->getModuleConfigs();
        if (!isset($configs[$module])) {
            return ['success' => false, 'message' => "Modul '{$module}' tidak valid."];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $rows = [];

        if ($extension === 'json') {
            $rows = json_decode(file_get_contents($path), true);
            if (!is_array($rows)) return ['success' => false, 'message' => 'Format file JSON tidak valid.'];
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
            $sql = file_get_contents($path);
            $targetTable = $configs[$module]['source_table'];
            $rows = $this->parseSqlInserts($sql, $targetTable);
            if (empty($rows)) {
                return ['success' => false, 'message' => "Tidak ditemukan query INSERT INTO `{$targetTable}` pada file SQL."];
            }
        } else {
            return ['success' => false, 'message' => 'Format file tidak didukung. Harap upload .sql, .json, atau .csv.'];
        }

        if ($options['truncate_first'] ?? false) {
            $this->truncateTarget($module);
        }

        $usersMap = User::pluck('id', 'name')->toArray();
        $emailMap = User::pluck('id', 'email')->toArray();
        $skipDuplicate = $options['skip_duplicate'] ?? true;

        $totalProcessed = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];
        $batch = [];

        foreach ($rows as $row) {
            $totalProcessed++;
            try {
                $mapped = $this->transformRow($module, $row, [
                    'users_map' => $usersMap,
                    'email_map' => $emailMap,
                ]);

                if ($skipDuplicate && $this->isDuplicate($module, $mapped)) {
                    $skippedCount++;
                    continue;
                }

                $batch[] = $mapped;
                if (count($batch) >= 200) {
                    $this->insertBatch($module, $batch);
                    $importedCount += count($batch);
                    $batch = [];
                }
            } catch (Throwable $e) {
                $failedCount++;
                if (count($errors) < 5) {
                    $errors[] = "Baris {$totalProcessed}: " . $e->getMessage();
                }
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($module, $batch);
            $importedCount += count($batch);
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'module' => $module,
            'module_name' => $configs[$module]['name'],
            'total_processed' => $totalProcessed,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'duration' => $duration,
            'errors' => $errors,
        ];
    }

    /**
     * Transform raw MySQL row to Sadaya Eloquent model data based on module.
     */
    public function transformRow(string $module, array $row, array $options = []): array
    {
        $id = (int)($row['id'] ?? 0);
        $dateCreated = $row['created_at'] ?? $row['date_updated'] ?? date('Y-m-d H:i:s');
        $timestamp = strtotime($dateCreated) ?: time();

        switch ($module) {
            case 'ship_surveillance':
                return (new ShipSurveillanceImportService())->transformRow($row, $options);

            case 'aircraft_sanitation':
                $regNo = sprintf('AIR-%s-%05d', date('Ymd', strtotime($row['tanggal'] ?: $dateCreated)), $id);
                $tanggal = date('Y-m-d', strtotime($row['tanggal'] ?: $dateCreated));
                $waktu = !empty($row['waktu']) ? substr($row['waktu'], 0, 5) : '00:00';

                return [
                    'registration_no' => $regNo,
                    'pemilik' => strtoupper(trim($row['pemilik'] ?? 'OPERATOR PESAWAT')),
                    'tanda_kebangsaan' => strtoupper(trim($row['tanda_kebangsaan'] ?? '-')),
                    'tanggal' => $tanggal,
                    'waktu' => $waktu,
                    'kabin_penumpang' => $this->ensureJson($row['kabin_penumpang'] ?? null),
                    'kompartemen_toilet' => $this->ensureJson($row['kompartemen_toilet'] ?? null),
                    'galley' => $this->ensureJson($row['galley'] ?? null),
                    'dokumen_kesehatan' => $this->ensureJson($row['dokumen_kesehatan'] ?? null),
                    'pembuangan_kotoran' => $row['pembuangan_kotoran'] ?? 'ya',
                    'penanganan_air' => $this->ensureJson($row['penanganan_air'] ?? null),
                    'keadaan_makanan' => $this->ensureJson($row['keadaan_makanan'] ?? null),
                    'binatang_pengganggu' => $this->ensureJson($row['binatang_pengganggu'] ?? null),
                    'surat_sanitasi' => $row['surat_sanitasi'] ?? 'ada',
                    'kesimpulan' => !empty($row['kesimpulan']) ? $row['kesimpulan'] : 'layak',
                    'tindakan' => $row['tindakan'] ?? null,
                    'catatan_rekomendasi' => $row['rekomendasi'] ?? null,
                    'petugas_kabin_nama' => $row['petugas_kabin_nama'] ?? 'Awak Kabin',
                    'petugas_kabin_ttd' => $row['petugas_kabin_ttd'] ?? null,
                    'petugas_bkk_nama' => $row['petugas_bkk_nama'] ?? 'Petugas Karantina',
                    'petugas_bkk_ttd' => $row['petugas_bkk_ttd'] ?? null,
                    'foto_dokumentasi' => !empty($row['foto_geotagging']) ? json_encode([['path' => $row['foto_geotagging']]]) : null,
                    'status' => !empty($row['petugas_bkk_ttd']) ? 'approved' : 'submitted',
                    'created_at' => date('Y-m-d H:i:s', $timestamp),
                    'updated_at' => date('Y-m-d H:i:s', $timestamp),
                ];

            case 'medical_alat':
            case 'medical_obat':
                $type = ($module === 'medical_alat') ? 'alat' : 'obat';
                $regNo = sprintf('MED-%s-%s-%05d', strtoupper($type), date('Ymd', $timestamp), $id);

                return [
                    'registration_no' => $regNo,
                    'type' => $type,
                    'penerbangan_haji_h' => $row['penerbangan_haji_h'] ?? '1447 H',
                    'penerbangan_haji_m' => $row['penerbangan_haji_m'] ?? '2026 M',
                    'embarkasi' => $row['embarkasi'] ?? 'Kertajati (KJT)',
                    'nomor_kloter' => $row['nomor_kloter'] ?? '01',
                    'no_kontak' => $row['no_kontak'] ?? null,
                    'items_p3k' => $this->ensureJson($row['items_p3k'] ?? null),
                    'items_emergensi' => $this->ensureJson($row['items_emergensi'] ?? null),
                    'foto_geotagging' => $row['foto_geotagging'] ?? null,
                    'crew_nama' => $row['crew_nama'] ?? 'Awak Kabin',
                    'crew_ttd' => $row['crew_ttd'] ?? null,
                    'pelapor_nama' => $row['pelapor_nama'] ?? 'Petugas BKK',
                    'pelapor_ttd' => $row['pelapor_ttd'] ?? null,
                    'status' => !empty($row['pelapor_ttd']) ? 'verified' : 'submitted',
                    'keterangan' => null,
                    'created_at' => date('Y-m-d H:i:s', $timestamp),
                    'updated_at' => date('Y-m-d H:i:s', $timestamp),
                ];

            case 'ship_sanitation':
                $regNo = sprintf('SSCC-%s-%05d', date('Ymd', strtotime($row['tanggal'] ?: $dateCreated)), $id);
                $tanggal = date('Y-m-d', strtotime($row['tanggal'] ?: $dateCreated));
                $pelabuhan = (new ShipSurveillanceImportService())->normalizePort($row['pelabuhan'] ?? 'Wilayah Kerja Patimban');

                return [
                    'registration_no' => $regNo,
                    'pelabuhan' => $pelabuhan,
                    'tanggal' => $tanggal,
                    'sertifikat_mencatat' => $row['sertifikat_mencatat'] ?? 'Pembebasan',
                    'imo_no' => $row['imo_no'] ?? '-',
                    'jenis_kapal' => $row['jenis_kapal'] ?? 'Kapal Barang / Kargo',
                    'detail_jenis_kapal' => $row['detail_jenis_kapal'] ?? null,
                    'nama_kapal' => strtoupper(trim($row['nama_kapal'] ?? 'KAPAL TANPA NAMA')),
                    'bendera' => strtoupper(trim($row['bendera'] ?? 'INDONESIA')),
                    'berat_gt' => (int)($row['berat_gt'] ?? 0),
                    'kondisi_palka' => $row['kondisi_palka'] ?? 'Bersih',
                    'nama_pemeriksa' => $row['nama_pemeriksa'] ?? 'Petugas Karantina',
                    'alamat_pemeriksa' => $row['alamat_pemeriksa'] ?? null,
                    'areas' => $this->ensureJson($row['area_diperiksa'] ?? null),
                    'pejabat_penerbit' => $row['pejabat_penerbit'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                    'tanda_tangan' => $row['tanda_tangan'] ?? null,
                    'foto_geotagging' => $row['foto_geotagging'] ?? null,
                    'status' => !empty($row['tanda_tangan']) ? 'verified' : 'submitted',
                    'created_at' => date('Y-m-d H:i:s', $timestamp),
                    'updated_at' => date('Y-m-d H:i:s', $timestamp),
                ];

            case 'users':
                $idUser = (int)($row['id_user'] ?? $row['id'] ?? 0);
                $username = trim($row['username'] ?? '');
                $nama = trim($row['nama'] ?? ($username ?: 'Pengguna Simply'));
                $email = !empty($row['email']) ? trim($row['email']) : ($username ? "{$username}@bkkbandung.net" : null);
                $phone = !empty($row['no_telp']) ? trim($row['no_telp']) : null;
                $statusPegawai = !empty($row['status_pegawai_aktif']) ? trim($row['status_pegawai_aktif']) : 'Aktif';
                $password = $row['password'] ?? '';
                $roleId = (int)($row['id_role_access'] ?? 12863);

                return [
                    'id_user' => $idUser,
                    'name' => $nama,
                    'username' => $username,
                    'simply_user_id' => $idUser,
                    'email' => $email,
                    'phone' => $phone,
                    'status_pegawai_aktif' => $statusPegawai,
                    'password' => $password,
                    'role_id' => $roleId,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ];

            default:
                throw new \InvalidArgumentException("Modul {$module} belum didukung.");
        }
    }

    /**
     * Check if record is a duplicate in target Sadaya table.
     */
    protected function isDuplicate(string $module, array $mapped): bool
    {
        $reg = $mapped['registration_no'] ?? '';
        switch ($module) {
            case 'ship_surveillance':
                return ShipHealthSurveillance::where('registration_no', $reg)->exists();
            case 'aircraft_sanitation':
                return AircraftSanitationInspection::where('registration_no', $reg)->exists() ||
                    AircraftSanitationInspection::where('tanda_kebangsaan', $mapped['tanda_kebangsaan'])->where('tanggal', $mapped['tanggal'])->exists();
            case 'ship_sanitation':
                return ShipSanitationInspection::where('registration_no', $reg)->exists() ||
                    ShipSanitationInspection::where('nama_kapal', $mapped['nama_kapal'])->where('tanggal', $mapped['tanggal'])->exists();
            case 'medical_alat':
            case 'medical_obat':
                return FlightMedicalInspection::where('registration_no', $reg)->exists() ||
                    FlightMedicalInspection::where('type', $mapped['type'])->where('nomor_kloter', $mapped['nomor_kloter'])->where('embarkasi', $mapped['embarkasi'])->exists();
            case 'users':
                $uname = $mapped['username'] ?? '';
                $email = $mapped['email'] ?? '';
                $simplyId = $mapped['simply_user_id'] ?? null;
                return User::where(function ($q) use ($uname, $email, $simplyId) {
                    if ($simplyId) $q->where('simply_user_id', $simplyId);
                    if ($uname) $q->orWhere('username', $uname);
                    if ($email) $q->orWhere('email', $email);
                })->exists();
            default:
                return false;
        }
    }

    /**
     * Batch insert mapped records into target model's database table.
     */
    protected function insertBatch(string $module, array $records): void
    {
        if ($module === 'users') {
            $this->insertUserBatch($records);
            return;
        }
        $configs = $this->getModuleConfigs();
        $table = $configs[$module]['target_table'];
        DB::table($table)->insert($records);
    }

    /**
     * Insert or update user accounts and assign roles.
     */
    protected function insertUserBatch(array $records): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        foreach ($records as $item) {
            $username = $item['username'] ?? '';
            $email = $item['email'] ?? '';
            $simplyId = $item['simply_user_id'] ?? null;

            $user = User::where(function ($q) use ($username, $email, $simplyId) {
                if ($simplyId) $q->where('simply_user_id', $simplyId);
                if ($username) $q->orWhere('username', $username);
                if ($email) $q->orWhere('email', $email);
            })->first();

            if (!$user) {
                $user = new User();
            }

            $user->name = $item['name'] ?? $user->name ?? 'Pengguna';
            $user->username = $username ?: $user->username;
            $user->email = $email ?: ($user->email ?: ($username ? "{$username}@bkkbandung.net" : null));
            $user->simply_user_id = $simplyId ?: $user->simply_user_id;
            $user->phone = $item['phone'] ?? $user->phone;
            $user->status_pegawai_aktif = $item['status_pegawai_aktif'] ?? 'Aktif';
            if (!empty($item['password'])) {
                $user->password = $item['password'];
            }
            $user->save();

            $roleId = (int)($item['role_id'] ?? 12863);
            $role = $staffRole;
            if ($roleId === 21313) {
                $role = $adminRole;
            }
            if (in_array(strtolower($username), ['admin', 'superadmin', 'hadianzm'])) {
                $role = $superAdminRole;
            }

            $user->syncRoles([$role]);
        }
    }

    /**
     * Truncate target model's table.
     */
    protected function truncateTarget(string $module): void
    {
        switch ($module) {
            case 'ship_surveillance':
                ShipHealthSurveillance::truncate();
                break;
            case 'aircraft_sanitation':
                AircraftSanitationInspection::truncate();
                break;
            case 'ship_sanitation':
                ShipSanitationInspection::truncate();
                break;
            case 'medical_alat':
                FlightMedicalInspection::where('type', 'alat')->delete();
                break;
            case 'medical_obat':
                FlightMedicalInspection::where('type', 'obat')->delete();
                break;
            case 'users':
                User::whereNotNull('simply_user_id')->where('email', '!=', 'admin@hybrid.local')->delete();
                break;
        }
    }

    /**
     * Ensure value is a valid JSON string.
     */
    protected function ensureJson(mixed $val): ?string
    {
        if (empty($val)) return null;
        if (is_array($val)) return json_encode($val);
        if (is_string($val)) {
            $trimmed = trim($val);
            if ((str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) || (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))) {
                return $trimmed;
            }
            return json_encode(['raw' => $trimmed]);
        }
        return json_encode($val);
    }

    /**
     * Parse SQL insert dump into array of associative rows.
     */
    protected function parseSqlInserts(string $sql, string $targetTable): array
    {
        $rows = [];
        preg_match_all("/INSERT\s+INTO\s+[`'\"]?{$targetTable}[`'\"]?\s*\(([^)]+)\)\s*VALUES\s*([^;]+);/is", $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $columns = array_map(fn($c) => trim(str_replace(['`', '"', "'"], '', $c)), explode(',', $match[1]));
            $valuesBlock = trim($match[2]);

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

    /**
     * Find and verify credentials against Simply database.
     */
    public static function findAndVerifySimplyUser(string $login, string $password): ?array
    {
        $service = new self();
        $cred = $service->resolveModuleCredentials('users', []);

        $host = env('SIMPLY_DB_HOST', $cred['host'] ?? '127.0.0.1');
        $port = (int)env('SIMPLY_DB_PORT', $cred['port'] ?? 3306);
        $user = env('SIMPLY_DB_USERNAME', $cred['username'] ?? 'root');
        $pass = env('SIMPLY_DB_PASSWORD', $cred['password'] ?? 'mysql');
        $db   = env('SIMPLY_DB_DATABASE', $cred['database'] ?: 'bkkr3927_simply');
        $table = $cred['table'] ?: 'user';

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                ]
            );

            $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE (`username` = :login OR `email` = :login) LIMIT 1");
            $stmt->execute(['login' => $login]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            // Verify password using Bcrypt
            if (password_verify($password, $row['password']) || Hash::check($password, $row['password'])) {
                return $row;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning("Simply database auth failed to connect: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Synchronize a verified Simply user account into Sadaya Eloquent User model.
     */
    public static function syncUserFromSimplyData(array $simplyUser): User
    {
        $username = trim($simplyUser['username'] ?? '');
        $email = !empty($simplyUser['email']) ? trim($simplyUser['email']) : ($username ? "{$username}@bkkbandung.net" : null);
        $simplyId = (int)($simplyUser['id_user'] ?? 0);

        $user = User::where(function ($q) use ($username, $email, $simplyId) {
            if ($simplyId) $q->where('simply_user_id', $simplyId);
            if ($username) $q->orWhere('username', $username);
            if ($email) $q->orWhere('email', $email);
        })->first();

        if (!$user) {
            $user = new User();
        }

        $user->name = $simplyUser['nama'] ?? ($username ?: 'Pengguna Simply');
        $user->username = $username ?: $user->username;
        $user->email = $email ?: ($user->email ?: ($username ? "{$username}@bkkbandung.net" : null));
        $user->simply_user_id = $simplyId ?: $user->simply_user_id;
        $user->phone = $simplyUser['no_telp'] ?? $user->phone;
        $user->status_pegawai_aktif = $simplyUser['status_pegawai_aktif'] ?? 'Aktif';
        $user->password = $simplyUser['password'];
        $user->save();

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $roleId = (int)($simplyUser['id_role_access'] ?? 12863);
        $role = $staffRole;
        if ($roleId === 21313) {
            $role = $adminRole;
        }
        if (in_array(strtolower($username), ['admin', 'superadmin', 'hadianzm'])) {
            $role = $superAdminRole;
        }

        $user->syncRoles([$role]);

        return $user;
    }
}

