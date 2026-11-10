<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlInspeksiSab extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_inspeksi_sab';

    protected $fillable = [
        'source_id',
        'prl_sab_id',
        'wilker',
        'tanggal_inspeksi',
        'nama_sab',
        'penanggung_jawab_sab',
        'penanggung_jawab_no_hp_sab',
        'sumber_air_sab',
        'kapasitas_penampungan_sab',
        'nama_pemeriksa',
        'sec_b_satu',
        'sec_b_dua',
        'sec_b_tiga',
        'sec_b_empat',
        'sec_c_satu',
        'sec_c_dua',
        'sec_c_tiga',
        'sec_c_empat',
        'sec_c_lima',
        'sec_c_enam',
        'sec_c_tujuh',
        'sec_c_delapan',
        'sec_c_sembilan',
        'sec_c_sepuluh',
        'hasil',
        'tingkat_risiko',
        'saran_rencana_tindakan',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
        'hasil' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_sab', 'tanggal_inspeksi', 'hasil', 'tingkat_risiko'])
            ->logOnlyDirty()
            ->useLogName('prl_inspeksi_sab');
    }

    public function sab()
    {
        return $this->belongsTo(PrlSab::class, 'prl_sab_id');
    }
}
