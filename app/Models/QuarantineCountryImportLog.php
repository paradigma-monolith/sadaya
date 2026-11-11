<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuarantineCountryImportLog extends Model
{
    protected $table = 'quarantine_country_import_logs';

    protected $fillable = [
        'filename',
        'mode',
        'total_extracted',
        'matched_count',
        'newly_flagged_count',
        'unflagged_count',
        'added_countries_count',
        'details',
        'imported_by',
    ];

    protected $casts = [
        'total_extracted' => 'integer',
        'matched_count' => 'integer',
        'newly_flagged_count' => 'integer',
        'unflagged_count' => 'integer',
        'added_countries_count' => 'integer',
        'details' => 'array',
    ];

    /**
     * User who performed this import.
     */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}

