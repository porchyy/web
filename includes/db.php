<?php
/**
 * Database Connection & Auto-Bootstrap
 * Automatically creates database and tables on startup if they don't exist yet.
 */
require_once __DIR__ . '/config.php';

function init_database_schema(PDO $pdo): void
{
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        return;
    }

    $sql = file_get_contents($schemaFile);
    if (empty($sql)) {
        return;
    }

    // Execute multi-query schema safely
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        error_log("Schema auto-bootstrap warning: " . $e->getMessage());
    }
}

/** ตรวจว่า request นี้เป็น API / AJAX ที่ต้องการ JSON กลับไป */
function request_wants_json(): bool
{
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

    return str_contains($uri, '/api/')
        || str_contains($accept, 'application/json')
        || strcasecmp($xhr, 'XMLHttpRequest') === 0;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Try connecting directly to configured database
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        if (DB_HOST === 'localhost' && PHP_OS_FAMILY !== 'Windows') {
            $lamppSock = '/opt/lampp/var/mysql/mysql.sock';
            if (file_exists($lamppSock)) {
                $dsn .= ';unix_socket=' . $lamppSock;
            }
        }
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (!DB_AUTO_BOOTSTRAP) {
            throw $e;
        }
        // If database does not exist (error 1049) or connection failed, attempt auto-creation
        try {
            $rootDsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
            if (DB_HOST === 'localhost' && PHP_OS_FAMILY !== 'Windows') {
                $lamppSock = '/opt/lampp/var/mysql/mysql.sock';
                if (file_exists($lamppSock)) {
                    $rootDsn .= ';unix_socket=' . $lamppSock;
                }
            }
            $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
            $dbNameEscaped = str_replace('`', '``', DB_NAME);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameEscaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            unset($rootPdo);

            // Re-connect to newly created database
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $subEx) {
            error_log("Database connection & auto-create error: " . $subEx->getMessage());
            http_response_code(500);
            if (request_wants_json()) {
                header('Content-Type: application/json; charset=utf-8');
                exit(json_encode(['success' => false, 'error' => 'Database connection error'], JSON_UNESCAPED_UNICODE));
            }
            exit('<p style="font-family:sans-serif;padding:2rem">เชื่อมต่อฐานข้อมูลไม่ได้ - ตรวจสอบค่าใน <code>.env</code> หรือ <code>includes/config.php</code></p>');
        }
    }

    // Auto-Bootstrap: Check if tables exist. If missing, initialize schema.
    try {
        $tableCheck = DB_AUTO_BOOTSTRAP ? $pdo->query("SHOW TABLES LIKE 'users'")->fetch() : true;
        if (DB_AUTO_BOOTSTRAP && !$tableCheck) {
            init_database_schema($pdo);
        }
    } catch (PDOException $e) {
        error_log("Schema check error: " . $e->getMessage());
    }

    return $pdo;
}

/** รัน Prepared Statement อย่างปลอดภัย */
function execute_query(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function q(string $sql, array $params = []): PDOStatement
{
    return execute_query($sql, $params);
}
