<?php

namespace App\Services;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\KantorWilker;
use App\Models\PrlHsbu;
use App\Models\PrlInspeksiHsbu;
use App\Models\PrlInspeksiSab;
use App\Models\PrlInspeksiTpp;
use App\Models\PrlSab;
use App\Models\PrlSiveraVektor;
use App\Models\PrlTpp;
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
            'prl_tpp' => [
                'id' => 'prl_tpp',
                'name' => 'Inspeksi Tempat Pengolahan Pangan (TPP)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'prl_inspeksi_tpp',
                'target_model' => PrlInspeksiTpp::class,
                'target_table' => 'prl_inspeksi_tpp',
                'description' => 'Registrasi master sarana TPP dan seluruh riwayat inspeksi kelaikan higiene sanitasi pangan.',
                'icon' => 'Utensils',
                'color' => 'text-emerald-600',
                'bg_color' => 'bg-emerald-50 dark:bg-emerald-950/40',
            ],
            'prl_sab' => [
                'id' => 'prl_sab',
                'name' => 'Inspeksi Sarana Air Bersih (SAB)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'prl_inspeksi_sab',
                'target_model' => PrlInspeksiSab::class,
                'target_table' => 'prl_inspeksi_sab',
                'description' => 'Registrasi master sarana air bersih dan pengawasan inspeksi sanitasi sumber & penampungan air.',
                'icon' => 'Droplet',
                'color' => 'text-blue-600',
                'bg_color' => 'bg-blue-50 dark:bg-blue-950/40',
            ],
            'prl_hsbu' => [
                'id' => 'prl_hsbu',
                'name' => 'Inspeksi Higiene Bangunan & Usaha (HSBU)',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'prl_inspeksi_hsbu',
                'target_model' => PrlInspeksiHsbu::class,
                'target_table' => 'prl_inspeksi_hsbu',
                'description' => 'Pengawasan faktor risiko fisik, kimia, biologi, tata gedung dan lingkungan kerja pelabuhan/bandara.',
                'icon' => 'Building',
                'color' => 'text-purple-600',
                'bg_color' => 'bg-purple-50 dark:bg-purple-950/40',
            ],
            'prl_sivera' => [
                'id' => 'prl_sivera',
                'name' => 'Surveilans Vektor SIVERA',
                'source_db' => 'bkkr3927_simply',
                'source_table' => 'prl_sivera_jentik',
                'target_model' => PrlSiveraVektor::class,
                'target_table' => 'prl_sivera_vektor',
                'description' => 'Surveilans kepadatan 7 jenis vektor (Jentik, Lalat, Kecoa, Tikus, Pinjal, Anopheles) dan pengendalian.',
                'icon' => 'Bug',
                'color' => 'text-teal-600',
                'bg_color' => 'bg-teal-50 dark:bg-teal-950/40',
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
            'prl_tpp' => PrlInspeksiTpp::count(),
            'prl_sab' => PrlInspeksiSab::count(),
            'prl_hsbu' => PrlInspeksiHsbu::count(),
            'prl_sivera' => PrlSiveraVektor::count(),
        ];
    }

    /**
     * Validate host and port to mitigate SSRF and internal network scanning.
     */
    public function validateHostAndPort(string $host, int $port): void
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException("Port database tidak valid: {$port}.");
        }

        // Mitigate SSRF attacks against cloud metadata endpoints (e.g. 169.254.169.254)
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : @gethostbyname($host);
        if (str_starts_with($ip, '169.254.')) {
            throw new \InvalidArgumentException("Akses ke host IP metadata [{$host}] diblokir untuk alasan keamanan.");
        }

        // Enforce whitelist if SIMPLY_ALLOWED_HOSTS is defined in .env
        $allowedHostsEnv = env('SIMPLY_ALLOWED_HOSTS');
        if (!empty($allowedHostsEnv)) {
            $allowed = array_map('trim', explode(',', strtolower($allowedHostsEnv)));
            if (!in_array(strtolower($host), $allowed, true) && !in_array($ip, $allowed, true)) {
                throw new \InvalidArgumentException("Host '{$host}' tidak terdaftar dalam daftar host yang diizinkan (SIMPLY_ALLOWED_HOSTS).");
            }
        }
    }

    /**
     * Sanitize and validate SQL identifiers (database name, table name) against SQL injection.
     */
    public function sanitizeIdentifier(string $identifier, string $type = 'Identifier'): string
    {
        $trimmed = trim($identifier);
        if ($trimmed === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $trimmed)) {
            throw new \InvalidArgumentException("Karakter tidak valid pada nama {$type}: '{$identifier}'. Hanya karakter alfanumerik dan underscore yang diperbolehkan.");
        }
        return $trimmed;
    }

    /**
     * Resolve effective credentials and target DB/table for a module.
     * Checks per-module credentials first, falls back to saved config, global credentials, then module default config.
     */
    public function resolveModuleCredentials(string $module, array $credentials): array
    {
        $configs = $this->getModuleConfigs();
        $cfg = $configs[$module] ?? [];

        // Check if credentials are nested under modules[module]
        $modCreds = $credentials['modules'][$module] ?? [];

        // Read persistent stored configurations if available
        $savedPath = storage_path('app/simply_migration_configs.json');
        $savedMod = [];
        if (file_exists($savedPath)) {
            $saved = json_decode(file_get_contents($savedPath), true);
            if (is_array($saved) && isset($saved[$module]) && is_array($saved[$module])) {
                $savedMod = $saved[$module];
            }
        }

        $host = !empty($modCreds['host'])
            ? $modCreds['host']
            : (!empty($credentials['host']) ? $credentials['host'] : ($savedMod['host'] ?? '127.0.0.1'));

        $port = (int)(!empty($modCreds['port'])
            ? $modCreds['port']
            : (!empty($credentials['port']) ? $credentials['port'] : ($savedMod['port'] ?? 3306)));

        $username = isset($modCreds['username']) && $modCreds['username'] !== ''
            ? $modCreds['username']
            : (isset($credentials['username']) && $credentials['username'] !== '' ? $credentials['username'] : ($savedMod['username'] ?? 'root'));

        $password = isset($modCreds['password']) && $modCreds['password'] !== ''
            ? $modCreds['password']
            : (isset($credentials['password']) && $credentials['password'] !== '' ? $credentials['password'] : ($savedMod['password'] ?? 'mysql'));

        $database = !empty($modCreds['database'])
            ? $modCreds['database']
            : (!empty($credentials['database']) ? $credentials['database'] : ($savedMod['database'] ?? ($cfg['source_db'] ?? '')));

        $table = !empty($modCreds['table'])
            ? $modCreds['table']
            : (!empty($credentials['table']) ? $credentials['table'] : ($savedMod['table'] ?? ($cfg['source_table'] ?? '')));

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'database' => $database,
            'table' => $table,
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
        $port = (int)$cred['port'];
        $username = $cred['username'];
        $password = $cred['password'];
        $dbName = $cred['database'];
        $tblName = $cred['table'];

        try {
            $this->validateHostAndPort($host, $port);
            $dbName = $this->sanitizeIdentifier($dbName, 'basis data');
            $tblName = $this->sanitizeIdentifier($tblName, 'tabel');
        } catch (\InvalidArgumentException $ve) {
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
                'error' => $ve->getMessage(),
            ];
        }

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
        $port = (int)$cred['port'];
        $username = $cred['username'];
        $password = $cred['password'];
        $dbName = $cred['database'];
        $tblName = $cred['table'];

        $skipDuplicate = $options['skip_duplicate'] ?? true;
        $truncateFirst = $options['truncate_first'] ?? false;
        $dryRun = $options['dry_run'] ?? false;

        try {
            $this->validateHostAndPort($host, $port);
            $dbName = $this->sanitizeIdentifier($dbName, 'basis data');
            $tblName = $this->sanitizeIdentifier($tblName, 'tabel');
        } catch (\InvalidArgumentException $ve) {
            return ['success' => false, 'message' => $ve->getMessage()];
        }

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

        if (in_array($module, ['prl_tpp', 'prl_sab', 'prl_hsbu', 'prl_sivera'])) {
            return $this->migratePrlModule($module, $pdo, $options, $startTime, $cfg);
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

            case 'prl_tpp':
                return [
                    'source_id' => $row['id_prl_inspeksi_tpp'] ?? $row['id'] ?? null,
                    'prl_tpp_id' => $row['id_prl_tpp'] ?? null,
                    'wilker' => $row['wilker'] ?? 'Wilayah Kerja Patimban',
                    'tanggal_inspeksi' => $row['tanggal_inspeksi'] ?? date('Y-m-d'),
                    'nama_tpp' => $row['nama_tpp'] ?? ($row['penanggung_jawab_tpp'] ?? 'Sarana TPP'),
                    'penanggung_jawab_tpp' => $row['penanggung_jawab_tpp'] ?? '-',
                    'jml_karyawan_tpp' => $row['jml_karyawan_tpp'] ?? '-',
                    'kapasitas_produksi_tpp' => $row['kapasitas_produksi_tpp'] ?? '-',
                    'nama_pemeriksa' => $row['nama_pemeriksa'] ?? 'Petugas Sanitasi BKK',
                    'sec_a_satu' => $row['sec_a_satu'] ?? 'Tidak',
                    'sec_a_dua' => $row['sec_a_dua'] ?? 'Tidak',
                    'sec_a_tiga' => $row['sec_a_tiga'] ?? 'Tidak',
                    'sec_a_skor' => (int)($row['sec_a_skor'] ?? 0),
                    'sec_b_satu' => $row['sec_b_satu'] ?? 'Tidak',
                    'sec_b_dua' => $row['sec_b_dua'] ?? 'Tidak',
                    'sec_b_tiga' => $row['sec_b_tiga'] ?? 'Tidak',
                    'sec_b_skor' => (int)($row['sec_b_skor'] ?? 0),
                    'sec_c_skor' => (int)($row['sec_c_skor'] ?? 0),
                    'sec_c_keterangan' => $row['sec_c_keterangan'] ?? '-',
                    'sec_d_nilai_inspeksi_sebelumnya' => (int)($row['sec_d_nilai_inspeksi_sebelumnya'] ?? 0),
                    'sec_d_skor' => (int)($row['sec_d_skor'] ?? 0),
                    'hasil' => (int)($row['hasil'] ?? 0),
                    'tingkat_risiko' => $row['tingkat_risiko'] ?? 'Sedang',
                    'kesimpulan' => $row['kesimpulan'] ?? null,
                    'makanan' => $row['makanan'] ?? null,
                    'saran_rencana_tindakan' => $row['saran_rencana_tindakan'] ?? null,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ];

            case 'prl_sab':
                return [
                    'source_id' => $row['id_prl_inspeksi_sab'] ?? $row['id'] ?? null,
                    'prl_sab_id' => $row['id_prl_sab'] ?? null,
                    'wilker' => $row['wilker'] ?? 'Wilayah Kerja Patimban',
                    'tanggal_inspeksi' => $row['tanggal_inspeksi'] ?? date('Y-m-d'),
                    'nama_sab' => $row['nama_sab'] ?? ($row['penanggung_jawab_sab'] ?? 'Sarana Air Bersih'),
                    'penanggung_jawab_sab' => $row['penanggung_jawab_sab'] ?? '-',
                    'penanggung_jawab_no_hp_sab' => $row['penanggung_jawab_no_hp_sab'] ?? null,
                    'sumber_air_sab' => $row['sumber_air_sab'] ?? 'Air Tanah',
                    'kapasitas_penampungan_sab' => $row['kapasitas_penampungan_sab'] ?? '-',
                    'nama_pemeriksa' => $row['nama_pemeriksa'] ?? 'Petugas Sanitasi BKK',
                    'sec_b_satu' => $row['sec_b_satu'] ?? 'Tidak',
                    'sec_b_dua' => $row['sec_b_dua'] ?? 'Tidak',
                    'sec_b_tiga' => $row['sec_b_tiga'] ?? 'Tidak',
                    'sec_b_empat' => $row['sec_b_empat'] ?? 'Tidak',
                    'sec_c_satu' => $row['sec_c_satu'] ?? 'Tidak',
                    'sec_c_dua' => $row['sec_c_dua'] ?? 'Tidak',
                    'sec_c_tiga' => $row['sec_c_tiga'] ?? 'Tidak',
                    'sec_c_empat' => $row['sec_c_empat'] ?? 'Tidak',
                    'sec_c_lima' => $row['sec_c_lima'] ?? 'Tidak',
                    'sec_c_enam' => $row['sec_c_enam'] ?? 'Tidak',
                    'sec_c_tujuh' => $row['sec_c_tujuh'] ?? 'Tidak',
                    'sec_c_delapan' => $row['sec_c_delapan'] ?? 'Tidak',
                    'sec_c_sembilan' => $row['sec_c_sembilan'] ?? 'Tidak',
                    'sec_c_sepuluh' => $row['sec_c_sepuluh'] ?? 'Tidak',
                    'hasil' => (int)($row['hasil'] ?? 0),
                    'tingkat_risiko' => $row['tingkat_risiko'] ?? 'Sedang',
                    'saran_rencana_tindakan' => $row['saran_rencana_tindakan'] ?? null,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ];

            case 'prl_hsbu':
                return [
                    'source_id' => $row['id_prl_inspeksi_hsbu'] ?? $row['id'] ?? null,
                    'prl_hsbu_id' => $row['id_prl_hsbu'] ?? null,
                    'wilker' => $row['wilker'] ?? 'Wilayah Kerja Patimban',
                    'tanggal_inspeksi' => $row['tanggal_inspeksi'] ?? date('Y-m-d'),
                    'nama_gedung' => $row['nama_gedung'] ?? ($row['penanggung_jawab_hsbu'] ?? 'Gedung/Bangunan'),
                    'penanggung_jawab_hsbu' => $row['penanggung_jawab_hsbu'] ?? '-',
                    'jml_karyawan_hsbu' => $row['jml_karyawan_hsbu'] ?? '-',
                    'nama_pemeriksa' => $row['nama_pemeriksa'] ?? 'Petugas Sanitasi BKK',
                    'komponen_penilaian' => !empty($row['komponen_penilaian']) ? (is_array($row['komponen_penilaian']) ? $row['komponen_penilaian'] : json_decode($row['komponen_penilaian'], true)) : [],
                    'total' => (float)($row['total'] ?? 0),
                    'tingkat_risiko' => $row['tingkat_risiko'] ?? 'Sedang',
                    'saran_rencana_tindakan' => $row['saran_rencana_tindakan'] ?? null,
                    'catatan' => $row['catatan'] ?? null,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ];

            case 'prl_sivera':
                return [
                    'source_id' => $row['source_id'] ?? $row['id'] ?? null,
                    'jenis_vektor' => $row['jenis_vektor'] ?? 'Jentik Aedes',
                    'wilker' => $row['wilker'] ?? 'Wilayah Kerja Patimban',
                    'lokasi' => $row['lokasi'] ?? '-',
                    'tanggal' => $row['tanggal'] ?? date('Y-m-d'),
                    'parameter_utama' => $row['parameter_utama'] ?? '-',
                    'nilai_indeks' => (float)($row['nilai_indeks'] ?? 0),
                    'baku_mutu_status' => $row['baku_mutu_status'] ?? 'Memenuhi Syarat',
                    'detail_data' => !empty($row['detail_data']) ? (is_array($row['detail_data']) ? $row['detail_data'] : json_decode($row['detail_data'], true)) : [],
                    'tindakan_pengendalian' => $this->cleanSiveraPengendalian($row['tindakan_pengendalian'] ?? null),
                    'created_by_name' => $row['created_by_name'] ?? 'Petugas Vektor',
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
            case 'prl_tpp':
                return !empty($mapped['source_id']) && PrlInspeksiTpp::where('source_id', $mapped['source_id'])->exists();
            case 'prl_sab':
                return !empty($mapped['source_id']) && PrlInspeksiSab::where('source_id', $mapped['source_id'])->exists();
            case 'prl_hsbu':
                return !empty($mapped['source_id']) && PrlInspeksiHsbu::where('source_id', $mapped['source_id'])->exists();
            case 'prl_sivera':
                return !empty($mapped['source_id']) && PrlSiveraVektor::where('source_id', $mapped['source_id'])->where('jenis_vektor', $mapped['jenis_vektor'] ?? '')->exists();
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
            case 'prl_tpp':
                PrlInspeksiTpp::truncate();
                PrlTpp::truncate();
                break;
            case 'prl_sab':
                PrlInspeksiSab::truncate();
                PrlSab::truncate();
                break;
            case 'prl_hsbu':
                PrlInspeksiHsbu::truncate();
                PrlHsbu::truncate();
                break;
            case 'prl_sivera':
                PrlSiveraVektor::truncate();
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
            $service->validateHostAndPort($host, $port);
            $db = $service->sanitizeIdentifier($db, 'basis data');
            $table = $service->sanitizeIdentifier($table, 'tabel');

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

    /**
     * Migrate PRL (Pengendalian Risiko Lingkungan) & SIVERA modules from Simply CI4.
     */
    public function migratePrlModule(string $module, PDO $pdo, array $options, float $startTime, array $cfg): array
    {
        $skipDuplicate = $options['skip_duplicate'] ?? true;
        $truncateFirst = $options['truncate_first'] ?? false;
        $dryRun = $options['dry_run'] ?? false;

        if ($truncateFirst && !$dryRun) {
            $this->truncateTarget($module);
        }

        // 1. Fetch lookups from Simply
        $wilkerMap = [];
        try {
            $wilkerMap = $pdo->query("SELECT id_unit_kerja, unit_kerja FROM unit_kerja")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (Throwable $e) {}

        $pegawaiMap = [];
        try {
            $pegawaiMap = $pdo->query("SELECT id_data_pegawai, nama FROM data_pegawai")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (Throwable $e) {}

        $userMap = [];
        try {
            $userMap = $pdo->query("SELECT id_user, nama FROM user")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (Throwable $e) {}

        $resolvePemeriksa = function ($rawId) use ($pegawaiMap) {
            if (empty($rawId)) return 'Petugas Sanitasi BKK';
            $ids = explode(' | ', (string)$rawId);
            $names = [];
            foreach ($ids as $id) {
                $id = trim($id);
                if (isset($pegawaiMap[$id])) {
                    $names[] = $pegawaiMap[$id];
                }
            }
            return !empty($names) ? implode('; ', $names) : "Petugas (ID: {$rawId})";
        };

        $totalProcessed = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            if ($module === 'prl_tpp') {
                // Sync Master TPP first
                $tppRows = $pdo->query("SELECT * FROM prl_tpp ORDER BY id_prl_tpp ASC")->fetchAll(PDO::FETCH_ASSOC);
                $tppMapId = [];
                foreach ($tppRows as $r) {
                    $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                    if (!$dryRun) {
                        $m = PrlTpp::updateOrCreate(
                            ['source_id' => $r['id_prl_tpp']],
                            [
                                'wilker' => $wilkerName,
                                'nama_tpp' => $r['nama_tpp'] ?: 'TPP Tanpa Nama',
                                'penanggung_jawab' => $r['penanggung_jawab'],
                                'jml_karyawan' => $r['jml_karyawan'],
                                'kapasitas_produksi' => $r['kapasitas_produksi'],
                                'created_by_name' => $userMap[$r['created_by']] ?? null,
                                'updated_by_name' => $userMap[$r['updated_by']] ?? null,
                                'created_at' => $r['created_at'],
                                'updated_at' => $r['updated_at'],
                            ]
                        );
                        $tppMapId[$r['id_prl_tpp']] = $m->id;
                    }
                }

                // Sync Inspeksi TPP
                $inspTppRows = $pdo->query("
                    SELECT i.*, t.nama_tpp as tpp_nama 
                    FROM prl_inspeksi_tpp i 
                    LEFT JOIN prl_tpp t ON t.id_prl_tpp = i.id_prl_tpp 
                    ORDER BY i.id_prl_inspeksi_tpp ASC
                ")->fetchAll(PDO::FETCH_ASSOC);

                foreach ($inspTppRows as $r) {
                    $totalProcessed++;
                    try {
                        if ($skipDuplicate && !$truncateFirst && PrlInspeksiTpp::where('source_id', $r['id_prl_inspeksi_tpp'])->exists()) {
                            $skippedCount++;
                            continue;
                        }

                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $score = (int)$r['hasil'];
                        $risk = match (true) {
                            $score < 35 => 'Rendah',
                            $score <= 50 => 'Sedang',
                            default => 'Tinggi',
                        };

                        if (!$dryRun) {
                            PrlInspeksiTpp::updateOrCreate(
                                ['source_id' => $r['id_prl_inspeksi_tpp']],
                                [
                                    'prl_tpp_id' => $tppMapId[$r['id_prl_tpp']] ?? null,
                                    'wilker' => $wilkerName,
                                    'tanggal_inspeksi' => $r['tanggal_inspeksi'],
                                    'nama_tpp' => $r['tpp_nama'] ?: ($r['penanggung_jawab_tpp'] ?: 'Sarana TPP'),
                                    'penanggung_jawab_tpp' => $r['penanggung_jawab_tpp'],
                                    'jml_karyawan_tpp' => $r['jml_karyawan_tpp'],
                                    'kapasitas_produksi_tpp' => $r['kapasitas_produksi_tpp'],
                                    'nama_pemeriksa' => $resolvePemeriksa($r['id_data_pegawai']),
                                    'sec_a_satu' => $r['sec_a_satu'],
                                    'sec_a_dua' => $r['sec_a_dua'],
                                    'sec_a_tiga' => $r['sec_a_tiga'],
                                    'sec_a_skor' => (int)$r['sec_a_skor'],
                                    'sec_b_satu' => $r['sec_b_satu'],
                                    'sec_b_dua' => $r['sec_b_dua'],
                                    'sec_b_tiga' => $r['sec_b_tiga'],
                                    'sec_b_skor' => (int)$r['sec_b_skor'],
                                    'sec_c_skor' => (int)$r['sec_c_skor'],
                                    'sec_c_keterangan' => $r['sec_c_keterangan'],
                                    'sec_d_nilai_inspeksi_sebelumnya' => (int)$r['sec_d_nilai_inspeksi_sebelumnya'],
                                    'sec_d_skor' => (int)$r['sec_d_skor'],
                                    'hasil' => $score,
                                    'tingkat_risiko' => $risk,
                                    'kesimpulan' => $r['kesimpulan'],
                                    'makanan' => $r['makanan'],
                                    'saran_rencana_tindakan' => $r['saran_rencana_tindakan'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    } catch (Throwable $e) {
                        $failedCount++;
                        if (count($errors) < 5) $errors[] = "Inspeksi TPP #{$r['id_prl_inspeksi_tpp']}: " . $e->getMessage();
                    }
                }
            } elseif ($module === 'prl_sab') {
                // Sync Master SAB
                $sabRows = $pdo->query("SELECT * FROM prl_sab ORDER BY id_prl_sab ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sabMapId = [];
                foreach ($sabRows as $r) {
                    $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                    if (!$dryRun) {
                        $m = PrlSab::updateOrCreate(
                            ['source_id' => $r['id_prl_sab']],
                            [
                                'wilker' => $wilkerName,
                                'nama_sab' => $r['nama_sab'] ?: 'SAB Tanpa Nama',
                                'penanggung_jawab' => $r['penanggung_jawab'],
                                'penanggung_jawab_no_hp' => $r['penanggung_jawab_no_hp'],
                                'sumber_air' => $r['sumber_air'],
                                'kapasitas_penampungan' => $r['kapasitas_penampungan'],
                                'created_by_name' => $userMap[$r['created_by']] ?? null,
                                'updated_by_name' => $userMap[$r['updated_by']] ?? null,
                                'created_at' => $r['created_at'],
                                'updated_at' => $r['updated_at'],
                            ]
                        );
                        $sabMapId[$r['id_prl_sab']] = $m->id;
                    }
                }

                // Sync Inspeksi SAB
                $inspSabRows = $pdo->query("
                    SELECT i.*, s.nama_sab as sab_nama 
                    FROM prl_inspeksi_sab i 
                    LEFT JOIN prl_sab s ON s.id_prl_sab = i.id_prl_sab 
                    ORDER BY i.id_prl_inspeksi_sab ASC
                ")->fetchAll(PDO::FETCH_ASSOC);

                foreach ($inspSabRows as $r) {
                    $totalProcessed++;
                    try {
                        if ($skipDuplicate && !$truncateFirst && PrlInspeksiSab::where('source_id', $r['id_prl_inspeksi_sab'])->exists()) {
                            $skippedCount++;
                            continue;
                        }

                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $score = (int)$r['hasil'];
                        $risk = match (true) {
                            $score < 25 => 'Rendah',
                            $score <= 50 => 'Sedang',
                            default => 'Tinggi',
                        };

                        if (!$dryRun) {
                            PrlInspeksiSab::updateOrCreate(
                                ['source_id' => $r['id_prl_inspeksi_sab']],
                                [
                                    'prl_sab_id' => $sabMapId[$r['id_prl_sab']] ?? null,
                                    'wilker' => $wilkerName,
                                    'tanggal_inspeksi' => $r['tanggal_inspeksi'],
                                    'nama_sab' => $r['sab_nama'] ?: ($r['penanggung_jawab_sab'] ?: 'Sarana Air Bersih'),
                                    'penanggung_jawab_sab' => $r['penanggung_jawab_sab'],
                                    'penanggung_jawab_no_hp_sab' => $r['penanggung_jawab_no_hp_sab'],
                                    'sumber_air_sab' => $r['sumber_air_sab'],
                                    'kapasitas_penampungan_sab' => $r['kapasitas_penampungan_sab'],
                                    'nama_pemeriksa' => $resolvePemeriksa($r['id_data_pegawai']),
                                    'sec_b_satu' => $r['sec_b_satu'],
                                    'sec_b_dua' => $r['sec_b_dua'],
                                    'sec_b_tiga' => $r['sec_b_tiga'],
                                    'sec_b_empat' => $r['sec_b_empat'],
                                    'sec_c_satu' => $r['sec_c_satu'],
                                    'sec_c_dua' => $r['sec_c_dua'],
                                    'sec_c_tiga' => $r['sec_c_tiga'],
                                    'sec_c_empat' => $r['sec_c_empat'],
                                    'sec_c_lima' => $r['sec_c_lima'],
                                    'sec_c_enam' => $r['sec_c_enam'],
                                    'sec_c_tujuh' => $r['sec_c_tujuh'],
                                    'sec_c_delapan' => $r['sec_c_delapan'],
                                    'sec_c_sembilan' => $r['sec_c_sembilan'],
                                    'sec_c_sepuluh' => $r['sec_c_sepuluh'],
                                    'hasil' => $score,
                                    'tingkat_risiko' => $risk,
                                    'saran_rencana_tindakan' => $r['saran_rencana_tindakan'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    } catch (Throwable $e) {
                        $failedCount++;
                        if (count($errors) < 5) $errors[] = "Inspeksi SAB #{$r['id_prl_inspeksi_sab']}: " . $e->getMessage();
                    }
                }
            } elseif ($module === 'prl_hsbu') {
                // Sync Master HSBU
                $hsbuRows = $pdo->query("SELECT * FROM prl_hsbu ORDER BY id_prl_hsbu ASC")->fetchAll(PDO::FETCH_ASSOC);
                $hsbuMapId = [];
                foreach ($hsbuRows as $r) {
                    $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                    if (!$dryRun) {
                        $m = PrlHsbu::updateOrCreate(
                            ['source_id' => $r['id_prl_hsbu']],
                            [
                                'wilker' => $wilkerName,
                                'nama_gedung' => $r['nama_gedung'] ?: 'Gedung Tanpa Nama',
                                'penanggung_jawab' => $r['penanggung_jawab'],
                                'jml_karyawan' => $r['jml_karyawan'],
                                'created_by_name' => $userMap[$r['created_by']] ?? null,
                                'updated_by_name' => $userMap[$r['updated_by']] ?? null,
                                'created_at' => $r['created_at'],
                                'updated_at' => $r['updated_at'],
                            ]
                        );
                        $hsbuMapId[$r['id_prl_hsbu']] = $m->id;
                    }
                }

                // Sync Inspeksi HSBU
                $inspHsbuRows = $pdo->query("
                    SELECT i.*, h.nama_gedung as hsbu_nama 
                    FROM prl_inspeksi_hsbu i 
                    LEFT JOIN prl_hsbu h ON h.id_prl_hsbu = i.id_prl_hsbu 
                    ORDER BY i.id_prl_inspeksi_hsbu ASC
                ")->fetchAll(PDO::FETCH_ASSOC);

                foreach ($inspHsbuRows as $r) {
                    $totalProcessed++;
                    try {
                        if ($skipDuplicate && !$truncateFirst && PrlInspeksiHsbu::where('source_id', $r['id_prl_inspeksi_hsbu'])->exists()) {
                            $skippedCount++;
                            continue;
                        }

                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $score = (float)$r['total'];
                        $risk = match (true) {
                            $score > 712.5 => 'Rendah',
                            $score >= 475 => 'Sedang',
                            default => 'Tinggi',
                        };

                        $komponen = [];
                        $keys = [
                            'lingkungan_luar_halaman', 'ruang_bangunan', 'penyehatan_air',
                            'penyehatan_ruang_udara', 'pengelolaan_limbah', 'pencahayaan',
                            'kebisingan_pada_ruang', 'getaran_di_ruang_kerja', 'pengendalian_vektor_penyakit',
                            'instalasi', 'pemeliharaan_jamban_dan_kamar_mandi'
                        ];
                        foreach ($keys as $k) {
                            if (!empty($r[$k])) {
                                $unser = @unserialize($r[$k]);
                                $komponen[$k] = $unser !== false ? $unser : $r[$k];
                            }
                        }

                        if (!$dryRun) {
                            PrlInspeksiHsbu::updateOrCreate(
                                ['source_id' => $r['id_prl_inspeksi_hsbu']],
                                [
                                    'prl_hsbu_id' => $hsbuMapId[$r['id_prl_hsbu']] ?? null,
                                    'wilker' => $wilkerName,
                                    'tanggal_inspeksi' => $r['tanggal_inspeksi'],
                                    'nama_gedung' => $r['hsbu_nama'] ?: ($r['penanggung_jawab_hsbu'] ?: 'Gedung/Bangunan'),
                                    'penanggung_jawab_hsbu' => $r['penanggung_jawab_hsbu'],
                                    'jml_karyawan_hsbu' => $r['jml_karyawan_hsbu'],
                                    'nama_pemeriksa' => $resolvePemeriksa($r['id_data_pegawai']),
                                    'komponen_penilaian' => $komponen,
                                    'total' => $score,
                                    'tingkat_risiko' => $risk,
                                    'saran_rencana_tindakan' => $r['saran_rencana_tindakan'],
                                    'catatan' => $r['catatan'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    } catch (Throwable $e) {
                        $failedCount++;
                        if (count($errors) < 5) $errors[] = "Inspeksi HSBU #{$r['id_prl_inspeksi_hsbu']}: " . $e->getMessage();
                    }
                }
            } elseif ($module === 'prl_sivera') {
                // Vector 1: Jentik Aedes
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_jentik ORDER BY id_jentik ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_jentik'])->where('jenis_vektor', 'Jentik Aedes')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $kontainer = (int)($r['jml_kontainer_diperiksa'] ?? 0);
                        $positif = (int)($r['jml_kontainer_positif'] ?? 0);
                        $ci = $kontainer > 0 ? round(($positif / $kontainer) * 100, 2) : 0;
                        $abj = 100 - $ci;

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_jentik'], 'jenis_vektor' => 'Jentik Aedes'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: ($r['wilayah'] ?: 'Area Pelabuhan/Bandara'),
                                    'tanggal' => $r['tanggal_mulai'] ?: $r['created_at'],
                                    'parameter_utama' => "ABJ: {$abj}% (CI: {$ci}%)",
                                    'nilai_indeks' => $abj,
                                    'baku_mutu_status' => $abj >= 95 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => $this->cleanSiveraPengendalian($r['pengendalian'] ?? null),
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Jentik: " . $e->getMessage();
                }

                // Vector 2: Lalat
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_lalat ORDER BY id_lalat ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_lalat'])->where('jenis_vektor', 'Lalat')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $fi = (float)($r['rata_rata'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_lalat'], 'jenis_vektor' => 'Lalat'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: 'Area Pelabuhan/Bandara',
                                    'tanggal' => $r['tanggal'] ?: $r['created_at'],
                                    'parameter_utama' => "Fly Index: {$fi}",
                                    'nilai_indeks' => $fi,
                                    'baku_mutu_status' => $fi <= 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => $this->cleanSiveraPengendalian($r['pengendalian'] ?? null),
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Lalat: " . $e->getMessage();
                }

                // Vector 3: Kecoa
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_kecoa ORDER BY id_kecoa ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_kecoa'])->where('jenis_vektor', 'Kecoa')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $rt = (float)($r['rata_rata'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_kecoa'], 'jenis_vektor' => 'Kecoa'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: ($r['nama_ruangan'] ?: 'Area Bangunan/Gedung'),
                                    'tanggal' => $r['tanggal'] ?: $r['created_at'],
                                    'parameter_utama' => "Indeks Populasi: {$rt} ekor/trap",
                                    'nilai_indeks' => $rt,
                                    'baku_mutu_status' => $rt < 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => $this->cleanSiveraPengendalian($r['pengendalian'] ?? null),
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Kecoa: " . $e->getMessage();
                }

                // Vector 4: Tikus
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_tikus ORDER BY id_tikus ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_tikus'])->where('jenis_vektor', 'Tikus')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $ts = (float)($r['success_trap'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_tikus'], 'jenis_vektor' => 'Tikus'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: ($r['lokasi_pemasangan'] ?: 'Area Pelabuhan/Bandara'),
                                    'tanggal' => $r['tanggal'] ?: ($r['tanggal_mulai'] ?: $r['created_at']),
                                    'parameter_utama' => "Trap Success: {$ts}%",
                                    'nilai_indeks' => $ts,
                                    'baku_mutu_status' => $ts <= 1 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => !empty($r['jml_perangkap_dipasang']) ? "Pemasangan {$r['jml_perangkap_dipasang']} perangkap" : 'Pemasangan perangkap',
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Tikus: " . $e->getMessage();
                }

                // Vector 5: Pinjal
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_pinjal ORDER BY id_pinjal ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_pinjal'])->where('jenis_vektor', 'Pinjal')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $ip = (float)($r['indeks_pinjal_umum'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_pinjal'], 'jenis_vektor' => 'Pinjal'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: ($r['lokasi_tikus_tertangkap'] ?: 'Perimeter/Buffer'),
                                    'tanggal' => $r['tanggal'] ?: $r['created_at'],
                                    'parameter_utama' => "Indeks Pinjal: {$ip}",
                                    'nilai_indeks' => $ip,
                                    'baku_mutu_status' => $ip <= 1 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => 'Dusting insektisida liang tikus',
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Pinjal: " . $e->getMessage();
                }

                // Vector 6: Anopheles Jentik
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_anopheles_jentik ORDER BY id_anopheles_jentik ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_anopheles_jentik'])->where('jenis_vektor', 'Anopheles (Jentik)')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $ih = (float)($r['indeks_habitat'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_anopheles_jentik'], 'jenis_vektor' => 'Anopheles (Jentik)'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: 'Habitat Perindukan Anopheles',
                                    'tanggal' => $r['tanggal'] ?: $r['created_at'],
                                    'parameter_utama' => "Indeks Habitat: {$ih}%",
                                    'nilai_indeks' => $ih,
                                    'baku_mutu_status' => $ih == 0 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => 'Larvasidasi biologi (BTI) / pembersihan lumut',
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Anopheles Jentik: " . $e->getMessage();
                }

                // Vector 7: Anopheles Nyamuk Dewasa
                try {
                    $rows = $pdo->query("SELECT * FROM prl_sivera_anopheles_nyamuk ORDER BY id_anopheles_nyamuk ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $r) {
                        $totalProcessed++;
                        if ($skipDuplicate && !$truncateFirst && PrlSiveraVektor::where('source_id', $r['id_anopheles_nyamuk'])->where('jenis_vektor', 'Anopheles (Nyamuk)')->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
                        $mbr = (float)($r['mbr'] ?? 0);

                        if (!$dryRun) {
                            PrlSiveraVektor::updateOrCreate(
                                ['source_id' => $r['id_anopheles_nyamuk'], 'jenis_vektor' => 'Anopheles (Nyamuk)'],
                                [
                                    'wilker' => $wilkerName,
                                    'lokasi' => $r['lokasi'] ?: 'Area Penangkapan Nyamuk',
                                    'tanggal' => $r['tanggal'] ?: $r['created_at'],
                                    'parameter_utama' => "MBR: {$mbr} gigitan/orang/malam",
                                    'nilai_indeks' => $mbr,
                                    'baku_mutu_status' => $mbr == 0 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu',
                                    'detail_data' => $r,
                                    'tindakan_pengendalian' => 'Indoor Residual Spraying (IRS)',
                                    'created_by_name' => $userMap[$r['created_by']] ?? $r['created_by'],
                                    'created_at' => $r['created_at'],
                                    'updated_at' => $r['updated_at'],
                                ]
                            );
                        }
                        $importedCount++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Anopheles Nyamuk: " . $e->getMessage();
                }
            }
        } catch (Throwable $e) {
            $errors[] = "Fatal: " . $e->getMessage();
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
     * Clean and format SIVERA tindakan pengendalian for human readability.
     */
    protected function cleanSiveraPengendalian(?string $raw): ?string
    {
        if (empty($raw)) return null;
        if (str_starts_with(trim($raw), '{')) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $parts = [];
                if (!empty($data['jentik_kimia']) || !empty($data['jentik_kimia_gram']) || !empty($data['jentik_kimia_sachet'])) {
                    $p = [];
                    if (!empty($data['jentik_kimia_gram'])) $p[] = $data['jentik_kimia_gram'] . ' gr';
                    if (!empty($data['jentik_kimia_sachet'])) $p[] = $data['jentik_kimia_sachet'] . ' sachet';
                    $parts[] = 'Larvasidasi' . (!empty($p) ? ' (' . implode(', ', $p) . ')' : '');
                }
                if (!empty($data['jentik_fisik'])) $parts[] = 'Fisik (3M Plus/Kuras)';
                if (!empty($data['jentik_biologi'])) $parts[] = 'Biologi (BTI/Ikan)';
                if (!empty($data['nyamuk_fogging']) || !empty($data['nyamuk_fogging_ml'])) {
                    $f = [];
                    if (!empty($data['nyamuk_fogging_ml'])) $f[] = $data['nyamuk_fogging_ml'] . ' ml';
                    if (!empty($data['nyamuk_fogging_merk'])) $f[] = $data['nyamuk_fogging_merk'];
                    $parts[] = 'Fogging' . (!empty($f) ? ' (' . implode(' ', $f) . ')' : '');
                }
                if (!empty($data['nyamuk_fisik'])) $parts[] = 'Fisik Nyamuk (Trap/Raket)';
                if (!empty($data['spraying']) || !empty($data['spraying_ml'])) {
                    $parts[] = 'Spraying' . (!empty($data['spraying_ml']) ? ' (' . $data['spraying_ml'] . ' ml)' : '');
                }
                if (!empty($data['granule']) || !empty($data['granule_gram'])) {
                    $parts[] = 'Granule Bait' . (!empty($data['granule_gram']) ? ' (' . $data['granule_gram'] . ' gr)' : '');
                }
                if (!empty($data['retrapping']) || !empty($data['retrapping_trap'])) {
                    $parts[] = 'Retrapping' . (!empty($data['retrapping_trap']) ? ' (' . $data['retrapping_trap'] . ' trap)' : '');
                }
                if (!empty($data['gel_bait']) || !empty($data['gel_bait_gram'])) {
                    $parts[] = 'Gel Bait' . (!empty($data['gel_bait_gram']) ? ' (' . $data['gel_bait_gram'] . ' gr)' : '');
                }
                if (!empty($data['catatan'])) $parts[] = $data['catatan'];
                if (!empty($parts)) return implode('; ', $parts);
            }
        }
        return $raw;
    }
}

