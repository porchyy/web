<?php
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('<p style="font-family:sans-serif;padding:2rem">เชื่อมต่อฐานข้อมูลไม่ได้ — ตรวจสอบค่าใน <code>includes/config.php</code> และนำเข้า <code>database/schema.sql</code></p>');
        }
    }
    return $pdo;
}

/** รัน Prepared Statement อย่างปลอดภัย */
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
