<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../includes/config.php';
$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
if (DB_HOST === 'localhost' && PHP_OS_FAMILY !== 'Windows') {
    $lamppSock = '/opt/lampp/var/mysql/mysql.sock';
    if (file_exists($lamppSock)) {
        $dsn .= ';unix_socket=' . $lamppSock;
    }
}
$pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (glob(__DIR__ . '/../database/schema/*.sql') as $file) {
    $sql = file_get_contents($file);
    if (preg_match('/^\s*(?:USE\b|CREATE\s+DATABASE\b)/mi', $sql)) {
        throw new RuntimeException('Schema modules must not change the selected database');
    }
    $pdo->exec($sql);
}
echo "Schema installed into the configured database. No demo seeds or accounts were created.\n";
