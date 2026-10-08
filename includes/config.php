<?php
/**
 * ตั้งค่าหลักของเว็บไซต์ — แผนกเทคโนโลยีธุรกิจดิจิทัล
 */

// ---- Database (MySQL / MariaDB via XAMPP) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'dbt_web');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- ข้อมูลแผนก / สถานศึกษา ----
define('SITE_NAME', 'เทคโนโลยีธุรกิจดิจิทัล');
define('SITE_NAME_EN', 'Digital Business Technology');
define('SITE_SHORT', 'DBT');
define('INSTITUTE_NAME', 'วิทยาลัยการอาชีพ / สถานศึกษา');

// ---- ช่องทางการติดต่อ ----
define('CONTACT_PHONE', '02-000-0000');
define('CONTACT_EMAIL', 'dbt@example.ac.th');
define('CONTACT_FACEBOOK_NAME', 'แผนกเทคโนโลยีธุรกิจดิจิทัล');
define('CONTACT_FACEBOOK_URL', 'https://facebook.com/');
define('CONTACT_ADDRESS', 'อาคาร 3 ชั้น 2 ห้อง 321');
define('CONTACT_HOURS', 'จันทร์–ศุกร์ 08:00–16:30 น.');

// ---- Paths & Storage ----
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5 MB

// BASE_URL: คำนวณอัตโนมัติจากตำแหน่ง URL ของไฟล์ (รองรับทั้ง localhost/web หรือ root domain)
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (basename($scriptDir) === 'admin') {
        $scriptDir = dirname($scriptDir);
    }
    $base = rtrim($scriptDir, '/');
    define('BASE_URL', ($base === '/' || $base === '.' || $base === '') ? '' : $base);
}

date_default_timezone_set('Asia/Bangkok');
