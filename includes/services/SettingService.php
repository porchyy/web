<?php
/**
 * SettingService - Data Access & Business Logic for System Settings & Metrics
 * 
 * Includes short-lived caching (60s TTL) and instant invalidation on updates.
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/CacheService.php';

class SettingService
{
    private static ?array $memoryCache = null;

    public static function getAll(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        try {
            self::$memoryCache = CacheService::remember('settings_all', 60, function () {
                return q('SELECT skey, svalue FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
            });
        } catch (\Throwable) {
            return [];
        }

        return self::$memoryCache ?: [];
    }

    public static function get(string $key, string $default = ''): string
    {
        $all = self::getAll();
        if (isset($all[$key]) && $all[$key] !== '') {
            return $all[$key];
        }
        return env(strtoupper($key), $default);
    }

    public static function set(string $key, string $value): void
    {
        q('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$key, $value]);
        self::$memoryCache = null;
        CacheService::forget('settings_all');
        CacheService::forget('home_stats');
    }

    public static function getHomeStats(): array
    {
        return CacheService::remember('home_stats', 60, function () {
            $countTeachers = (int) q("SELECT COUNT(*) FROM teachers")->fetchColumn();

            $employmentRate = self::get('stat_employment', '96%');

            return [
                'mode' => 'dynamic',
                'items' => [
                    ['num' => (string) $countTeachers, 'unit' => 'ท่าน', 'label' => 'คณาจารย์ประจำสาขาวิชา'],
                    ['num' => $employmentRate, 'unit' => '', 'label' => 'อัตราการได้งานทำหลังจบ'],
                ]
            ];
        });
    }
}
