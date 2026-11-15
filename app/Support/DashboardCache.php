<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class DashboardCache
{
    private const VERSION_KEY = 'sadaya.dashboard.version';

    private const OFFICES_KEY = 'sadaya.bkk_offices.v1';

    /**
     * Read or compute a versioned dashboard payload that automatically invalidates on write.
     */
    public static function remember(string $key, int $ttlSeconds, Closure $resolver): mixed
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return Cache::remember("sadaya.dash.v{$version}.{$key}", $ttlSeconds, $resolver);
    }

    /**
     * Remember BKK office directory lookup without repeated schema probes.
     */
    public static function rememberOffices(Closure $resolver): mixed
    {
        return Cache::remember(self::OFFICES_KEY, 3600, $resolver);
    }

    /**
     * Bump cache version and clear office directory cache after domain mutations.
     */
    public static function invalidate(): void
    {
        $current = (int) Cache::get(self::VERSION_KEY, 1);
        Cache::forever(self::VERSION_KEY, $current + 1);
        Cache::forget(self::OFFICES_KEY);
    }
}
