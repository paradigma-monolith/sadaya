<?php

namespace App\Http\Controllers;

use App\Models\AircraftSanitationInspection;
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
}
