<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../includes/config.php';

$failures = [];
if (APP_ENV !== 'production') $failures[] = 'Set APP_ENV=production';
if (DB_AUTO_BOOTSTRAP) $failures[] = 'Set DB_AUTO_BOOTSTRAP=0; apply schema changes explicitly';
if (!str_starts_with(BASE_URL, 'https://') || str_contains(BASE_URL, '.example')) $failures[] = 'Set BASE_URL to the real HTTPS site URL';
if (strtolower(DB_USER) === 'root') $failures[] = 'Use a dedicated database account instead of root';
if (DB_PASS === '' || str_contains(DB_PASS, 'replace-with-')) $failures[] = 'Configure a real database password';
if (PHP_VERSION_ID < 80200) $failures[] = 'PHP 8.2 or newer is required';
foreach (['pdo_mysql', 'fileinfo', 'mbstring', 'dom'] as $extension) {
    if (!extension_loaded($extension)) $failures[] = 'Missing PHP extension: ' . $extension;
}
foreach (['uploads', 'scratch'] as $directory) {
    if (!is_dir(ROOT_PATH . '/' . $directory) || !is_writable(ROOT_PATH . '/' . $directory)) {
        $failures[] = 'Create a writable ' . $directory . '/ directory';
    }
}
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if (!$pdo->query("SHOW TABLES LIKE 'class_timetables'")->fetch()) $failures[] = 'Run the timetable migration';
    if ($pdo->query("SHOW COLUMNS FROM classes LIKE 'course_type'")->fetch()) $failures[] = 'Course classification migration is incomplete';
    $hashes = $pdo->query('SELECT password_hash FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (!$pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn()) $failures[] = 'Provision an administrator account';
    foreach ($hashes as $hash) {
        if (password_verify('admin1234', $hash)) {
            $failures[] = 'Replace the seeded default account password before launch';
            break;
        }
    }
} catch (PDOException $exception) {
    $failures[] = 'Database connection or required schema checks failed';
}
foreach ($failures as $failure) echo 'FAIL: ', $failure, PHP_EOL;
echo $failures ? "Production configuration is not ready.\n" : "Production configuration checks passed. Verify HTTPS, backups and browser behavior before launch.\n";
exit($failures ? 1 : 0);
