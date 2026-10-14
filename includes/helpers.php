<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/services/SettingService.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $cookiePath = defined('BASE_URL') && BASE_URL !== '' ? BASE_URL : '/';
    session_set_cookie_params([
        'path'     => $cookiePath,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

/* ---------- Output & Escaping ---------- */

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function setting(string $key, string $default = ''): string
{
    return SettingService::get($key, $default);
}

function url(string $path = ''): string
{
    $clean = ltrim($path, '/');
    if ($clean !== '' && !str_starts_with($clean, 'uploads/') && !str_starts_with($clean, 'assets/') && !str_starts_with($clean, 'favicon.')) {
        if (str_starts_with($clean, 'pages/')) {
            $clean = substr($clean, 6);
        }
        $clean = preg_replace('/\.php(\?.*)?$/', '$1', $clean);
        if ($clean === 'index') {
            $clean = '';
        } elseif ($clean === 'admin/index') {
            $clean = 'admin';
        }
    }
    $root = rtrim(BASE_URL, '/');
    if ($clean === '') {
        return $root !== '' ? $root . '/' : '/';
    }
    return ($root !== '' ? $root : '') . '/' . $clean;
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
    $token = $_POST['csrf'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) $token)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'รหัสความปลอดภัยของแบบฟอร์มหมดอายุ กรุณารีเฟรชหน้าเว็บ']);
            exit;
        }
        redirect('error?code=419');
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

function activity_countdown_badge(string $startDate, ?string $endDate = null): array
{
    $today = new DateTime('today');
    $start = new DateTime($startDate);
    $end   = !empty($endDate) ? new DateTime($endDate) : clone $start;

    if ($today > $end) {
        return ['text' => 'สิ้นสุดแล้ว', 'class' => 'badge-ended', 'is_soon' => false];
    }
    if ($today >= $start && $today <= $end) {
        return ['text' => 'กำลังจัดกิจกรรม', 'class' => 'badge-active', 'is_soon' => true];
    }

    $diff = (int) $today->diff($start)->format('%r%a');
    if ($diff === 0) {
        return ['text' => 'วันนี้', 'class' => 'badge-active', 'is_soon' => true];
    } elseif ($diff === 1) {
        return ['text' => 'พรุ่งนี้', 'class' => 'badge-soon', 'is_soon' => true];
    } elseif ($diff <= 7) {
        return ['text' => "อีก {$diff} วัน", 'class' => 'badge-soon', 'is_soon' => true];
    } else {
        return ['text' => 'เร็ว ๆ นี้', 'class' => 'badge-upcoming', 'is_soon' => false];
    }
}

function hm(?string $time): string
{
    return $time ? substr($time, 0, 5) : '';
}

function initials(string $name): string
{
    $name = preg_replace('/^(อาจารย์|ครู|ดร\.|ผศ\.ดร\.|รศ\.ดร\.|ผศ\.|รศ\.|ศ\.|อ\.|นาย|นางสาว|นาง|นส\.)\s*/u', '', trim($name));
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

/* ---------- Pagination Component ---------- */

function render_pagination(int $currentPage, int $totalPages, string $baseUrl, array $params = []): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav aria-label="การแบ่งหน้า" class="my-4"><ul class="pagination justify-content-center m-0">';

    // Previous
    if ($currentPage > 1) {
        $prevParams = array_merge($params, ['page' => $currentPage - 1]);
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '?' . http_build_query($prevParams)) . '">ก่อนหน้า</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ก่อนหน้า</span></li>';
    }

    // Numbers
    for ($p = 1; $p <= $totalPages; $p++) {
        if ($p === $currentPage) {
            $html .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $p . '</span></li>';
        } else {
            $pageParams = array_merge($params, ['page' => $p]);
            $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '?' . http_build_query($pageParams)) . '">' . $p . '</a></li>';
        }
    }

    // Next
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($params, ['page' => $currentPage + 1]);
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . '?' . http_build_query($nextParams)) . '">ถัดไป</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ถัดไป</span></li>';
    }

    $html .= '</ul></nav>';
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

    // Verify valid image header and payload integrity
    $imgInfo = @getimagesize($f['tmp_name']);
    if ($imgInfo === false) {
        throw new RuntimeException('ไฟล์รูปภาพไม่ถูกต้องหรือข้อมูลรูปภาพเสียหาย');
    }

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new RuntimeException('บันทึกไฟล์ไม่สำเร็จ ตรวจสอบสิทธิ์โฟลเดอร์ uploads/');
    }
    return 'uploads/' . $name;
}

function delete_upload(?string $path): void
{
    if (!$path || !str_starts_with($path, 'uploads/') || str_starts_with($path, 'uploads/seed/')) {
        return;
    }
    $full = ROOT_PATH . '/' . $path;
    $realUploadDir = realpath(UPLOAD_DIR);
    $realTarget    = realpath($full);

    if ($realTarget && $realUploadDir && str_starts_with($realTarget, $realUploadDir) && is_file($realTarget)) {
        @unlink($realTarget);
    }
}

function delete_record_with_asset(string $table, int $id, string $column = 'image_path'): void
{
    $allowedTables  = ['news', 'teachers', 'club_members'];
    $allowedColumns = ['image_path'];
    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException("Invalid table for asset deletion: {$table}");
    }
    if (!in_array($column, $allowedColumns, true)) {
        throw new InvalidArgumentException("Invalid column for asset deletion: {$column}");
    }
    $row = q("SELECT `{$column}` FROM `{$table}` WHERE id = ?", [$id])->fetch();
    if ($row && !empty($row[$column])) {
        delete_upload($row[$column]);
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
        'settings'      => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
        'copy'          => '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
        'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'refresh'       => '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/>',
        'rotate-cw'     => '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/>',
        'x'             => '<path d="M18 6 6 18M6 6l12 12"/>',
        'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];
    $c = $class ? ' class="' . e($class) . '"' : '';
    return '<svg' . $c . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($p[$name] ?? '') . '</svg>';
}

/**
 * Send short-lived HTTP public caching headers (RULES.md: 60-120s max TTL)
 */
function send_cache_headers(int $maxAge = 60, int $sMaxAge = 120): void
{
    require_once __DIR__ . '/services/CacheService.php';
    CacheService::sendPublicHeaders($maxAge, $sMaxAge);
}

/**
 * Parse Markdown & inline formatting (**bold**, *italic*, ~~strike~~, [text](url)) into HTML
 */
function parse_markdown(string $text): string
{
    if ($text === '') {
        return '';
    }
    // Bold: **text** or __text__
    $text = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.+?)__/u', '<strong>$1</strong>', $text);

    // Strikethrough: ~~text~~
    $text = preg_replace('/~~(.+?)~~/u', '<s>$1</s>', $text);

    // Italic: *text* or _text_ (not preceded or followed by an asterisk/underscore)
    $text = preg_replace('/(?<!\*)\*([^\*\s][^\*]*?)\*(?!\*)/u', '<em>$1</em>', $text);
    $text = preg_replace('/(?<!_)_([^_\s][^_]*?)_(?!_)/u', '<em>$1</em>', $text);

    // Links: [text](url)
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/u', function ($m) {
        $safeUrl = function_exists('safe_url') ? safe_url($m[2]) : '#';
        return '<a href="' . e($safeUrl) . '" target="_blank" rel="noopener noreferrer">' . $m[1] . '</a>';
    }, $text);

    return $text;
}


