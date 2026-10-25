<?php

namespace App\Http\Controllers;

use App\Models\AircraftSanitationInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        ]);

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
}
