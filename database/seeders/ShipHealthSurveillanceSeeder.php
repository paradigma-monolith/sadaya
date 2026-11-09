<?php

namespace Database\Seeders;

use App\Models\ShipHealthSurveillance;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipHealthSurveillanceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $verifierName = $admin ? ($admin->name ?? 'Administrator BKK') : 'Petugas Karantina BKK';

        $vessels = [
            [
                'registration_no' => 'MDH-202611-001',
                'nama_kapal' => 'MV PACIFIC VOYAGER',
                'datang_dari' => 'Port of Singapore',
                'origin_country' => 'SINGAPORE',
                'pelabuhan' => 'Wilayah Kerja Patimban',
                'gross_tonnage' => 32450,
                'jml_crew' => 24,
                'negara_singgah_14_hari' => 'Singapore, Malaysia',
                'kategori' => 'Luar Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => 'surat_pernyataan_pacific_voyager.pdf',
                'dokumen_mdh' => 'mdh_pacific_voyager.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Pemeriksaan dokumen karantina lengkap dan memenuhi syarat bebas penyakit. Diberikan izin sandar.',
                'tanggal_kedatangan' => now()->subDays(1)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-002',
                'nama_kapal' => 'KM DHARMA SAMUDRA VI',
                'datang_dari' => 'Tanjung Perak, Surabaya',
                'origin_country' => 'INDONESIA',
                'pelabuhan' => 'Wilayah Kerja Cirebon',
                'gross_tonnage' => 4820,
                'jml_crew' => 18,
                'negara_singgah_14_hari' => 'Indonesia',
                'kategori' => 'Dalam Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => null,
                'dokumen_mdh' => 'mdh_dharma_samudra.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Pelayaran domestik. Seluruh ABK dalam kondisi sehat.',
                'tanggal_kedatangan' => now()->subDays(2)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-003',
                'nama_kapal' => 'MT OCEAN MARINER',
                'datang_dari' => 'Port Klang, Malaysia',
                'origin_country' => 'PANAMA',
                'pelabuhan' => 'Wilayah Kerja Indramayu',
                'gross_tonnage' => 18750,
                'jml_crew' => 22,
                'negara_singgah_14_hari' => 'Malaysia, Thailand',
                'kategori' => 'Luar Negeri',
                'skoring' => 20,
                'surat_pernyataan_nahkoda' => 'surat_nahkoda_ocean_mariner.pdf',
                'dokumen_mdh' => 'mdh_ocean_mariner.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Terdapat 1 kru demam 38.5 C. Diperintahkan lego jangkar di Zona Karantina untuk pemeriksaan on board oleh Tim PHEOC.',
                'tanggal_kedatangan' => now()->subDays(3)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-004',
                'nama_kapal' => 'TB SEMARANG JAYA 08',
                'datang_dari' => 'Pelabuhan Tanjung Emas, Semarang',
                'origin_country' => 'INDONESIA',
                'pelabuhan' => 'Wilayah Kerja Cirebon',
                'gross_tonnage' => 280,
                'jml_crew' => 10,
                'negara_singgah_14_hari' => 'Indonesia',
                'kategori' => 'Dalam Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => null,
                'dokumen_mdh' => 'mdh_semarang_jaya.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Tugboat domestik dalam kondisi laik karantina.',
                'tanggal_kedatangan' => now()->subDays(4)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-005',
                'nama_kapal' => 'MV EVER HARMONY',
                'datang_dari' => 'Shanghai, China',
                'origin_country' => 'LIBERIA',
                'pelabuhan' => 'Wilayah Kerja Patimban',
                'gross_tonnage' => 45200,
                'jml_crew' => 26,
                'negara_singgah_14_hari' => 'China, Hong Kong',
                'kategori' => 'Luar Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => 'statement_ever_harmony.pdf',
                'dokumen_mdh' => 'mdh_ever_harmony.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Dokumen Maritime Declaration of Health diverifikasi lengkap. Sandar di Dermaga Kontainer Patimban.',
                'tanggal_kedatangan' => now()->subDays(5)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-006',
                'nama_kapal' => 'KM BAHARI UTAMA',
                'datang_dari' => 'Pelabuhan Panjang, Lampung',
                'origin_country' => 'INDONESIA',
                'pelabuhan' => 'Wilayah Kerja Palabuhanratu',
                'gross_tonnage' => 1250,
                'jml_crew' => 14,
                'negara_singgah_14_hari' => 'Indonesia',
                'kategori' => 'Dalam Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => null,
                'dokumen_mdh' => 'mdh_bahari_utama.pdf',
                'status_verifikasi' => 'pending',
                'catatan_verifikasi' => null,
                'tanggal_kedatangan' => now()->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-007',
                'nama_kapal' => 'MT PETRO GAS II',
                'datang_dari' => 'Balongan, Indramayu',
                'origin_country' => 'INDONESIA',
                'pelabuhan' => 'Wilayah Kerja Indramayu',
                'gross_tonnage' => 8400,
                'jml_crew' => 20,
                'negara_singgah_14_hari' => 'Indonesia',
                'kategori' => 'Dalam Negeri',
                'skoring' => 10,
                'surat_pernyataan_nahkoda' => null,
                'dokumen_mdh' => 'mdh_petro_gas.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => '1 kru mengalami batuk pilek ringan tanpa demam, pengawasan tetap dilakukan.',
                'tanggal_kedatangan' => now()->subDays(7)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-008',
                'nama_kapal' => 'MV NORDIC STAR',
                'datang_dari' => 'Busan, South Korea',
                'origin_country' => 'MARSHALL ISLANDS',
                'pelabuhan' => 'Wilayah Kerja Patimban',
                'gross_tonnage' => 24800,
                'jml_crew' => 21,
                'negara_singgah_14_hari' => 'South Korea, Taiwan',
                'kategori' => 'Luar Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => 'statement_nordic_star.pdf',
                'dokumen_mdh' => 'mdh_nordic_star.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Pemeriksaan karantina laut clear. Izin sandar diterbitkan.',
                'tanggal_kedatangan' => now()->subDays(8)->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-009',
                'nama_kapal' => 'KM SUMBER REJEKI',
                'datang_dari' => 'Pelabuhan Tanjung Priok, Jakarta',
                'origin_country' => 'INDONESIA',
                'pelabuhan' => 'Wilayah Kerja Cirebon',
                'gross_tonnage' => 950,
                'jml_crew' => 12,
                'negara_singgah_14_hari' => 'Indonesia',
                'kategori' => 'Dalam Negeri',
                'skoring' => 0,
                'surat_pernyataan_nahkoda' => null,
                'dokumen_mdh' => 'mdh_sumber_rejeki.pdf',
                'status_verifikasi' => 'pending',
                'catatan_verifikasi' => null,
                'tanggal_kedatangan' => now()->toDateString(),
            ],
            [
                'registration_no' => 'MDH-202611-010',
                'nama_kapal' => 'MV GLOBAL HORIZON',
                'datang_dari' => 'Ho Chi Minh, Vietnam',
                'origin_country' => 'PANAMA',
                'pelabuhan' => 'Wilayah Kerja Patimban',
                'gross_tonnage' => 14200,
                'jml_crew' => 19,
                'negara_singgah_14_hari' => 'Vietnam',
                'kategori' => 'Luar Negeri',
                'skoring' => 40,
                'surat_pernyataan_nahkoda' => 'statement_global_horizon.pdf',
                'dokumen_mdh' => 'mdh_global_horizon.pdf',
                'status_verifikasi' => 'verified',
                'catatan_verifikasi' => 'Ditemukan 2 kru demam tinggi dan ruam kulit. Ditetapkan karantina penuh di Red Anchorage.',
                'tanggal_kedatangan' => now()->subDays(11)->toDateString(),
            ],
        ];

        foreach ($vessels as $v) {
            $risk = ShipHealthSurveillance::calculateRisk($v['skoring']);
            $pnbp = ShipHealthSurveillance::calculatePnbp($v['gross_tonnage']);

            $questions = [];
            foreach (ShipHealthSurveillance::QUARANTINE_QUESTIONS as $qNo => $questionText) {
                if ($qNo === 1) {
                    $questions[$qNo] = [
                        'text' => $questionText,
                        'answer' => $v['negara_singgah_14_hari'],
                        'score' => 0,
                    ];
                } elseif ($v['skoring'] > 0 && in_array($qNo, [2, 4, 11])) {
                    $questions[$qNo] = [
                        'text' => $questionText,
                        'answer' => 'Ya',
                        'score' => 20,
                    ];
                } else {
                    $questions[$qNo] = [
                        'text' => $questionText,
                        'answer' => 'Tidak',
                        'score' => 0,
                    ];
                }
            }

            ShipHealthSurveillance::updateOrCreate(
                ['registration_no' => $v['registration_no']],
                [
                    'nama_kapal' => $v['nama_kapal'],
                    'datang_dari' => $v['datang_dari'],
                    'origin_country' => $v['origin_country'],
                    'pelabuhan' => $v['pelabuhan'],
                    'gross_tonnage' => $v['gross_tonnage'],
                    'jml_crew' => $v['jml_crew'],
                    'negara_singgah_14_hari' => $v['negara_singgah_14_hari'],
                    'kategori' => $v['kategori'],
                    'questions' => $questions,
                    'skoring' => $v['skoring'],
                    'kresiko' => $risk['kresiko'],
                    'hasil_status' => $risk['hasil_status'],
                    'surat_pernyataan_nahkoda' => $v['surat_pernyataan_nahkoda'],
                    'dokumen_mdh' => $v['dokumen_mdh'],
                    'dokumen_pendukung' => [
                        'crew_list' => 'crew_list_' . strtolower(str_replace(' ', '_', $v['nama_kapal'])) . '.pdf',
                        'port_of_call' => 'voyage_memo.pdf',
                    ],
                    'estimasi_pnbp' => $pnbp,
                    'status_verifikasi' => $v['status_verifikasi'],
                    'catatan_verifikasi' => $v['catatan_verifikasi'],
                    'verified_by' => $v['status_verifikasi'] === 'verified' ? $admin?->id : null,
                    'verified_at' => $v['status_verifikasi'] === 'verified' ? now()->subDays(rand(1, 10)) : null,
                    'verifier_name' => $v['status_verifikasi'] === 'verified' ? $verifierName : null,
                    'tanggal_kedatangan' => $v['tanggal_kedatangan'],
                ]
            );
        }
    }
}

