<?php
/**
 * DBT Admin News Category - JSON API Micro-Endpoint
 * 
 * Handles:
 * - Quick AJAX category creation from news modal
 * 
 * Decoupled from HTML View Controller per RULES.md & ADR 0003
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/services/AuthService.php';
require_once __DIR__ . '/../../includes/services/NewsService.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: Admin Authentication
if (!AuthService::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'จำเป็นต้องเข้าสู่ระบบก่อนทำรายการ'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Guard: Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Guard: CSRF Validation
try {
    verify_csrf();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'เซสชันหมดอายุ กรุณารีเฟรชหน้าเว็บแล้วลองใหม่อีกครั้ง'], JSON_UNESCAPED_UNICODE);
    exit;
}

$catName = clean_text($_POST['name'] ?? '');

try {
    $category = NewsService::createCategory($catName);
    echo json_encode([
        'success' => true,
        'id'      => $category['id'],
        'name'    => $category['name'],
        'slug'    => $category['slug'],
    ], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
