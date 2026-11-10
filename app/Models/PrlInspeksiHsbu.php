<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlInspeksiHsbu extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_inspeksi_hsbu';

    protected $fillable = [
        'source_id',
        'prl_hsbu_id',
        'wilker',
        'tanggal_inspeksi',
        'nama_gedung',
        'penanggung_jawab_hsbu',
        'jml_karyawan_hsbu',
        'nama_pemeriksa',
        'komponen_penilaian',
        'total',
        'tingkat_risiko',
        'saran_rencana_tindakan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
        'komponen_penilaian' => 'array',
        'total' => 'float',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_gedung', 'tanggal_inspeksi', 'total', 'tingkat_risiko'])
            ->logOnlyDirty()
            ->useLogName('prl_inspeksi_hsbu');
    }

    public function hsbu()
    {
        return $this->belongsTo(PrlHsbu::class, 'prl_hsbu_id');
    }
}
