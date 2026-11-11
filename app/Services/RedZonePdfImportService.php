<?php

namespace App\Services;

use App\Models\QuarantineCountry;
use App\Models\QuarantineCountryImportLog;
use App\Models\ShipHealthSurveillance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class RedZonePdfImportService
{
    /**
     * Common Kemenkes / WHO / UN naming aliases mapped to Sadaya's canonical country names.
     */
    public const COUNTRY_ALIASES = [
        'united states of america' => 'United States',
        'usa' => 'United States',
        'us' => 'United States',
        'vietnam' => 'Viet Nam',
        'turkiye' => 'Türkiye',
        'turkey' => 'Türkiye',
        'lao pdr' => 'Laos',
        'lao peoples democratic republic' => 'Laos',
        'lao people s democratic republic' => 'Laos',
        'republic of the congo' => 'Congo',
        'congo brazzaville' => 'Congo',
        'dr congo' => 'Democratic Republic of the Congo',
        'drc' => 'Democratic Republic of the Congo',
        'democratic republic of congo' => 'Democratic Republic of the Congo',
        'tanzania' => 'United Republic of Tanzania',
        'moldova' => 'Republic of Moldova',
        'venezuela' => 'Venezuela (Bolivarian Republic o',
        'venezuela bolivarian republic of' => 'Venezuela (Bolivarian Republic o',
        'russia' => 'Russian Federation',
        'syria' => 'Syrian Arab Republic',
        'brunei' => 'Brunei Darussalam',
        'czech republic' => 'Czechia',
        'swaziland' => 'Eswatini',
        'cape verde' => 'Cabo Verde',
        'east timor' => 'Timor-Leste',
        'ivory coast' => 'Cote d\'Ivoire',
        'cote d ivoire' => 'Cote d\'Ivoire',
        'philippines' => 'The Philippines',
        'bolivia plurinational state of' => 'Bolivia',
        'iran islamic republic of' => 'Iran',
        'micronesia' => 'Federated States of Micronesia',
        'micronesia federated states of' => 'Federated States of Micronesia',
        'macedonia' => 'North Macedonia',
        'holy see' => 'Vatican',
        'vatican city' => 'Vatican',
        'burma' => 'Myanmar',
        'state of palestine' => 'Palestine',
        'occupied palestinian territory' => 'Palestine',
        'korea south' => 'South Korea',
        'korea republic of' => 'Republic of Korea',
        'uk' => 'United Kingdom',
        'great britain' => 'United Kingdom',
    ];

    /**
     * Normalize country strings for diacritic-insensitive and punctuation-tolerant matching.
     */
    public static function normalizeKey(string $name): string
    {
        $ascii = Str::ascii(trim($name));
        $lower = mb_strtolower($ascii, 'UTF-8');
        $clean = preg_replace('/[^a-z0-9]+/', ' ', $lower);

        return trim((string) $clean);
    }

    /**
     * Resolve an extracted PDF country label to a canonical country name if present in $knownNames.
     */
    public static function resolveCanonicalName(string $rawName, array $knownNames): ?string
    {
        $rawTrimmed = trim(preg_replace('/\s+/', ' ', $rawName));
        if ($rawTrimmed === '') {
            return null;
        }

        $knownByNormalized = [];
        foreach ($knownNames as $known) {
            if (strcasecmp($known, $rawTrimmed) === 0) {
                return $known;
            }
            $knownByNormalized[self::normalizeKey($known)] = $known;
        }

        $normRaw = self::normalizeKey($rawTrimmed);
        if (isset($knownByNormalized[$normRaw])) {
            return $knownByNormalized[$normRaw];
        }

        if (isset(self::COUNTRY_ALIASES[$normRaw])) {
            $aliasTarget = self::COUNTRY_ALIASES[$normRaw];
            if (in_array($aliasTarget, $knownNames, true)) {
                return $aliasTarget;
            }
            $normTarget = self::normalizeKey($aliasTarget);
            if (isset($knownByNormalized[$normTarget])) {
                return $knownByNormalized[$normTarget];
            }
        }

        return null;
    }

    /**
     * Build non-destructive preview comparing extracted PDF `NEGARA` column with current DB status.
     */
    public function buildPreviewFromPdf(string $filePath, string $originalFilename, string $mode = 'replace'): array
    {
        $mode = in_array($mode, ['replace', 'merge'], true) ? $mode : 'replace';
        $extraction = $this->extractCountriesFromPdf($filePath);
        $extracted = $extraction['countries'];

        $currentMap = QuarantineCountry::getCountriesMap();
        $knownNames = array_keys($currentMap);

        $matchedSet = [];
        $matchedRecords = [];
        $unmatched = [];

        foreach ($extracted as $rawCountry) {
            $canonical = self::resolveCanonicalName($rawCountry, $knownNames);
            if ($canonical !== null) {
                if (!isset($matchedSet[$canonical])) {
                    $matchedSet[$canonical] = $rawCountry;
                    $matchedRecords[] = [
                        'canonical_name' => $canonical,
                        'pdf_name' => $rawCountry,
                        'current_is_red' => (bool) ($currentMap[$canonical] ?? false),
                    ];
                }
            } else {
                $unmatched[] = $rawCountry;
            }
        }

        $newlyFlagged = [];
        $alreadyRed = [];
        foreach ($matchedRecords as $rec) {
            if ($rec['current_is_red']) {
                $alreadyRed[] = $rec;
            } else {
                $newlyFlagged[] = $rec;
            }
        }

        $toUnflag = [];
        foreach ($currentMap as $name => $isRed) {
            if ($isRed && !isset($matchedSet[$name])) {
                $toUnflag[] = [
                    'canonical_name' => $name,
                    'current_is_red' => true,
                ];
            }
        }

        // Suggested final red list according to mode
        $recommendedRed = array_keys($matchedSet);
        if ($mode === 'merge') {
            foreach ($currentMap as $name => $isRed) {
                if ($isRed && !isset($matchedSet[$name])) {
                    $recommendedRed[] = $name;
                }
            }
        }
        $recommendedRed = array_values(array_unique($recommendedRed));
        sort($recommendedRed);

        $indonesiaMatched = isset($matchedSet['Indonesia']);

        return [
            'filename' => $originalFilename,
            'mode' => $mode,
            'pages_parsed' => $extraction['pages_parsed'],
            'total_extracted' => count($extracted),
            'extracted_countries' => $extracted,
            'matched_count' => count($matchedRecords),
            'matched_records' => $matchedRecords,
            'newly_flagged' => $newlyFlagged,
            'already_red' => $alreadyRed,
            'to_unflag' => $toUnflag,
            'unmatched_countries' => array_values(array_unique($unmatched)),
            'recommended_red_countries' => $recommendedRed,
            'indonesia_in_pdf' => $indonesiaMatched,
            'warnings' => $extraction['warnings'],
        ];
    }

    /**
     * Persist red zone updates and log audit history atomically.
     */
    public function applyUpdate(
        array $redCountries,
        array $newCountries,
        string $mode,
        string $sourceFilename,
        ?int $userId = null,
        array $extractedCountries = [],
        array $sourceLabelMap = []
    ): array {
        $mode = in_array($mode, ['replace', 'merge'], true) ? $mode : 'replace';

        $cleanRed = array_values(array_unique(array_filter(array_map(
            static fn ($v) => trim((string) $v),
            $redCountries
        ), static fn ($v) => $v !== '')));

        $cleanNew = array_values(array_unique(array_filter(array_map(
            static fn ($v) => trim((string) $v),
            $newCountries
        ), static fn ($v) => $v !== '')));

        return DB::transaction(function () use ($cleanRed, $cleanNew, $mode, $sourceFilename, $userId, $extractedCountries, $sourceLabelMap) {
            $beforeMap = QuarantineCountry::query()
                ->orderBy('name')
                ->pluck('is_red_zone', 'name')
                ->all();

            if (empty($beforeMap)) {
                $this->seedBaselineCountries();
                $beforeMap = QuarantineCountry::query()
                    ->orderBy('name')
                    ->pluck('is_red_zone', 'name')
                    ->all();
            }

            $now = now();
            $redLookup = array_flip($cleanRed);
            $addedCount = 0;

            // Optionally insert any newly approved unmatched territories/countries from PDF
            foreach ($cleanNew as $newName) {
                if (!array_key_exists($newName, $beforeMap)) {
                    QuarantineCountry::create([
                        'name' => mb_substr($newName, 0, 150),
                        'is_red_zone' => true,
                        'source_label' => mb_substr($sourceFilename, 0, 255),
                        'last_imported_at' => $now,
                        'updated_by' => $userId,
                    ]);
                    $redLookup[$newName] = true;
                    $addedCount++;
                } else {
                    $redLookup[$newName] = true;
                }
            }

            $newlyFlagged = [];
            $unflagged = [];
            $matchedCount = 0;

            foreach ($beforeMap as $name => $wasRed) {
                $wasRed = (bool) $wasRed;
                $isInSelected = isset($redLookup[$name]);

                if ($isInSelected) {
                    $matchedCount++;
                }

                $nextRed = $mode === 'replace'
                    ? $isInSelected
                    : ($wasRed || $isInSelected);

                $updatePayload = [
                    'last_imported_at' => $now,
                    'updated_by' => $userId,
                ];

                if (isset($sourceLabelMap[$name])) {
                    $updatePayload['source_label'] = mb_substr((string) $sourceLabelMap[$name], 0, 255);
                } elseif ($isInSelected) {
                    $updatePayload['source_label'] = mb_substr($sourceFilename, 0, 255);
                }

                if ($nextRed !== $wasRed) {
                    $updatePayload['is_red_zone'] = $nextRed;
                    if ($nextRed) {
                        $newlyFlagged[] = $name;
                    } else {
                        $unflagged[] = $name;
                    }
                }

                QuarantineCountry::where('name', $name)->update($updatePayload);
            }

            QuarantineCountry::clearCache();

            $log = QuarantineCountryImportLog::create([
                'filename' => mb_substr($sourceFilename, 0, 255),
                'mode' => $mode,
                'total_extracted' => !empty($extractedCountries) ? count($extractedCountries) : count($cleanRed) + $addedCount,
                'matched_count' => $matchedCount,
                'newly_flagged_count' => count($newlyFlagged),
                'unflagged_count' => count($unflagged),
                'added_countries_count' => $addedCount,
                'details' => [
                    'newly_flagged' => $newlyFlagged,
                    'unflagged' => $unflagged,
                    'added_countries' => $cleanNew,
                    'extracted_countries' => array_values($extractedCountries),
                ],
                'imported_by' => $userId,
            ]);

            if (function_exists('activity')) {
                activity('ship_health_surveillance')
                    ->withProperties([
                        'filename' => $sourceFilename,
                        'mode' => $mode,
                        'matched_count' => $matchedCount,
                        'newly_flagged_count' => count($newlyFlagged),
                        'unflagged_count' => count($unflagged),
                        'added_countries_count' => $addedCount,
                    ])
                    ->log("Memperbarui daftar negara zona merah TANGKAP dari PDF: {$sourceFilename}");
            }

            return [
                'log' => $log->load('importer:id,name'),
                'newly_flagged' => $newlyFlagged,
                'unflagged' => $unflagged,
                'added_count' => $addedCount,
            ];
        });
    }

    /**
     * Extract unique country names from the `NEGARA` / `Country` column of a vector PDF file.
     * Supports Chrome/PDFium (Google Sheets export), DomPDF, Excel/LibreOffice PDFs,
     * `/FlateDecode`, PDF 1.5+ `/ObjStm`, `/ToUnicode` CMaps, and CTM + text-matrix transforms.
     */
    public function extractCountriesFromPdf(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new InvalidArgumentException('File PDF tidak ditemukan.');
        }

        $rawPdf = @file_get_contents($filePath);
        if ($rawPdf === false || !str_starts_with(ltrim($rawPdf), '%PDF-')) {
            throw new InvalidArgumentException('Berkas yang diunggah tidak dapat dibaca atau bukan dokumen PDF yang valid.');
        }

        $objects = $this->parsePdfObjects($rawPdf);
        if (empty($objects)) {
            throw new RuntimeException('Struktur objek PDF tidak dapat dibaca.');
        }

        $fontCMaps = $this->buildFontCMaps($objects);
        $pages = $this->resolveOrderedPages($rawPdf, $objects);
        if (empty($pages)) {
            throw new RuntimeException('Tidak ada halaman yang ditemukan di dalam berkas PDF.');
        }

        $warnings = [];
        $countries = [];
        $seen = [];
        $fallbackColBounds = null;

        foreach ($pages as $pageInfo) {
            $pageFonts = $this->resolvePageFonts($pageInfo['resources'], $objects, $fontCMaps);
            $pageXObjects = $this->resolvePageXObjects($pageInfo['resources'], $objects);

            $stream = $this->readPageContentsStream($pageInfo['dict'], $objects);
            if ($stream === '') {
                continue;
            }

            $fragments = $this->extractPositionedFragments($stream, $pageFonts, $pageXObjects, $objects, $fontCMaps);
            if (empty($fragments)) {
                continue;
            }

            $colInfo = $this->detectNegaraColumn($fragments, $fallbackColBounds);
            if ($colInfo === null) {
                continue;
            }

            $fallbackColBounds = [
                'min_x' => $colInfo['min_x'],
                'max_x' => $colInfo['max_x'],
            ];

            $pageCountries = $this->extractColumnValues($fragments, $colInfo);
            foreach ($pageCountries as $country) {
                $key = mb_strtolower($country, 'UTF-8');
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $countries[] = $country;
                }
            }
        }

        if (empty($countries)) {
            $warnings[] = 'Tidak ada data negara yang berhasil diekstrak dari kolom NEGARA. Pastikan PDF berupa teks tabel (bukan hasil scan gambar) dan memiliki kolom header NEGARA atau COUNTRY.';
        }

        return [
            'countries' => $countries,
            'pages_parsed' => count($pages),
            'warnings' => $warnings,
        ];
    }

    /**
     * Parse indirect PDF objects and unpack any PDF 1.5+ /ObjStm compressed object streams.
     */
    protected function parsePdfObjects(string $rawPdf): array
    {
        $objects = [];

        if (preg_match_all('/(\d+)\s+\d+\s+obj\b(.*?)\bendobj\b/s', $rawPdf, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $id = (int) $m[1];
                $body = $m[2];
                $dict = $body;
                $stream = null;

                if (preg_match('/^(.*?)stream\r?\n(.*?)(?:\r?\n)?endstream/s', $body, $sm)) {
                    $dict = $sm[1];
                    $stream = $this->decodeStream($sm[2], $dict);
                }

                $objects[$id] = [
                    'dict' => $dict,
                    'stream' => $stream,
                ];
            }
        }

        // Unpack PDF 1.5+ Object Streams (/Type /ObjStm)
        foreach ($objects as $obj) {
            if ($obj['stream'] !== null && preg_match('/\/Type\s*\/ObjStm\b/', $obj['dict'])) {
                $n = preg_match('/\/N\s+(\d+)/', $obj['dict'], $nm) ? (int) $nm[1] : 0;
                $first = preg_match('/\/First\s+(\d+)/', $obj['dict'], $fm) ? (int) $fm[1] : 0;
                if ($n > 0 && $first > 0 && $first < strlen($obj['stream'])) {
                    $header = trim(substr($obj['stream'], 0, $first));
                    $nums = preg_split('/\s+/', $header);
                    $pairs = [];
                    for ($i = 0; $i + 1 < count($nums) && count($pairs) < $n; $i += 2) {
                        $pairs[] = [(int) $nums[$i], (int) $nums[$i + 1]];
                    }
                    $dataPart = substr($obj['stream'], $first);
                    $countPairs = count($pairs);
                    for ($i = 0; $i < $countPairs; $i++) {
                        [$innerId, $startOff] = $pairs[$i];
                        $endOff = ($i + 1 < $countPairs) ? $pairs[$i + 1][1] : strlen($dataPart);
                        $innerBody = trim(substr($dataPart, $startOff, max(0, $endOff - $startOff)));
                        if (!isset($objects[$innerId])) {
                            $objects[$innerId] = [
                                'dict' => $innerBody,
                                'stream' => null,
                            ];
                        }
                    }
                }
            }
        }

        return $objects;
    }

    /**
     * Decompress a PDF stream if FlateDecode filter is specified.
     */
    protected function decodeStream(string $rawStream, string $dict): string
    {
        if (str_contains($dict, '/FlateDecode') || str_contains($dict, '/Fl')) {
            $trimmed = ltrim($rawStream, "\r\n");
            $decoded = @gzuncompress($rawStream);
            if ($decoded === false) {
                $decoded = @gzuncompress($trimmed);
            }
            if ($decoded === false) {
                $decoded = @gzinflate($rawStream);
            }
            if ($decoded === false && strlen($trimmed) > 2) {
                $decoded = @gzinflate(substr($trimmed, 2));
            }
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $rawStream;
    }

    /**
     * Parse /ToUnicode CMap tables for every font object.
     */
    protected function buildFontCMaps(array $objects): array
    {
        $cmaps = [];
        foreach ($objects as $id => $obj) {
            if (!preg_match('/\/ToUnicode\s+(\d+)\s+\d+\s+R/', $obj['dict'], $m)) {
                continue;
            }
            $toUniId = (int) $m[1];
            $cmapStream = $objects[$toUniId]['stream'] ?? null;
            if (!$cmapStream) {
                continue;
            }
            $cmaps[$id] = $this->parseToUnicodeCMap($cmapStream);
        }

        return $cmaps;
    }

    /**
     * Parse a ToUnicode CMap stream into code => UTF-8 char map plus code hex length.
     */
    protected function parseToUnicodeCMap(string $cmap): array
    {
        $map = [];
        $hexDigits = 4;

        if (preg_match('/begincodespacerange\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/s', $cmap, $csr)) {
            $hexDigits = max(2, strlen($csr[1]));
        }

        if (preg_match_all('/beginbfchar(.*?)endbfchar/s', $cmap, $blocks)) {
            foreach ($blocks[1] as $block) {
                if (preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $block, $pairs, PREG_SET_ORDER)) {
                    foreach ($pairs as $p) {
                        $hexDigits = max(2, strlen($p[1]));
                        $map[hexdec($p[1])] = $this->hexToUtf8($p[2]);
                    }
                }
            }
        }

        if (preg_match_all('/beginbfrange(.*?)endbfrange/s', $cmap, $blocks)) {
            foreach ($blocks[1] as $block) {
                if (preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*\[(.*?)\]/s', $block, $arrRanges, PREG_SET_ORDER)) {
                    foreach ($arrRanges as $r) {
                        $start = hexdec($r[1]);
                        $hexDigits = max(2, strlen($r[1]));
                        if (preg_match_all('/<([0-9A-Fa-f]+)>/', $r[3], $hexList)) {
                            foreach ($hexList[1] as $offset => $dstHex) {
                                $map[$start + $offset] = $this->hexToUtf8($dstHex);
                            }
                        }
                    }
                }

                $blockNoArrays = preg_replace('/\[.*?\]/s', '', $block);
                if (preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', (string) $blockNoArrays, $ranges, PREG_SET_ORDER)) {
                    foreach ($ranges as $r) {
                        $start = hexdec($r[1]);
                        $end = hexdec($r[2]);
                        $dstStart = hexdec($r[3]);
                        $hexDigits = max(2, strlen($r[1]));
                        if ($end - $start <= 4096) {
                            for ($c = $start; $c <= $end; $c++) {
                                $map[$c] = mb_chr($dstStart + ($c - $start), 'UTF-8') ?: '';
                            }
                        }
                    }
                }
            }
        }

        return [
            'hex_digits' => $hexDigits,
            'map' => $map,
        ];
    }

    protected function hexToUtf8(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex);
        if ($hex === '') {
            return '';
        }
        if (strlen($hex) % 4 === 0) {
            return (string) mb_convert_encoding((string) pack('H*', $hex), 'UTF-8', 'UTF-16BE');
        }
        if (strlen($hex) === 2) {
            return mb_chr(hexdec($hex), 'UTF-8') ?: '';
        }
        $padded = str_pad($hex, (int) ceil(strlen($hex) / 4) * 4, '0', STR_PAD_LEFT);

        return (string) mb_convert_encoding((string) pack('H*', $padded), 'UTF-8', 'UTF-16BE');
    }

    /**
     * Resolve document pages in logical reading order along with inherited /Resources dicts.
     */
    protected function resolveOrderedPages(string $rawPdf, array $objects): array
    {
        $rootId = null;
        if (preg_match('/\/Root\s+(\d+)\s+\d+\s+R/', $rawPdf, $rm)) {
            $rootId = (int) $rm[1];
        } else {
            foreach ($objects as $id => $obj) {
                if (preg_match('/\/Type\s*\/Catalog\b/', $obj['dict'])) {
                    $rootId = $id;
                    break;
                }
            }
        }

        if ($rootId !== null && isset($objects[$rootId]) && preg_match('/\/Pages\s+(\d+)\s+\d+\s+R/', $objects[$rootId]['dict'], $pm)) {
            $pagesRootId = (int) $pm[1];
            $ordered = $this->walkPageTree($pagesRootId, $objects, []);
            if (!empty($ordered)) {
                return $ordered;
            }
        }

        // Fallback: iterate /Type /Page objects in ID order
        $fallback = [];
        ksort($objects);
        foreach ($objects as $obj) {
            if (preg_match('/\/Type\s*\/Page\b(?!s)/', $obj['dict'])) {
                $fallback[] = [
                    'dict' => $obj['dict'],
                    'resources' => [$this->extractResourcesDict($obj['dict'], $objects)],
                ];
            }
        }

        return $fallback;
    }

    protected function walkPageTree(int $nodeId, array $objects, array $inheritedResources, int $depth = 0): array
    {
        if ($depth > 16 || !isset($objects[$nodeId])) {
            return [];
        }

        $dict = $objects[$nodeId]['dict'];
        $res = $this->extractResourcesDict($dict, $objects);
        $resourceChain = $res !== '' ? array_merge($inheritedResources, [$res]) : $inheritedResources;

        if (preg_match('/\/Type\s*\/Pages\b/', $dict) && preg_match('/\/Kids\s*\[(.*?)\]/s', $dict, $km)) {
            $pages = [];
            if (preg_match_all('/(\d+)\s+\d+\s+R/', $km[1], $kids)) {
                foreach ($kids[1] as $kidId) {
                    $pages = array_merge($pages, $this->walkPageTree((int) $kidId, $objects, $resourceChain, $depth + 1));
                }
            }

            return $pages;
        }

        if (preg_match('/\/Type\s*\/Page\b(?!s)/', $dict) || preg_match('/\/Contents\b/', $dict)) {
            return [[
                'dict' => $dict,
                'resources' => $resourceChain,
            ]];
        }

        return [];
    }

    protected function extractResourcesDict(string $dict, array $objects): string
    {
        if (preg_match('/\/Resources\s+(\d+)\s+\d+\s+R/', $dict, $m)) {
            return $objects[(int) $m[1]]['dict'] ?? '';
        }

        $pos = strpos($dict, '/Resources');
        if ($pos === false) {
            return '';
        }
        $sub = substr($dict, $pos);
        $start = strpos($sub, '<<');
        if ($start === false) {
            return '';
        }

        $depth = 0;
        $len = strlen($sub);
        for ($i = $start; $i < $len - 1; $i++) {
            if ($sub[$i] === '<' && $sub[$i + 1] === '<') {
                $depth++;
                $i++;
            } elseif ($sub[$i] === '>' && $sub[$i + 1] === '>') {
                $depth--;
                $i++;
                if ($depth === 0) {
                    return substr($sub, $start, $i - $start + 1);
                }
            }
        }

        return $sub;
    }

    protected function resolvePageFonts(array $resourceChain, array $objects, array $fontCMaps): array
    {
        $fonts = [];
        foreach ($resourceChain as $resDict) {
            if ($resDict === '') {
                continue;
            }
            if (preg_match('/\/Font\s+(\d+)\s+\d+\s+R/', $resDict, $fm)) {
                $fontSub = $objects[(int) $fm[1]]['dict'] ?? '';
            } elseif (preg_match('/\/Font\s*<<(.*?)>>/s', $resDict, $fm)) {
                $fontSub = $fm[1];
            } else {
                $fontSub = '';
            }

            if ($fontSub !== '' && preg_match_all('/\/([A-Za-z0-9_+.-]+)\s+(\d+)\s+\d+\s+R/', $fontSub, $frefs, PREG_SET_ORDER)) {
                foreach ($frefs as $fr) {
                    $fontId = (int) $fr[2];
                    $fonts[$fr[1]] = $fontCMaps[$fontId] ?? null;
                }
            }
        }

        return $fonts;
    }

    protected function resolvePageXObjects(array $resourceChain, array $objects): array
    {
        $xobjs = [];
        foreach ($resourceChain as $resDict) {
            if ($resDict === '') {
                continue;
            }
            if (preg_match('/\/XObject\s+(\d+)\s+\d+\s+R/', $resDict, $xm)) {
                $xSub = $objects[(int) $xm[1]]['dict'] ?? '';
            } elseif (preg_match('/\/XObject\s*<<(.*?)>>/s', $resDict, $xm)) {
                $xSub = $xm[1];
            } else {
                $xSub = '';
            }

            if ($xSub !== '' && preg_match_all('/\/([A-Za-z0-9_+.-]+)\s+(\d+)\s+\d+\s+R/', $xSub, $xrefs, PREG_SET_ORDER)) {
                foreach ($xrefs as $xr) {
                    $xobjs[$xr[1]] = (int) $xr[2];
                }
            }
        }

        return $xobjs;
    }

    protected function readPageContentsStream(string $pageDict, array $objects): string
    {
        $ids = [];
        if (preg_match('/\/Contents\s+(\d+)\s+\d+\s+R/', $pageDict, $cm)) {
            $ids[] = (int) $cm[1];
        } elseif (preg_match('/\/Contents\s*\[(.*?)\]/s', $pageDict, $cm)) {
            if (preg_match_all('/(\d+)\s+\d+\s+R/', $cm[1], $cids)) {
                $ids = array_map('intval', $cids[1]);
            }
        }

        $combined = '';
        foreach ($ids as $cid) {
            if (!empty($objects[$cid]['stream'])) {
                $combined .= $objects[$cid]['stream'] . "\n";
            }
        }

        return $combined;
    }

    /**
     * Walk PDF graphics and text operators, resolving physical page coordinates (x, y) via CTM & Tm.
     */
    protected function extractPositionedFragments(
        string $stream,
        array $pageFonts,
        array $pageXObjects,
        array $objects,
        array $fontCMaps,
        array $initialCtm = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0],
        int $depth = 0
    ): array {
        if ($depth > 5) {
            return [];
        }

        $tokens = $this->tokenizeContentStream($stream);
        $fragments = [];

        $ctmStack = [];
        $ctm = $initialCtm;
        $inBt = false;
        $tm = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        $tlm = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
        $leading = 0.0;
        $fontName = null;
        $rawFontSize = 10.0;
        $curText = '';
        $curStartPt = null;
        $curFontSize = 10.0;
        $lastLineY = null;

        $flush = function () use (&$curText, &$curStartPt, &$curFontSize, &$fragments): void {
            $clean = trim(preg_replace('/\s+/', ' ', $curText));
            if ($clean !== '' && $curStartPt !== null) {
                $fragments[] = [
                    'x' => $curStartPt[0],
                    'y' => $curStartPt[1],
                    'fontSize' => max(4.0, abs($curFontSize)),
                    'text' => $clean,
                ];
            }
            $curText = '';
            $curStartPt = null;
        };

        $operands = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $tok = $tokens[$i];
            if ($tok['type'] !== 'op') {
                $operands[] = $tok;
                continue;
            }

            $op = $tok['value'];

            switch ($op) {
                case 'q':
                    $ctmStack[] = $ctm;
                    break;

                case 'Q':
                    if (!empty($ctmStack)) {
                        $ctm = array_pop($ctmStack);
                    }
                    break;

                case 'cm':
                    if (count($operands) >= 6) {
                        $args = array_slice($operands, -6);
                        $m = array_map(static fn ($t) => (float) $t['value'], $args);
                        $ctm = $this->multiplyMatrix($m, $ctm);
                    }
                    break;

                case 'Do':
                    if (!empty($operands)) {
                        $xName = ltrim((string) end($operands)['value'], '/');
                        if (isset($pageXObjects[$xName])) {
                            $xObj = $objects[$pageXObjects[$xName]] ?? null;
                            if ($xObj && $xObj['stream'] !== null && preg_match('/\/Subtype\s*\/Form\b/', $xObj['dict'])) {
                                $formRes = $this->extractResourcesDict($xObj['dict'], $objects);
                                $formFonts = $formRes !== ''
                                    ? array_merge($pageFonts, $this->resolvePageFonts([$formRes], $objects, $fontCMaps))
                                    : $pageFonts;
                                $formXObjs = $formRes !== ''
                                    ? array_merge($pageXObjects, $this->resolvePageXObjects([$formRes], $objects))
                                    : $pageXObjects;
                                $formCtm = $ctm;
                                if (preg_match('/\/Matrix\s*\[\s*([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s*\]/', $xObj['dict'], $mm)) {
                                    $fm = [(float) $mm[1], (float) $mm[2], (float) $mm[3], (float) $mm[4], (float) $mm[5], (float) $mm[6]];
                                    $formCtm = $this->multiplyMatrix($fm, $ctm);
                                }
                                $fragments = array_merge(
                                    $fragments,
                                    $this->extractPositionedFragments($xObj['stream'], $formFonts, $formXObjs, $objects, $fontCMaps, $formCtm, $depth + 1)
                                );
                            }
                        }
                    }
                    break;

                case 'BT':
                    $inBt = true;
                    $tm = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];
                    $tlm = $tm;
                    $curText = '';
                    $curStartPt = null;
                    $lastLineY = null;
                    break;

                case 'ET':
                    $flush();
                    $inBt = false;
                    break;

                case 'Tf':
                    if ($inBt && count($operands) >= 2) {
                        $fontName = ltrim((string) $operands[count($operands) - 2]['value'], '/');
                        $rawFontSize = (float) $operands[count($operands) - 1]['value'];
                    }
                    break;

                case 'TL':
                    if ($inBt && !empty($operands)) {
                        $leading = (float) end($operands)['value'];
                    }
                    break;

                case 'Tm':
                    if ($inBt && count($operands) >= 6) {
                        $args = array_slice($operands, -6);
                        $newTm = array_map(static fn ($t) => (float) $t['value'], $args);
                        $pt = $this->applyMatrix($newTm[4], $newTm[5], $ctm);
                        if ($curText !== '' && ($lastLineY === null || abs($pt[1] - $lastLineY) >= 1.5)) {
                            $flush();
                        }
                        $tm = $newTm;
                        $tlm = $newTm;
                        if ($curStartPt === null) {
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        }
                    }
                    break;

                case 'Td':
                case 'TD':
                    if ($inBt && count($operands) >= 2) {
                        $tx = (float) $operands[count($operands) - 2]['value'];
                        $ty = (float) $operands[count($operands) - 1]['value'];
                        if ($op === 'TD') {
                            $leading = -$ty;
                        }
                        $tlm = $this->multiplyMatrix([1.0, 0.0, 0.0, 1.0, $tx, $ty], $tlm);
                        $tm = $tlm;
                        $pt = $this->applyMatrix($tm[4], $tm[5], $ctm);
                        if ($curText !== '' && ($lastLineY === null || abs($pt[1] - $lastLineY) >= 1.5)) {
                            $flush();
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        } elseif ($curStartPt === null) {
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        }
                    }
                    break;

                case 'T*':
                case "'":
                case '"':
                    if ($inBt) {
                        $tlm = $this->multiplyMatrix([1.0, 0.0, 0.0, 1.0, 0.0, -$leading], $tlm);
                        $tm = $tlm;
                        $pt = $this->applyMatrix($tm[4], $tm[5], $ctm);
                        if ($curText !== '' && ($lastLineY === null || abs($pt[1] - $lastLineY) >= 1.5)) {
                            $flush();
                        }
                        if ($curStartPt === null) {
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        }
                        if (($op === "'" || $op === '"') && !empty($operands)) {
                            $arg = end($operands);
                            $curText .= $this->decodeTextToken($arg, $pageFonts[$fontName] ?? null);
                        }
                    }
                    break;

                case 'Tj':
                    if ($inBt && !empty($operands)) {
                        if ($curStartPt === null) {
                            $pt = $this->applyMatrix($tm[4], $tm[5], $ctm);
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        }
                        $curText .= $this->decodeTextToken(end($operands), $pageFonts[$fontName] ?? null);
                    }
                    break;

                case 'TJ':
                    if ($inBt && !empty($operands)) {
                        if ($curStartPt === null) {
                            $pt = $this->applyMatrix($tm[4], $tm[5], $ctm);
                            $curStartPt = $pt;
                            $lastLineY = $pt[1];
                            $curFontSize = $this->effectiveFontSize($rawFontSize, $tm, $ctm);
                        }
                        $arr = end($operands);
                        if ($arr['type'] === 'array') {
                            $cmap = $pageFonts[$fontName] ?? null;
                            foreach ($arr['value'] as $item) {
                                if ($item['type'] === 'hex' || $item['type'] === 'str') {
                                    $curText .= $this->decodeTextToken($item, $cmap);
                                } elseif ($item['type'] === 'num' && (float) $item['value'] < -250) {
                                    $curText .= ' ';
                                }
                            }
                        }
                    }
                    break;
            }

            $operands = [];
        }

        $flush();

        return $fragments;
    }

    /**
     * Tokenize a decompressed PDF content stream safely handling literals, hex strings, arrays, and names.
     */
    protected function tokenizeContentStream(string $stream): array
    {
        $tokens = [];
        $len = strlen($stream);
        $i = 0;

        while ($i < $len) {
            $ch = $stream[$i];

            // Whitespace
            if (ctype_space($ch)) {
                $i++;
                continue;
            }

            // Comment
            if ($ch === '%') {
                while ($i < $len && $stream[$i] !== "\n" && $stream[$i] !== "\r") {
                    $i++;
                }
                continue;
            }

            // Dictionary << ... >> (skip inline dicts if encountered)
            if ($ch === '<' && $i + 1 < $len && $stream[$i + 1] === '<') {
                $depth = 1;
                $i += 2;
                while ($i < $len && $depth > 0) {
                    if ($stream[$i] === '<' && $i + 1 < $len && $stream[$i + 1] === '<') {
                        $depth++;
                        $i += 2;
                    } elseif ($stream[$i] === '>' && $i + 1 < $len && $stream[$i + 1] === '>') {
                        $depth--;
                        $i += 2;
                    } else {
                        $i++;
                    }
                }
                continue;
            }

            // Hex string <...>
            if ($ch === '<') {
                $end = strpos($stream, '>', $i + 1);
                if ($end === false) {
                    break;
                }
                $tokens[] = [
                    'type' => 'hex',
                    'value' => substr($stream, $i + 1, $end - $i - 1),
                ];
                $i = $end + 1;
                continue;
            }

            // Literal string (...)
            if ($ch === '(') {
                $i++;
                $buf = '';
                $parenDepth = 1;
                while ($i < $len && $parenDepth > 0) {
                    $c = $stream[$i];
                    if ($c === '\\' && $i + 1 < $len) {
                        $next = $stream[$i + 1];
                        if ($next === "\r" || $next === "\n") {
                            $i += ($next === "\r" && $i + 2 < $len && $stream[$i + 2] === "\n") ? 3 : 2;
                            continue;
                        }
                        if (ctype_digit($next) && $next < '8') {
                            $oct = $next;
                            if ($i + 2 < $len && $stream[$i + 2] >= '0' && $stream[$i + 2] < '8') {
                                $oct .= $stream[$i + 2];
                                if ($i + 3 < $len && $stream[$i + 3] >= '0' && $stream[$i + 3] < '8') {
                                    $oct .= $stream[$i + 3];
                                }
                            }
                            $buf .= chr((int) octdec($oct));
                            $i += 1 + strlen($oct);
                            continue;
                        }
                        $buf .= match ($next) {
                            'n' => "\n",
                            'r' => "\r",
                            't' => "\t",
                            'b' => "\x08",
                            'f' => "\f",
                            default => $next,
                        };
                        $i += 2;
                        continue;
                    }
                    if ($c === '(') {
                        $parenDepth++;
                        $buf .= $c;
                        $i++;
                        continue;
                    }
                    if ($c === ')') {
                        $parenDepth--;
                        if ($parenDepth > 0) {
                            $buf .= $c;
                        }
                        $i++;
                        continue;
                    }
                    $buf .= $c;
                    $i++;
                }
                $tokens[] = ['type' => 'str', 'value' => $buf];
                continue;
            }

            // Array [...] for TJ
            if ($ch === '[') {
                $i++;
                $arrItems = [];
                while ($i < $len) {
                    while ($i < $len && ctype_space($stream[$i])) {
                        $i++;
                    }
                    if ($i >= $len || $stream[$i] === ']') {
                        $i++;
                        break;
                    }
                    if ($stream[$i] === '<') {
                        $end = strpos($stream, '>', $i + 1);
                        if ($end === false) {
                            break;
                        }
                        $arrItems[] = ['type' => 'hex', 'value' => substr($stream, $i + 1, $end - $i - 1)];
                        $i = $end + 1;
                    } elseif ($stream[$i] === '(') {
                        // Parse nested literal inside array cleanly via small slice
                        $startParen = $i;
                        $i++;
                        $buf = '';
                        $depth = 1;
                        while ($i < $len && $depth > 0) {
                            $c = $stream[$i];
                            if ($c === '\\' && $i + 1 < $len) {
                                $buf .= match ($stream[$i + 1]) {
                                    'n' => "\n", 'r' => "\r", 't' => "\t",
                                    default => $stream[$i + 1],
                                };
                                $i += 2;
                                continue;
                            }
                            if ($c === '(') {
                                $depth++;
                            } elseif ($c === ')') {
                                $depth--;
                                if ($depth === 0) {
                                    $i++;
                                    break;
                                }
                            }
                            $buf .= $c;
                            $i++;
                        }
                        $arrItems[] = ['type' => 'str', 'value' => $buf];
                    } else {
                        $start = $i;
                        while ($i < $len && !ctype_space($stream[$i]) && !in_array($stream[$i], [']', '<', '('], true)) {
                            $i++;
                        }
                        $val = substr($stream, $start, $i - $start);
                        $arrItems[] = ['type' => is_numeric($val) ? 'num' : 'word', 'value' => $val];
                    }
                }
                $tokens[] = ['type' => 'array', 'value' => $arrItems];
                continue;
            }

            // /Name
            if ($ch === '/') {
                $start = $i;
                $i++;
                while ($i < $len && !ctype_space($stream[$i]) && !in_array($stream[$i], ['/', '<', '>', '(', ')', '[', ']', '%'], true)) {
                    $i++;
                }
                $tokens[] = ['type' => 'name', 'value' => substr($stream, $start, $i - $start)];
                continue;
            }

            // Number or operator word
            $start = $i;
            while ($i < $len && !ctype_space($stream[$i]) && !in_array($stream[$i], ['/', '<', '>', '(', ')', '[', ']', '%'], true)) {
                $i++;
            }
            $word = substr($stream, $start, $i - $start);
            if ($word === '') {
                $i++;
                continue;
            }
            if (is_numeric($word)) {
                $tokens[] = ['type' => 'num', 'value' => $word];
            } else {
                $tokens[] = ['type' => 'op', 'value' => $word];
            }
        }

        return $tokens;
    }

    protected function decodeTextToken(array $token, ?array $cmap): string
    {
        if ($token['type'] === 'hex') {
            $hex = preg_replace('/\s+/', '', (string) $token['value']);
            if ($hex === '') {
                return '';
            }
            if ($cmap && !empty($cmap['map'])) {
                $digits = $cmap['hex_digits'];
                $map = $cmap['map'];
                $out = '';
                for ($j = 0; $j + $digits <= strlen($hex); $j += $digits) {
                    $code = hexdec(substr($hex, $j, $digits));
                    $out .= $map[$code] ?? '';
                }

                return $out;
            }

            $rawBytes = @pack('H*', strlen($hex) % 2 === 0 ? $hex : '0' . $hex);

            return $rawBytes !== false ? $this->winAnsiToUtf8($rawBytes) : '';
        }

        if ($token['type'] === 'str') {
            $raw = (string) $token['value'];
            if ($cmap && !empty($cmap['map'])) {
                $map = $cmap['map'];
                $bytesPerChar = (int) (($cmap['hex_digits'] ?? 2) / 2);
                $len = strlen($raw);
                $mapped = '';
                $hit = false;
                if ($bytesPerChar === 2 && $len % 2 === 0) {
                    for ($j = 0; $j < $len; $j += 2) {
                        $code = (ord($raw[$j]) << 8) | ord($raw[$j + 1]);
                        if (isset($map[$code])) {
                            $mapped .= $map[$code];
                            $hit = true;
                        }
                    }
                    if ($hit) {
                        return $mapped;
                    }
                } elseif ($bytesPerChar === 1) {
                    for ($j = 0; $j < $len; $j++) {
                        $code = ord($raw[$j]);
                        $mapped .= $map[$code] ?? $this->winAnsiToUtf8($raw[$j]);
                        if (isset($map[$code])) {
                            $hit = true;
                        }
                    }
                    if ($hit) {
                        return $mapped;
                    }
                }
            }

            return $this->winAnsiToUtf8($raw);
        }

        return '';
    }

    protected function winAnsiToUtf8(string $str): string
    {
        if (mb_check_encoding($str, 'UTF-8')) {
            return $str;
        }
        $conv = @mb_convert_encoding($str, 'UTF-8', 'Windows-1252');

        return $conv !== false ? $conv : $str;
    }

    protected function multiplyMatrix(array $m1, array $m2): array
    {
        return [
            $m1[0] * $m2[0] + $m1[1] * $m2[2],
            $m1[0] * $m2[1] + $m1[1] * $m2[3],
            $m1[2] * $m2[0] + $m1[3] * $m2[2],
            $m1[2] * $m2[1] + $m1[3] * $m2[3],
            $m1[4] * $m2[0] + $m1[5] * $m2[2] + $m2[4],
            $m1[4] * $m2[1] + $m1[5] * $m2[3] + $m2[5],
        ];
    }

    protected function applyMatrix(float $x, float $y, array $m): array
    {
        return [
            round($m[0] * $x + $m[2] * $y + $m[4], 2),
            round($m[1] * $x + $m[3] * $y + $m[5], 2),
        ];
    }

    protected function effectiveFontSize(float $rawSize, array $tm, array $ctm): float
    {
        $combined = $this->multiplyMatrix($tm, $ctm);
        $scaleY = hypot($combined[2], $combined[3]);

        return round($rawSize * ($scaleY > 0 ? $scaleY : 1.0), 2);
    }

    /**
     * Detect horizontal boundaries and header baseline of the `NEGARA` / `COUNTRY` column.
     */
    protected function detectNegaraColumn(array $fragments, ?array $fallbackBounds): ?array
    {
        $bestHeader = null;
        $maxRowSiblings = -1;

        foreach ($fragments as $frag) {
            if (!preg_match('/^(NEGARA(\s*\/\s*WILAYAH)?|NAMA\s+NEGARA|COUNTRY(\s*\/\s*TERRITORY)?)$/i', trim($frag['text']))) {
                continue;
            }
            $siblings = 0;
            foreach ($fragments as $other) {
                if ($other !== $frag && abs($other['y'] - $frag['y']) <= 12.0 && abs($other['x'] - $frag['x']) > 15.0) {
                    $siblings++;
                }
            }
            if ($siblings > $maxRowSiblings) {
                $maxRowSiblings = $siblings;
                $bestHeader = $frag;
            }
        }

        if ($bestHeader !== null && $maxRowSiblings >= 1) {
            $negaraX = $bestHeader['x'];
            $headerY = $bestHeader['y'];

            $leftNeighborMaxX = null;
            $nextColX = null;
            foreach ($fragments as $other) {
                if (abs($other['y'] - $headerY) > 12.0) {
                    continue;
                }
                if ($other['x'] < $negaraX - 18.0) {
                    $leftNeighborMaxX = $leftNeighborMaxX === null ? $other['x'] : max($leftNeighborMaxX, $other['x']);
                } elseif ($other['x'] > $negaraX + 18.0) {
                    $nextColX = $nextColX === null ? $other['x'] : min($nextColX, $other['x']);
                }
            }

            $minX = $leftNeighborMaxX !== null ? ($leftNeighborMaxX + $negaraX) / 2 : 0.0;
            $maxX = $nextColX !== null ? ($nextColX - 6.0) : ($negaraX + 140.0);

            // Refine right boundary if column 2 body items are left-aligned slightly before centered col 2 header
            if ($nextColX !== null && $nextColX - $negaraX > 45.0) {
                $bodyAfterNegara = array_filter(
                    $fragments,
                    static fn ($f) => $f['y'] < $headerY - 4.0 && $f['x'] > $negaraX + 22.0 && $f['x'] < $nextColX
                );
                if (!empty($bodyAfterNegara)) {
                    $minCol2X = min(array_map(static fn ($f) => $f['x'], $bodyAfterNegara));
                    if ($minCol2X > $negaraX + 25.0) {
                        $maxX = min($maxX, $minCol2X - 4.0);
                    }
                }
            }

            return [
                'min_x' => $minX,
                'max_x' => $maxX,
                'header_y' => $headerY,
            ];
        }

        if ($fallbackBounds !== null) {
            return [
                'min_x' => $fallbackBounds['min_x'],
                'max_x' => $fallbackBounds['max_x'],
                'header_y' => 1e9,
            ];
        }

        return null;
    }

    /**
     * Extract clean cell values from the identified `NEGARA` column, joining wrapped cell lines
     * and ignoring footers/legends and WHO regional section headers.
     */
    protected function extractColumnValues(array $fragments, array $colInfo): array
    {
        // Detect footer / legend boundary (Keterangan:, Diterbitkan oleh:, Catatan:, Note:, Sumber:)
        $footerY = null;
        foreach ($fragments as $frag) {
            if ($frag['y'] < $colInfo['header_y'] && preg_match('/^(Keterangan|Diterbitkan\s+oleh|Catatan|Source|Sumber)\s*:/i', $frag['text'])) {
                $footerY = $footerY === null ? $frag['y'] : max($footerY, $frag['y']);
            }
        }

        $colFrags = array_values(array_filter($fragments, static function ($frag) use ($colInfo, $footerY) {
            if ($frag['x'] < $colInfo['min_x'] || $frag['x'] >= $colInfo['max_x']) {
                return false;
            }
            if ($frag['y'] >= $colInfo['header_y'] - 3.0) {
                return false;
            }
            if ($footerY !== null && $frag['y'] <= $footerY + 1.0) {
                return false;
            }

            return true;
        }));

        usort($colFrags, static fn ($a, $b) => $b['y'] <=> $a['y'] ?: $a['x'] <=> $b['x']);

        // Join wrapped lines inside the same table cell (vertical gap <= 1.45 * fontSize)
        $merged = [];
        foreach ($colFrags as $frag) {
            $txt = trim($frag['text']);
            if ($txt === '' || $txt === '-') {
                continue;
            }

            $lastIdx = count($merged) - 1;
            if ($lastIdx >= 0) {
                $prev = $merged[$lastIdx];
                $dy = $prev['last_y'] - $frag['y'];
                $wrapThreshold = max(8.2, min(14.5, $prev['font_size'] * 1.45));
                if ($dy >= 0 && $dy <= $wrapThreshold && abs($prev['x'] - $frag['x']) <= 18.0) {
                    $merged[$lastIdx]['text'] .= ' ' . $txt;
                    $merged[$lastIdx]['last_y'] = $frag['y'];
                    continue;
                }
            }

            $merged[] = [
                'text' => $txt,
                'x' => $frag['x'],
                'last_y' => $frag['y'],
                'font_size' => $frag['fontSize'] ?? 8.0,
            ];
        }

        $results = [];
        foreach ($merged as $row) {
            $clean = trim(preg_replace('/\s+/', ' ', $row['text']));
            if ($clean === '' || $clean === '-' || is_numeric($clean)) {
                continue;
            }
            if (preg_match('/^(NEGARA|COUNTRY|WHO Regional Office|•)/i', $clean)) {
                continue;
            }
            $results[] = $clean;
        }

        return $results;
    }

    /**
     * Reset all countries to default ShipHealthSurveillance::QUARANTINE_COUNTRIES baseline.
     */
    public function resetToDefaults(?int $userId = null): void
    {
        DB::transaction(function () use ($userId) {
            $now = now();
            foreach (ShipHealthSurveillance::QUARANTINE_COUNTRIES as $name => $isRed) {
                QuarantineCountry::updateOrCreate(
                    ['name' => $name],
                    [
                        'is_red_zone' => (bool) $isRed,
                        'source_label' => null,
                        'last_imported_at' => $now,
                        'updated_by' => $userId,
                    ]
                );
            }
            QuarantineCountry::clearCache();
        });
    }

    protected function seedBaselineCountries(): void
    {
        $now = now();
        $rows = [];
        foreach (ShipHealthSurveillance::QUARANTINE_COUNTRIES as $name => $isRed) {
            $rows[] = [
                'name' => $name,
                'is_red_zone' => (bool) $isRed,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('quarantine_countries')->insertOrIgnore($chunk);
        }
        QuarantineCountry::clearCache();
    }
}
