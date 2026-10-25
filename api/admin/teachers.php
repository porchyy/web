<?php
/**
 * DBT Admin Faculty & Teachers - JSON API Micro-Endpoint
 * 
 * Handles:
 * - HTML5 drag-and-drop faculty reordering
 * - Head of department position swapping
 * 
 * Decoupled from HTML View Controller per RULES.md & ADR 0003
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/services/AuthService.php';
require_once __DIR__ . '/../../includes/services/FacultyService.php';

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

$subAction = trim((string) ($_POST['sub_action'] ?? ''));

// Guard: Admin Role for Structural Operations
if (!AuthService::hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถจัดลำดับหรือแต่งตั้งหัวหน้าสาขาได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($subAction) {
        case 'reorder':
            $orderJson = $_POST['order_ids'] ?? '[]';
            $orderIds = json_decode($orderJson, true) ?: [];
            if (!empty($orderIds)) {
                FacultyService::reorderTeachers($orderIds);
            }
            echo json_encode(['success' => true, 'message' => 'บันทึกลำดับอาจารย์เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            break;

        case 'swap_head':
            $newHeadId = filter_input(INPUT_POST, 'new_head_id', FILTER_VALIDATE_INT);
            if (!$newHeadId) {
                throw new InvalidArgumentException('ไม่พบรหัสอาจารย์ที่ต้องการแต่งตั้ง');
            }
            $success = FacultyService::setHeadTeacher($newHeadId);
            echo json_encode(['success' => $success], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ไม่พบคำสั่ง sub_action ที่ร้องขอ'], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => public_error_message($e)], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => public_error_message($e)], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'เกิดข้อผิดพลาดภายในระบบ'], JSON_UNESCAPED_UNICODE);
}
