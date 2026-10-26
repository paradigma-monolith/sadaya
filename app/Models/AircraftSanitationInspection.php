<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AircraftSanitationInspection extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'aircraft_sanitation_inspections';

    protected $fillable = [
        'registration_no',
        'pemilik',
        'tanda_kebangsaan',
        'tanggal',
        'waktu',
        'kabin_penumpang',
        'kompartemen_toilet',
        'galley',
        'dokumen_kesehatan',
        'pembuangan_kotoran',
        'penanganan_air',
        'keadaan_makanan',
        'binatang_pengganggu',
        'surat_sanitasi',
        'kesimpulan',
        'tindakan',
        'catatan_rekomendasi',
        'petugas_kabin_nama',
        'petugas_kabin_ttd',
        'petugas_bkk_nama',
        'petugas_bkk_ttd',
        'latitude',
        'longitude',
        'lokasi_geotag',
        'foto_dokumentasi',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'latitude' => 'float',
        'longitude' => 'float',
        'foto_dokumentasi' => 'array',
        'kabin_penumpang' => 'array',
        'kompartemen_toilet' => 'array',
        'galley' => 'array',
        'dokumen_kesehatan' => 'array',
        'penanganan_air' => 'array',
        'keadaan_makanan' => 'array',
        'binatang_pengganggu' => 'array',
        'verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'registration_no',
                'pemilik',
                'tanda_kebangsaan',
                'tanggal',
                'kesimpulan',
                'tindakan',
                'status',
                'latitude',
                'longitude',
                'lokasi_geotag',
                'petugas_kabin_nama',
                'petugas_bkk_nama',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Inspeksi Sanitasi Pesawat ({$this->registration_no}) {$eventName}");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inspection) {
            if (empty($inspection->registration_no)) {
                $dateCode = now()->format('Ymd');
                $randomCode = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
                $inspection->registration_no = "BKK-AC-{$dateCode}-{$randomCode}";
            }
        });
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('registration_no', 'like', "%{$search}%")
                ->orWhere('pemilik', 'like', "%{$search}%")
                ->orWhere('tanda_kebangsaan', 'like', "%{$search}%")
                ->orWhere('petugas_kabin_nama', 'like', "%{$search}%")
                ->orWhere('petugas_bkk_nama', 'like', "%{$search}%");
        });
    }

    public function scopeFilterKesimpulan($query, ?string $kesimpulan)
    {
        if (!empty($kesimpulan) && in_array($kesimpulan, ['layak', 'tidak_layak'])) {
            return $query->where('kesimpulan', $kesimpulan);
        }
        return $query;
    }

    public function scopeFilterStatus($query, ?string $status)
    {
        if (!empty($status) && in_array($status, ['submitted', 'verified', 'approved', 'rejected'])) {
            return $query->where('status', $status);
        }
        return $query;
    }
}
