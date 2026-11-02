<?php

namespace Database\Seeders;

use App\Models\ShipSanitationInspection;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipSanitationInspectionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $sampleVessels = [
            [
                'registration_no' => 'SSCC-202610-0001',
                'pelabuhan' => 'Pelabuhan Patimban',
                'tanggal' => now()->subDays(12)->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pembebasan',
                'imo_no' => 'IMO 9872154',
                'jenis_kapal' => 'Kapal Barang (Cargo)',
                'detail_jenis_kapal' => 'Container Ship',
                'nama_kapal' => 'MV PATIMBAN GLORY',
                'bendera' => 'Indonesia',
                'berat_gt' => 28500,
                'kondisi_palka' => 'Kosong',
                'nama_pemeriksa' => 'dr. Agus Suryana, M.Kes',
                'alamat_pemeriksa' => 'Wilker Patimban, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Seluruh area kapal memenuhi standar kebersihan IHR 2005. Tidak ditemukan tanda-tanda vektor atau hewan penular penyakit.',
                'status' => 'approved',
                'latitude' => -6.248312,
                'longitude' => 107.902341,
                'lokasi_geotag' => 'Dermaga Petikemas Pelabuhan Patimban, Subang',
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(12)->addHours(3),
            ],
            [
                'registration_no' => 'SSCC-202610-0002',
                'pelabuhan' => 'Pelabuhan Cirebon',
                'tanggal' => now()->subDays(9)->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pengendalian',
                'imo_no' => 'IMO 9345129',
                'jenis_kapal' => 'Kapal Tangki (Tanker)',
                'detail_jenis_kapal' => 'Oil / Chemical Tanker',
                'nama_kapal' => 'MT CIREBON SAMUDRA',
                'bendera' => 'Panama',
                'berat_gt' => 16400,
                'kondisi_palka' => 'Bermuatan',
                'nama_pemeriksa' => 'H. Deni Kurniawan, SKM',
                'alamat_pemeriksa' => 'Wilker Cirebon, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Ditemukan indikasi kecoa di galley pantry; telah dilakukan tindakan pengendalian disinseksi dan pengawasan sanitasi pangan.',
                'status' => 'approved',
                'latitude' => -6.719823,
                'longitude' => 108.572145,
                'lokasi_geotag' => 'Dermaga Surya Pelabuhan Cirebon',
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(9)->addHours(4),
            ],
            [
                'registration_no' => 'SSCC-202610-0003',
                'pelabuhan' => 'Pelabuhan Indramayu',
                'tanggal' => now()->subDays(6)->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pembebasan',
                'imo_no' => 'IMO 9120487',
                'jenis_kapal' => 'Kapal Tunda & Tongkang',
                'detail_jenis_kapal' => 'Tug Boat',
                'nama_kapal' => 'TB PATRA JAYA 08',
                'bendera' => 'Indonesia',
                'berat_gt' => 450,
                'kondisi_palka' => 'Kosong',
                'nama_pemeriksa' => 'Rahmat Hidayat, Amd.Kes',
                'alamat_pemeriksa' => 'Wilker Indramayu, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Kondisi sanitasi ruang ABK, dapur, dan kamar mesin bersih serta higienis. Layak diberikan SSCEC.',
                'status' => 'verified',
                'latitude' => -6.326712,
                'longitude' => 108.324567,
                'lokasi_geotag' => 'Jetty PLTU Karangsong Indramayu',
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(6)->addHours(2),
            ],
            [
                'registration_no' => 'SSCC-202610-0004',
                'pelabuhan' => 'Pelabuhan Pelabuhan Ratu',
                'tanggal' => now()->subDays(4)->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pembebasan',
                'imo_no' => 'IMO 8761234',
                'jenis_kapal' => 'Kapal Penumpang / Wisata',
                'detail_jenis_kapal' => 'Passenger Ship',
                'nama_kapal' => 'KM RATU SAMUDRA II',
                'bendera' => 'Indonesia',
                'berat_gt' => 1250,
                'kondisi_palka' => 'Kosong',
                'nama_pemeriksa' => 'Yudi Permana, SKM',
                'alamat_pemeriksa' => 'Wilker Pelabuhan Ratu, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Sanitasi dek penumpang dan fasilitas toilet dalam keadaan layak dan bersih.',
                'status' => 'approved',
                'latitude' => -6.985412,
                'longitude' => 106.543219,
                'lokasi_geotag' => 'Dermaga Nusantara Pelabuhan Ratu, Sukabumi',
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(4)->addHours(1),
            ],
            [
                'registration_no' => 'SSCC-202611-0005',
                'pelabuhan' => 'Pelabuhan Patimban',
                'tanggal' => now()->subDays(2)->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pengendalian',
                'imo_no' => 'IMO 9642019',
                'jenis_kapal' => 'Kapal Barang (Cargo)',
                'detail_jenis_kapal' => 'Bulk Carrier',
                'nama_kapal' => 'MV PACIFIC HORIZON',
                'bendera' => 'Singapore',
                'berat_gt' => 34200,
                'kondisi_palka' => 'Bermuatan',
                'nama_pemeriksa' => 'dr. Agus Suryana, M.Kes',
                'alamat_pemeriksa' => 'Wilker Patimban, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Telah dilakukan tindakan deratisasi pada palka kargo nomor 2 dan 3 sesuai panduan WHO.',
                'status' => 'approved',
                'latitude' => -6.248400,
                'longitude' => 107.902500,
                'lokasi_geotag' => 'Terminal Curah Kering Pelabuhan Patimban',
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(2)->addHours(5),
            ],
            [
                'registration_no' => 'SSCC-202611-0006',
                'pelabuhan' => 'Pelabuhan Cirebon',
                'tanggal' => now()->subDay()->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pembebasan',
                'imo_no' => 'IMO 9781120',
                'jenis_kapal' => 'Kapal Tangki (Tanker)',
                'detail_jenis_kapal' => 'LPG Tanker',
                'nama_kapal' => 'MT GAS NUSANTARA',
                'bendera' => 'Indonesia',
                'berat_gt' => 8900,
                'kondisi_palka' => 'Bermuatan',
                'nama_pemeriksa' => 'H. Deni Kurniawan, SKM',
                'alamat_pemeriksa' => 'Wilker Cirebon, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Semua indikator sanitasi ruang akomodasi dan air minum memenuhi baku mutu kesehatan pelabuhan.',
                'status' => 'submitted',
                'latitude' => -6.719900,
                'longitude' => 108.572300,
                'lokasi_geotag' => 'Dermaga Muara Jati Pelabuhan Cirebon',
                'verified_by' => null,
                'verified_at' => null,
            ],
            [
                'registration_no' => 'SSCC-202611-0007',
                'pelabuhan' => 'Pelabuhan Patimban',
                'tanggal' => now()->format('Y-m-d'),
                'sertifikat_mencatat' => 'Pembebasan',
                'imo_no' => 'IMO 9901456',
                'jenis_kapal' => 'Kapal Pengangkut Mobil (PCC / RORO)',
                'detail_jenis_kapal' => 'Vehicle Carrier',
                'nama_kapal' => 'MV TRANS LEADER',
                'bendera' => 'Japan',
                'berat_gt' => 48000,
                'kondisi_palka' => 'Bermuatan',
                'nama_pemeriksa' => 'dr. Agus Suryana, M.Kes',
                'alamat_pemeriksa' => 'Wilker Patimban, BKK Kelas I Bandung',
                'pejabat_penerbit' => 'dr. H. Rinaldi, Sp.KP',
                'keterangan' => 'Inspeksi kedatangan kapal RORO pengangkut kendaraan ekspor; nihil vektor, sanitasi sangat baik.',
                'status' => 'submitted',
                'latitude' => -6.248200,
                'longitude' => 107.902100,
                'lokasi_geotag' => 'Terminal Kendaraan (Car Terminal) Pelabuhan Patimban',
                'verified_by' => null,
                'verified_at' => null,
            ],
        ];

        // 32 standard WHO IHR 2005 areas
        $standardAreas = ShipSanitationInspection::STANDARD_AREAS;

        foreach ($sampleVessels as $data) {
            $isControlledShip = ($data['sertifikat_mencatat'] === 'Pengendalian');
            $areasData = [];
            foreach ($standardAreas as $area) {
                $isControlledArea = $isControlledShip && in_array($area['no'], [1, 2, 4]);
                $areasData[] = [
                    'no' => $area['no'],
                    'nama' => $area['nama'],
                    'kategori' => $area['kategori'],
                    'status' => $isControlledArea ? 'Ditemukan Faktor Risiko' : 'Tidak Ditemukan Faktor Risiko',
                    'result' => $isControlledArea ? 'Dikendalikan' : 'Memenuhi Syarat',
                    'finding' => $isControlledArea ? 'Ditemukan indikasi vektor; tindakan pengendalian & desinfeksi telah dilaksanakan' : 'Nihil tanda vektor / kondisi bersih',
                    'sample' => 'Sesuai Baku Mutu',
                    'keterangan' => $isControlledArea ? 'Telah dilakukan tindakan pengendalian' : 'Memenuhi syarat standar sanitasi',
                    'sampel' => 'Sesuai Baku Mutu',
                    'bukti' => '-',
                    'tindakan' => $isControlledArea ? 'Disinseksi / Pengendalian Vektor' : '-',
                ];
            }
            $data['areas'] = $areasData;

            ShipSanitationInspection::updateOrCreate(
                ['registration_no' => $data['registration_no']],
                $data
            );
        }
    }
}

