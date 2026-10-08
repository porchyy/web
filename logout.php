<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

logout();
flash('ออกจากระบบเรียบร้อยแล้ว');
redirect('login.php');
