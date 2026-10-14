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

        self::$memoryCache = CacheService::remember('settings_all', 60, function () {
            return q('SELECT skey, svalue FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        });

        return self::$memoryCache;
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
            $mode = self::get('stat_mode', 'dynamic');

            if ($mode === 'manual') {
                return [
                    'mode' => 'manual',
                    'items' => [
                        ['num' => self::get('stat_courses', '18'), 'unit' => 'รายวิชา', 'label' => 'หลักสูตรวิชาชีพเฉพาะทาง'],
                        ['num' => self::get('stat_labs', '04'), 'unit' => 'ห้อง', 'label' => 'ห้องปฏิบัติการคอมพิวเตอร์'],
                        ['num' => self::get('stat_employment', '96%'), 'unit' => '', 'label' => 'อัตราการได้งานทำหลังจบ'],
                    ]
                ];
            }

            // Dynamic counts from live DB
            $cntTeachers   = (int) q("SELECT COUNT(*) FROM teachers")->fetchColumn();
            $cntNews       = (int) q("SELECT COUNT(*) FROM news")->fetchColumn();
            $cntActivities = (int) q("SELECT COUNT(*) FROM activities WHERE event_date >= CURDATE()")->fetchColumn();

            return [
                'mode' => 'dynamic',
                'items' => [
                    ['num' => (string) $cntTeachers, 'unit' => 'ท่าน', 'label' => 'คณาจารย์ประจำแผนกวิชา'],
                    ['num' => (string) $cntNews, 'unit' => 'เรื่อง', 'label' => 'ข่าวสารและประกาศเผยแพร่'],
                    ['num' => (string) $cntActivities, 'unit' => 'กิจกรรม', 'label' => 'กิจกรรมและโครงการเร็ว ๆ นี้'],
                ]
            ];
        });
    }
}
