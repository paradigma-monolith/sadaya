<?php

namespace App\Http\Controllers;

use App\Models\AircraftSanitationInspection;
use App\Models\ShipSanitationInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TravelConsentController extends Controller
{
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspeksi sanitasi pesawat berhasil disimpan.',
                'data' => $inspection,
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

        return $pdf->download($fileName);
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

        $standardAreas = [
            ['no' => 1, 'nama' => 'Dapur', 'kategori' => 'Pangan & Dapur'],
            ['no' => 2, 'nama' => 'Ruang rakit makanan', 'kategori' => 'Pangan & Dapur'],
            ['no' => 3, 'nama' => 'Gudang', 'kategori' => 'Pangan & Dapur'],
            ['no' => 4, 'nama' => 'Palka', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 5, 'nama' => 'Ruang tidur', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 6, 'nama' => 'ABK', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 7, 'nama' => 'Perwira', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 8, 'nama' => 'Penumpang', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 9, 'nama' => 'Geladak', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 10, 'nama' => 'Air bersih', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 11, 'nama' => 'Limbah cair', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 12, 'nama' => 'Tangki air Ballast', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 13, 'nama' => 'Sampah medik dan sampah padat', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 14, 'nama' => 'Air permukaan', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 15, 'nama' => 'Kamar mesin', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 16, 'nama' => 'Fasilitas medik', 'kategori' => 'Medik & Keselamatan'],
            ['no' => 17, 'nama' => 'Area lainnya', 'kategori' => 'Lainnya'],
            ['no' => 18, 'nama' => 'Sumber Makanan', 'kategori' => 'Pangan & Dapur'],
            ['no' => 19, 'nama' => 'Penyimpanan Makanan', 'kategori' => 'Pangan & Dapur'],
            ['no' => 20, 'nama' => 'Penyiapan Makanan', 'kategori' => 'Pangan & Dapur'],
            ['no' => 21, 'nama' => 'Penyajian Makanan', 'kategori' => 'Pangan & Dapur'],
            ['no' => 22, 'nama' => 'Sumber Air', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 23, 'nama' => 'Penyimpanan Air', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 24, 'nama' => 'Distribusi Air', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 25, 'nama' => 'Penanganan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 26, 'nama' => 'Perlakuan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 27, 'nama' => 'Pembuangan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
            ['no' => 28, 'nama' => 'Pengoperasian Kolam Renang', 'kategori' => 'Akomodasi & Palka'],
            ['no' => 29, 'nama' => 'Perlengkapan dan alat-alat medis', 'kategori' => 'Medik & Keselamatan'],
            ['no' => 30, 'nama' => 'Pengoperasian Fasilitas Medik', 'kategori' => 'Medik & Keselamatan'],
            ['no' => 31, 'nama' => 'Obat-obatan', 'kategori' => 'Medik & Keselamatan'],
            ['no' => 32, 'nama' => 'Bagian lainnya yang diperiksa', 'kategori' => 'Lainnya'],
        ];

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
            'foto_geotagging' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:12288'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'lokasi_geotag' => ['nullable', 'string', 'max:255'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_geotagging')) {
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
}
