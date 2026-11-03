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
        // Available 7 official Wilayah Kerja options from KantorWilker table
        try {
            $dbWilkers = KantorWilker::where('is_active', true)->orderBy('no')->pluck('nama_pendek')->toArray();
        } catch (\Throwable $e) {
            $dbWilkers = [];
        }

        if (!empty($dbWilkers)) {
            $wilkerOptions = array_values($dbWilkers);
        } else {
            $wilkerOptions = [
                'Induk BKK Bandung',
                'POS Bandara Husen',
                'Wilker Cirebon',
                'Wilker Indramayu',
                'Wilker Kertajati',
                'Wilker Patimban',
                'Wilker Pelabuharatu',
            ];
        }

        $defaultWilker = $wilkerOptions[0] ?? 'Induk BKK Bandung';
        $defaultYear = 2026;
        $defaultMonth = (int) date('n');

        $requestedWilker = $request->query('wilker');
        if ($requestedWilker) {
            // Case-insensitive match & alias matching (e.g. BANDARA HUSEIN -> POS Bandara Husen)
            $matched = collect($wilkerOptions)->first(function ($opt) use ($requestedWilker) {
                if (strcasecmp($opt, $requestedWilker) === 0) return true;
                if ((stripos($opt, 'Husen') !== false || stripos($opt, 'Husein') !== false) &&
                    (stripos($requestedWilker, 'Husen') !== false || stripos($requestedWilker, 'Husein') !== false)) {
                    return true;
                }
                return false;
            });
            $wilker = $matched ?? $requestedWilker;
        } else {
            $wilker = $defaultWilker;
        }

        $year = (int) $request->query('year', $defaultYear);
        $month = (int) $request->query('month', $defaultMonth);
        if ($month < 1 || $month > 12) {
            $month = 1;
        }

        // Load all 12 reports for this Wilker and Year (with alias fallback)
        $reports = SinkarkesReport::where(function ($q) use ($wilker) {
                $q->where('wilker', $wilker);
                if (stripos($wilker, 'Husen') !== false || stripos($wilker, 'Husein') !== false) {
                    $q->orWhere('wilker', 'BANDARA HUSEIN')
                      ->orWhere('wilker', 'Bandara Husein')
                      ->orWhere('wilker', 'POS Bandara Husen');
                }
            })
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

