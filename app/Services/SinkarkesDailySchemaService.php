<?php

namespace App\Services;

use App\Models\KantorWilker;

class SinkarkesDailySchemaService
{
    protected array $schema;

    public function __construct()
    {
        $path = app_path('Data/sinkarkes_daily_schema.json');
        $this->schema = file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getFields(string $transportType): array
    {
        return $this->schema[$transportType]['fields'] ?? [];
    }

    public function getGroups(string $transportType): array
    {
        return $this->schema[$transportType]['groups'] ?? [];
    }

    public function getRedMappedFields(string $transportType): array
    {
        return array_values(array_filter(
            $this->getFields($transportType),
            fn (array $f) => !empty($f['is_red']) && !empty($f['sinkarkes_target_row'])
        ));
    }

    /**
     * All leaf excel_row IDs in SinkarkesReport managed by Daily Kapal + Pesawat accumulation.
     *
     * @return int[]
     */
    public function getManagedSinkarkesLeafRows(): array
    {
        $rows = [];
        foreach (['kapal', 'pesawat'] as $type) {
            foreach ($this->getRedMappedFields($type) as $field) {
                $rows[] = (int) $field['sinkarkes_target_row'];
            }
        }
        $unique = array_values(array_unique($rows));
        sort($unique);
        return $unique;
    }

    /**
     * Sanitize incoming metric map for a given transport type into positive integers.
     */
    public function sanitizeMetrics(string $transportType, array $input): array
    {
        $allowedKeys = array_flip(array_map(
            fn (array $f) => $f['key'],
            $this->getFields($transportType)
        ));

        $clean = [];
        foreach ($input as $key => $val) {
            if (!isset($allowedKeys[$key])) {
                continue;
            }
            if ($val === null || $val === '' || !is_numeric($val)) {
                continue;
            }
            $intVal = max(0, (int) round((float) $val));
            if ($intVal > 0) {
                $clean[$key] = $intVal;
            }
        }

        return $clean;
    }

    /**
     * Get canonical active Wilayah Kerja options.
     *
     * @return string[]
     */
    public function getWilkerOptions(): array
    {
        try {
            $dbWilkers = KantorWilker::where('is_active', true)
                ->orderBy('no')
                ->pluck('nama_pendek')
                ->toArray();
        } catch (\Throwable $e) {
            $dbWilkers = [];
        }

        if (!empty($dbWilkers)) {
            return array_values($dbWilkers);
        }

        return [
            'Induk BKK Bandung',
            'POS Bandara Husen',
            'Wilker Cirebon',
            'Wilker Indramayu',
            'Wilker Kertajati',
            'Wilker Patimban',
            'Wilker Pelabuharatu',
        ];
    }

    public function canonicalizeWilker(?string $wilker, ?array $options = null): string
    {
        $options ??= $this->getWilkerOptions();
        $trimmed = trim((string) $wilker);
        if ($trimmed === '') {
            return $options[0] ?? 'Induk BKK Bandung';
        }

        foreach ($options as $opt) {
            if (strcasecmp($opt, $trimmed) === 0) {
                return $opt;
            }
        }

        if (stripos($trimmed, 'Husen') !== false || stripos($trimmed, 'Husein') !== false) {
            return 'POS Bandara Husen';
        }
        if (stripos($trimmed, 'Kertajati') !== false) {
            return 'Wilker Kertajati';
        }
        if (stripos($trimmed, 'Cirebon') !== false) {
            return 'Wilker Cirebon';
        }
        if (stripos($trimmed, 'Indramayu') !== false || stripos($trimmed, 'Balongan') !== false) {
            return 'Wilker Indramayu';
        }
        if (stripos($trimmed, 'Patimban') !== false) {
            return 'Wilker Patimban';
        }
        if (stripos($trimmed, 'Ratu') !== false) {
            return 'Wilker Pelabuharatu';
        }
        if (stripos($trimmed, 'Induk') !== false || stripos($trimmed, 'Bandung') !== false) {
            return 'Induk BKK Bandung';
        }

        return $trimmed;
    }

    /**
     * Return DB query variants matching historical alias names for a canonical wilker.
     *
     * @return string[]
     */
    public function getWilkerQueryVariants(string $wilker): array
    {
        $variants = [$wilker];
        if (stripos($wilker, 'Husen') !== false || stripos($wilker, 'Husein') !== false) {
            $variants = array_merge($variants, [
                'POS Bandara Husen',
                'BANDARA HUSEIN',
                'Bandara Husein',
                'Bandara Husen',
            ]);
        }

        return array_values(array_unique($variants));
    }
}

