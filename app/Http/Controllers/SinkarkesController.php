<?php

namespace App\Http\Controllers;

use App\Exports\SinkarkesExport;
use App\Models\KantorWilker;
use App\Models\SinkarkesReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SinkarkesController extends Controller
{
    /**
     * Display the Sinkarkes Form page.
     */
    public function index(Request $request): Response
    {
        $defaultWilker = 'BANDARA HUSEIN';
        $defaultYear = 2026;
        $defaultMonth = (int) date('n');

        $wilker = $request->query('wilker', $defaultWilker);
        $year = (int) $request->query('year', $defaultYear);
        $month = (int) $request->query('month', $defaultMonth);
        if ($month < 1 || $month > 12) {
            $month = 1;
        }

        // Available Wilayah Kerja options
        $wilkerOptions = [
            'BANDARA HUSEIN',
            'WILKER KERTAJATI',
            'WILKER CIREBON',
            'WILKER PATIMBAN',
            'WILKER INDRAMAYU',
            'WILKER PELABUHARATU',
            'INDUK BKK BANDUNG',
        ];

        try {
            $dbWilkers = KantorWilker::where('is_active', true)->pluck('nama_pendek')->toArray();
            if (!empty($dbWilkers)) {
                $wilkerOptions = array_values(array_unique(array_merge($wilkerOptions, $dbWilkers)));
            }
        } catch (\Throwable $e) {
            // Fallback to static list
        }

        // Load all 12 reports for this Wilker and Year
        $reports = SinkarkesReport::where('wilker', $wilker)
            ->where('year', $year)
            ->get()
            ->keyBy('month');

        $monthlyData = [];
        $monthlyStatus = [];
        for ($m = 1; $m <= 12; $m++) {
            $report = $reports->get($m);
            $monthlyData[$m] = $report ? ($report->data ?? []) : [];
            $monthlyStatus[$m] = [
                'has_data' => !empty($report?->data),
                'filled_count' => count(array_filter($report?->data ?? [], fn($v) => $v !== null && $v !== '')),
                'status' => $report?->status ?? 'empty',
                'updated_at' => $report?->updated_at?->toIso8601String(),
                'updater' => $report?->updater?->name ?? null,
            ];
        }

        return Inertia::render('Sinkarkes/Index', [
            'wilker' => $wilker,
            'year' => $year,
            'month' => $month,
            'wilkerOptions' => $wilkerOptions,
            'monthlyData' => $monthlyData,
            'monthlyStatus' => $monthlyStatus,
        ]);
    }

    /**
     * Save / Update a Sinkarkes monthly report.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'data' => 'required|array',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,submitted,verified',
        ]);

        $report = SinkarkesReport::updateOrCreate(
            [
                'wilker' => $validated['wilker'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'data' => $validated['data'],
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? 'submitted',
                'updated_by' => auth()->id(),
            ]
        );

        if (!$report->created_by) {
            $report->update(['created_by' => auth()->id()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data SINKARKES bulan ' . $validated['month'] . ' tahun ' . $validated['year'] . ' berhasil disimpan.',
                'report' => $report->load(['updater:id,name']),
            ]);
        }

        return back()->with('success', 'Data laporan SINKARKES berhasil disimpan.');
    }

    /**
     * Export reports to Excel spreadsheet.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $wilker = $request->query('wilker', 'BANDARA HUSEIN');
        $year = (int) $request->query('year', 2026);
        $month = $request->has('month') && $request->query('month') ? (int) $request->query('month') : null;

        $safeWilker = preg_replace('/[^a-zA-Z0-9_-]/', '_', $wilker);
        $filename = "SINKARKES_{$safeWilker}_{$year}" . ($month ? "_M{$month}" : '') . ".xlsx";

        return Excel::download(new SinkarkesExport($wilker, $year, $month), $filename);
    }
}
