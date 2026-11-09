<?php

namespace App\Console\Commands;

use App\Services\SimplyDataMigrationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class SimplyAutoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simply:auto-sync {--force : Paksa jalankan sinkronisasi tanpa memeriksa interval waktu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan sinkronisasi otomatis data Simply (CI4) ke Sadaya sesuai jadwal yang telah dikonfigurasi';

    /**
     * Execute the console command.
     */
    public function handle(SimplyDataMigrationService $service): int
    {
        $configPath = storage_path('app/simply_sync_schedule.json');
        $config = [
            'enabled' => false,
            'frequency' => 'hourly',
            'modules' => array_keys($service->getModuleConfigs()),
            'last_run_at' => null,
            'last_status' => null,
            'last_imported_count' => 0,
            'last_duration' => 0,
            'last_message' => null,
        ];

        if (file_exists($configPath)) {
            $saved = json_decode(file_get_contents($configPath), true);
            if (is_array($saved)) {
                $config = array_merge($config, $saved);
            }
        }

        $force = (bool)$this->option('force');

        if (!$force && empty($config['enabled'])) {
            $this->comment('Sinkronisasi otomatis Simply dalam status NONAKTIF.');
            return self::SUCCESS;
        }

        // Check frequency interval
        if (!$force && !empty($config['last_run_at'])) {
            $lastRun = Carbon::parse($config['last_run_at']);
            $now = Carbon::now();
            $due = match ($config['frequency']) {
                'every_15_minutes' => $lastRun->diffInMinutes($now) >= 15,
                'every_30_minutes' => $lastRun->diffInMinutes($now) >= 30,
                'hourly' => $lastRun->diffInHours($now) >= 1,
                'every_6_hours' => $lastRun->diffInHours($now) >= 6,
                'daily' => $lastRun->diffInDays($now) >= 1,
                default => $lastRun->diffInHours($now) >= 1,
            };

            if (!$due) {
                $this->comment("Belum mencapai jadwal eksekusi berikutnya (Frekuensi: {$config['frequency']}, Terakhir: {$config['last_run_at']}).");
                return self::SUCCESS;
            }
        }

        $this->info('Memulai sinkronisasi otomatis basis data Simply -> Sadaya...');

        // Load credentials from persistent storage if available
        $credentials = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => 'mysql',
        ];

        $dbConfigPath = storage_path('app/simply_migration_configs.json');
        if (file_exists($dbConfigPath)) {
            $savedDb = json_decode(file_get_contents($dbConfigPath), true);
            if (is_array($savedDb)) {
                $credentials['modules'] = $savedDb;
            }
        }

        $modulesToSync = !empty($config['modules']) ? $config['modules'] : array_keys($service->getModuleConfigs());
        $totalImported = 0;
        $totalSkipped = 0;
        $totalProcessed = 0;
        $startTime = microtime(true);
        $errors = [];

        foreach ($modulesToSync as $module) {
            $this->line("Sinkronisasi modul: {$module}...");
            try {
                $res = $service->migrateModule($module, $credentials, [
                    'skip_duplicate' => true,
                    'truncate_first' => false,
                    'recalculate_pnbp' => true,
                    'normalize_ports' => true,
                    'dry_run' => false,
                ]);

                if ($res['success']) {
                    $totalImported += $res['imported'];
                    $totalSkipped += $res['skipped'];
                    $totalProcessed += $res['total_processed'];
                    $this->info(" -> {$module}: {$res['imported']} diimpor, {$res['skipped']} duplikat dilewati.");
                } else {
                    $errors[] = "{$module}: " . ($res['message'] ?? 'Gagal');
                    $this->warn(" -> {$module}: Gagal - " . ($res['message'] ?? 'Error'));
                }
            } catch (Throwable $e) {
                $errors[] = "{$module}: " . $e->getMessage();
                $this->error(" -> {$module}: Exception - " . $e->getMessage());
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        // Update persistent auto-sync state
        $config['last_run_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $config['last_status'] = empty($errors) ? 'success' : (count($errors) === count($modulesToSync) ? 'failed' : 'partial');
        $config['last_imported_count'] = $totalImported;
        $config['last_duration'] = $duration;
        $config['last_message'] = empty($errors)
            ? "Sinkronisasi otomatis berhasil menarik {$totalImported} data baru ({$totalSkipped} duplikat dilewati)."
            : "Sinkronisasi selesai dengan catatan: " . implode(', ', $errors);

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("✅ Sinkronisasi otomatis selesai dalam {$duration}s. Total data baru: {$totalImported}.");

        return self::SUCCESS;
    }
}

