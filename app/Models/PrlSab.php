<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlSab extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_sab';

    protected $fillable = [
        'source_id',
        'wilker',
        'nama_sab',
        'penanggung_jawab',
        'penanggung_jawab_no_hp',
        'sumber_air',
        'kapasitas_penampungan',
        'created_by_name',
        'updated_by_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_sab', 'wilker', 'penanggung_jawab', 'sumber_air'])
            ->logOnlyDirty()
            ->useLogName('prl_sab');
    }

    public function inspections()
    {
        return $this->hasMany(PrlInspeksiSab::class, 'prl_sab_id');
    }
}

