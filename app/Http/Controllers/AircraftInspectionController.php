<?php

namespace App\Http\Controllers;

use App\Exports\AircraftInspectionsExport;
use App\Models\AircraftSanitationInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AircraftInspectionController extends Controller
{
    /**
     * Display data management table for aircraft sanitation inspections.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kesimpulan = $request->input('kesimpulan');
        $status = $request->input('status');

        $query = AircraftSanitationInspection::with('verifier')
            ->latest('tanggal')
            ->latest('id');

        if ($search) {
            $query->search($search);
        }

        if ($kesimpulan) {
            $query->filterKesimpulan($kesimpulan);
        }

        if ($status) {
            $query->filterStatus($status);
        }

        $inspections = $query->paginate($request->input('per_page', 10))->withQueryString();

        // Calculate summary counters
        $total = AircraftSanitationInspection::count();
        $layakCount = AircraftSanitationInspection::where('kesimpulan', 'layak')->count();
        $tidakLayakCount = AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count();
        $needActionCount = AircraftSanitationInspection::whereNotNull('tindakan')->count();
        $todayCount = AircraftSanitationInspection::whereDate('tanggal', now()->toDateString())->count();
        $complianceRate = $total > 0 ? round(($layakCount / $total) * 100, 1) : 100;

        return Inertia::render('AircraftInspection/Index', [
            'inspections' => $inspections,
            'filters' => [
                'search' => $search,
                'kesimpulan' => $kesimpulan,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'layak_count' => $layakCount,
                'tidak_layak_count' => $tidakLayakCount,
                'need_action_count' => $needActionCount,
                'today_count' => $todayCount,
                'compliance_rate' => $complianceRate,
            ],
        ]);
    }

    /**
     * Display the interactive and professional inspection analytics dashboard.
     */
    public function dashboard()
    {
        $total = AircraftSanitationInspection::count();
        $layakCount = AircraftSanitationInspection::where('kesimpulan', 'layak')->count();
        $tidakLayakCount = AircraftSanitationInspection::where('kesimpulan', 'tidak_layak')->count();
        $todayCount = AircraftSanitationInspection::whereDate('tanggal', now()->toDateString())->count();
        $complianceRate = $total > 0 ? round(($layakCount / $total) * 100, 1) : 100;

        // 1. 14-Day Trend Chart
        $trendRaw = AircraftSanitationInspection::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN kesimpulan = "layak" THEN 1 ELSE 0 END) as layak'),
                DB::raw('SUM(CASE WHEN kesimpulan = "tidak_layak" THEN 1 ELSE 0 END) as tidak_layak')
            )
            ->where('tanggal', '>=', now()->subDays(13)->toDateString())
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $trendDates = [];
        $trendTotal = [];
        $trendLayak = [];
        $trendTidakLayak = [];

        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $trendDates[] = now()->subDays($i)->format('d M');
            $record = $trendRaw->firstWhere('date', $d);
            $trendTotal[] = $record ? (int)$record->total : 0;
            $trendLayak[] = $record ? (int)$record->layak : 0;
            $trendTidakLayak[] = $record ? (int)$record->tidak_layak : 0;
        }

        // 2. Breakdown per Operator / Airline
        $airlineRaw = AircraftSanitationInspection::select('pemilik', DB::raw('COUNT(*) as count'))
            ->groupBy('pemilik')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        $airlineData = $airlineRaw->map(function ($row) {
            $shortName = str_replace(['PT ', ' (Persero) Tbk', ' Airlines', ' Indonesia'], '', $row->pemilik);
            return [
                'name' => $shortName,
                'fullName' => $row->pemilik,
                'value' => (int)$row->count,
            ];
        });

        // 3. Breakdown Tindakan Penyehatan
        $actionsRaw = AircraftSanitationInspection::whereNotNull('tindakan')
            ->select('tindakan', DB::raw('COUNT(*) as count'))
            ->groupBy('tindakan')
            ->get()
            ->pluck('count', 'tindakan');

        $actionData = [
            'disinseksi' => $actionsRaw['disinseksi'] ?? 0,
            'fumigasi' => $actionsRaw['fumigasi'] ?? 0,
            'disinfeksi' => $actionsRaw['disinfeksi'] ?? 0,
            'general_cleaning' => $actionsRaw['general_cleaning'] ?? 0,
        ];

        // 4. Recent Inspections
        $recent = AircraftSanitationInspection::latest('tanggal')->latest('id')->take(6)->get();

        return Inertia::render('AircraftInspection/Dashboard', [
            'stats' => [
                'total' => $total,
                'layak_count' => $layakCount,
                'tidak_layak_count' => $tidakLayakCount,
                'today_count' => $todayCount,
                'compliance_rate' => $complianceRate,
            ],
            'chart_data' => [
                'trend_dates' => $trendDates,
                'trend_total' => $trendTotal,
                'trend_layak' => $trendLayak,
                'trend_tidak_layak' => $trendTidakLayak,
                'airlines' => $airlineData,
                'actions' => $actionData,
                'ratio' => [
                    ['value' => $layakCount, 'name' => 'Layak Bebas Serangga'],
                    ['value' => $tidakLayakCount, 'name' => 'Tidak Layak (Tindakan)'],
                ],
            ],
            'recent' => $recent,
        ]);
    }

    /**
     * Update inspection status and recommendations.
     */
    public function updateStatus(Request $request, $id)
    {
        $inspection = AircraftSanitationInspection::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:submitted,verified,approved,rejected'],
            'kesimpulan' => ['required', 'string', 'in:layak,tidak_layak'],
            'tindakan' => ['nullable', 'string', 'max:100'],
            'catatan_rekomendasi' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['verified_by'] = auth()->id();
        $validated['verified_at'] = now();

        $inspection->update($validated);

        return redirect()->back()->with('success', "Status inspeksi {$inspection->registration_no} berhasil diperbarui.");
    }

    /**
     * Delete an inspection record.
     */
    public function destroy($id)
    {
        $inspection = AircraftSanitationInspection::findOrFail($id);
        $regNo = $inspection->registration_no;
        $inspection->delete();

        return redirect()->back()->with('success', "Data inspeksi {$regNo} berhasil dihapus.");
    }

    /**
     * Export inspection records to Excel or CSV.
     */
    public function export(Request $request)
    {
        $kesimpulan = $request->query('kesimpulan');
        $status = $request->query('status');
        $format = $request->query('format', 'xlsx');

        $fileName = 'LAPORAN_INSPEKSI_PESAWAT_' . now()->format('Ymd_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        return Excel::download(
            new AircraftInspectionsExport($kesimpulan, $status),
            $fileName,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Download the official PDF certificate for an inspection.
     */
    public function downloadPdf($id)
    {
        $inspection = AircraftSanitationInspection::findOrFail($id);

        $pdf = Pdf::loadView('pdf.aircraft-inspection-pdf', [
            'inspection' => $inspection,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = 'AIRCRAFT_SANITATION_' . str_replace('-', '_', $inspection->registration_no) . '.pdf';

        return $pdf->download($fileName);
    }
}
