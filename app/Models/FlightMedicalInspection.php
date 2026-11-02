<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FlightMedicalInspection extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'flight_medical_inspections';

    protected $fillable = [
        'registration_no',
        'type',
        'penerbangan_haji_h',
        'penerbangan_haji_m',
        'embarkasi',
        'nomor_kloter',
        'no_kontak',
        'items_p3k',
        'items_emergensi',
        'foto_geotagging',
        'latitude',
        'longitude',
        'lokasi_geotag',
        'crew_nama',
        'crew_ttd',
        'pelapor_nama',
        'pelapor_ttd',
        'status',
        'keterangan',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'items_p3k' => 'array',
        'items_emergensi' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
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
            ->setDescriptionForEvent(fn(string $eventName) => "Data pemakaian {$this->type} medis kloter {$this->nomor_kloter} ({$this->registration_no}) telah di-{$eventName}");
    }

    /**
     * User who verified the inspection.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope for Alat Kesehatan records.
     */
    public function scopeAlat($query)
    {
        return $query->where('type', 'alat');
    }

    /**
     * Scope for Obat-obatan records.
     */
    public function scopeObat($query)
    {
        return $query->where('type', 'obat');
    }

    /**
     * Search scope for kloter, embarkasi, PIC, registration_no.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nomor_kloter', 'like', "%{$term}%")
              ->orWhere('embarkasi', 'like', "%{$term}%")
              ->orWhere('registration_no', 'like', "%{$term}%")
              ->orWhere('crew_nama', 'like', "%{$term}%")
              ->orWhere('pelapor_nama', 'like', "%{$term}%")
              ->orWhere('no_kontak', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by embarkasi.
     */
    public function scopeFilterEmbarkasi($query, ?string $embarkasi)
    {
        if (empty($embarkasi)) {
            return $query;
        }

        return $query->where('embarkasi', $embarkasi);
    }

    /**
     * Filter by status.
     */
    public function scopeFilterStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
