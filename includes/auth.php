<?php
require_once __DIR__ . '/helpers.php';

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $user = q('SELECT id, username, display_name, role FROM users WHERE id = ?', [$_SESSION['uid']])->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) redirect('login.php');
    return $u;
}

function attempt_login(string $username, string $password): bool
{
    $row = q('SELECT id, password_hash FROM users WHERE username = ?', [$username])->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $row['id'];
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $row['id']]);
        }
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
