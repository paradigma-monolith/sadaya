<?php

namespace App\Exports;

use App\Models\SinkarkesReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SinkarkesExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected string $wilker;
    protected int $year;
    protected ?int $month;
    protected array $schema;
    protected array $monthlyData = [];

    public function __construct(string $wilker, int $year, ?int $month = null)
    {
        $this->wilker = $wilker;
        $this->year = $year;
        $this->month = $month;

        $schemaPath = app_path('Data/sinkarkes_schema.json');
        if (!file_exists($schemaPath)) {
            $schemaPath = storage_path('app/sinkarkes_schema.json');
        }
        if (file_exists($schemaPath)) {
            $this->schema = json_decode(file_get_contents($schemaPath), true);
        } else {
            $this->schema = ['sections' => []];
        }

        $this->loadMonthlyData();
    }

    /**
     * Load reports for all 12 months for this wilker & year, or single month.
     */
    protected function loadMonthlyData(): void
    {
        $query = SinkarkesReport::where(function ($q) {
                $q->where('wilker', $this->wilker);
                if (stripos($this->wilker, 'Husen') !== false || stripos($this->wilker, 'Husein') !== false) {
                    $q->orWhere('wilker', 'BANDARA HUSEIN')
                      ->orWhere('wilker', 'Bandara Husein')
                      ->orWhere('wilker', 'POS Bandara Husen');
                }
            })
            ->where('year', $this->year);

        if ($this->month) {
            $query->where('month', $this->month);
        }

        $reports = $query->get()->keyBy('month');

        for ($m = 1; $m <= 12; $m++) {
            $data = isset($reports[$m]) ? ($reports[$m]->data ?? []) : [];
            $this->monthlyData[$m] = $this->resolveFormulasForMonth($data);
        }
    }

    /**
     * Compute formula_rows sums iteratively until stable.
     */
    protected function resolveFormulasForMonth(array $rawVals): array
    {
        $vals = [];
        foreach ($rawVals as $k => $v) {
            if ($v !== null && $v !== '') {
                $vals[$k] = is_numeric($v) ? (float)$v : $v;
            }
        }

        $changed = true;
        $passes = 0;
        while ($changed && $passes < 10) {
            $changed = false;
            $passes++;
            foreach ($this->schema['sections'] ?? [] as $sec) {
                foreach ($sec['items'] ?? [] as $item) {
                    if (($item['type'] ?? '') === 'summary' && !empty($item['formula_rows'])) {
                        $sum = 0;
                        foreach ($item['formula_rows'] as $r) {
                            $sum += (isset($vals[$r]) && is_numeric($vals[$r])) ? (float)$vals[$r] : 0;
                        }
                        $cur = $vals[$item['excel_row']] ?? null;
                        if ($cur !== $sum) {
                            $vals[$item['excel_row']] = $sum;
                            $changed = true;
                        }
                    }
                }
            }
        }

        return $vals;
    }

    public function headings(): array
    {
        return [
            'Row',
            'No',
            'Kode',
            'Uraian Indikator / Kegiatan',
            'Satuan',
            'Tipe',
            'JAN',
            'FEB',
            'MART',
            'APRL',
            'MEI',
            'JUN',
            'JULI',
            'AGUST',
            'SEPT',
            'OKT',
            'NOV',
            'DES',
            'TOTAL TAHUNAN'
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->schema['sections'] ?? [] as $section) {
            // Section Header Separator
            $rows[] = [
                '',
                '',
                $section['section_code'] ?? '',
                strtoupper($section['section_title'] ?? ''),
                '',
                'SECTION',
                '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            foreach ($section['items'] ?? [] as $item) {
                $rowNum = $item['excel_row'] ?? '';
                $type = $item['type'] ?? 'input';
                $no = $item['no'] ?? '';
                $code = $item['code'] ?? '';
                $label = ($item['level'] > 0 ? str_repeat('   ', $item['level']) : '') . ($item['label'] ?? '');
                $unit = $item['unit'] ?? '-';

                if ($type === 'header') {
                    $rows[] = [
                        $rowNum,
                        $no,
                        $code,
                        $label,
                        '-',
                        'HEADER',
                        '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ];
                    continue;
                }

                $monthVals = [];
                $rowTotal = 0;
                for ($m = 1; $m <= 12; $m++) {
                    $v = $this->monthlyData[$m][$rowNum] ?? null;
                    if ($v !== null && $v !== '') {
                        $monthVals[] = is_numeric($v) ? (float)$v : $v;
                        if (is_numeric($v)) {
                            $rowTotal += (float)$v;
                        }
                    } else {
                        $monthVals[] = 0;
                    }
                }

                $rows[] = array_merge([
                    $rowNum,
                    $no,
                    $code,
                    $label,
                    $unit,
                    strtoupper($type),
                ], $monthVals, [$rowTotal]);
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '005851'] // Kemenkes Dark Teal
                ]
            ],
        ];
    }
}
