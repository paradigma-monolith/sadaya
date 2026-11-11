<?php

namespace App\Services;

use App\Models\SinkarkesDailyReport;
use App\Models\SinkarkesReport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SinkarkesDailySyncService
{
    public function __construct(
        protected SinkarkesDailySchemaService $schemaService
    ) {
    }

    /**
     * Compute monthly accumulation broken down by Kapal, Pesawat, and Target SINKARKES rows.
     */
    public function accumulateMonth(string $wilker, int $year, int $month): array
    {
        $canonicalWilker = $this->schemaService->canonicalizeWilker($wilker);
        $variants = $this->schemaService->getWilkerQueryVariants($canonicalWilker);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $dailyRecords = SinkarkesDailyReport::whereIn('wilker', $variants)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $kapalByRow = [];
        $pesawatByRow = [];
        $kapalRecordCount = 0;
        $pesawatRecordCount = 0;

        foreach ($dailyRecords as $record) {
            $type = $record->transport_type;
            $metrics = is_array($record->metrics) ? $record->metrics : [];
            $contribution = $this->extractSinkarkesContributionForRecord($type, $metrics);

            if ($type === SinkarkesDailyReport::TYPE_KAPAL) {
                $kapalRecordCount++;
                foreach ($contribution as $row => $val) {
                    $kapalByRow[$row] = ($kapalByRow[$row] ?? 0) + $val;
                }
            } elseif ($type === SinkarkesDailyReport::TYPE_PESAWAT) {
                $pesawatRecordCount++;
                foreach ($contribution as $row => $val) {
                    $pesawatByRow[$row] = ($pesawatByRow[$row] ?? 0) + $val;
                }
            }
        }

        $managedLeafRows = $this->schemaService->getManagedSinkarkesLeafRows();
        $combinedLeafRows = [];
        foreach ($managedLeafRows as $row) {
            $total = ($kapalByRow[$row] ?? 0) + ($pesawatByRow[$row] ?? 0);
            $combinedLeafRows[$row] = $total;
        }

        // Load existing SINKARKES report to compare live values
        $existingReport = SinkarkesReport::whereIn('wilker', $variants)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $savedData = $existingReport ? ($existingReport->data ?? []) : [];

        // Build full indicator comparison table using canonical red mapping metadata
        $indicators = $this->buildIndicatorComparison(
            $kapalByRow,
            $pesawatByRow,
            $combinedLeafRows,
            $savedData
        );

        $allInSync = true;
        foreach ($indicators as $ind) {
            if (!$ind['in_sync']) {
                $allInSync = false;
                break;
            }
        }

        return [
            'wilker' => $canonicalWilker,
            'year' => $year,
            'month' => $month,
            'kapal_records_count' => $kapalRecordCount,
            'pesawat_records_count' => $pesawatRecordCount,
            'kapal_by_row' => $kapalByRow,
            'pesawat_by_row' => $pesawatByRow,
            'leaf_totals' => $combinedLeafRows,
            'summary_totals' => $this->computeSummaryPreview($combinedLeafRows, $savedData),
            'indicators' => $indicators,
            'report_exists' => $existingReport !== null,
            'report_updated_at' => $existingReport?->updated_at?->toIso8601String(),
            'last_synced_at' => $dailyRecords->max('sinkarkes_synced_at')?->toIso8601String(),
            'in_sync' => $allInSync,
        ];
    }

    /**
     * Extract SINKARKES target row contributions from one daily record's metrics,
     * applying anti-double-counting across paired ship/COP/PHQC columns.
     *
     * @return array<int, int>
     */
    public function extractSinkarkesContributionForRecord(string $transportType, array $metrics): array
    {
        $fields = $this->schemaService->getRedMappedFields($transportType);
        $totals = [];
        $pairedGroups = [];

        foreach ($fields as $field) {
            $row = (int) $field['sinkarkes_target_row'];
            $val = max(0, (int) ($metrics[$field['key']] ?? 0));

            if (!empty($field['dedupe_pair'])) {
                $pairKey = $field['dedupe_pair'] . '|' . $row;
                $pairedGroups[$pairKey]['row'] = $row;
                $pairedGroups[$pairKey]['values'][] = $val;
                continue;
            }

            $totals[$row] = ($totals[$row] ?? 0) + $val;
        }

        foreach ($pairedGroups as $pair) {
            $row = $pair['row'];
            $maxVal = max($pair['values']);
            $totals[$row] = ($totals[$row] ?? 0) + $maxVal;
        }

        return $totals;
    }

    /**
     * Recalculate monthly daily totals and write them into the corresponding SinkarkesReport.
     */
    public function syncMonthToSinkarkes(string $wilker, int $year, int $month, ?int $userId = null): array
    {
        return DB::transaction(function () use ($wilker, $year, $month, $userId) {
            $canonicalWilker = $this->schemaService->canonicalizeWilker($wilker);
            $variants = $this->schemaService->getWilkerQueryVariants($canonicalWilker);

            $acc = $this->accumulateMonth($canonicalWilker, $year, $month);
            $leafTotals = $acc['leaf_totals'];
            $hasAnyDailyRecords = ($acc['kapal_records_count'] + $acc['pesawat_records_count']) > 0;

            $report = SinkarkesReport::whereIn('wilker', $variants)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            $existingData = $report ? ($report->data ?? []) : [];
            $updatedData = $existingData;

            foreach ($leafTotals as $row => $total) {
                $strKey = (string) $row;
                if ($total > 0) {
                    $updatedData[$strKey] = $total;
                } else {
                    unset($updatedData[$strKey]);
                }
            }

            // Clean sparse integer keys while keeping manual non-daily values intact
            if (!empty($updatedData) || $report !== null) {
                if ($report) {
                    $report->update([
                        'data' => $updatedData,
                        'updated_by' => $userId ?? $report->updated_by,
                    ]);
                } else {
                    $report = SinkarkesReport::create([
                        'wilker' => $canonicalWilker,
                        'year' => $year,
                        'month' => $month,
                        'data' => $updatedData,
                        'status' => 'submitted',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }

            if ($hasAnyDailyRecords) {
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

                SinkarkesDailyReport::whereIn('wilker', $variants)
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->update(['sinkarkes_synced_at' => now()]);
            }

            return $this->accumulateMonth($canonicalWilker, $year, $month);
        });
    }

    /**
     * Import daily Kapal & Pesawat sheets from Excel matching rekap data harian kapal_pesawat.xlsx.
     */
    public function importFromExcel(UploadedFile $file, ?string $defaultWilker = null, ?int $userId = null): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $wilkerOptions = $this->schemaService->getWilkerOptions();

        $sheetsProcessed = [];
        $recordsImported = 0;
        $affectedPeriods = [];
        $stagedEntries = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $title = trim($sheet->getTitle());
            $lowerTitle = strtolower($title);

            $transportType = null;
            if (str_contains($lowerTitle, 'kapal')) {
                $transportType = SinkarkesDailyReport::TYPE_KAPAL;
            } elseif (str_contains($lowerTitle, 'pesawat')) {
                $transportType = SinkarkesDailyReport::TYPE_PESAWAT;
            }

            if ($transportType === null) {
                continue;
            }

            $fields = $this->schemaService->getFields($transportType);
            $highestRow = $sheet->getHighestDataRow();
            $sheetCount = 0;

            for ($r = 2; $r <= $highestRow; $r++) {
                $rawDate = $sheet->getCell("A{$r}")->getCalculatedValue();
                $tanggal = $this->parseExcelDate($rawDate);
                if (!$tanggal) {
                    continue;
                }

                $rawWilker = trim((string) $sheet->getCell("B{$r}")->getCalculatedValue());
                $resolvedWilker = $this->schemaService->canonicalizeWilker(
                    $rawWilker !== '' ? $rawWilker : ($defaultWilker ?? $wilkerOptions[0]),
                    $wilkerOptions
                );

                $rowMetrics = [];
                $hasAnyMetric = false;
                foreach ($fields as $field) {
                    $col = $field['excel_col'];
                    $val = $sheet->getCell("{$col}{$r}")->getCalculatedValue();
                    if ($val !== null && $val !== '' && is_numeric($val)) {
                        $intVal = max(0, (int) round((float) $val));
                        if ($intVal > 0) {
                            $rowMetrics[$field['key']] = $intVal;
                            $hasAnyMetric = true;
                        }
                    }
                }

                if (!$hasAnyMetric && $rawWilker === '') {
                    continue;
                }

                $stageKey = "{$transportType}|{$resolvedWilker}|{$tanggal}";
                if (!isset($stagedEntries[$stageKey])) {
                    $stagedEntries[$stageKey] = [
                        'transport_type' => $transportType,
                        'wilker' => $resolvedWilker,
                        'tanggal' => $tanggal,
                        'metrics' => [],
                    ];
                }
                foreach ($rowMetrics as $k => $v) {
                    $stagedEntries[$stageKey]['metrics'][$k] = ($stagedEntries[$stageKey]['metrics'][$k] ?? 0) + $v;
                }

                $sheetCount++;
            }

            $sheetsProcessed[] = [
                'sheet_name' => $title,
                'transport_type' => $transportType,
                'records_count' => $sheetCount,
            ];
        }

        foreach ($stagedEntries as $entry) {
            $record = SinkarkesDailyReport::updateOrCreate(
                [
                    'tanggal' => $entry['tanggal'],
                    'wilker' => $entry['wilker'],
                    'transport_type' => $entry['transport_type'],
                ],
                [
                    'metrics' => $entry['metrics'],
                    'updated_by' => $userId,
                ]
            );

            if (!$record->created_by && $userId) {
                $record->update(['created_by' => $userId]);
            }

            $recordsImported++;

            $carbon = Carbon::parse($entry['tanggal']);
            $periodKey = "{$entry['wilker']}|{$carbon->year}|{$carbon->month}";
            $affectedPeriods[$periodKey] = [$entry['wilker'], $carbon->year, $carbon->month];
        }

        foreach ($affectedPeriods as [$w, $y, $m]) {
            $this->syncMonthToSinkarkes($w, $y, $m, $userId);
        }

        return [
            'sheets_processed' => $sheetsProcessed,
            'records_imported' => $recordsImported,
            'periods_synced' => count($affectedPeriods),
        ];
    }

    /**
     * Build multi-sheet Excel export (or blank official template matching rekap data harian kapal_pesawat.xlsx).
     */
    public function buildSpreadsheet(string $wilker, int $year, ?int $month = null, bool $templateOnly = false): Spreadsheet
    {
        $canonicalWilker = $this->schemaService->canonicalizeWilker($wilker);
        $variants = $this->schemaService->getWilkerQueryVariants($canonicalWilker);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $configs = [
            SinkarkesDailyReport::TYPE_KAPAL => 'Sinkarkes kapal mentah',
            SinkarkesDailyReport::TYPE_PESAWAT => 'Sinkarkes pesawat mentah',
        ];

        foreach ($configs as $type => $sheetTitle) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetTitle);

            $fields = $this->schemaService->getFields($type);
            $groups = collect($this->schemaService->getGroups($type))->keyBy('id');

            // Row 1-3 headers for A & B
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('B1', 'Nama Balai Karkes');
            $sheet->mergeCells('A1:A3');
            $sheet->mergeCells('B1:B3');

            $sheet->setCellValue('A4', 'inputan harian tersebut terakumulasi dan masuk kedata Formulir Laporan SINKARKES');
            $sheet->mergeCells('A4:B4');

            foreach ($fields as $idx => $field) {
                $col = $field['excel_col'];
                $group = $groups->get($field['group']);
                $groupTitle = $group['title'] ?? $field['group'];

                $sheet->setCellValue("{$col}1", $groupTitle);
                $sheet->setCellValue("{$col}2", $group['badge'] ?? '');
                $sheet->setCellValue("{$col}3", $field['label']);

                if ($field['is_red']) {
                    $markText = $field['sinkarkes_excel_mark'] . ' (' . $field['sinkarkes_code'] . ')';
                    $sheet->setCellValue("{$col}4", $markText);
                }

                $sheet->getColumnDimension($col)->setWidth(18);
            }

            $sheet->getColumnDimension('A')->setWidth(14);
            $sheet->getColumnDimension('B')->setWidth(24);

            $lastCol = end($fields)['excel_col'] ?? 'B';

            // Header styling
            $sheet->getStyle("A1:{$lastCol}3")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                ],
            ]);

            // Row 4 SINKARKES highlight strip (matching red indicator row in reference Excel)
            $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '991B1B']],
                ],
            ]);
            $sheet->getRowDimension(4)->setRowHeight(34);

            if (!$templateOnly) {
                $query = SinkarkesDailyReport::whereIn('wilker', $variants)
                    ->where('transport_type', $type)
                    ->whereYear('tanggal', $year);

                if ($month) {
                    $query->whereMonth('tanggal', $month);
                }

                $rows = $query->orderBy('tanggal')->get();
                $rowIdx = 5;

                foreach ($rows as $entry) {
                    $metrics = is_array($entry->metrics) ? $entry->metrics : [];
                    $sheet->setCellValue("A{$rowIdx}", $entry->tanggal?->format('Y-m-d'));
                    $sheet->setCellValue("B{$rowIdx}", $entry->wilker);

                    foreach ($fields as $field) {
                        $val = $metrics[$field['key']] ?? 0;
                        $sheet->setCellValue("{$field['excel_col']}{$rowIdx}", (int) $val);
                    }
                    $rowIdx++;
                }
            } else {
                // Single starter sample guide row
                $sheet->setCellValue('A5', Carbon::createFromDate($year, $month ?: (int) date('n'), 1)->format('Y-m-d'));
                $sheet->setCellValue('B5', $canonicalWilker);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    protected function buildIndicatorComparison(
        array $kapalByRow,
        array $pesawatByRow,
        array $combinedLeafRows,
        array $savedData
    ): array {
        $schemaPath = app_path('Data/sinkarkes_schema.json');
        $sinkarkesMetaByRow = [];
        if (file_exists($schemaPath)) {
            $sinkarkesSchema = json_decode(file_get_contents($schemaPath), true) ?? [];
            foreach ($sinkarkesSchema['sections'] ?? [] as $sec) {
                foreach ($sec['items'] ?? [] as $item) {
                    $sinkarkesMetaByRow[(int) $item['excel_row']] = $item;
                }
            }
        }

        $rows = [];
        foreach ($combinedLeafRows as $leafRow => $total) {
            $item = $sinkarkesMetaByRow[$leafRow] ?? null;
            $savedVal = isset($savedData[(string) $leafRow]) && is_numeric($savedData[(string) $leafRow])
                ? (int) $savedData[(string) $leafRow]
                : (isset($savedData[$leafRow]) && is_numeric($savedData[$leafRow]) ? (int) $savedData[$leafRow] : 0);

            $summaryInfo = match ($leafRow) {
                101 => ['row' => 99, 'code' => 'KSE.11'],
                104 => ['row' => 102, 'code' => 'KSE.12'],
                124 => ['row' => 123, 'code' => 'KSE.18.A'],
                199 => ['row' => 198, 'code' => 'UKP.7'],
                default => null,
            };

            $rows[] = [
                'excel_row' => $leafRow,
                'summary_row' => $summaryInfo['row'] ?? null,
                'summary_code' => $summaryInfo['code'] ?? null,
                'code' => $item['code'] ?? "ROW.{$leafRow}",
                'label' => $item['label'] ?? "Indikator Baris {$leafRow}",
                'unit' => $item['unit'] ?? '',
                'kapal_total' => $kapalByRow[$leafRow] ?? 0,
                'pesawat_total' => $pesawatByRow[$leafRow] ?? 0,
                'daily_total' => $total,
                'saved_sinkarkes_value' => $savedVal,
                'in_sync' => $savedVal === $total,
            ];
        }

        return $rows;
    }

    protected function computeSummaryPreview(array $combinedLeafRows, array $savedData): array
    {
        $valOrSaved = function (int $row) use ($combinedLeafRows, $savedData): int {
            if (array_key_exists($row, $combinedLeafRows)) {
                return (int) $combinedLeafRows[$row];
            }
            $raw = $savedData[(string) $row] ?? $savedData[$row] ?? 0;
            return is_numeric($raw) ? (int) $raw : 0;
        };

        return [
            99 => $valOrSaved(100) + $valOrSaved(101),
            102 => $valOrSaved(103) + $valOrSaved(104),
            123 => $valOrSaved(124) + $valOrSaved(125),
            198 => $valOrSaved(199) + $valOrSaved(200),
        ];
    }

    protected function parseExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                if ((float) $value < 20000 || (float) $value > 80000) {
                    return null;
                }
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $str = trim((string) $value);
        if ($str === '' || str_starts_with(strtolower($str), 'tanggal') || str_starts_with(strtolower($str), 'inputan')) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'] as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $str);
                if ($dt && $dt->year >= 2000 && $dt->year <= 2100) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            $parsed = Carbon::parse($str);
            if ($parsed->year >= 2000 && $parsed->year <= 2100) {
                return $parsed->format('Y-m-d');
            }
        } catch (\Throwable $e) {
        }

        return null;
    }
}
