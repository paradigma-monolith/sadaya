<?php

namespace App\Http\Controllers;

use App\Exports\FlightMedicalInspectionsExport;
use App\Models\FlightMedicalInspection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FlightMedicalInspectionController extends Controller
{
    /**
     * Display data management table for Alat Kesehatan P3K & Emergensi
     * inside the unified Travel Consent multi-tab hub.
     */
    public function indexAlat(Request $request)
    {
        return app(TravelConsentHubController::class)->alatTab($request);
    }

    /**
     * Display data management table for Obat-obatan P3K & Emergensi
     * inside the unified Travel Consent multi-tab hub.
     */
    public function indexObat(Request $request)
    {
        return app(TravelConsentHubController::class)->obatTab($request);
    }

    /**
     * Update verification status & officer notes.
     */
    public function updateStatus(Request $request, $id)
    {
        $inspection = FlightMedicalInspection::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:submitted,verified,approved,rejected'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['verified_by'] = auth()->id();
        $validated['verified_at'] = now();

        $inspection->update($validated);

        return redirect()->back()->with('success', "Status data pemakaian {$inspection->registration_no} berhasil diperbarui.");
    }

    /**
     * Delete an inspection record.
     */
    public function destroy($id)
    {
        $inspection = FlightMedicalInspection::findOrFail($id);
        $regNo = $inspection->registration_no;
        $inspection->delete();

        return redirect()->back()->with('success', "Data {$regNo} berhasil dihapus.");
    }

    /**
     * Export inspection records to Excel or CSV.
     */
    public function export(Request $request)
    {
        $type = $request->query('type', 'alat');
        $embarkasi = $request->query('embarkasi');
        $status = $request->query('status');
        $format = $request->query('format', 'xlsx');

        $prefix = $type === 'alat' ? 'LAPORAN_ALKES_P3K_' : 'LAPORAN_OBAT_P3K_';
        $fileName = $prefix . now()->format('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(
            new FlightMedicalInspectionsExport($type, $embarkasi, $status),
            $fileName,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }
}

