<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ShipSanitationInspection extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'ship_sanitation_inspections';

    protected $fillable = [
        'registration_no',
        'pelabuhan',
        'tanggal',
        'sertifikat_mencatat',
        'imo_no',
        'jenis_kapal',
        'detail_jenis_kapal',
        'nama_kapal',
        'bendera',
        'berat_gt',
        'kondisi_palka',
        'nama_pemeriksa',
        'alamat_pemeriksa',
        'areas',
        'pejabat_penerbit',
        'keterangan',
        'tanda_tangan',
        'foto_geotagging',
        'latitude',
        'longitude',
        'lokasi_geotag',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'berat_gt' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'areas' => 'array',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_areas',
    ];

    /**
     * Standard 32 areas for ship sanitation inspection based on WHO IHR 2005.
     */
    public const STANDARD_AREAS = [
        ['no' => 1, 'nama' => 'Dapur', 'kategori' => 'Pangan & Dapur'],
        ['no' => 2, 'nama' => 'Ruang rakit makanan', 'kategori' => 'Pangan & Dapur'],
        ['no' => 3, 'nama' => 'Gudang', 'kategori' => 'Pangan & Dapur'],
        ['no' => 4, 'nama' => 'Palka', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 5, 'nama' => 'Ruang tidur', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 6, 'nama' => 'ABK', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 7, 'nama' => 'Perwira', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 8, 'nama' => 'Penumpang', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 9, 'nama' => 'Geladak', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 10, 'nama' => 'Air bersih', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 11, 'nama' => 'Limbah cair', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 12, 'nama' => 'Tangki air Ballast', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 13, 'nama' => 'Sampah medik dan sampah padat', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 14, 'nama' => 'Air permukaan', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 15, 'nama' => 'Kamar mesin', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 16, 'nama' => 'Fasilitas medik', 'kategori' => 'Medik & Keselamatan'],
        ['no' => 17, 'nama' => 'Area lainnya', 'kategori' => 'Lainnya'],
        ['no' => 18, 'nama' => 'Sumber Makanan', 'kategori' => 'Pangan & Dapur'],
        ['no' => 19, 'nama' => 'Penyimpanan Makanan', 'kategori' => 'Pangan & Dapur'],
        ['no' => 20, 'nama' => 'Penyiapan Makanan', 'kategori' => 'Pangan & Dapur'],
        ['no' => 21, 'nama' => 'Penyajian Makanan', 'kategori' => 'Pangan & Dapur'],
        ['no' => 22, 'nama' => 'Sumber Air', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 23, 'nama' => 'Penyimpanan Air', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 24, 'nama' => 'Distribusi Air', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 25, 'nama' => 'Penanganan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 26, 'nama' => 'Perlakuan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 27, 'nama' => 'Pembuangan Sampah', 'kategori' => 'Sistem Air & Sanitasi'],
        ['no' => 28, 'nama' => 'Pengoperasian Kolam Renang', 'kategori' => 'Akomodasi & Palka'],
        ['no' => 29, 'nama' => 'Perlengkapan dan alat-alat medis', 'kategori' => 'Medik & Keselamatan'],
        ['no' => 30, 'nama' => 'Pengoperasian Fasilitas Medik', 'kategori' => 'Medik & Keselamatan'],
        ['no' => 31, 'nama' => 'Obat-obatan', 'kategori' => 'Medik & Keselamatan'],
        ['no' => 32, 'nama' => 'Bagian lainnya yang diperiksa', 'kategori' => 'Lainnya'],
    ];

    /**
     * Always returns full 32 areas normalized for certificate and display.
     */
    public function getFormattedAreasAttribute(): array
    {
        $raw = $this->areas ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }

        // If raw already has 32 items, normalize and return
        if (count($raw) >= 32) {
            $normalized = [];
            foreach ($raw as $idx => $it) {
                if (!is_array($it)) continue;
                $no = $it['no'] ?? ($idx + 1);
                $std = self::STANDARD_AREAS[$no - 1] ?? ($self::STANDARD_AREAS[$idx] ?? null);
                $normalized[] = [
                    'no' => $no,
                    'nama' => !empty($it['nama']) ? $it['nama'] : (!empty($it['area_name']) ? $it['area_name'] : ($std['nama'] ?? "Area #{$no}")),
                    'kategori' => !empty($it['kategori']) ? $it['kategori'] : ($std['kategori'] ?? '-'),
                    'status' => !empty($it['status']) ? $it['status'] : (!empty($it['result']) ? $it['result'] : 'Tidak Ditemukan Faktor Risiko'),
                    'keterangan' => !empty($it['keterangan']) ? $it['keterangan'] : (!empty($it['finding']) ? $it['finding'] : (!empty($it['komentar']) ? $it['komentar'] : 'Nihil faktor risiko / memenuhi syarat')),
                    'sampel' => !empty($it['sampel']) ? $it['sampel'] : (!empty($it['sample']) ? $it['sample'] : 'Sesuai Baku Mutu'),
                    'bukti' => !empty($it['bukti']) ? $it['bukti'] : (!empty($it['finding']) ? $it['finding'] : '-'),
                    'dokumen' => $it['dokumen'] ?? '-',
                    'tindakan' => $it['tindakan'] ?? '-',
                    'tanggal_ulang' => $it['tanggal_ulang'] ?? '-',
                    'komentar' => !empty($it['komentar']) ? $it['komentar'] : (!empty($it['keterangan']) ? $it['keterangan'] : '-'),
                ];
            }
            return $normalized;
        }

        // If fewer than 32, map existing and fill the rest so all 32 areas are present
        $existingByName = [];
        $existingByIndex = array_values($raw);

        foreach ($raw as $it) {
            if (!is_array($it)) continue;
            $nameKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $it['nama'] ?? $it['area_name'] ?? ''));
            if ($nameKey) {
                $existingByName[$nameKey] = $it;
            }
        }

        $result = [];
        foreach (self::STANDARD_AREAS as $i => $std) {
            $matched = null;
            $stdClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $std['nama']));

            foreach ($existingByName as $k => $item) {
                if (str_contains($k, $stdClean) || str_contains($stdClean, $k)) {
                    $matched = $item;
                    break;
                }
            }

            if (!$matched && isset($existingByIndex[$i])) {
                $matched = $existingByIndex[$i];
            }

            if ($matched) {
                $result[] = [
                    'no' => $std['no'],
                    'nama' => !empty($matched['nama']) ? $matched['nama'] : (!empty($matched['area_name']) ? $matched['area_name'] : $std['nama']),
                    'kategori' => !empty($matched['kategori']) ? $matched['kategori'] : $std['kategori'],
                    'status' => !empty($matched['status']) ? $matched['status'] : (!empty($matched['result']) ? $matched['result'] : 'Tidak Ditemukan Faktor Risiko'),
                    'keterangan' => !empty($matched['keterangan']) ? $matched['keterangan'] : (!empty($matched['finding']) ? $matched['finding'] : (!empty($matched['komentar']) ? $matched['komentar'] : 'Nihil faktor risiko / memenuhi syarat')),
                    'sampel' => !empty($matched['sampel']) ? $matched['sampel'] : (!empty($matched['sample']) ? $matched['sample'] : 'Sesuai Baku Mutu'),
                    'bukti' => !empty($matched['bukti']) ? $matched['bukti'] : (!empty($matched['finding']) ? $matched['finding'] : '-'),
                    'dokumen' => $matched['dokumen'] ?? '-',
                    'tindakan' => $matched['tindakan'] ?? '-',
                    'tanggal_ulang' => $matched['tanggal_ulang'] ?? '-',
                    'komentar' => !empty($matched['komentar']) ? $matched['komentar'] : (!empty($matched['keterangan']) ? $matched['keterangan'] : '-'),
                ];
            } else {
                $result[] = [
                    'no' => $std['no'],
                    'nama' => $std['nama'],
                    'kategori' => $std['kategori'],
                    'status' => 'Tidak Ditemukan Faktor Risiko',
                    'keterangan' => 'Nihil faktor risiko / kondisi higienis',
                    'sampel' => 'Sesuai Baku Mutu',
                    'bukti' => '-',
                    'dokumen' => '-',
                    'tindakan' => '-',
                    'tanggal_ulang' => '-',
                    'komentar' => '-',
                ];
            }
        }

        return $result;
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Inspeksi sanitasi kapal {$this->nama_kapal} ({$this->registration_no}) telah di-{$eventName}");
    }

    /**
     * User who verified the inspection.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope a query to search by ship name, IMO, registration number, port, or inspector.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nama_kapal', 'like', "%{$term}%")
              ->orWhere('registration_no', 'like', "%{$term}%")
              ->orWhere('imo_no', 'like', "%{$term}%")
              ->orWhere('pelabuhan', 'like', "%{$term}%")
              ->orWhere('bendera', 'like', "%{$term}%")
              ->orWhere('nama_pemeriksa', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by port/wilker.
     */
    public function scopeFilterPelabuhan($query, ?string $pelabuhan)
    {
        if (empty($pelabuhan)) {
            return $query;
        }

        return $query->where('pelabuhan', $pelabuhan);
    }

    /**
     * Scope a query to filter by certificate type (Pembebasan vs Pengendalian).
     */
    public function scopeFilterSertifikat($query, ?string $type)
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('sertifikat_mencatat', $type);
    }

    /**
     * Scope a query to filter by verification status.
     */
    public function scopeFilterStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}

