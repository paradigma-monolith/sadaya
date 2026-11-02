<?php

namespace App\Http\Controllers;

use App\Models\AircraftSanitationInspection;
use App\Models\FlightMedicalInspection;
use App\Models\ShipSanitationInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TravelConsentController extends Controller
{
    /**
     * Show public landing page / home for travel consent & inspection services.
     */
    public function publicHome(Request $request)
    {
        return view('travelconsent.home');
    }

    /**
     * Show the public aircraft sanitation inspection form.
     */
    public function publicAircraft(Request $request)
    {
        return view('travelconsent.public_aircraft', [
            'today' => now()->format('Y-m-d'),
            'nowTime' => now()->format('H:i'),
        ]);
    }

    /**
     * Handle public aircraft inspection submission.
     */
    public function submitAircraft(Request $request)
    {
        $validated = $request->validate([
            'pemilik' => ['required', 'string', 'max:255'],
            'tanda_kebangsaan' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['required', 'string', 'max:20'],
            'kabin_penumpang' => ['nullable', 'array'],
            'kompartemen_toilet' => ['nullable', 'array'],
            'galley' => ['nullable', 'array'],
            'dokumen_kesehatan' => ['nullable', 'array'],
            'pembuangan_kotoran' => ['required', 'string', 'in:ya,tidak'],
            'penanganan_air' => ['nullable', 'array'],
            'keadaan_makanan' => ['nullable', 'array'],
            'binatang_pengganggu' => ['nullable', 'array'],
            'surat_sanitasi' => ['required', 'string', 'in:ada,tidak_ada'],
            'kesimpulan' => ['required', 'string', 'in:layak,tidak_layak'],
            'tindakan' => ['nullable', 'string', 'max:100'],
            'catatan_rekomendasi' => ['nullable', 'string', 'max:1000'],
            'petugas_kabin_nama' => ['required', 'string', 'max:255'],
            'petugas_kabin_ttd' => ['nullable', 'string'],
            'petugas_bkk_nama' => ['required', 'string', 'max:255'],
            'petugas_bkk_ttd' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'lokasi_geotag' => ['nullable', 'string', 'max:255'],
            'foto_dokumentasi' => ['nullable', 'array'],
            'foto_dokumentasi.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:12288'],
            'foto_kategori' => ['nullable', 'array'],
            'foto_latitudes' => ['nullable', 'array'],
            'foto_longitudes' => ['nullable', 'array'],
            'foto_accuracies' => ['nullable', 'array'],
        ]);

        // Process geotagged photo uploads
        $uploadedPhotos = [];
        if ($request->hasFile('foto_dokumentasi')) {
            $categories = $request->input('foto_kategori', []);
            $photoLats = $request->input('foto_latitudes', []);
            $photoLngs = $request->input('foto_longitudes', []);
            $photoAccuracies = $request->input('foto_accuracies', []);

            foreach ($request->file('foto_dokumentasi') as $index => $file) {
                if ($file && $file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $cleanName = 'inspection_' . date('Ymd_His') . '_' . Str::random(8) . '.' . strtolower($ext);
                    $path = $file->storeAs('aircraft_inspections', $cleanName, 'public');

                    $pLat = $photoLats[$index] ?? $validated['latitude'] ?? null;
                    $pLng = $photoLngs[$index] ?? $validated['longitude'] ?? null;
                    $pAcc = $photoAccuracies[$index] ?? null;

                    // Extract EXIF GPS coordinates if not provided from browser GPS
                    if ((empty($pLat) || empty($pLng)) && function_exists('exif_read_data')) {
                        try {
                            $exif = @exif_read_data($file->getRealPath());
                            if ($exif && isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
                                $extracted = $this->extractGpsFromExif($exif);
                                if ($extracted) {
                                    $pLat = $pLat ?: $extracted['latitude'];
                                    $pLng = $pLng ?: $extracted['longitude'];
                                }
                            }
                        } catch (\Throwable $e) {
                            // ignore exif parse error
                        }
                    }

                    $uploadedPhotos[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'category' => $categories[$index] ?? 'Dokumentasi Lapangan',
                        'latitude' => $pLat !== null && $pLat !== '' ? (float) $pLat : null,
                        'longitude' => $pLng !== null && $pLng !== '' ? (float) $pLng : null,
                        'accuracy' => $pAcc !== null && $pAcc !== '' ? (float) $pAcc : null,
                        'captured_at' => now()->toIso8601String(),
                    ];

                    if (empty($validated['latitude']) && $pLat) {
                        $validated['latitude'] = (float) $pLat;
                    }
                    if (empty($validated['longitude']) && $pLng) {
                        $validated['longitude'] = (float) $pLng;
                    }
                }
            }
        }

        $validated['foto_dokumentasi'] = !empty($uploadedPhotos) ? $uploadedPhotos : null;

        $inspection = AircraftSanitationInspection::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspeksi sanitasi pesawat berhasil disimpan.',
                'registration_no' => $inspection->registration_no,
                'receipt_url' => route('travelconsent.public_pdf', $inspection->registration_no),
                'pdf_url' => route('travelconsent.public_pdf', $inspection->registration_no),
            ]);
        }

        return redirect()->route('travelconsent.receipt', $inspection->registration_no)
            ->with('success', 'Formulir Aircraft Sanitation Inspection berhasil disubmit secara resmi.');
    }

    /**
     * Show submission receipt with tracking code.
     */
    public function receipt(string $registration_no)
    {
        $inspection = AircraftSanitationInspection::where('registration_no', $registration_no)->firstOrFail();

        return view('travelconsent.receipt', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Download inspection PDF certificate for public/crew.
     */
    public function downloadPdf(string $registration_no)
    {
        $inspection = AircraftSanitationInspection::where('registration_no', $registration_no)->firstOrFail();

        $pdf = Pdf::loadView('pdf.aircraft-inspection-pdf', [
            'inspection' => $inspection,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = 'AIRCRAFT_SANITATION_' . str_replace('-', '_', $inspection->registration_no) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Extract decimal latitude and longitude from EXIF data if present.
     */
    protected function extractGpsFromExif(array $exif): ?array
    {
        if (empty($exif['GPSLatitude']) || empty($exif['GPSLongitude']) || empty($exif['GPSLatitudeRef']) || empty($exif['GPSLongitudeRef'])) {
            return null;
        }

        $parseCoord = function ($coord) {
            if (is_numeric($coord)) return (float) $coord;
            if (is_string($coord) && str_contains($coord, '/')) {
                $parts = explode('/', $coord);
                if (count($parts) === 2 && (float) $parts[1] != 0) {
                    return (float) $parts[0] / (float) $parts[1];
                }
            }
            return 0;
        };

        $calc = function ($coordinate, $hemisphere) use ($parseCoord) {
            if (!is_array($coordinate) || count($coordinate) < 3) return null;
            $degrees = $parseCoord($coordinate[0]);
            $minutes = $parseCoord($coordinate[1]);
            $seconds = $parseCoord($coordinate[2]);
            $flip = (strtoupper($hemisphere) === 'S' || strtoupper($hemisphere) === 'W') ? -1 : 1;
            return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
        };

        $lat = $calc($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
        $lng = $calc($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

        if ($lat !== null && $lng !== null) {
            return [
                'latitude' => round($lat, 7),
                'longitude' => round($lng, 7),
            ];
        }

        return null;
    }

    /**
     * Show the public ship sanitation inspection form (SSCC / SSCEC).
     */
    public function publicSanitasi(Request $request)
    {
        $ports = [
            'Pelabuhan Cirebon',
            'Pelabuhan Patimban',
            'Pelabuhan Indramayu',
            'Pelabuhan Pelabuharatu',
            'Pelabuhan Sunda Kelapa',
            'Pelabuhan Tanjung Priok',
            'Pelabuhan Banten (Merak)',
            'Pelabuhan Lainnya',
        ];

        $countries = [
            'Indonesia', 'Singapura', 'Malaysia', 'Panama', 'Liberia', 'Kepulauan Marshall',
            'Hong Kong', 'Tiongkok', 'Jepang', 'Korea Selatan', 'Filipina', 'Thailand',
            'Vietnam', 'India', 'Australia', 'Amerika Serikat', 'Britania Raya', 'Belanda',
            'Jerman', 'Norwegia', 'Yunani', 'Siprus', 'Malta', 'Bahama', 'Arab Saudi',
            'Uni Emirat Arab', 'Lainnya'
        ];

        $standardAreas = ShipSanitationInspection::STANDARD_AREAS;

        return view('travelconsent.public_sanitasi', [
            'today' => now()->format('Y-m-d'),
            'ports' => $ports,
            'countries' => $countries,
            'standardAreas' => $standardAreas,
        ]);
    }

    /**
     * Handle public ship sanitation inspection submission.
     */
    public function submitSanitasi(Request $request)
    {
        $validated = $request->validate([
            'pelabuhan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'sertifikat_mencatat' => ['required', 'string', 'in:Pembebasan,Pengendalian'],
            'imo_no' => ['required', 'string', 'max:100'],
            'jenis_kapal' => ['required', 'string', 'max:100'],
            'detail_jenis_kapal' => ['required', 'string', 'max:100'],
            'nama_kapal' => ['required', 'string', 'max:255'],
            'bendera' => ['nullable', 'string', 'max:100'],
            'berat_gt' => ['required', 'numeric', 'min:0'],
            'kondisi_palka' => ['required', 'string', 'max:100'],
            'nama_pemeriksa' => ['required', 'string', 'max:255'],
            'alamat_pemeriksa' => ['required', 'string', 'max:1000'],
            'areas' => ['required', 'array', 'min:1'],
            'pejabat_penerbit' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['required', 'string', 'max:2000'],
            'tanda_tangan' => ['required', 'string'],
            'foto_dokumentasi' => ['nullable', 'array'],
            'foto_dokumentasi.*' => ['nullable', 'file', 'image', 'max:12288'],
            'foto_geotagging' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:12288'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'lokasi_geotag' => ['nullable', 'string', 'max:255'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $photos = $request->file('foto_dokumentasi');
            if (is_array($photos) && isset($photos[0])) {
                $photoPath = $photos[0]->store('inspections/ships', 'public');
            } elseif ($photos instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $photos->store('inspections/ships', 'public');
            }
        } elseif ($request->hasFile('foto_geotagging')) {
            $photoPath = $request->file('foto_geotagging')->store('inspections/ships', 'public');
        }

        $regNo = 'SSCC-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $inspection = ShipSanitationInspection::create([
            'registration_no' => $regNo,
            'pelabuhan' => $validated['pelabuhan'],
            'tanggal' => $validated['tanggal'],
            'sertifikat_mencatat' => $validated['sertifikat_mencatat'],
            'imo_no' => $validated['imo_no'],
            'jenis_kapal' => $validated['jenis_kapal'],
            'detail_jenis_kapal' => $validated['detail_jenis_kapal'],
            'nama_kapal' => $validated['nama_kapal'],
            'bendera' => $validated['bendera'] ?? 'Indonesia',
            'berat_gt' => (int) $validated['berat_gt'],
            'kondisi_palka' => $validated['kondisi_palka'],
            'nama_pemeriksa' => $validated['nama_pemeriksa'],
            'alamat_pemeriksa' => $validated['alamat_pemeriksa'],
            'areas' => $validated['areas'],
            'pejabat_penerbit' => $validated['pejabat_penerbit'] ?: 'Balai Kekarantinaan Kesehatan Kelas I Bandung',
            'keterangan' => $validated['keterangan'],
            'tanda_tangan' => $validated['tanda_tangan'],
            'foto_geotagging' => $photoPath,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'lokasi_geotag' => $validated['lokasi_geotag'] ?? null,
            'status' => 'submitted',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Formulir Sertifikat Pengendalian Sanitasi Kapal berhasil disubmit.',
                'registration_no' => $inspection->registration_no,
                'receipt_url' => route('travelconsent.receipt_sanitasi', $inspection->registration_no),
            ]);
        }

        return redirect()->route('travelconsent.receipt_sanitasi', $inspection->registration_no)
            ->with('success', 'Formulir Sertifikat Pengendalian Sanitasi Kapal berhasil disubmit.');
    }

    /**
     * Show ship sanitation submission receipt.
     */
    public function receiptSanitasi(string $registration_no)
    {
        $inspection = ShipSanitationInspection::where('registration_no', $registration_no)->firstOrFail();

        return view('travelconsent.receipt_sanitasi', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Show public form for Pemakaian Alat Kesehatan Kotak P3K & Emergensi.
     */
    public function publicAlat()
    {
        $p3kItems = [
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

        $emergensiItems = [
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

        return view('travelconsent.public_alat', [
            'p3kItems' => $p3kItems,
            'emergensiItems' => $emergensiItems,
            'currentH' => '1447',
            'currentM' => date('Y'),
        ]);
    }

    /**
     * Submit public form for Pemakaian Alat Kesehatan.
     */
    public function submitAlat(Request $request)
    {
        $validated = $request->validate([
            'penerbangan_haji_h' => ['nullable', 'string', 'max:20'],
            'penerbangan_haji_m' => ['nullable', 'string', 'max:20'],
            'embarkasi' => ['required', 'string', 'max:100'],
            'nomor_kloter' => ['required', 'string', 'max:100'],
            'no_kontak' => ['nullable', 'string', 'max:50'],
            'items_p3k' => ['required', 'array'],
            'items_emergensi' => ['required', 'array'],
            'crew_nama' => ['required', 'string', 'max:255'],
            'crew_ttd' => ['nullable', 'string'],
            'pelapor_nama' => ['required', 'string', 'max:255'],
            'pelapor_ttd' => ['nullable', 'string'],
            'foto_dokumentasi' => ['nullable', 'array'],
            'foto_dokumentasi.*' => ['nullable', 'file', 'image', 'max:12288'],
            'foto_geotagging' => ['nullable', 'image', 'max:10240'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'lokasi_geotag' => ['nullable', 'string', 'max:255'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $photos = $request->file('foto_dokumentasi');
            if (is_array($photos) && isset($photos[0])) {
                $photoPath = $photos[0]->store('medical_kits/alat', 'public');
            } elseif ($photos instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $photos->store('medical_kits/alat', 'public');
            }
        } elseif ($request->hasFile('foto_geotagging')) {
            $photoPath = $request->file('foto_geotagging')->store('medical_kits/alat', 'public');
        }

        $regNo = 'ALKES-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $inspection = FlightMedicalInspection::create([
            'registration_no' => $regNo,
            'type' => 'alat',
            'penerbangan_haji_h' => $validated['penerbangan_haji_h'],
            'penerbangan_haji_m' => $validated['penerbangan_haji_m'],
            'embarkasi' => $validated['embarkasi'],
            'nomor_kloter' => $validated['nomor_kloter'],
            'no_kontak' => $validated['no_kontak'],
            'items_p3k' => $validated['items_p3k'],
            'items_emergensi' => $validated['items_emergensi'],
            'crew_nama' => $validated['crew_nama'],
            'crew_ttd' => $validated['crew_ttd'],
            'pelapor_nama' => $validated['pelapor_nama'],
            'pelapor_ttd' => $validated['pelapor_ttd'],
            'foto_geotagging' => $photoPath,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'lokasi_geotag' => $validated['lokasi_geotag'] ?? null,
            'status' => 'submitted',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar pemakaian alat kesehatan kotak P3K & emergensi berhasil disubmit.',
                'registration_no' => $inspection->registration_no,
                'receipt_url' => route('travelconsent.receipt_alat', $inspection->registration_no),
            ]);
        }

        return redirect()->route('travelconsent.receipt_alat', $inspection->registration_no)
            ->with('success', 'Daftar pemakaian alat kesehatan kotak P3K & emergensi berhasil disubmit.');
    }

    /**
     * Show receipt for Alat submission.
     */
    public function receiptAlat(string $registration_no)
    {
        $inspection = FlightMedicalInspection::where('registration_no', $registration_no)
            ->where('type', 'alat')
            ->firstOrFail();

        return view('travelconsent.receipt_alat', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Show public form for Pemakaian Obat Kotak P3K & Emergensi.
     */
    public function publicObat()
    {
        $p3kItems = [
            ['nama' => 'Mild to moderate analgesic', 'satuan' => 'Tablet', 'isi' => '30'],
            ['nama' => 'Antiemetic', 'satuan' => 'Ampul / Tablet', 'isi' => '12'],
            ['nama' => 'Nasal decongestant', 'satuan' => 'Tablet / Spray', 'isi' => '10'],
            ['nama' => 'Antacid (gastric)', 'satuan' => 'Tablet', 'isi' => '30'],
            ['nama' => 'Antidiarrheal', 'satuan' => 'Tablet', 'isi' => '20'],
            ['nama' => 'Antihistamine', 'satuan' => 'Tablet', 'isi' => '20'],
        ];

        $emergensiItems = [
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

        return view('travelconsent.public_obat', [
            'p3kItems' => $p3kItems,
            'emergensiItems' => $emergensiItems,
            'currentH' => '1447',
            'currentM' => date('Y'),
        ]);
    }

    /**
     * Submit public form for Pemakaian Obat.
     */
    public function submitObat(Request $request)
    {
        $validated = $request->validate([
            'penerbangan_haji_h' => ['nullable', 'string', 'max:20'],
            'penerbangan_haji_m' => ['nullable', 'string', 'max:20'],
            'embarkasi' => ['required', 'string', 'max:100'],
            'nomor_kloter' => ['required', 'string', 'max:100'],
            'no_kontak' => ['nullable', 'string', 'max:50'],
            'items_p3k' => ['required', 'array'],
            'items_emergensi' => ['required', 'array'],
            'crew_nama' => ['required', 'string', 'max:255'],
            'crew_ttd' => ['nullable', 'string'],
            'pelapor_nama' => ['required', 'string', 'max:255'],
            'pelapor_ttd' => ['nullable', 'string'],
            'foto_dokumentasi' => ['nullable', 'array'],
            'foto_dokumentasi.*' => ['nullable', 'file', 'image', 'max:12288'],
            'foto_geotagging' => ['nullable', 'image', 'max:10240'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'lokasi_geotag' => ['nullable', 'string', 'max:255'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $photos = $request->file('foto_dokumentasi');
            if (is_array($photos) && isset($photos[0])) {
                $photoPath = $photos[0]->store('medical_kits/obat', 'public');
            } elseif ($photos instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $photos->store('medical_kits/obat', 'public');
            }
        } elseif ($request->hasFile('foto_geotagging')) {
            $photoPath = $request->file('foto_geotagging')->store('medical_kits/obat', 'public');
        }

        $regNo = 'OBAT-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $inspection = FlightMedicalInspection::create([
            'registration_no' => $regNo,
            'type' => 'obat',
            'penerbangan_haji_h' => $validated['penerbangan_haji_h'],
            'penerbangan_haji_m' => $validated['penerbangan_haji_m'],
            'embarkasi' => $validated['embarkasi'],
            'nomor_kloter' => $validated['nomor_kloter'],
            'no_kontak' => $validated['no_kontak'],
            'items_p3k' => $validated['items_p3k'],
            'items_emergensi' => $validated['items_emergensi'],
            'crew_nama' => $validated['crew_nama'],
            'crew_ttd' => $validated['crew_ttd'],
            'pelapor_nama' => $validated['pelapor_nama'],
            'pelapor_ttd' => $validated['pelapor_ttd'],
            'foto_geotagging' => $photoPath,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'lokasi_geotag' => $validated['lokasi_geotag'] ?? null,
            'status' => 'submitted',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar pemakaian obat kotak P3K & emergensi berhasil disubmit.',
                'registration_no' => $inspection->registration_no,
                'receipt_url' => route('travelconsent.receipt_obat', $inspection->registration_no),
            ]);
        }

        return redirect()->route('travelconsent.receipt_obat', $inspection->registration_no)
            ->with('success', 'Daftar pemakaian obat kotak P3K & emergensi berhasil disubmit.');
    }

    /**
     * Show receipt for Obat submission.
     */
    public function receiptObat(string $registration_no)
    {
        $inspection = FlightMedicalInspection::where('registration_no', $registration_no)
            ->where('type', 'obat')
            ->firstOrFail();

        return view('travelconsent.receipt_obat', [
            'inspection' => $inspection,
        ]);
    }
}
