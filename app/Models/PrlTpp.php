<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlTpp extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_tpp';

    protected $fillable = [
        'source_id',
        'wilker',
        'nama_tpp',
        'penanggung_jawab',
        'jml_karyawan',
        'kapasitas_produksi',
        'created_by_name',
        'updated_by_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_tpp', 'wilker', 'penanggung_jawab'])
            ->logOnlyDirty()
            ->useLogName('prl_tpp');
    }

    public function inspections()
    {
        return $this->hasMany(PrlInspeksiTpp::class, 'prl_tpp_id');
    }
}
