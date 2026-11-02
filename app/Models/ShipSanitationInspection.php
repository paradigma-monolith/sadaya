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
}
