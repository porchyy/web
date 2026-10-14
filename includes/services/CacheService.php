<?php
/**
 * CacheService - Short-Lived Caching & Instant Invalidation (TTL 60s–120s)
 * 
 * Complies with RULES.md: Strict 1-2 mins max TTL, zero stale admin data, instant cache busting.
 */

class CacheService
{
    public const DEFAULT_TTL = 60;  // 1 minute default
    public const MAX_TTL     = 120; // 2 minutes maximum limit

    private static ?string $cacheDir = null;
    private static array $memoryCache = [];

    /**
     * Get the directory used for storing cache files
     */
    public static function getCacheDir(): string
    {
        if (self::$cacheDir === null) {
            $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'scratch' . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            self::$cacheDir = $dir;
        }
        return self::$cacheDir;
    }

    /**
     * Build the file path for a cache key
     */
    private static function getFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return self::getCacheDir() . DIRECTORY_SEPARATOR . 'dbt_' . $safeKey . '.cache';
    }

    /**
     * Retrieve an item from the cache
     */
    public static function get(string $key, $default = null)
    {
        // Check in-process memory cache first
        if (isset(self::$memoryCache[$key])) {
            $mem = self::$memoryCache[$key];
            if ($mem['expires_at'] > time()) {
                return $mem['data'];
            }
            unset(self::$memoryCache[$key]);
        }

        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return $default;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $payload = @unserialize($content);
        if (!is_array($payload) || !isset($payload['expires_at'], $payload['data'])) {
            @unlink($file);
            return $default;
        }

        if ($payload['expires_at'] < time()) {
            @unlink($file);
            return $default;
        }

        // Store in memory cache for subsequent requests in the same process
        self::$memoryCache[$key] = $payload;

        return $payload['data'];
    }

    /**
     * Store an item in the cache with a strict TTL (60s–120s max)
     */
    public static function set(string $key, $data, int $ttl = self::DEFAULT_TTL): bool
    {
        // Enforce strict TTL boundary: max 120s, min 5s
        $effectiveTtl = max(5, min($ttl, self::MAX_TTL));
        $expiresAt    = time() + $effectiveTtl;

        $payload = [
            'expires_at' => $expiresAt,
            'created_at' => time(),
            'data'       => $data,
        ];

        self::$memoryCache[$key] = $payload;

        $file = self::getFilePath($key);
        $serialized = serialize($payload);

        return (bool) @file_put_contents($file, $serialized, LOCK_EX);
    }

    /**
     * Get an item from the cache, or execute the callback and store the result
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Bust a single cache key immediately
     */
    public static function forget(string $key): bool
    {
        unset(self::$memoryCache[$key]);
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Bust all cache keys matching a prefix (e.g. 'schedule_grid_')
     */
    public static function forgetPattern(string $prefix): int
    {
        $safePrefix = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $prefix);
        $count = 0;
        $files = glob(self::getCacheDir() . DIRECTORY_SEPARATOR . 'dbt_' . $safePrefix . '*.cache');

        if ($files) {
            foreach ($files as $file) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }

        // Clear memory cache entries matching prefix
        foreach (array_keys(self::$memoryCache) as $k) {
            if (str_starts_with($k, $prefix)) {
                unset(self::$memoryCache[$k]);
            }
        }

        return $count;
    }

    /**
     * Flush all DBT cache entries
     */
    public static function flush(): bool
    {
        self::$memoryCache = [];
        $files = glob(self::getCacheDir() . DIRECTORY_SEPARATOR . 'dbt_*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return true;
    }

    /**
     * Send HTTP caching headers for public pages
     */
    public static function sendPublicHeaders(int $maxAge = self::DEFAULT_TTL, int $sMaxAge = self::MAX_TTL): void
    {
        if (headers_sent()) {
            return;
        }
        $clampedMaxAge  = max(10, min($maxAge, self::MAX_TTL));
        $clampedSMaxAge = max(10, min($sMaxAge, self::MAX_TTL));
        header("Cache-Control: public, max-age={$clampedMaxAge}, s-maxage={$clampedSMaxAge}, stale-while-revalidate=30");
    }

    /**
     * Send HTTP no-cache headers for admin pages
     */
    public static function sendPrivateHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header("Cache-Control: no-store, no-cache, must-revalidate, private");
        header("Pragma: no-cache");
    }
}
