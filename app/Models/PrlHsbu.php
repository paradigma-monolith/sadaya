<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlHsbu extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_hsbu';

    protected $fillable = [
        'source_id',
        'wilker',
        'nama_gedung',
        'penanggung_jawab',
        'jml_karyawan',
        'created_by_name',
        'updated_by_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_gedung', 'wilker', 'penanggung_jawab'])
            ->logOnlyDirty()
            ->useLogName('prl_hsbu');
    }

    public function inspections()
    {
        return $this->hasMany(PrlInspeksiHsbu::class, 'prl_hsbu_id');
    }
}

