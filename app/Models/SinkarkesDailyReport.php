<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinkarkesDailyReport extends Model
{
    use HasFactory;

    public const TYPE_KAPAL = 'kapal';
    public const TYPE_PESAWAT = 'pesawat';

    protected $table = 'sinkarkes_daily_reports';

    protected $fillable = [
        'tanggal',
        'wilker',
        'transport_type',
        'metrics',
        'notes',
        'sinkarkes_synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'metrics' => 'array',
        'sinkarkes_synced_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

