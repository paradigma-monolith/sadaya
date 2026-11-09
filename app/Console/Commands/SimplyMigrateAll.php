<?php

namespace App\Console\Commands;

use App\Services\SimplyDataMigrationService;
use Illuminate\Console\Command;

class SimplyMigrateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simply:migrate
                            {--module=all : Modul yang akan dimigrasikan (all, ship_surveillance, aircraft_sanitation, ship_sanitation, medical_alat, medical_obat)}
                            {--host=127.0.0.1 : Host MySQL Simply}
                            {--port=3306 : Port MySQL Simply}
                            {--username=root : Username MySQL}
                            {--password=mysql : Password MySQL}
                            {--truncate : Kosongkan data tabel tujuan sebelum impor}
                            {--dry-run : Uji simulasi tanpa menyimpan perubahan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasikan seluruh data operasional (Pengawasan Kapal, Travel Consent Pesawat, Sanitasi Kapal, Alkes & Obat Haji) dari Simply ke Sadaya';

    /**
     * Execute the console command.
     */
    public function handle(SimplyDataMigrationService $service): int
    {
        $this->info("=================================================================");
        $this->info("  PUSAT MIGRASI BASIS DATA SIMPLY (CI4) -> SADAYA (LARAVEL/VUE)  ");
        $this->info("=================================================================");

        $credentials = [
            'host' => $this->option('host'),
            'port' => (int)$this->option('port'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
        ];

        $module = $this->option('module') ?: 'all';
        $options = [
            'skip_duplicate' => true,
            'truncate_first' => (bool)$this->option('truncate'),
            'recalculate_pnbp' => true,
            'normalize_ports' => true,
            'dry_run' => (bool)$this->option('dry-run'),
        ];

        $this->info("Memverifikasi koneksi ke basis data Simply...");
        $test = $service->testAll($credentials);

        $testRows = [];
        foreach ($test['modules'] as $k => $m) {
            $status = $m['connected'] && $m['table_exists'] ? '<info>Siap (' . number_format($m['count']) . ' data)</info>' : '<comment>' . ($m['error'] ?? 'Tidak ditemukan') . '</comment>';
            $testRows[] = [
                $m['name'],
                $m['database'],
                $m['table'],
                $status,
                $m['min_date'] ? "{$m['min_date']} - {$m['max_date']}" : '-'
            ];
        }

        $this->table(['Modul', 'Database', 'Tabel Sumber', 'Status & Data', 'Periode'], $testRows);

        if ($this->option('dry-run')) {
            $this->warn("⚠️  MODE SIMULASI (--dry-run) AKTIF: Data tidak akan disimpan.");
        }
        if ($this->option('truncate')) {
            $this->warn("⚠️  MODE TRUNCATE AKTIF: Tabel tujuan akan dikosongkan terlebih dahulu!");
        }

        $this->newLine();
        $this->info("Memulai proses migrasi data...");

        if ($module === 'all') {
            $result = $service->migrateAll($credentials, $options);
            $summaryRows = [];
            foreach ($result['details'] as $mKey => $detail) {
                $summaryRows[] = [
                    $detail['module_name'] ?? $mKey,
                    number_format($detail['total_processed']),
                    number_format($detail['imported']),
                    number_format($detail['skipped']),
                    number_format($detail['failed']),
                    "{$detail['duration']}s",
                ];
            }
            $this->table(['Modul', 'Diproses', 'Diimpor', 'Dilewati (Duplikat)', 'Gagal', 'Durasi'], $summaryRows);
            $this->info("✅ Seluruh proses migrasi selesai dalam {$result['duration']} detik. Total diimpor: {$result['total_imported']} rekaman.");
        } else {
            $result = $service->migrateModule($module, $credentials, $options);
            if (!$result['success']) {
                $this->error("Gagal migrasi modul {$module}: " . ($result['message'] ?? 'Error'));
                return self::FAILURE;
            }
            $this->table(
                ['Metrik', 'Jumlah'],
                [
                    ['Modul', $result['module_name']],
                    ['Total Diproses', number_format($result['total_processed'])],
                    ['Berhasil Diimpor', number_format($result['imported'])],
                    ['Dilewati (Duplikat)', number_format($result['skipped'])],
                    ['Gagal', number_format($result['failed'])],
                    ['Durasi', "{$result['duration']} detik"],
                ]
            );
        }

        return self::SUCCESS;
    }
}

