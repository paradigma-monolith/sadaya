<?php

namespace App\Http\Controllers;

use App\Exports\ShipInspectionsExport;
use App\Models\ShipSanitationInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ShipInspectionController extends Controller
{
    /**
     * Display data management table for ship sanitation inspections (SSCC / SSCEC).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $pelabuhan = $request->input('pelabuhan');
        $sertifikat = $request->input('sertifikat');
        $status = $request->input('status');

        $query = ShipSanitationInspection::with('verifier')
            ->latest('tanggal')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }

        if ($pelabuhan) {
            $query->filterPelabuhan($pelabuhan);
        }

        if ($sertifikat) {
            $query->filterSertifikat($sertifikat);
        }

        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($request->input('per_page', 10))->withQueryString();

        // Calculate KPI summary counters
        $total = ShipSanitationInspection::count();
        $pembebasanCount = ShipSanitationInspection::where('sertifikat_mencatat', 'Pembebasan')->count();
        $pengendalianCount = ShipSanitationInspection::where('sertifikat_mencatat', 'Pengendalian')->count();
        $approvedCount = ShipSanitationInspection::where('status', 'approved')->count();
        $pendingCount = ShipSanitationInspection::whereIn('status', ['submitted', 'verified'])->count();
        $todayCount = ShipSanitationInspection::whereDate('tanggal', now()->toDateString())->count();

        // Available Ports for Filter
        $ports = ShipSanitationInspection::whereNotNull('pelabuhan')
            ->distinct()
            ->pluck('pelabuhan')
            ->toArray();

        if (empty($ports)) {
            $ports = [
                'Pelabuhan Cirebon',
                'Pelabuhan Patimban',
                'Pelabuhan Indramayu',
                'Pelabuhan Pelabuhan Ratu',
            ];
        }

        return Inertia::render('ShipInspection/Index', [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'pelabuhan' => $pelabuhan,
                'sertifikat' => $sertifikat,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'pembebasan_count' => $pembebasanCount,
                'pengendalian_count' => $pengendalianCount,
                'approved_count' => $approvedCount,
                'pending_count' => $pendingCount,
                'today_count' => $todayCount,
            ],
            'ports' => $ports,
        ]);
    }

    /**
     * Update inspection verification status and issuing officer.
     */
    public function updateStatus(Request $request, $id)
    {
        $inspection = ShipSanitationInspection::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:submitted,verified,approved,rejected'],
            'sertifikat_mencatat' => ['required', 'string', 'in:Pembebasan,Pengendalian'],
            'pejabat_penerbit' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['verified_by'] = auth()->id();
        $validated['verified_at'] = now();

        $inspection->update($validated);

        return redirect()->back()->with('success', "Status sertifikat kapal {$inspection->nama_kapal} ({$inspection->registration_no}) berhasil diperbarui.");
    }

    /**
     * Delete an inspection record.
     */
    public function destroy($id)
    {
        $inspection = ShipSanitationInspection::findOrFail($id);
        $regNo = $inspection->registration_no;
        $namaKapal = $inspection->nama_kapal;
        $inspection->delete();

        return redirect()->back()->with('success', "Data inspeksi kapal {$namaKapal} ({$regNo}) berhasil dihapus.");
    }

    /**
     * Export inspection records to Excel or CSV.
     */
    public function export(Request $request)
    {
        $pelabuhan = $request->query('pelabuhan');
        $sertifikat = $request->query('sertifikat');
        $status = $request->query('status');
        $format = $request->query('format', 'xlsx');

        $fileName = 'LAPORAN_INSPEKSI_KAPAL_' . now()->format('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(
            new ShipInspectionsExport($pelabuhan, $sertifikat, $status),
            $fileName,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Download the official PDF receipt/certificate for a ship inspection.
     */
    public function downloadPdf($id)
    {
        $inspection = ShipSanitationInspection::findOrFail($id);

        return redirect()->route('travelconsent.receipt_sanitasi', ['registration_no' => $inspection->registration_no]);
    }
}

