<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this migration from the command line.');
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/services/CacheService.php';

$pdo = db();
$pdo->exec(file_get_contents(__DIR__ . '/../schema/07_class_timetables.sql'));
if ($pdo->query("SHOW COLUMNS FROM classes LIKE 'course_type'")->fetch()) {
    // The web server denies access to scratch/; preserve metadata before dropping it.
    $backupDir = ROOT_PATH . '/scratch/migration-backups';
    if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true)) {
        throw new RuntimeException('Cannot create migration backup directory');
    }
    $backupFile = $backupDir . '/class-types-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
    $rows = $pdo->query('SELECT id, subject_code, course_type FROM classes')->fetchAll();
    if (file_put_contents($backupFile, json_encode($rows, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)) === false) {
        throw new RuntimeException('Cannot back up classification values');
    }
    if ($pdo->query("SHOW INDEX FROM classes WHERE Key_name = 'idx_class_type'")->fetch()) {
        $pdo->exec('ALTER TABLE classes DROP INDEX idx_class_type');
    }
    $pdo->exec('ALTER TABLE classes DROP COLUMN course_type');
}
CacheService::forget('home_stats');
CacheService::forgetPattern('schedule_');
echo "Timetable image schema ready; course classification retired.\n";
