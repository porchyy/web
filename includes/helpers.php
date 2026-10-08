<?php
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ---------- Output & Escaping ---------- */

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function img_url(?string $path): string
{
    return $path ? url($path) : '';
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/* ---------- Settings (Key/Value Store) ---------- */

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = q('SELECT skey, svalue FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $cache[$key] ?? $default;
}

function set_setting(string $key, string $value): void
{
    q('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)', [$key, $value]);
}

/* ---------- CSRF & Flash Messages ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('เซสชันหมดอายุ กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง');
    }
}

function flash(?string $msg = null, string $type = 'success'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Thai & Technical Date Formats ---------- */

const TH_MONTHS_SHORT = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
const TH_MONTHS       = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
const TH_DAYS         = [1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์'];
const TH_DAYS_SHORT   = [1 => 'จ.', 2 => 'อ.', 3 => 'พ.', 4 => 'พฤ.', 5 => 'ศ.', 6 => 'ส.', 7 => 'อา.'];

function thai_date(?string $date, bool $long = false): string
{
    if (!$date) return '';
    $t = strtotime($date);
    $m = (int) date('n', $t);
    return date('j', $t) . ' ' . ($long ? TH_MONTHS[$m] : TH_MONTHS_SHORT[$m]) . ' ' . (date('Y', $t) + 543);
}

function mono_date(?string $date): string
{
    return $date ? date('d.m.y', strtotime($date)) : '';
}

function activity_countdown_badge(string $startDate, ?string $endDate = null): array
{
    $today = new DateTime('today');
    $start = new DateTime($startDate);
    $end   = !empty($endDate) ? new DateTime($endDate) : clone $start;

    if ($today > $end) {
        return ['text' => 'สิ้นสุดแล้ว', 'class' => 'badge-ended', 'is_soon' => false];
    }
    if ($today >= $start && $today <= $end) {
        return ['text' => 'กำลังจัดกิจกรรม', 'class' => 'badge-soon', 'is_soon' => true];
    }

    $diff = (int) $today->diff($start)->format('%r%a');
    if ($diff === 0) {
        return ['text' => 'วันนี้', 'class' => 'badge-soon', 'is_soon' => true];
    } elseif ($diff === 1) {
        return ['text' => 'พรุ่งนี้', 'class' => 'badge-soon', 'is_soon' => true];
    } elseif ($diff <= 7) {
        return ['text' => "อีก {$diff} วัน", 'class' => 'badge-soon', 'is_soon' => true];
    } elseif ($diff <= 30) {
        return ['text' => "อีก {$diff} วัน", 'class' => 'badge-upcoming', 'is_soon' => false];
    } else {
        return ['text' => 'เร็ว ๆ นี้', 'class' => 'badge-upcoming', 'is_soon' => false];
    }
}

function format_time_hm(?string $time): string
{
    return $time ? substr($time, 0, 5) : '';
}

function hm(?string $time): string
{
    return format_time_hm($time);
}

function initials(string $name): string
{
    $name = preg_replace('/^(นาย|นางสาว|นาง|ดร\.|อ\.|ผศ\.|รศ\.)\s*/u', '', trim($name));
    return mb_substr($name, 0, 1);
}

function render_avatar(string $name, ?string $imagePath, string $class = 'figure-img marks'): string
{
    $c = $class ? ' ' . e($class) : '';
    $hasImage = $imagePath && is_file(ROOT_PATH . '/' . $imagePath);
    $html = '<div class="avatar' . $c . '">';
    if ($hasImage) {
        $html .= '<img src="' . e(url($imagePath)) . '" alt="' . e($name) . '" loading="lazy">';
    } else {
        $html .= '<div class="monogram">' . e(initials($name)) . '</div>';
    }
    $html .= '</div>';
    return $html;
}

/* ---------- Image Upload Handling ---------- */

function upload_image(string $field): ?string
{
    $f = $_FILES[$field] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ (รหัส ' . $f['error'] . ')');
    if ($f['size'] > MAX_UPLOAD_BYTES) throw new RuntimeException('ไฟล์ใหญ่เกิน 5 MB');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('รองรับเฉพาะไฟล์ JPG, PNG หรือ WebP');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('บันทึกไฟล์ไม่สำเร็จ ตรวจสอบสิทธิ์โฟลเดอร์ uploads/');
    }
    return 'uploads/' . $name;
}

function delete_upload(?string $path): void
{
    if ($path && str_starts_with($path, 'uploads/') && !str_starts_with($path, 'uploads/seed/')) {
        $full = ROOT_PATH . '/' . $path;
        if (is_file($full)) @unlink($full);
    }
}

function delete_record_with_asset(string $table, int $id, string $column = 'image_path'): void
{
    $allowed = ['news', 'teachers', 'club_members'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException("Invalid table for asset deletion: {$table}");
    }
    $col = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $row = q("SELECT `{$col}` FROM `{$table}` WHERE id = ?", [$id])->fetch();
    if ($row && !empty($row[$col])) {
        delete_upload($row[$col]);
    }
    q("DELETE FROM `{$table}` WHERE id = ?", [$id]);
}

/* ---------- Clean Lucide-style SVG Icons ---------- */

function icon(string $name, int $size = 20, string $class = ''): string
{
    static $p = [
        'home'          => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9.5 21v-6h5v6"/>',
        'info'          => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.5h.01"/>',
        'news'          => '<path d="M4 5h13v14H6a2 2 0 0 1-2-2z"/><path d="M17 9h3v8a2 2 0 0 1-2 2"/><path d="M8 9h5M8 13h5M8 16h3"/>',
        'calendar'      => '<rect x="3.5" y="5" width="17" height="15" rx="1"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'users'         => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
        'user'          => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left'    => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'log-in'        => '<path d="M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4"/><path d="M10 17l5-5-5-5M15 12H3"/>',
        'log-out'       => '<path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
        'phone'         => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1"/>',
        'mail'          => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/>',
        'map-pin'       => '<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
        'facebook'      => '<path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6.5v3.5H9V21h3.5v-7.5H15l.5-3.5h-3V7a1 1 0 0 1 1-1H15z"/>',
        'clock'         => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'eye'           => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'       => '<path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3 3.9M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'plus'          => '<path d="M12 5v14M5 12h14"/>',
        'pencil'        => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'trash'         => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'image'         => '<rect x="3" y="4" width="18" height="16" rx="1"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
        'grid'          => '<rect x="4" y="4" width="7" height="7"/><rect x="13" y="4" width="7" height="7"/><rect x="4" y="13" width="7" height="7"/><rect x="13" y="13" width="7" height="7"/>',
        'book'          => '<path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z"/>',
        'flag'          => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'check'         => '<path d="m5 12 5 5 9-10"/>',
        'alert'         => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5h.01"/>',
        'external'      => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    ];
    $c = $class ? ' class="' . e($class) . '"' : '';
    return '<svg' . $c . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($p[$name] ?? '') . '</svg>';
}
