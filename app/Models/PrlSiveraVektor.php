<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlSiveraVektor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_sivera_vektor';

    protected $fillable = [
        'source_id',
        'jenis_vektor',
        'wilker',
        'lokasi',
        'tanggal',
        'parameter_utama',
        'nilai_indeks',
        'baku_mutu_status',
        'detail_data',
        'tindakan_pengendalian',
        'created_by_name',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai_indeks' => 'float',
        'detail_data' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['jenis_vektor', 'wilker', 'lokasi', 'nilai_indeks', 'baku_mutu_status'])
            ->logOnlyDirty()
            ->useLogName('prl_sivera_vektor');
    }
}
