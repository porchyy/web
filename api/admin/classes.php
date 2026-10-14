<?php
/**
 * DBT Admin Visual Timetable Scheduler - JSON API Micro-Endpoint
 * 
 * Handles:
 * - Drag & drop slot repositioning
 * - Edge-resize slot time duration adjustments
 * - Special period (Flag / Lunch) time updates
 * 
 * Decoupled from HTML View Controller per RULES.md & ADR 0003
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/services/AuthService.php';
require_once __DIR__ . '/../../includes/services/ScheduleService.php';
require_once __DIR__ . '/../../includes/renderers/scheduler-table.php';

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

try {
    switch ($subAction) {
        case 'ajax_update_slot':
            $slotId    = filter_input(INPUT_POST, 'slot_id', FILTER_VALIDATE_INT);
            $newDay    = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
            $timeStart = trim((string) ($_POST['time_start'] ?? ''));
            $timeEnd   = trim((string) ($_POST['time_end'] ?? ''));

            if (!$slotId || !$newDay || $timeStart === '' || $timeEnd === '') {
                throw new InvalidArgumentException("ข้อมูลพารามิเตอร์ไม่ครบถ้วน (slot_id: {$slotId}, day: {$newDay}, start: '{$timeStart}', end: '{$timeEnd}')");
            }

            $updated  = ScheduleService::updateSlotPosition($slotId, $newDay, $timeStart, $timeEnd);
            $group    = $updated['class_group'] ?? trim((string) ($_POST['class_group'] ?? 'ปวช.2/1'));
            $gridData = ScheduleService::getTimetableGrid($group);

            ob_start();
            renderSchedulerTbody($gridData['grid'], $group);
            $tbodyHtml = ob_get_clean();

            echo json_encode([
                'success'    => true,
                'message'    => 'อัปเดตตำแหน่งคาบเรียนเรียบร้อยแล้ว',
                'slot'       => $updated,
                'tbody_html' => $tbodyHtml,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'ajax_update_special_period':
            if (!AuthService::hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถตั้งค่าคาบพิเศษได้'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $rawPeriodNum = $_POST['period_num'] ?? null;
            $specialType  = trim((string) ($_POST['special_type'] ?? ''));
            $timeStart    = trim((string) ($_POST['time_start'] ?? ''));
            $timeEnd      = trim((string) ($_POST['time_end'] ?? ''));
            $group        = trim((string) ($_POST['class_group'] ?? 'ปวช.2/1'));

            if ($timeStart === '' || $timeEnd === '') {
                throw new InvalidArgumentException("ข้อมูลพารามิเตอร์คาบพิเศษไม่ครบถ้วน (start: '{$timeStart}', end: '{$timeEnd}')");
            }
            $periodNum = ($rawPeriodNum !== null && is_numeric($rawPeriodNum)) ? (int) $rawPeriodNum : 0;

            $updatedSpecial = ScheduleService::updateSpecialPeriod($periodNum, $timeStart, $timeEnd, $specialType);
            $gridData       = ScheduleService::getTimetableGrid($group);
            $updatedPeriods = ScheduleService::getPeriods();

            ob_start();
            renderSchedulerThead($updatedPeriods);
            $theadHtml = ob_get_clean();

            ob_start();
            renderSchedulerTbody($gridData['grid'], $group);
            $tbodyHtml = ob_get_clean();

            echo json_encode([
                'success'    => true,
                'message'    => 'อัปเดตคาบพิเศษเรียบร้อยแล้ว',
                'period'     => $updatedSpecial,
                'periods'    => $updatedPeriods,
                'thead_html' => $theadHtml,
                'tbody_html' => $tbodyHtml,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'ajax_restore_special_slots':
            if (!AuthService::hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถกู้คืนคาบกิจกรรมมาตรฐานได้'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $group = trim((string) ($_POST['class_group'] ?? 'ปวช.2/1'));
            $restoredCount = ScheduleService::restoreDefaultSpecialSlots($group);
            $gridData = ScheduleService::getTimetableGrid($group);
            ob_start();
            renderSchedulerTbody($gridData['grid'], $group);
            $tbodyHtml = ob_get_clean();

            echo json_encode([
                'success'    => true,
                'message'    => "กู้คืนคาบกิจกรรมมาตรฐาน (เสาธง/พักกลางวัน) เรียบร้อยแล้ว ({$restoredCount} รายการ)",
                'restored'   => $restoredCount,
                'tbody_html' => $tbodyHtml,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'ajax_get_slot':
            $slotId = filter_input(INPUT_POST, 'slot_id', FILTER_VALIDATE_INT);
            if (!$slotId) {
                throw new InvalidArgumentException('ไม่พบรหัสคาบเรียนที่ต้องการค้นหา');
            }
            $slot = ScheduleService::getSlotById($slotId);
            if (!$slot) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'ไม่พบข้อมูลคาบเรียนนี้ในระบบ'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo json_encode(['success' => true, 'slot' => $slot], JSON_UNESCAPED_UNICODE);
            break;

        case 'save':
            $editId      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
            $classId     = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: null;
            $dayOfWeek   = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
            $timeStart   = trim((string) ($_POST['time_start'] ?? ''));
            $timeEnd     = trim((string) ($_POST['time_end'] ?? ''));
            $subjectCode = trim((string) ($_POST['subject_code'] ?? ''));
            $subjectName = trim((string) ($_POST['subject_name'] ?? ''));
            $teacherName = trim((string) ($_POST['teacher_name'] ?? ''));
            $room        = trim((string) ($_POST['room'] ?? ''));
            $classGroup  = trim((string) ($_POST['class_group'] ?? 'ปวช.2/1'));

            if (!$dayOfWeek || $timeStart === '' || $timeEnd === '' || ($classId === null && $subjectName === '')) {
                throw new InvalidArgumentException('กรุณากรอกข้อมูลวัน เวลา และเลือกหรือระบุรายวิชาให้ครบถ้วน');
            }

            $savedId = ScheduleService::saveClassSlot([
                'id'           => $editId,
                'class_id'     => $classId,
                'day_of_week'  => $dayOfWeek,
                'time_start'   => $timeStart,
                'time_end'     => $timeEnd,
                'subject_code' => $subjectCode,
                'subject_name' => $subjectName,
                'teacher_name' => $teacherName,
                'room'         => $room,
                'class_group'  => $classGroup,
            ]);

            $gridData = ScheduleService::getTimetableGrid($classGroup);
            ob_start();
            renderSchedulerTbody($gridData['grid'], $classGroup);
            $tbodyHtml = ob_get_clean();

            echo json_encode([
                'success'    => true,
                'message'    => $editId ? 'แก้ไขข้อมูลคาบเรียนเรียบร้อยแล้ว' : 'เพิ่มคาบเรียนลงในตารางเรียบร้อยแล้ว',
                'slot_id'    => $savedId,
                'tbody_html' => $tbodyHtml,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'save_course':
            if (!AuthService::hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถแก้ไขทะเบียนรายวิชาได้'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $courseId       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
            $subjectCode    = trim((string) ($_POST['subject_code'] ?? ''));
            $subjectName    = trim((string) ($_POST['subject_name'] ?? ''));
            $courseType     = trim((string) ($_POST['course_type'] ?? 'professional'));
            $defaultTeacher = trim((string) ($_POST['default_teacher'] ?? ''));
            $defaultRoom    = trim((string) ($_POST['default_room'] ?? ''));
            $credit         = filter_input(INPUT_POST, 'credit', FILTER_VALIDATE_INT) ?: 3;

            $savedId = ScheduleService::saveClass([
                'id'              => $courseId,
                'subject_code'    => $subjectCode,
                'subject_name'    => $subjectName,
                'course_type'     => $courseType,
                'default_teacher' => $defaultTeacher,
                'default_room'    => $defaultRoom,
                'credit'          => $credit,
            ]);

            $course = ScheduleService::getClassById($savedId);
            echo json_encode([
                'success' => true,
                'message' => $courseId ? 'แก้ไขข้อมูลรายวิชาเรียบร้อยแล้ว' : 'เพิ่มรายวิชาใหม่เรียบร้อยแล้ว',
                'course'  => $course,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'delete_course':
            if (!AuthService::hasRole('admin')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถลบรายวิชาได้'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $courseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$courseId) {
                throw new InvalidArgumentException('ไม่พบรหัสรายวิชาที่ต้องการลบ');
            }
            ScheduleService::deleteClass($courseId);
            echo json_encode([
                'success' => true,
                'message' => 'ลบรายวิชาออกจากระบบเรียบร้อยแล้ว',
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_courses':
            $prefix     = trim((string) ($_POST['prefix'] ?? ''));
            $courseType = trim((string) ($_POST['course_type'] ?? ''));
            $courses    = ScheduleService::getClasses($prefix ?: null, $courseType ?: null);
            echo json_encode([
                'success' => true,
                'courses' => $courses,
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_course_detail':
            $courseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$courseId) {
                throw new InvalidArgumentException('ไม่พบรหัสรายวิชา');
            }
            $course = ScheduleService::getClassById($courseId);
            if (!$course) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'ไม่พบข้อมูลรายวิชาในระบบ'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo json_encode([
                'success' => true,
                'course'  => $course,
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ไม่พบคำสั่ง sub_action ที่ร้องขอ'], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'เกิดข้อผิดพลาดภายในระบบ',
    ], JSON_UNESCAPED_UNICODE);
}
