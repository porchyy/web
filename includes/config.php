<?php
/**
 * ตั้งค่าหลักของเว็บไซต์ - สาขาเทคโนโลยีธุรกิจดิจิทัล
 */

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5 MB

// ---- โหลดค่าคอนฟิกจาก .env ----
(function () {
    $envFile = ROOT_PATH . '/.env';
    if (!file_exists($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);
            $v = trim($v, "\"'");
            $_ENV[$k] = $v;
            putenv("{$k}={$v}");
        }
    }
})();

function env(string $key, string $default = ''): string
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ---- Database (MySQL / MariaDB via XAMPP) ----
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'dbt_web'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// ---- Helper to resolve setting DB-first with fallback ----
function get_initial_setting(string $key, string $default = ''): string
{
    static $dbSettings = null;
    if ($dbSettings === null) {
        $dbSettings = [];
        try {
            $host = env('DB_HOST', 'localhost');
            $port = env('DB_PORT', '3306');
            $name = env('DB_NAME', 'dbt_web');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $stmt = $pdo->query("SELECT skey, svalue FROM settings");
            if ($stmt) {
                $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
            }
        } catch (\Throwable) {
            $dbSettings = [];
        }
    }
    return (isset($dbSettings[$key]) && $dbSettings[$key] !== '') ? $dbSettings[$key] : env(strtoupper($key), $default);
}

// ---- ข้อมูลสาขา / สถานศึกษา (DB-First) ----
define('SITE_NAME', get_initial_setting('site_name', 'เทคโนโลยีธุรกิจดิจิทัล'));
define('SITE_NAME_EN', get_initial_setting('site_name_en', 'Digital Business Technology'));
define('SITE_SHORT', get_initial_setting('site_short', 'DBT'));
define('INSTITUTE_NAME', get_initial_setting('institute_name', 'วิทยาลัยการอาชีพ / สถานศึกษา'));

// ---- ช่องทางการติดต่อ (DB-First) ----
define('CONTACT_PHONE', get_initial_setting('contact_phone', '02-000-0000'));
define('CONTACT_EMAIL', get_initial_setting('contact_email', 'dbt@example.ac.th'));
define('CONTACT_FACEBOOK_NAME', get_initial_setting('contact_facebook_name', 'สาขาเทคโนโลยีธุรกิจดิจิทัล'));
define('CONTACT_FACEBOOK_URL', get_initial_setting('contact_facebook_url', 'https://www.facebook.com/profile.php?id=61569347616102'));
define('CONTACT_ADDRESS', get_initial_setting('contact_address', 'อาคาร 3 ชั้น 2 ห้อง 321'));
define('CONTACT_HOURS', get_initial_setting('contact_hours', 'จันทร์–ศุกร์ 08:00–16:30 น.'));

// BASE_URL: คำนวณอัตโนมัติจากตำแหน่ง URL ของไฟล์ (รองรับทั้ง localhost/web หรือ root domain)
if (!defined('BASE_URL')) {
    $envBase = env('BASE_URL', '');
    if ($envBase !== '') {
        $base = rtrim($envBase, '/');
    } else {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
        $rootPath = str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH);
        if ($docRoot !== '' && str_starts_with($rootPath, $docRoot)) {
            $base = substr($rootPath, strlen($docRoot));
        } else {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $base = preg_replace('#/(?:pages|admin)(?:/.*)?$#i', '', $scriptDir);
        }
    }
    $base = rtrim($base, '/');
    define('BASE_URL', ($base === '/' || $base === '.' || $base === '') ? '' : $base);
}

date_default_timezone_set('Asia/Bangkok');
