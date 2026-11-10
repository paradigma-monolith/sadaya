<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlInspeksiTpp extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_inspeksi_tpp';

    protected $fillable = [
        'source_id',
        'prl_tpp_id',
        'wilker',
        'tanggal_inspeksi',
        'nama_tpp',
        'penanggung_jawab_tpp',
        'jml_karyawan_tpp',
        'kapasitas_produksi_tpp',
        'nama_pemeriksa',
        'sec_a_satu',
        'sec_a_dua',
        'sec_a_tiga',
        'sec_a_skor',
        'sec_b_satu',
        'sec_b_dua',
        'sec_b_tiga',
        'sec_b_skor',
        'sec_c_skor',
        'sec_c_keterangan',
        'sec_d_nilai_inspeksi_sebelumnya',
        'sec_d_skor',
        'hasil',
        'tingkat_risiko',
        'kesimpulan',
        'makanan',
        'saran_rencana_tindakan',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
        'sec_a_skor' => 'integer',
        'sec_b_skor' => 'integer',
        'sec_c_skor' => 'integer',
        'sec_d_skor' => 'integer',
        'hasil' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_tpp', 'tanggal_inspeksi', 'hasil', 'tingkat_risiko'])
            ->logOnlyDirty()
            ->useLogName('prl_inspeksi_tpp');
    }

    public function tpp()
    {
        return $this->belongsTo(PrlTpp::class, 'prl_tpp_id');
    }
}
