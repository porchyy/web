<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

$username = getenv('ADMIN_USERNAME') ?: '';
$password = getenv('ADMIN_PASSWORD') ?: '';
if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/D', $username) || strlen($password) < 12 || validate_password_strength($password) !== null) {
    fwrite(STDERR, "Set ADMIN_USERNAME (3-50 letters/numbers/._-) and ADMIN_PASSWORD (12+ characters, satisfying the site's password rules).\n");
    exit(1);
}
if (q('SELECT id FROM users WHERE username = ?', [$username])->fetch()) {
    fwrite(STDERR, "Username already exists; use the authenticated user-management page to change its password.\n");
    exit(1);
}
$algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
q('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)',
    [$username, password_hash($password, $algorithm), 'ผู้ดูแลระบบ', 'admin']);
echo "Administrator created. Clear ADMIN_PASSWORD from the shell environment.\n";
