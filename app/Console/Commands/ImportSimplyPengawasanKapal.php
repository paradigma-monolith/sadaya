<?php

namespace App\Console\Commands;

use App\Services\ShipSurveillanceImportService;
use Illuminate\Console\Command;

class ImportSimplyPengawasanKapal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ship-surveillance:import-simply
                            {--host=127.0.0.1 : Host MySQL Simply}
                            {--port=3306 : Port MySQL Simply}
                            {--database=bkkr3927_cop : Nama Database Simply}
                            {--username=root : Username MySQL}
                            {--password=mysql : Password MySQL}
                            {--table=tbl_pasien : Nama tabel di Simply}
                            {--truncate : Kosongkan data di Sadaya sebelum impor}
                            {--dry-run : Uji simulasi tanpa menyimpan ke database}
                            {--file= : Path ke file backup SQL / JSON / CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan migrasikan data pengawasan kapal (tbl_pasien) dari aplikasi Simply (CI4) ke Sadaya';

    /**
     * Execute the console command.
     */
    public function handle(ShipSurveillanceImportService $service): int
    {
        $this->info("=== MIGRASI DATA PENGAWASAN KAPAL (SIMPLY -> SADAYA) ===");

        $filePath = $this->option('file');

        if ($filePath) {
            if (!file_exists($filePath)) {
                $this->error("File backup tidak ditemukan: {$filePath}");
                return self::FAILURE;
            }

            $this->info("Mengimpor dari file backup: {$filePath}");
            // Use dummy UploadedFile or direct import
            $file = new \Illuminate\Http\UploadedFile(
                $filePath,
                basename($filePath),
                mime_content_type($filePath) ?: 'text/plain',
                null,
                true
            );

            $result = $service->importFromFile($file, [
                'truncate_first' => (bool)$this->option('truncate'),
                'skip_duplicate' => true,
                'recalculate_pnbp' => true,
                'normalize_ports' => true,
            ]);
        } else {
            $config = [
                'host' => $this->option('host'),
                'port' => (int)$this->option('port'),
                'database' => $this->option('database'),
                'username' => $this->option('username'),
                'password' => $this->option('password'),
                'table' => $this->option('table'),
            ];

            $this->info("Menghubungi MySQL {$config['host']}:{$config['port']} / DB: {$config['database']} ...");

            // Test connection first
            $test = $service->testConnection($config);
            if (!$test['success']) {
                $this->error($test['message']);
                return self::FAILURE;
            }

            $stats = $test['stats'];
            $this->table(
                ['Parameter', 'Nilai'],
                [
                    ['Total Data di Simply', number_format($stats['total']) . ' rekaman'],
                    ['Rute Luar Negeri', number_format($stats['luar_negeri']) . ' kapal'],
                    ['Rute Dalam Negeri', number_format($stats['dalam_negeri']) . ' kapal'],
                    ['Faktor Risiko Tinggi', number_format($stats['tinggi_risiko']) . ' kapal'],
                    ['Terverifikasi', number_format($stats['terverifikasi']) . ' kapal'],
                    ['Rentang Tanggal', "{$stats['min_date']} s/d {$stats['max_date']}"],
                ]
            );

            if ($this->option('dry-run')) {
                $this->warn("Mode --dry-run aktif. Data tidak akan disimpan.");
            }

            if ($this->option('truncate')) {
                $this->warn("Opsi --truncate aktif: Tabel ship_health_surveillances akan dikosongkan terlebih dahulu!");
            }

            $this->info("Memulai proses migrasi data...");

            $result = $service->importFromMysql($config, [
                'truncate_first' => (bool)$this->option('truncate'),
                'skip_duplicate' => true,
                'recalculate_pnbp' => true,
                'normalize_ports' => true,
                'dry_run' => (bool)$this->option('dry-run'),
            ]);
        }

        if (!$result['success']) {
            $this->error("Gagal melakukan import: " . ($result['message'] ?? 'Terjadi kesalahan'));
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("✅ HASIL MIGRASI DATA BERHASIL:");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Diproses', number_format($result['total_processed'])],
                ['Berhasil Diimpor', number_format($result['imported'])],
                ['Dilewati (Duplikat)', number_format($result['skipped'])],
                ['Gagal / Error', number_format($result['failed'])],
                ['Total Estimasi PNBP', 'Rp ' . number_format($result['total_pnbp'], 0, ',', '.')],
                ['Durasi Waktu', "{$result['duration']} detik"],
            ]
        );

        if (!empty($result['errors'])) {
            $this->warn("Beberapa catatan error:");
            foreach ($result['errors'] as $err) {
                $this->line(" - {$err}");
            }
        }

        return self::SUCCESS;
    }
}

