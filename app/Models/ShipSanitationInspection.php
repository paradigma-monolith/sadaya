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

