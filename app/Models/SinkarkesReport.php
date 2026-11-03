<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinkarkesReport extends Model
{
    use HasFactory;

    protected $table = 'sinkarkes_reports';

    protected $fillable = [
        'wilker',
        'year',
        'month',
        'data',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'data' => 'array',
    ];

    /**
     * Relationship to the user who initially created the report.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to the user who last updated the report.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope query to specific Wilker, Year, and optional Month.
     */
    public function scopePeriod($query, string $wilker, int $year, ?int $month = null)
    {
        $query->where('wilker', $wilker)->where('year', $year);
        if ($month !== null) {
            $query->where('month', $month);
        }
        return $query;
    }
}

