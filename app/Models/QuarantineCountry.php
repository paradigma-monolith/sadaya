<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class QuarantineCountry extends Model
{
    public const CACHE_KEY = 'ship_surveillance.quarantine_countries_map';

    protected $table = 'quarantine_countries';

    protected $fillable = [
        'name',
        'is_red_zone',
        'source_label',
        'last_imported_at',
        'updated_by',
    ];

    protected $casts = [
        'is_red_zone' => 'boolean',
        'last_imported_at' => 'datetime',
    ];

    /**
     * Clear cached country map after mutations.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Retrieve associative map of [countryName => isRedZone] sorted alphabetically.
     * Falls back to ShipHealthSurveillance::QUARANTINE_COUNTRIES if table is absent or empty.
     */
    public static function getCountriesMap(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            try {
                if (!Schema::hasTable('quarantine_countries')) {
                    return ShipHealthSurveillance::QUARANTINE_COUNTRIES;
                }

                $rows = self::query()
                    ->orderBy('name')
                    ->pluck('is_red_zone', 'name')
                    ->all();

                if (empty($rows)) {
                    return ShipHealthSurveillance::QUARANTINE_COUNTRIES;
                }

                return array_map(static fn ($v) => (bool) $v, $rows);
            } catch (\Throwable $e) {
                return ShipHealthSurveillance::QUARANTINE_COUNTRIES;
            }
        });
    }

    /**
     * Boot model events to invalidate cache cleanly.
     */
    protected static function booted(): void
    {
        static::saved(static fn () => self::clearCache());
        static::deleted(static fn () => self::clearCache());
    }

    /**
     * User who last updated this record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

