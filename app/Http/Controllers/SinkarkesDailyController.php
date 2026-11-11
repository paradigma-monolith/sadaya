<?php

namespace App\Http\Controllers;

use App\Models\SinkarkesDailyReport;
use App\Services\SinkarkesDailySchemaService;
use App\Services\SinkarkesDailySyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SinkarkesDailyController extends Controller
{
    public function __construct(
        protected SinkarkesDailySchemaService $schemaService,
        protected SinkarkesDailySyncService $syncService
    ) {
    }

    /**
     * Render Rekap Data Harian Kapal & Pesawat (Multi-Tab + Akumulasi SINKARKES).
     */
    public function index(Request $request): Response
    {
        $wilkerOptions = $this->schemaService->getWilkerOptions();
        $defaultWilker = $wilkerOptions[0] ?? 'Induk BKK Bandung';

        $requestedWilker = $request->query('wilker');
        $wilker = $requestedWilker
            ? $this->schemaService->canonicalizeWilker($requestedWilker, $wilkerOptions)
            : $defaultWilker;

        $year = max(2020, min(2050, (int) $request->query('year', (int) date('Y'))));
        $month = (int) $request->query('month', (int) date('n'));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $activeTab = $request->query('tab', 'kapal');
        if (!in_array($activeTab, ['kapal', 'pesawat', 'akumulasi'], true)) {
            $activeTab = 'kapal';
        }

        $variants = $this->schemaService->getWilkerQueryVariants($wilker);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $allDaily = SinkarkesDailyReport::with(['updater:id,name', 'creator:id,name'])
            ->whereIn('wilker', $variants)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->get();

        $transformRecord = function (SinkarkesDailyReport $rec): array {
            $metrics = is_array($rec->metrics) ? $rec->metrics : [];
            $isKapal = $rec->transport_type === SinkarkesDailyReport::TYPE_KAPAL;

            if ($isKapal) {
                $totalArmada = (int) ($metrics['kd_dn_kapal'] ?? 0)
                    + (int) ($metrics['kd_ln_kapal'] ?? 0)
                    + (int) ($metrics['kd_lnt_kapal'] ?? 0)
                    + (int) ($metrics['kb_dn_kapal'] ?? 0)
                    + (int) ($metrics['kb_ln_kapal'] ?? 0)
                    + (int) ($metrics['kb_lnt_kapal'] ?? 0);
                $totalKru = (int) ($metrics['kd_dn_kru_kapal'] ?? 0)
                    + (int) ($metrics['kd_ln_kru_kapal'] ?? 0)
                    + (int) ($metrics['kd_lnt_kru_kapal'] ?? 0)
                    + (int) ($metrics['kb_dn_kru_kapal'] ?? 0)
                    + (int) ($metrics['kb_ln_kru_kapal'] ?? 0)
                    + (int) ($metrics['kb_lnt_kru_kapal'] ?? 0);
            } else {
                $totalArmada = (int) ($metrics['kd_dn_pesawat'] ?? 0)
                    + (int) ($metrics['kd_ln_pesawat'] ?? 0)
                    + (int) ($metrics['kd_lnt_pesawat'] ?? 0)
                    + (int) ($metrics['kb_dn_pesawat'] ?? 0)
                    + (int) ($metrics['kb_ln_pesawat'] ?? 0)
                    + (int) ($metrics['kb_lnt_pesawat'] ?? 0);
                $totalKru = (int) ($metrics['kd_dn_kru_pesawat'] ?? 0)
                    + (int) ($metrics['kd_ln_kru_pesawat'] ?? 0)
                    + (int) ($metrics['kd_lnt_kru_pesawat'] ?? 0)
                    + (int) ($metrics['kb_dn_kru_pesawat'] ?? 0)
                    + (int) ($metrics['kb_ln_kru_pesawat'] ?? 0)
                    + (int) ($metrics['kb_lnt_kru_pesawat'] ?? 0);
            }

            $totalPenumpang = (int) ($metrics['kd_dn_penumpang'] ?? 0)
                + (int) ($metrics['kd_ln_penumpang'] ?? 0)
                + (int) ($metrics['kd_lnt_penumpang'] ?? 0)
                + (int) ($metrics['kb_dn_penumpang'] ?? 0)
                + (int) ($metrics['kb_ln_penumpang'] ?? 0)
                + (int) ($metrics['kb_lnt_penumpang'] ?? 0);

            $contribution = $this->syncService->extractSinkarkesContributionForRecord($rec->transport_type, $metrics);

            return [
                'id' => $rec->id,
                'tanggal' => $rec->tanggal?->format('Y-m-d'),
                'wilker' => $rec->wilker,
                'transport_type' => $rec->transport_type,
                'metrics' => $metrics,
                'notes' => $rec->notes,
                'total_armada' => $totalArmada,
                'total_kru' => $totalKru,
                'total_penumpang' => $totalPenumpang,
                'sinkarkes_contribution' => $contribution,
                'sinkarkes_synced_at' => $rec->sinkarkes_synced_at?->toIso8601String(),
                'updater_name' => $rec->updater?->name ?? $rec->creator?->name ?? 'Petugas BKK',
                'updated_at' => $rec->updated_at?->toIso8601String(),
            ];
        };

        $kapalRecords = $allDaily
            ->where('transport_type', SinkarkesDailyReport::TYPE_KAPAL)
            ->values()
            ->map($transformRecord)
            ->all();

        $pesawatRecords = $allDaily
            ->where('transport_type', SinkarkesDailyReport::TYPE_PESAWAT)
            ->values()
            ->map($transformRecord)
            ->all();

        $accumulation = $this->syncService->accumulateMonth($wilker, $year, $month);

        return Inertia::render('SinkarkesDaily/Index', [
            'active_tab' => $activeTab,
            'wilker' => $wilker,
            'year' => $year,
            'month' => $month,
            'wilkerOptions' => $wilkerOptions,
            'kapalRecords' => $kapalRecords,
            'pesawatRecords' => $pesawatRecords,
            'accumulation' => $accumulation,
            'dailySchema' => $this->schemaService->getSchema(),
        ]);
    }

    /**
     * Save or update a daily Kapal/Pesawat entry and recalculate SINKARKES monthly accumulation.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:sinkarkes_daily_reports,id',
            'tanggal' => 'required|date',
            'wilker' => 'required|string|max:100',
            'transport_type' => 'required|string|in:kapal,pesawat',
            'metrics' => 'nullable|array',
            'notes' => 'nullable|string|max:2000',
        ]);

        $canonicalWilker = $this->schemaService->canonicalizeWilker($validated['wilker']);
        $tanggal = Carbon::parse($validated['tanggal'])->format('Y-m-d');
        $cleanMetrics = $this->schemaService->sanitizeMetrics(
            $validated['transport_type'],
            $validated['metrics'] ?? []
        );

        $affectedPeriods = [];
        $newDate = Carbon::parse($tanggal);
        $affectedPeriods["{$canonicalWilker}|{$newDate->year}|{$newDate->month}"] = [
            $canonicalWilker,
            $newDate->year,
            $newDate->month,
        ];

        $existing = !empty($validated['id'])
            ? SinkarkesDailyReport::find($validated['id'])
            : null;

        if ($existing) {
            $oldDate = Carbon::parse($existing->tanggal);
            $oldWilker = $this->schemaService->canonicalizeWilker($existing->wilker);
            $affectedPeriods["{$oldWilker}|{$oldDate->year}|{$oldDate->month}"] = [
                $oldWilker,
                $oldDate->year,
                $oldDate->month,
            ];

            // Guard unique key collision if moving date/wilker/type onto another existing record
            $collision = SinkarkesDailyReport::where('tanggal', $tanggal)
                ->where('wilker', $canonicalWilker)
                ->where('transport_type', $validated['transport_type'])
                ->where('id', '!=', $existing->id)
                ->first();

            if ($collision) {
                $collision->update([
                    'metrics' => $cleanMetrics,
                    'notes' => $validated['notes'] ?? $collision->notes,
                    'updated_by' => auth()->id(),
                ]);
                $existing->delete();
                $record = $collision;
            } else {
                $existing->update([
                    'tanggal' => $tanggal,
                    'wilker' => $canonicalWilker,
                    'transport_type' => $validated['transport_type'],
                    'metrics' => $cleanMetrics,
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]);
                $record = $existing;
            }
        } else {
            $record = SinkarkesDailyReport::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'wilker' => $canonicalWilker,
                    'transport_type' => $validated['transport_type'],
                ],
                [
                    'metrics' => $cleanMetrics,
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]
            );
            if (!$record->created_by && auth()->id()) {
                $record->update(['created_by' => auth()->id()]);
            }
        }

        $accumulation = null;
        foreach ($affectedPeriods as [$w, $y, $m]) {
            $res = $this->syncService->syncMonthToSinkarkes($w, $y, $m, auth()->id());
            if ($w === $canonicalWilker && $y === $newDate->year && $m === $newDate->month) {
                $accumulation = $res;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data harian berhasil disimpan dan terakumulasi ke SINKARKES.',
                'record' => $record->fresh(),
                'accumulation' => $accumulation,
            ]);
        }

        return back()->with('success', 'Data rekap harian berhasil disimpan & diakumulasikan ke Formulir SINKARKES.');
    }

    /**
     * Delete a daily record and immediately recalculate monthly SINKARKES accumulation.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $record = SinkarkesDailyReport::findOrFail($id);
        $wilker = $this->schemaService->canonicalizeWilker($record->wilker);
        $date = Carbon::parse($record->tanggal);

        $record->delete();

        $accumulation = $this->syncService->syncMonthToSinkarkes(
            $wilker,
            $date->year,
            $date->month,
            auth()->id()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data harian dihapus dan akumulasi SINKARKES diperbarui.',
                'accumulation' => $accumulation,
            ]);
        }

        return back()->with('success', 'Data harian dihapus dan akumulasi SINKARKES berhasil diperbarui.');
    }

    /**
     * Trigger explicit recalculation & sync to Formulir SINKARKES for a period.
     */
    public function sync(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'wilker' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $accumulation = $this->syncService->syncMonthToSinkarkes(
            $validated['wilker'],
            (int) $validated['year'],
            (int) $validated['month'],
            auth()->id()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Akumulasi harian berhasil disinkronkan ke Formulir Laporan SINKARKES.',
                'accumulation' => $accumulation,
            ]);
        }

        return back()->with('success', 'Akumulasi harian berhasil disinkronkan ke Formulir Laporan SINKARKES.');
    }

    /**
     * Import daily Kapal & Pesawat data from Excel matching rekap data harian kapal_pesawat.xlsx.
     */
    public function importExcel(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
            'wilker' => 'nullable|string|max:100',
        ]);

        try {
            $result = $this->syncService->importFromExcel(
                $request->file('file'),
                $validated['wilker'] ?? null,
                auth()->id()
            );

            $msg = "Berhasil mengimpor {$result['records_imported']} entri harian dan menyinkronkan {$result['periods_synced']} periode ke SINKARKES.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'result' => $result,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor file Excel harian: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['file' => 'Gagal mengimpor file Excel harian: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel export or official blank template (matching rekap data harian kapal_pesawat.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $wilker = $this->schemaService->canonicalizeWilker($request->query('wilker', 'Induk BKK Bandung'));
        $year = max(2020, min(2050, (int) $request->query('year', (int) date('Y'))));
        $monthParam = $request->query('month');
        $month = ($monthParam !== null && $monthParam !== '' && is_numeric($monthParam))
            ? max(1, min(12, (int) $monthParam))
            : null;
        $templateOnly = filter_var($request->query('template', false), FILTER_VALIDATE_BOOLEAN);

        $spreadsheet = $this->syncService->buildSpreadsheet($wilker, $year, $month, $templateOnly);

        $safeWilker = preg_replace('/[^a-zA-Z0-9_-]/', '_', $wilker);
        $prefix = $templateOnly ? 'Template_Rekap_Harian_Kapal_Pesawat' : 'Rekap_Harian_Kapal_Pesawat';
        $periodTag = $month ? sprintf('%04d_%02d', $year, $month) : (string) $year;
        $filename = "{$prefix}_{$safeWilker}_{$periodTag}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

