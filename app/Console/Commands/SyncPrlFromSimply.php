<?php

namespace App\Console\Commands;

use App\Models\PrlHsbu;
use App\Models\PrlInspeksiHsbu;
use App\Models\PrlInspeksiSab;
use App\Models\PrlInspeksiTpp;
use App\Models\PrlSab;
use App\Models\PrlSiveraVektor;
use App\Models\PrlTpp;
use Illuminate\Console\Command;
use PDO;
use Throwable;

class SyncPrlFromSimply extends Command
{
    protected $signature = 'prl:sync-simply {--truncate : Kosongkan tabel PRL Sadaya sebelum sinkronisasi}';
    protected $description = 'Sinkronisasi seluruh data Pengendalian Risiko Lingkungan (TPP, SAB, HSBU, SIVERA) dari Simply MySQL ke Sadaya';

    public function handle(): int
    {
        $this->info('Memulai sinkronisasi data PRL dari basis data Simply MySQL...');

        $host = env('SIMPLY_DB_HOST', '127.0.0.1');
        $port = env('SIMPLY_DB_PORT', 3306);
        $user = env('SIMPLY_DB_USERNAME', 'root');
        $pass = env('SIMPLY_DB_PASSWORD', 'mysql');
        $db   = env('SIMPLY_DB_DATABASE', 'bkkr3927_simply');

        try {
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Throwable $e) {
            $this->error("Gagal terhubung ke MySQL Simply ({$host}:{$port}/{$db}): " . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            $this->warn('Mengosongkan tabel PRL di Sadaya...');
            PrlInspeksiTpp::truncate();
            PrlTpp::truncate();
            PrlInspeksiSab::truncate();
            PrlSab::truncate();
            PrlInspeksiHsbu::truncate();
            PrlHsbu::truncate();
            PrlSiveraVektor::truncate();
        }

        // 1. Ambil data lookup Wilker & Pegawai
        $wilkerMap = $pdo->query("SELECT id_unit_kerja, unit_kerja FROM unit_kerja")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $pegawaiMap = $pdo->query("SELECT id_data_pegawai, nama FROM data_pegawai")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $userMap = $pdo->query("SELECT id_user, nama FROM user")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // Helper resolver nama pegawai
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

        // ==========================================
        // 1. TPP (Tempat Pengelolaan Pangan)
        // ==========================================
        $this->line('Menyinkronkan Tempat Pengelolaan Pangan (TPP)...');
        $tppRows = $pdo->query("SELECT * FROM prl_tpp ORDER BY id_prl_tpp ASC")->fetchAll();
        $tppMapId = [];
        $tppCount = 0;
        foreach ($tppRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
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
            $tppCount++;
        }
        $this->info(" -> Berhasil memproses {$tppCount} sarana TPP.");

        // Inspeksi TPP
        $this->line('Menyinkronkan Inspeksi TPP...');
        $inspTppRows = $pdo->query("
            SELECT i.*, t.nama_tpp as tpp_nama 
            FROM prl_inspeksi_tpp i 
            LEFT JOIN prl_tpp t ON t.id_prl_tpp = i.id_prl_tpp 
            ORDER BY i.id_prl_inspeksi_tpp ASC
        ")->fetchAll();
        $inspTppCount = 0;
        foreach ($inspTppRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $score = (int)$r['hasil'];
            $risk = match (true) {
                $score < 35 => 'Rendah',
                $score <= 50 => 'Sedang',
                default => 'Tinggi',
            };

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
            $inspTppCount++;
        }
        $this->info(" -> Berhasil memproses {$inspTppCount} riwayat inspeksi TPP.");

        // ==========================================
        // 2. SAB (Sarana Air Bersih)
        // ==========================================
        $this->line('Menyinkronkan Sarana Air Bersih (SAB)...');
        $sabRows = $pdo->query("SELECT * FROM prl_sab ORDER BY id_prl_sab ASC")->fetchAll();
        $sabMapId = [];
        $sabCount = 0;
        foreach ($sabRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
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
            $sabCount++;
        }
        $this->info(" -> Berhasil memproses {$sabCount} sarana SAB.");

        // Inspeksi SAB
        $this->line('Menyinkronkan Inspeksi SAB...');
        $inspSabRows = $pdo->query("
            SELECT i.*, s.nama_sab as sab_nama 
            FROM prl_inspeksi_sab i 
            LEFT JOIN prl_sab s ON s.id_prl_sab = i.id_prl_sab 
            ORDER BY i.id_prl_inspeksi_sab ASC
        ")->fetchAll();
        $inspSabCount = 0;
        foreach ($inspSabRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $score = (int)$r['hasil'];
            $risk = match (true) {
                $score < 25 => 'Rendah',
                $score <= 50 => 'Sedang',
                default => 'Tinggi',
            };

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
            $inspSabCount++;
        }
        $this->info(" -> Berhasil memproses {$inspSabCount} riwayat inspeksi SAB.");

        // ==========================================
        // 3. HSBU (Higiene Sanitasi Bangunan dan Usaha)
        // ==========================================
        $this->line('Menyinkronkan Bangunan dan Usaha (HSBU)...');
        $hsbuRows = $pdo->query("SELECT * FROM prl_hsbu ORDER BY id_prl_hsbu ASC")->fetchAll();
        $hsbuMapId = [];
        $hsbuCount = 0;
        foreach ($hsbuRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
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
            $hsbuCount++;
        }
        $this->info(" -> Berhasil memproses {$hsbuCount} sarana HSBU.");

        // Inspeksi HSBU
        $this->line('Menyinkronkan Inspeksi HSBU...');
        $inspHsbuRows = $pdo->query("
            SELECT i.*, h.nama_gedung as hsbu_nama 
            FROM prl_inspeksi_hsbu i 
            LEFT JOIN prl_hsbu h ON h.id_prl_hsbu = i.id_prl_hsbu 
            ORDER BY i.id_prl_inspeksi_hsbu ASC
        ")->fetchAll();
        $inspHsbuCount = 0;
        foreach ($inspHsbuRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $score = (float)$r['total'];
            $risk = match (true) {
                $score > 712.5 => 'Rendah',
                $score >= 475 => 'Sedang',
                default => 'Tinggi',
            };

            // Parse PHP serialized checklist components if present
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
            $inspHsbuCount++;
        }
        $this->info(" -> Berhasil memproses {$inspHsbuCount} riwayat inspeksi HSBU.");

        // ==========================================
        // 4. SIVERA (Surveilans Vektor)
        // ==========================================
        $this->line('Menyinkronkan Surveilans Vektor (SIVERA)...');
        $siveraCount = 0;

        // Jentik
        $jentikRows = $pdo->query("SELECT * FROM prl_sivera_jentik ORDER BY id_jentik ASC")->fetchAll();
        foreach ($jentikRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $kontainer = (int)($r['jml_kontainer_diperiksa'] ?? 0);
            $positif = (int)($r['jml_kontainer_positif'] ?? 0);
            $ci = $kontainer > 0 ? round(($positif / $kontainer) * 100, 2) : 0;
            $abj = 100 - $ci;

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
                    'tindakan_pengendalian' => $r['pengendalian'],
                    'created_by_name' => $r['created_by'],
                    'created_at' => $r['created_at'],
                    'updated_at' => $r['updated_at'],
                ]
            );
            $siveraCount++;
        }

        // Lalat
        $lalatRows = $pdo->query("SELECT * FROM prl_sivera_lalat ORDER BY id_lalat ASC")->fetchAll();
        foreach ($lalatRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $fi = (float)($r['rata_rata'] ?? 0);

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
                    'tindakan_pengendalian' => $r['pengendalian'],
                    'created_by_name' => $r['created_by'],
                    'created_at' => $r['created_at'],
                    'updated_at' => $r['updated_at'],
                ]
            );
            $siveraCount++;
        }

        // Tikus
        $tikusRows = $pdo->query("SELECT * FROM prl_sivera_tikus ORDER BY id_tikus ASC")->fetchAll();
        foreach ($tikusRows as $r) {
            $wilkerName = $wilkerMap[$r['id_wilayah_kerja']] ?? 'Wilayah Kerja Patimban';
            $ts = (float)($r['success_trap'] ?? 0);

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
                    'tindakan_pengendalian' => "Pemasangan {$r['jml_perangkap_dipasang']} perangkap",
                    'created_by_name' => $r['created_by'],
                    'created_at' => $r['created_at'],
                    'updated_at' => $r['updated_at'],
                ]
            );
            $siveraCount++;
        }

        $this->info(" -> Berhasil memproses {$siveraCount} surveilans vektor SIVERA.");

        $this->info('====================================================');
        $this->info('Sinkronisasi data PRL dari Simply ke Sadaya SELESAI!');
        $this->info("Total: {$tppCount} TPP ({$inspTppCount} inspeksi), {$sabCount} SAB ({$inspSabCount} inspeksi), {$hsbuCount} HSBU ({$inspHsbuCount} inspeksi), {$siveraCount} surveilans SIVERA.");
        $this->info('====================================================');

        return self::SUCCESS;
    }
}

