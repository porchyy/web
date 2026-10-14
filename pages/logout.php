<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';

// Enforce POST with CSRF to prevent cross-site forced logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin');
}

verify_csrf();
AuthService::logout();
flash('ออกจากระบบเรียบร้อยแล้ว');
redirect('login');

