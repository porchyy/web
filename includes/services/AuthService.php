<?php
/**
 * AuthService - Authentication & User Management Service
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../security.php';

if (!defined('AUTH_PASSWORD_ALGO')) {
    define('AUTH_PASSWORD_ALGO', defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT));
}

class AuthService
{
    private static $cachedUser = false;

    public static function getPasswordAlgo(): string|int|null
    {
        return AUTH_PASSWORD_ALGO;
    }

    public static function currentUser(): ?array
    {
        if (self::$cachedUser === false) {
            self::$cachedUser = null;
            if (!empty($_SESSION['uid'])) {
                self::$cachedUser = q('SELECT id, username, full_name, role FROM users WHERE id = ?', [$_SESSION['uid']])->fetch() ?: null;
            }
        }
        return self::$cachedUser;
    }

    public static function isLoggedIn(): bool
    {
        return self::currentUser() !== null;
    }

    public static function requireLogin(): array
    {
        $u = self::currentUser();
        if (!$u) {
            redirect('login');
        }
        return $u;
    }

    public static function hasRole(string $role): bool
    {
        $u = self::currentUser();
        return $u && ($u['role'] ?? '') === $role;
    }

    public static function requireRole(string $role): array
    {
        $u = self::requireLogin();
        if (($u['role'] ?? '') !== $role) {
            http_response_code(403);
            flash('คุณไม่มีสิทธิ์เข้าถึงหน้านี้ (สำหรับสิทธิ์ ' . e($role) . ' เท่านั้น)', 'error');
            redirect('admin');
        }
        return $u;
    }

    public static function attemptLogin(string $username, string $password): bool
    {
        $row = q('SELECT id, password_hash FROM users WHERE username = ?', [$username])->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int) $row['id'];
            self::$cachedUser = false;

            if (password_needs_rehash($row['password_hash'], AUTH_PASSWORD_ALGO)) {
                q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, AUTH_PASSWORD_ALGO), $row['id']]);
            }
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$cachedUser = false;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
    }

    public static function isUsingDefaultPassword(): bool
    {
        $u = self::currentUser();
        if (!$u) return false;
        $row = q('SELECT password_hash FROM users WHERE id = ?', [$u['id']])->fetch();
        return $row && password_verify('admin1234', $row['password_hash']);
    }

    /* Admin User Management */

    public static function getAllUsers(): array
    {
        return q("SELECT id, username, full_name, role, created_at FROM users ORDER BY id ASC")->fetchAll();
    }

    public static function getUserById(int $id): ?array
    {
        $userRecord = q("SELECT id, username, full_name, role, created_at FROM users WHERE id = ?", [$id])->fetch();
        return $userRecord ?: null;
    }

    public static function saveUser(array $data): int
    {
        $id       = !empty($data['id']) ? (int) $data['id'] : null;
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $role     = trim((string) ($data['role'] ?? 'editor'));

        if ($id) {
            if ($password !== '') {
                $hash = password_hash($password, AUTH_PASSWORD_ALGO);
                q("
                    UPDATE users
                    SET username = ?, password_hash = ?, full_name = ?, role = ?
                    WHERE id = ?
                ", [$username, $hash, $fullName, $role, $id]);
            } else {
                q("
                    UPDATE users
                    SET username = ?, full_name = ?, role = ?
                    WHERE id = ?
                ", [$username, $fullName, $role, $id]);
            }
            return $id;
        } else {
            $hash = password_hash($password, AUTH_PASSWORD_ALGO);
            q("
                INSERT INTO users (username, password_hash, full_name, role)
                VALUES (?, ?, ?, ?)
            ", [$username, $hash, $fullName, $role]);
            return (int) db()->lastInsertId();
        }
    }

    public static function deleteUser(int $id, int $currentUserId): bool
    {
        if ($id === $currentUserId) {
            throw new RuntimeException('ไม่สามารถลบบัญชีผู้ใช้ของตัวเองที่กำลังใช้งานอยู่ได้');
        }
        $targetUser = self::getUserById($id);
        if ($targetUser && ($targetUser['role'] ?? '') === 'admin') {
            $adminCount = (int) q("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            if ($adminCount <= 1) {
                throw new RuntimeException('ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายของระบบได้');
            }
        }
        q("DELETE FROM users WHERE id = ?", [$id]);
        return true;
    }

    public static function countUsers(): int
    {
        return (int) q("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
