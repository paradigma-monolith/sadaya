<?php

namespace App\Services;

use App\Models\KantorWilker;
use App\Models\SinkarkesReport;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SinkarkesImportService
{
    protected array $config;

    public function __construct()
    {
        $configPath = app_path('Data/sinkarkes_import_config.json');
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = [];
        }
    }

    /**
     * Import SINKARKES Excel workbook.
     *
     * @param UploadedFile $file
     * @param int|null $targetYear
     * @param string|null $targetWilker
     * @param bool $overwrite
     * @return array Result summary
     */
    public function import(UploadedFile $file, ?int $targetYear = null, ?string $targetWilker = null, bool $overwrite = true): array
    {
        $year = $targetYear ?? ($this->config['import_metadata']['tahun'] ?? 2026);
        $startRow = $this->config['import_metadata']['start_row'] ?? 7;
        $endRow = $this->config['import_metadata']['end_row'] ?? 344;
        $ignoredSheets = array_map('strtoupper', $this->config['import_metadata']['ignored_sheets'] ?? ['REKAP 1', 'REKAP 2', 'GRAFIK']);
        $monthCols = $this->config['month_columns'] ?? [];
        $rowMap = $this->config['row_map'] ?? [];
        $sheetsConfig = $this->config['sheets'] ?? [];
        $summaryRows = $this->config['summary_rows'] ?? [];

        // Load Spreadsheet
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $reader->load($file->getRealPath());

        $importedSheets = [];
        $totalRecordsSaved = 0;
        $totalCellsImported = 0;
        $errors = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = trim($sheet->getTitle());
            $upperSheetName = strtoupper($sheetName);

            // Skip ignored sheets
            if (in_array($upperSheetName, $ignoredSheets)) {
                continue;
            }

            // Match sheet with canonical wilker
            $matchedWilker = $this->resolveWilkerFromSheet($sheetName, $sheetsConfig);
            if (!$matchedWilker) {
                continue;
            }

            // If a specific wilker was targeted, skip non-matching sheets
            if ($targetWilker && $targetWilker !== 'all' && !empty($targetWilker)) {
                if (strcasecmp($matchedWilker, $targetWilker) !== 0 &&
                    stripos($matchedWilker, $targetWilker) === false &&
                    stripos($targetWilker, $matchedWilker) === false) {
                    continue;
                }
            }

            $wilkerMonthsImported = 0;
            $wilkerCellsCount = 0;

            // Process each month column (G=1 .. R=12)
            foreach ($monthCols as $colLetter => $colMeta) {
                $monthNumber = (int) ($colMeta['bulan'] ?? 1);
                $monthData = [];

                // 1. Read input rows defined in row_map
                foreach ($rowMap as $rowStr => $meta) {
                    $rowNum = (int) $rowStr;
                    $cellCoordinate = $colLetter . $rowNum;

                    try {
                        $cell = $sheet->getCell($cellCoordinate);
                        $val = $cell ? $cell->getCalculatedValue() : null;
                    } catch (\Throwable $e) {
                        try {
                            $cell = $sheet->getCell($cellCoordinate);
                            $val = $cell ? $cell->getValue() : null;
                        } catch (\Throwable $ex) {
                            $val = null;
                        }
                    }

                    if ($val !== null && $val !== '' && is_numeric($val)) {
                        $numVal = ($meta['type'] === 'float') ? (float) $val : (int) round((float) $val);
                        if ($numVal != 0) {
                            $monthData[$rowNum] = $numVal;
                            $wilkerCellsCount++;
                        }
                    }
                }

                // 2. Summary Fallback: check if summary rows have values while children are empty
                if (!empty($this->config['import_metadata']['import_summary_fallback'])) {
                    foreach ($summaryRows as $sumRowStr => $sumMeta) {
                        $sumRowNum = (int) $sumRowStr;
                        if (!empty($sumMeta['fallback_target_code']) && !empty($sumMeta['children_rows'])) {
                            $childRow = $sumMeta['children_rows'][0];
                            if (empty($monthData[$childRow])) {
                                try {
                                    $cell = $sheet->getCell($colLetter . $sumRowNum);
                                    $val = $cell ? $cell->getCalculatedValue() : null;
                                    if (is_numeric($val) && (float) $val != 0) {
                                        $monthData[$childRow] = (int) round((float) $val);
                                        $wilkerCellsCount++;
                                    }
                                } catch (\Throwable $e) {}
                            }
                        }
                    }
                }

                // If month has data or overwrite is required, persist to database
                if (!empty($monthData)) {
                    $existingReport = SinkarkesReport::where('wilker', $matchedWilker)
                        ->where('year', $year)
                        ->where('month', $monthNumber)
                        ->first();

                    $finalData = $monthData;
                    if (!$overwrite && $existingReport && !empty($existingReport->data)) {
                        // Merge: existing data takes precedence unless empty
                        $finalData = array_replace($monthData, $existingReport->data);
                    }

                    SinkarkesReport::updateOrCreate(
                        [
                            'wilker' => $matchedWilker,
                            'year' => $year,
                            'month' => $monthNumber,
                        ],
                        [
                            'data' => $finalData,
                            'status' => 'submitted',
                            'updated_by' => auth()->id(),
                        ]
                    );

                    $wilkerMonthsImported++;
                    $totalRecordsSaved++;
                }
            }

            $importedSheets[] = [
                'sheet_name' => $sheetName,
                'wilker' => $matchedWilker,
                'months_count' => $wilkerMonthsImported,
                'cells_count' => $wilkerCellsCount,
            ];

            $totalCellsImported += $wilkerCellsCount;
        }

        return [
            'success' => true,
            'year' => $year,
            'sheets_processed' => count($importedSheets),
            'records_saved' => $totalRecordsSaved,
            'cells_imported' => $totalCellsImported,
            'details' => $importedSheets,
            'errors' => $errors,
        ];
    }

    /**
     * Resolve Wilker canonical name from Excel sheet title.
     */
    protected function resolveWilkerFromSheet(string $sheetTitle, array $sheetsConfig): ?string
    {
        $clean = trim($sheetTitle);
        $upper = strtoupper($clean);

        // 1. Direct match with sheet_name or wilker_code
        foreach ($sheetsConfig as $conf) {
            if (strcasecmp($conf['sheet_name'], $clean) === 0 || strcasecmp($conf['wilker_code'], $clean) === 0) {
                return $conf['wilker_canonical'] ?? $conf['wilker_name'];
            }
        }

        // 2. Fuzzy substring matching
        if (stripos($upper, 'HUSEIN') !== false || stripos($upper, 'HUSEN') !== false) {
            return 'POS Bandara Husen';
        }
        if (stripos($upper, 'KERTAJATI') !== false || stripos($upper, 'BIJB') !== false) {
            return 'Wilker Kertajati';
        }
        if (stripos($upper, 'CIREBON') !== false) {
            return 'Wilker Cirebon';
        }
        if (stripos($upper, 'INDRAMAYU') !== false || stripos($upper, 'BALONGAN') !== false) {
            return 'Wilker Indramayu';
        }
        if (stripos($upper, 'PATIMBAN') !== false) {
            return 'Wilker Patimban';
        }
        if (stripos($upper, 'RATU') !== false || stripos($upper, 'PELABUHANRATU') !== false || stripos($upper, 'PALABUHANRATU') !== false) {
            return 'Wilker Pelabuharatu';
        }
        if (stripos($upper, 'INDUK') !== false || stripos($upper, 'BANDUNG') !== false) {
            return 'Induk BKK Bandung';
        }

        return null;
    }
}

