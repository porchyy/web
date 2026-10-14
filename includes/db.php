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
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If database does not exist (error 1049) or connection failed, attempt auto-creation
        try {
            $rootDsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
            $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
            $dbNameEscaped = str_replace('`', '``', DB_NAME);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameEscaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            unset($rootPdo);

            // Re-connect to newly created database
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $subEx) {
            error_log("Database connection & auto-create error: " . $subEx->getMessage());
            http_response_code(500);
            exit('<p style="font-family:sans-serif;padding:2rem">เชื่อมต่อฐานข้อมูลไม่ได้ - ตรวจสอบค่าใน <code>.env</code> หรือ <code>includes/config.php</code></p>');
        }
    }

    // Auto-Bootstrap: Check if tables exist. If missing, initialize schema.
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if (!$tableCheck) {
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
