<?php

namespace App\Http\Controllers;

use App\Exports\FlightMedicalInspectionsExport;
use App\Models\FlightMedicalInspection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class FlightMedicalInspectionController extends Controller
{
    /**
     * Display data management table for Alat Kesehatan P3K & Emergensi.
     */
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');
        $embarkasi = $request->input('embarkasi');
        $status = $request->input('status');

        $query = FlightMedicalInspection::alat()
            ->with('verifier')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }

        if ($embarkasi) {
            $query->filterEmbarkasi($embarkasi);
        }

        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($request->input('per_page', 10))->withQueryString();

        // Calculate statistics
        $allAlat = FlightMedicalInspection::alat()->get();
        $total = $allAlat->count();
        $approvedCount = $allAlat->where('status', 'approved')->count();
        $submittedCount = $allAlat->where('status', 'submitted')->count();
        $distinctKloter = $allAlat->pluck('nomor_kloter')->unique()->count();

        $totalItemsUsed = 0;
        foreach ($allAlat as $row) {
            if (is_array($row->items_p3k)) {
                foreach ($row->items_p3k as $item) {
                    $totalItemsUsed += (int)($item['terpakai'] ?? 0);
                }
            }
            if (is_array($row->items_emergensi)) {
                foreach ($row->items_emergensi as $item) {
                    $totalItemsUsed += (int)($item['terpakai'] ?? 0);
                }
            }
        }

        $embarkasiList = FlightMedicalInspection::alat()
            ->distinct()
            ->pluck('embarkasi')
            ->toArray();

        return Inertia::render('FlightMedicalInspection/IndexAlat', [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'embarkasi' => $embarkasi,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'approved_count' => $approvedCount,
                'submitted_count' => $submittedCount,
                'total_kloter' => $distinctKloter,
                'total_items_used' => $totalItemsUsed,
            ],
            'embarkasiList' => $embarkasiList,
        ]);
    }

    /**
     * Display data management table for Obat-obatan P3K & Emergensi.
     */
    public function indexObat(Request $request)
    {
        $search = $request->input('search');
        $embarkasi = $request->input('embarkasi');
        $status = $request->input('status');

        $query = FlightMedicalInspection::obat()
            ->with('verifier')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }

        if ($embarkasi) {
            $query->filterEmbarkasi($embarkasi);
        }

        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($request->input('per_page', 10))->withQueryString();

        // Calculate statistics
        $allObat = FlightMedicalInspection::obat()->get();
        $total = $allObat->count();
        $approvedCount = $allObat->where('status', 'approved')->count();
        $submittedCount = $allObat->where('status', 'submitted')->count();
        $distinctKloter = $allObat->pluck('nomor_kloter')->unique()->count();

        $totalItemsUsed = 0;
        foreach ($allObat as $row) {
            if (is_array($row->items_p3k)) {
                foreach ($row->items_p3k as $item) {
                    $totalItemsUsed += (int)($item['terpakai'] ?? 0);
                }
            }
            if (is_array($row->items_emergensi)) {
                foreach ($row->items_emergensi as $item) {
                    $totalItemsUsed += (int)($item['terpakai'] ?? 0);
                }
            }
        }

        $embarkasiList = FlightMedicalInspection::obat()
            ->distinct()
            ->pluck('embarkasi')
            ->toArray();

        return Inertia::render('FlightMedicalInspection/IndexObat', [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'embarkasi' => $embarkasi,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'approved_count' => $approvedCount,
                'submitted_count' => $submittedCount,
                'total_kloter' => $distinctKloter,
                'total_items_used' => $totalItemsUsed,
            ],
            'embarkasiList' => $embarkasiList,
        ]);
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

