<?php

namespace Database\Seeders;

use App\Models\FlightMedicalInspection;
use App\Models\User;
use Illuminate\Database\Seeder;

class FlightMedicalInspectionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // Standard 17 items for Alkes P3K
        $alatP3kMaster = [
            ['nama' => 'Adhesive bandage compresses, 1-inch', 'satuan' => 'Pcs', 'isi' => '16'],
            ['nama' => 'Antiseptic swab', 'satuan' => 'Pcs', 'isi' => '20'],
            ['nama' => 'Ammonia inhalants', 'satuan' => 'Pcs', 'isi' => '10'],
            ['nama' => 'Bandage compresses, 4-inch', 'satuan' => 'Pcs', 'isi' => '8'],
            ['nama' => 'Triangular bandage compresses, 40-inch', 'satuan' => 'Pcs', 'isi' => '5'],
            ['nama' => 'Arm splint, noninflatable', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Leg splint, noninflatable', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Roller bandage, 4-inch', 'satuan' => 'Pcs', 'isi' => '4'],
            ['nama' => 'Adhesive tape, 1-inch standard roll', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Bandage scissors', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Skin closure strips', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Disinfectant wipes', 'satuan' => 'Pcs', 'isi' => '20'],
            ['nama' => 'Burn dressings', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Disposable gloves (several pairs)', 'satuan' => 'Pcs', 'isi' => '10'],
            ['nama' => 'Thermometer (non-mercury)', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Resuscitation mask with one-way valve', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Gauze / eye protection pad', 'satuan' => 'Pcs', 'isi' => '2'],
        ];

        // Standard 21 items for Alkes Emergensi
        $alatEmergensiMaster = [
            ['nama' => 'Stethoscope', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Sphygmomanometer (electronic preferred)', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Airways, oropharyngeal (three sizes)', 'satuan' => 'Pcs', 'isi' => '3'],
            ['nama' => 'Syringes (appropriate range of sizes)', 'satuan' => 'Pcs', 'isi' => '6'],
            ['nama' => 'Needles (appropriate range of sizes)', 'satuan' => 'Pcs', 'isi' => '6'],
            ['nama' => 'Intravenous catheters (appropriate range of sizes)', 'satuan' => 'Pcs', 'isi' => '4'],
            ['nama' => 'Antiseptic wipes', 'satuan' => 'Pcs', 'isi' => '10'],
            ['nama' => 'Gloves (disposable)', 'satuan' => 'Pcs', 'isi' => '6'],
            ['nama' => 'Needle disposal box', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Urinary catheter', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'System for delivering intravenous fluids', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Venous tourniquet', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Sponge gauze', 'satuan' => 'Pcs', 'isi' => '10'],
            ['nama' => 'Tape-adhesive', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Surgical mask', 'satuan' => 'Pcs', 'isi' => '10'],
            ['nama' => 'Emergency tracheal catheter (or large gauge intravenous cannula)', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Umbilical cord clamp', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Thermometers (non-mercury)', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Basic life support card', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Bag-valve mask', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Flashlight and batteries', 'satuan' => 'Pcs', 'isi' => '1'],
        ];

        // Standard 6 items for Obat P3K
        $obatP3kMaster = [
            ['nama' => 'Mild to moderate analgesic', 'satuan' => 'Tablet', 'isi' => '30'],
            ['nama' => 'Antiemetic', 'satuan' => 'Ampul / Tablet', 'isi' => '12'],
            ['nama' => 'Nasal decongestant', 'satuan' => 'Tablet / Spray', 'isi' => '10'],
            ['nama' => 'Antacid (gastric)', 'satuan' => 'Tablet', 'isi' => '30'],
            ['nama' => 'Antidiarrheal', 'satuan' => 'Tablet', 'isi' => '20'],
            ['nama' => 'Antihistamine', 'satuan' => 'Tablet', 'isi' => '20'],
        ];

        // Standard 15 items for Obat Emergensi
        $obatEmergensiMaster = [
            ['nama' => 'Epinephrine 1:1000, 1 cc, injectable, (single dose ampule or equivalent)', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Antihistamine injectable, 50 mg, (single dose ampule or equivalent)', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Dextrose, 50%/50 cc injectable, (single dose ampule or equivalent)', 'satuan' => 'Ampul', 'isi' => '1'],
            ['nama' => 'Nitroglycerin tablets, 0.4 mg, or spray', 'satuan' => 'Tablet / Spray', 'isi' => '1'],
            ['nama' => 'Major analgesic', 'satuan' => 'Tablet', 'isi' => '10'],
            ['nama' => 'Sedative Anticonvulsant', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Antiemetic injectable', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Bronchial dilator, inhaled (metered dose inhale or equivalent)', 'satuan' => 'Tabung', 'isi' => '1'],
            ['nama' => 'Atropine, 0.5 mg, 5 cc(single dose ampule or equivalent)', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Adrenocortical steroid', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Diuretic', 'satuan' => 'Ampul', 'isi' => '2'],
            ['nama' => 'Medication for postpartum bleeding', 'satuan' => 'Pcs', 'isi' => '1'],
            ['nama' => 'Sodium Chloride 0.9% (minimum 250 ml)', 'satuan' => 'Pcs', 'isi' => '2'],
            ['nama' => 'Acetyl salicylic acid (aspirin) for oral use', 'satuan' => 'Tablet', 'isi' => '10'],
            ['nama' => 'Oral beta blocker', 'satuan' => 'Tablet', 'isi' => '10'],
        ];

        // Sample Records for ALKES (Alat Kesehatan)
        $sampleAlat = [
            [
                'registration_no' => 'ALKES-202610-0001',
                'type' => 'alat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Kertajati (KJT)',
                'nomor_kloter' => 'Kloter 01 KJT',
                'no_kontak' => '081223344551',
                'crew_nama' => 'Pramugari Santi Wulandari',
                'pelapor_nama' => 'dr. Dian Pratama (TKHI)',
                'status' => 'approved',
                'keterangan' => 'Pemakaian Alkes untuk jemaah lansia (penanganan luka lecet dan pemantauan tekanan darah selama penerbangan).',
                'latitude' => -6.649987,
                'longitude' => 108.196543,
                'lokasi_geotag' => 'Apron Bandara Internasional Kertajati (BIJB)',
                'used_p3k' => [1 => '4', 2 => '6', 12 => '4', 15 => '1'],
                'used_em' => [1 => '1', 2 => '1', 15 => '2'],
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(10),
            ],
            [
                'registration_no' => 'ALKES-202610-0002',
                'type' => 'alat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Kertajati (KJT)',
                'nomor_kloter' => 'Kloter 02 KJT',
                'no_kontak' => '081334455662',
                'crew_nama' => 'Senior Cabin Crew Irwan',
                'pelapor_nama' => 'Ns. Siti Fatimah, S.Kep',
                'status' => 'approved',
                'keterangan' => 'Kotak P3K dibuka untuk pemasangan kompres perban pada jemaah yang mengalami cedera ringan.',
                'latitude' => -6.649950,
                'longitude' => 108.196500,
                'lokasi_geotag' => 'Gate 2 Bandara Internasional Kertajati',
                'used_p3k' => [1 => '2', 4 => '1', 14 => '2'],
                'used_em' => [],
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(7),
            ],
            [
                'registration_no' => 'ALKES-202611-0003',
                'type' => 'alat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Bekasi (JKS)',
                'nomor_kloter' => 'Kloter 05 JKS',
                'no_kontak' => '081556677883',
                'crew_nama' => 'Purser Ahmad Zaki',
                'pelapor_nama' => 'dr. Bambang Irawan',
                'status' => 'submitted',
                'keterangan' => 'Penggantian strip penutup luka dan pemakaian masker bedah cadangan.',
                'latitude' => -6.903892,
                'longitude' => 107.618615,
                'lokasi_geotag' => 'Pos Pelayanan Karantina BKK Bandung',
                'used_p3k' => [11 => '1', 12 => '5'],
                'used_em' => [15 => '5'],
                'verified_by' => null,
                'verified_at' => null,
            ],
        ];

        foreach ($sampleAlat as $row) {
            $p3kItems = [];
            foreach ($alatP3kMaster as $idx => $m) {
                $itemIdx = $idx + 1;
                $terpakai = $row['used_p3k'][$itemIdx] ?? '0';
                $p3kItems[] = [
                    'nama' => $m['nama'],
                    'satuan' => $m['satuan'],
                    'isi' => $m['isi'],
                    'terpakai' => $terpakai,
                    'keterangan' => (int)$terpakai > 0 ? 'Terpakai saat penerbangan' : 'Utuh / Tersegel',
                ];
            }

            $emItems = [];
            foreach ($alatEmergensiMaster as $idx => $m) {
                $itemIdx = $idx + 1;
                $terpakai = $row['used_em'][$itemIdx] ?? '0';
                $emItems[] = [
                    'nama' => $m['nama'],
                    'satuan' => $m['satuan'],
                    'isi' => $m['isi'],
                    'terpakai' => $terpakai,
                    'keterangan' => (int)$terpakai > 0 ? 'Digunakan untuk observasi medis' : 'Utuh / Siap Pakai',
                ];
            }

            FlightMedicalInspection::updateOrCreate(
                ['registration_no' => $row['registration_no']],
                [
                    'type' => 'alat',
                    'penerbangan_haji_h' => $row['penerbangan_haji_h'],
                    'penerbangan_haji_m' => $row['penerbangan_haji_m'],
                    'embarkasi' => $row['embarkasi'],
                    'nomor_kloter' => $row['nomor_kloter'],
                    'no_kontak' => $row['no_kontak'],
                    'items_p3k' => $p3kItems,
                    'items_emergensi' => $emItems,
                    'crew_nama' => $row['crew_nama'],
                    'pelapor_nama' => $row['pelapor_nama'],
                    'status' => $row['status'],
                    'keterangan' => $row['keterangan'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'lokasi_geotag' => $row['lokasi_geotag'],
                    'verified_by' => $row['verified_by'],
                    'verified_at' => $row['verified_at'],
                ]
            );
        }

        // Sample Records for OBAT (Obat-obatan)
        $sampleObat = [
            [
                'registration_no' => 'OBAT-202610-0001',
                'type' => 'obat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Kertajati (KJT)',
                'nomor_kloter' => 'Kloter 01 KJT',
                'no_kontak' => '081223344551',
                'crew_nama' => 'Pramugari Santi Wulandari',
                'pelapor_nama' => 'dr. Dian Pratama (TKHI)',
                'status' => 'approved',
                'keterangan' => 'Pemberian analgetik untuk keluhan sakit kepala dan antasida untuk jemaah dengan dispepsia.',
                'latitude' => -6.649987,
                'longitude' => 108.196543,
                'lokasi_geotag' => 'Klinik Port Health Kertajati',
                'used_p3k' => [1 => '8', 4 => '6', 2 => '2'],
                'used_em' => [13 => '1'],
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(10),
            ],
            [
                'registration_no' => 'OBAT-202610-0002',
                'type' => 'obat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Kertajati (KJT)',
                'nomor_kloter' => 'Kloter 02 KJT',
                'no_kontak' => '081334455662',
                'crew_nama' => 'Senior Cabin Crew Irwan',
                'pelapor_nama' => 'Ns. Siti Fatimah, S.Kep',
                'status' => 'approved',
                'keterangan' => 'Pemberian obat antiemetik dan dekongestan hidung karena turbulensi serta flu ringan.',
                'latitude' => -6.649950,
                'longitude' => 108.196500,
                'lokasi_geotag' => 'Terminal Haji BIJB Kertajati',
                'used_p3k' => [2 => '4', 3 => '3', 5 => '2'],
                'used_em' => [],
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(7),
            ],
            [
                'registration_no' => 'OBAT-202611-0003',
                'type' => 'obat',
                'penerbangan_haji_h' => '1447',
                'penerbangan_haji_m' => '2026',
                'embarkasi' => 'Bekasi (JKS)',
                'nomor_kloter' => 'Kloter 08 JKS',
                'no_kontak' => '081889900112',
                'crew_nama' => 'Flight Attendant Maya',
                'pelapor_nama' => 'dr. Tri Nugroho',
                'status' => 'submitted',
                'keterangan' => 'Pemberian bronkodilator inhalasi untuk jemaah asma dalam perjalanan udara.',
                'latitude' => -6.903892,
                'longitude' => 107.618615,
                'lokasi_geotag' => 'Wilker Husein Sastranegara',
                'used_p3k' => [1 => '4', 6 => '2'],
                'used_em' => [8 => '1'],
                'verified_by' => null,
                'verified_at' => null,
            ],
        ];

        foreach ($sampleObat as $row) {
            $p3kItems = [];
            foreach ($obatP3kMaster as $idx => $m) {
                $itemIdx = $idx + 1;
                $terpakai = $row['used_p3k'][$itemIdx] ?? '0';
                $p3kItems[] = [
                    'nama' => $m['nama'],
                    'satuan' => $m['satuan'],
                    'isi' => $m['isi'],
                    'terpakai' => $terpakai,
                    'keterangan' => (int)$terpakai > 0 ? 'Diberikan sesuai resep dokter kloter' : 'Utuh / Tersegel',
                ];
            }

            $emItems = [];
            foreach ($obatEmergensiMaster as $idx => $m) {
                $itemIdx = $idx + 1;
                $terpakai = $row['used_em'][$itemIdx] ?? '0';
                $emItems[] = [
                    'nama' => $m['nama'],
                    'satuan' => $m['satuan'],
                    'isi' => $m['isi'],
                    'terpakai' => $terpakai,
                    'keterangan' => (int)$terpakai > 0 ? 'Penanganan kegawatdaruratan kabin' : 'Utuh / Masa Berlaku Aman',
                ];
            }

            FlightMedicalInspection::updateOrCreate(
                ['registration_no' => $row['registration_no']],
                [
                    'type' => 'obat',
                    'penerbangan_haji_h' => $row['penerbangan_haji_h'],
                    'penerbangan_haji_m' => $row['penerbangan_haji_m'],
                    'embarkasi' => $row['embarkasi'],
                    'nomor_kloter' => $row['nomor_kloter'],
                    'no_kontak' => $row['no_kontak'],
                    'items_p3k' => $p3kItems,
                    'items_emergensi' => $emItems,
                    'crew_nama' => $row['crew_nama'],
                    'pelapor_nama' => $row['pelapor_nama'],
                    'status' => $row['status'],
                    'keterangan' => $row['keterangan'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'lokasi_geotag' => $row['lokasi_geotag'],
                    'verified_by' => $row['verified_by'],
                    'verified_at' => $row['verified_at'],
                ]
            );
        }
    }
}

