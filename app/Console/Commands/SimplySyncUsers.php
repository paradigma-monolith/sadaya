<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SimplyDataMigrationService;
use Illuminate\Console\Command;
use Throwable;

class SimplySyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simply:sync-users
                            {--host= : Host MySQL Simply}
                            {--port= : Port MySQL Simply}
                            {--username= : Username MySQL}
                            {--password= : Password MySQL}
                            {--database= : Nama basis data Simply (default: bkkr3927_simply)}
                            {--table= : Tabel pengguna Simply (default: user)}
                            {--force : Paksa update password dan data pengguna yang sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi seluruh data akun pengguna (username/NIP, password Bcrypt, peran) dari Simply ke Sadaya';

    /**
     * Execute the console command.
     */
    public function handle(SimplyDataMigrationService $service): int
    {
        $this->info("=================================================================");
        $this->info("    SINKRONISASI PENGGUNA SIMPLY -> SADAYA (AKUN & KATA SANDI)   ");
        $this->info("=================================================================");

        $credentials = [];
        if ($this->option('host')) $credentials['host'] = $this->option('host');
        if ($this->option('port')) $credentials['port'] = (int)$this->option('port');
        if ($this->option('username')) $credentials['username'] = $this->option('username');
        if ($this->option('password')) $credentials['password'] = $this->option('password');
        if ($this->option('database')) $credentials['database'] = $this->option('database');
        if ($this->option('table')) $credentials['table'] = $this->option('table');

        // Check persistent module configs
        $savedPath = storage_path('app/simply_migration_configs.json');
        if (file_exists($savedPath)) {
            $saved = json_decode(file_get_contents($savedPath), true);
            if (is_array($saved)) {
                $credentials['modules'] = $saved;
                $this->info("ℹ️  Memuat konfigurasi MySQL dari penyimpanan lokal.");
            }
        }

        $force = (bool)$this->option('force');

        $this->line("Menghubungkan ke basis data Simply...");
        try {
            $result = $service->migrateModule('users', $credentials, [
                'skip_duplicate' => !$force,
                'truncate_first' => false,
                'dry_run' => false,
            ]);

            if ($result['success']) {
                $this->info("✅ Berhasil! " . $result['imported'] . " pengguna diproses/disinkronkan.");
                $this->comment("   Dilewati (sudah ada): " . $result['skipped']);
                $this->comment("   Total dipindai: " . $result['total_processed']);
                $this->comment("   Durasi: " . $result['duration'] . " detik");

                if (!empty($result['errors'])) {
                    $this->warn("   Peringatan pada baris:");
                    foreach ($result['errors'] as $err) {
                        $this->line("   - {$err}");
                    }
                }

                $this->info("🎉 Seluruh akun pengguna dari Simply sekarang dapat login langsung ke Sadaya!");
                return self::SUCCESS;
            } else {
                $this->error("❌ Gagal migrasi pengguna: " . ($result['message'] ?? 'Error tidak diketahui'));
                return self::FAILURE;
            }
        } catch (Throwable $e) {
            $this->error("❌ Terjadi kesalahan: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}

