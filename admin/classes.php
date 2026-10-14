<?php
/**
 * Admin - จัดการตารางเรียน (Visual Slot Scheduler)
 */
$adminNav = 'classes';
$pageTitle = 'จัดการตารางเรียน';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';

AuthService::requireLogin();

// --------------------------------------------------------------------------
// Timetable Header & Body Renderer Helpers
// --------------------------------------------------------------------------
function renderSchedulerThead(array $periods): void { ?>
    <tr>
      <th class="day-col">วัน / คาบ</th>
      <?php foreach ($periods as $p): ?>
        <th>
          <div class="period-name text-truncate" title="<?= e($p['name'] ?: 'คาบ ' . $p['num']) ?>">
            <?= e($p['name'] ?: 'คาบ ' . $p['num']) ?>
          </div>
          <div class="period-time mono text-muted"><?= e($p['label']) ?></div>
        </th>
      <?php endforeach; ?>
    </tr>
<?php }

function renderSchedulerTbody(array $grid, string $selectedGroup): void {
    for ($d = 1; $d <= 5; $d++): ?>
        <tr data-day="<?= $d ?>">
          <td class="day-cell">
            วัน<?= TH_DAYS[$d] ?>
          </td>
          <?php 
          $daySlots = $grid[$d] ?? [];
          foreach ($daySlots as $cell): 
          ?>
            <?php if ($cell['type'] === 'flag' || $cell['type'] === 'lunch'): ?>
              <?php $s = $cell['slot']; ?>
              <td colspan="<?= $cell['colspan'] ?>">
                <div class="admin-slot-card admin-slot-card-special slot-type-<?= $cell['type'] ?>"
                     draggable="true"
                     data-slot-id="<?= (int) $s['id'] ?>"
                     data-day="<?= $d ?>"
                     data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                     data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                     data-span="<?= $cell['colspan'] ?>"
                     data-period-num="<?= (int) $cell['period']['num'] ?>">
                  <div class="slot-resize-handle handle-left" data-side="left" title="ลากขอบซ้ายเพื่อปรับเวลาเริ่มต้น"></div>
                  <div class="slot-resize-handle handle-right" data-side="right" title="ลากขอบขวาเพื่อปรับเวลาสิ้นสุด"></div>
                  
                  <div class="card-head">
                    <span class="subject-code d-inline-flex align-items-center gap-1">
                      <?= icon($cell['type'] === 'flag' ? 'flag' : 'clock', 12) ?>
                      <span><?= $cell['type'] === 'flag' ? 'FLAG' : 'LUNCH' ?></span>
                    </span>
                    <span class="slot-drag-handle" title="ลากเพื่อย้ายคาบเรียน">⠿</span>
                  </div>
                  <div class="subject-name" title="<?= e($cell['title']) ?>">
                    <?= e($cell['title']) ?>
                  </div>
                  <div class="card-meta">
                    <span class="card-room text-muted"><?= $cell['type'] === 'flag' ? 'หน้าเสาธง / โฮมรูม' : 'พักรับประทานอาหาร' ?></span>
                    <span class="card-time mono">
                      <?= hm($s['time_start']) ?>–<?= hm($s['time_end']) ?>
                      <?php if ($cell['colspan'] > 1): ?>
                        (<?= $cell['colspan'] ?> คาบ)
                      <?php endif; ?>
                    </span>
                  </div>

                  <!-- Hover Action Overlay -->
                  <div class="slot-hover-overlay">
                    <div class="slot-hover-actions">
                      <button type="button"
                              class="btn-overlay-action btn-overlay-edit btn-edit-slot-modal"
                              data-id="<?= (int) $s['id'] ?>"
                              data-day="<?= $d ?>"
                              data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                              data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                              data-subject-code="<?= e($s['subject_code']) ?>"
                              data-subject-name="<?= e($s['subject_name']) ?>"
                              data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                              data-room="<?= e($s['room'] ?? '') ?>"
                              data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                              title="แก้ไขข้อมูล">
                        <?= icon('pencil', 12) ?>
                        <span>แก้ไข</span>
                      </button>
                      <form method="POST" action="<?= url('admin/classes') ?>" class="d-inline m-0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="sub_action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
                        <button type="submit" class="btn-overlay-action btn-overlay-del" title="ลบ" data-confirm="ยืนยันการลบคาบ<?= $cell['type'] === 'flag' ? 'กิจกรรมหน้าเสาธง' : 'พักกลางวัน' ?>ของวัน<?= TH_DAYS[$d] ?>?">
                          <?= icon('trash', 12) ?>
                          <span>ลบ</span>
                        </button>
                      </form>
                    </div>
                    <span class="slot-overlay-hint">ลากเพื่อย้าย</span>
                  </div>
                </div>
              </td>
            <?php elseif ($cell['type'] === 'class'): ?>
              <?php $s = $cell['slot']; ?>
              <td colspan="<?= $cell['colspan'] ?>">
                <div class="admin-slot-card"
                     draggable="true"
                     data-slot-id="<?= (int) $s['id'] ?>"
                     data-day="<?= $d ?>"
                     data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                     data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                     data-span="<?= $cell['colspan'] ?>"
                     data-period-num="<?= (int) $cell['period']['num'] ?>">
                  <div class="slot-resize-handle handle-left" data-side="left" title="ลากขอบซ้ายเพื่อปรับเวลาเริ่มต้น"></div>
                  <div class="slot-resize-handle handle-right" data-side="right" title="ลากขอบขวาเพื่อปรับเวลาสิ้นสุด"></div>
                  
                  <div class="card-head">
                    <span class="subject-code" title="<?= e($s['subject_code']) ?>"><?= e($s['subject_code']) ?></span>
                    <span class="slot-drag-handle" title="ลากเพื่อย้ายคาบเรียน">⠿</span>
                  </div>
                  <div class="subject-name" title="<?= e($s['subject_name']) ?>">
                    <?= e($s['subject_name']) ?>
                  </div>
                  <div class="card-meta">
                    <span class="card-room" title="<?= e($s['room'] ?: '-') ?>">
                      <?= e($s['room'] ?: '-') ?><?= !empty($s['teacher_name']) ? ' · ' . e($s['teacher_name']) : '' ?>
                    </span>
                    <span class="card-time mono">
                      <?= hm($s['time_start']) ?>–<?= hm($s['time_end']) ?>
                      <?php if ($cell['colspan'] > 1): ?>
                        (<?= $cell['colspan'] ?> คาบ)
                      <?php endif; ?>
                    </span>
                  </div>

                  <!-- Hover Action Overlay -->
                  <div class="slot-hover-overlay">
                    <div class="slot-hover-actions">
                      <button type="button"
                              class="btn-overlay-action btn-overlay-edit btn-edit-slot-modal"
                              data-id="<?= (int) $s['id'] ?>"
                              data-day="<?= $d ?>"
                              data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                              data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                              data-subject-code="<?= e($s['subject_code']) ?>"
                              data-subject-name="<?= e($s['subject_name']) ?>"
                              data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                              data-room="<?= e($s['room'] ?? '') ?>"
                              data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                              title="แก้ไขข้อมูล">
                        <?= icon('pencil', 12) ?>
                        <span>แก้ไข</span>
                      </button>
                      <form method="POST" action="<?= url('admin/classes') ?>" class="d-inline m-0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="sub_action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
                        <button type="submit" class="btn-overlay-action btn-overlay-del" title="ลบ" data-confirm="ยืนยันการลบคาบเรียนนี้?">
                          <?= icon('trash', 12) ?>
                          <span>ลบ</span>
                        </button>
                      </form>
                    </div>
                    <span class="slot-overlay-hint">ลากเพื่อย้าย</span>
                  </div>
                </div>
              </td>
            <?php else: ?>
              <td class="admin-slot-empty btn-open-slot-modal"
                  data-dropzone="true"
                  data-day="<?= $d ?>"
                  data-time-start="<?= e(substr($cell['period']['start'], 0, 5)) ?>"
                  data-time-end="<?= e(substr($cell['period']['end'], 0, 5)) ?>"
                  data-period-num="<?= (int) $cell['period']['num'] ?>"
                  data-group="<?= e($selectedGroup) ?>"
                  title="คลิกเพื่อเพิ่มคาบเรียน ณ เวลานี้">
                <div class="empty-slot-inner">
                  <span class="btn-slot-add">
                    <?= icon('plus', 13) ?>
                  </span>
                  <span class="empty-slot-label">เพิ่มคาบ</span>
                </div>
              </td>
            <?php endif; ?>
          <?php endforeach; ?>
        </tr>
    <?php endfor;
}

// --------------------------------------------------------------------------
// AJAX Handlers (Drag-and-Drop & Slot Resize)
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_action'] ?? '') === 'ajax_update_slot') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        verify_csrf();
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
    } catch (Exception $e) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_action'] ?? '') === 'ajax_update_special_period') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        verify_csrf();
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
    } catch (Exception $e) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// --------------------------------------------------------------------------
// Standard Form Submissions (CRUD & Period Settings)
// --------------------------------------------------------------------------
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            ScheduleService::deleteClassSlot($delId);
            flash('ลบคาบเรียนออกจากตารางเรียบร้อยแล้ว');
        }
        $groupRedirect = trim((string) ($_POST['class_group'] ?? ''));
        redirect('admin/classes' . ($groupRedirect ? '?group=' . urlencode($groupRedirect) : ''));
    }

    if ($subAction === 'save') {
        $editId      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $dayOfWeek   = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
        $timeStart   = trim((string) ($_POST['time_start'] ?? ''));
        $timeEnd     = trim((string) ($_POST['time_end'] ?? ''));
        $subjectCode = trim((string) ($_POST['subject_code'] ?? ''));
        $subjectName = trim((string) ($_POST['subject_name'] ?? ''));
        $teacherName = trim((string) ($_POST['teacher_name'] ?? ''));
        $room        = trim((string) ($_POST['room'] ?? ''));
        $classGroup  = trim((string) ($_POST['class_group'] ?? 'ปวช.2/1'));
        $isAjax      = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || ($_POST['ajax'] ?? '') === '1';

        if (!$dayOfWeek || $timeStart === '' || $timeEnd === '' || $subjectName === '') {
            $error = 'กรุณากรอกข้อมูลวัน เวลา และชื่อวิชาให้ครบถ้วน';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            try {
                $saved = ScheduleService::saveClassSlot([
                    'id'           => $editId,
                    'day_of_week'  => $dayOfWeek,
                    'time_start'   => $timeStart,
                    'time_end'     => $timeEnd,
                    'subject_code' => $subjectCode,
                    'subject_name' => $subjectName,
                    'teacher_name' => $teacherName,
                    'room'         => $room,
                    'class_group'  => $classGroup,
                ]);

                if ($isAjax) {
                    $gridData = ScheduleService::getTimetableGrid($classGroup);
                    ob_start();
                    renderSchedulerTbody($gridData['grid'], $classGroup);
                    $tbodyHtml = ob_get_clean();

                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success'    => true,
                        'message'    => $editId ? 'แก้ไขข้อมูลคาบเรียนเรียบร้อยแล้ว' : 'เพิ่มคาบเรียนลงในตารางเรียบร้อยแล้ว',
                        'slot'       => $saved,
                        'tbody_html' => $tbodyHtml,
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                flash($editId ? 'แก้ไขข้อมูลคาบเรียนเรียบร้อยแล้ว' : 'เพิ่มคาบเรียนลงในตารางเรียบร้อยแล้ว');
                redirect('admin/classes?group=' . urlencode($classGroup));
            } catch (Exception $e) {
                $error = $e->getMessage();
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(422);
                    echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }
    }

    if ($subAction === 'save_periods') {
        $pNums   = $_POST['p_num'] ?? [];
        $pStarts = $_POST['p_start'] ?? [];
        $pEnds   = $_POST['p_end'] ?? [];
        $pTypes  = $_POST['p_type'] ?? [];
        $pNames  = $_POST['p_name'] ?? [];

        $newPeriods = [];
        foreach ($pNums as $idx => $num) {
            $s = trim((string) ($pStarts[$idx] ?? ''));
            $e = trim((string) ($pEnds[$idx] ?? ''));
            if ($s !== '' && $e !== '') {
                $newPeriods[] = [
                    'num'   => (int) $num,
                    'start' => $s,
                    'end'   => $e,
                    'type'  => $pTypes[$idx] ?? 'class',
                    'name'  => trim((string) ($pNames[$idx] ?? '')),
                ];
            }
        }

        if (!empty($newPeriods)) {
            ScheduleService::savePeriods($newPeriods);
            flash('บันทึกการตั้งค่าคาบเวลาเรียนเรียบร้อยแล้ว');
        }
        $grp = trim((string) ($_POST['current_group'] ?? ''));
        redirect('admin/classes' . ($grp ? '?group=' . urlencode($grp) : ''));
    }
}

// --------------------------------------------------------------------------
// View Data Preparation
// --------------------------------------------------------------------------
$containerFluid = true;
require_once __DIR__ . '/../includes/admin-header.php';

$action   = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'grid';
$viewMode = filter_input(INPUT_GET, 'view', FILTER_DEFAULT) ?: 'grid';
$slotId   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Available Groups
$availableGroups = ScheduleService::getAvailableGroups();
if (empty($availableGroups)) {
    $availableGroups = ['ปวช.2/1'];
}
$queryGroup = trim((string) (filter_input(INPUT_GET, 'group', FILTER_DEFAULT) ?? ''));
$selectedGroup = in_array($queryGroup, $availableGroups, true) ? $queryGroup : $availableGroups[0];

// Data for visual grid and period settings
$gridData = ScheduleService::getTimetableGrid($selectedGroup);
$periods  = $gridData['periods'];
$grid     = $gridData['grid'];

// Pre-fill for quick slot addition
$presetDay   = filter_input(INPUT_GET, 'preset_day', FILTER_VALIDATE_INT);
$presetStart = filter_input(INPUT_GET, 'preset_start', FILTER_DEFAULT);
$presetEnd   = filter_input(INPUT_GET, 'preset_end', FILTER_DEFAULT);

$editItem = null;
if ($action === 'edit' && $slotId) {
    $editItem = ScheduleService::getClassSlotById($slotId);
}

$allSlots = ScheduleService::getAllClassSlots();
?>

<!-- Header & Toolbar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <p class="eyebrow eyebrow-muted mb-0">ตารางเรียนและเวลาเรียน</p>
    <h1 style="font-size: 1.5rem; margin: 0; font-family: var(--font-heading); font-weight: 600;">ระบบจัดการและจัดตารางเรียนรายสัปดาห์</h1>
  </div>
  <div class="d-flex align-items-center gap-2">
    <?php if ($action === 'new' || $action === 'edit'): ?>
      <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup)) ?>" class="btn btn-line">
        <?= icon('arrow-left', 16) ?>
        <span>กลับหน้าตารางเรียน</span>
      </a>
    <?php else: ?>
      <button type="button" class="btn btn-line" data-bs-toggle="modal" data-bs-target="#periodsModal">
        <?= icon('clock', 16) ?>
        <span>ตั้งค่าคาบเวลาเรียน</span>
      </button>
      <button type="button" class="btn btn-navy btn-open-slot-modal" data-group="<?= e($selectedGroup) ?>">
        <?= icon('plus', 16) ?>
        <span>เพิ่มคาบเรียน</span>
      </button>
    <?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
  <!-- ====================================================================
       FORM: ADD / EDIT CLASS SLOT
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3><?= $action === 'edit' ? 'แก้ไขคาบเรียน #' . (int) $editItem['id'] : 'เพิ่มคาบเรียนใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/classes') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label for="class_group" class="form-label">กลุ่มเรียน / ชั้นปี <span class="req">*</span></label>
          <input type="text"
                 name="class_group"
                 id="class_group"
                 class="form-control"
                 placeholder="ปวช.2/1"
                 value="<?= e($_POST['class_group'] ?? $editItem['class_group'] ?? $selectedGroup) ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="day_of_week" class="form-label">วันในสัปดาห์ <span class="req">*</span></label>
          <select name="day_of_week" id="day_of_week" class="form-select" required>
            <?php for ($d = 1; $d <= 5; $d++): ?>
              <option value="<?= $d ?>" <?= (int) ($_POST['day_of_week'] ?? $editItem['day_of_week'] ?? $presetDay ?? 1) === $d ? 'selected' : '' ?>>
                วัน<?= TH_DAYS[$d] ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-6 col-md-2">
          <label for="time_start" class="form-label">เวลาเริ่ม <span class="req">*</span></label>
          <input type="time"
                 name="time_start"
                 id="time_start"
                 class="form-control mono"
                 value="<?= e(substr($_POST['time_start'] ?? $editItem['time_start'] ?? $presetStart ?? '08:30', 0, 5)) ?>"
                 required>
        </div>

        <div class="col-6 col-md-2">
          <label for="time_end" class="form-label">เวลาสิ้นสุด <span class="req">*</span></label>
          <input type="time"
                 name="time_end"
                 id="time_end"
                 class="form-control mono"
                 value="<?= e(substr($_POST['time_end'] ?? $editItem['time_end'] ?? $presetEnd ?? '10:30', 0, 5)) ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="subject_code" class="form-label">รหัสวิชา <span class="req">*</span></label>
          <input type="text"
                 name="subject_code"
                 id="subject_code"
                 class="form-control mono"
                 placeholder="30204-2001"
                 value="<?= e($_POST['subject_code'] ?? $editItem['subject_code'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-8">
          <label for="subject_name" class="form-label">ชื่อรายวิชา <span class="req">*</span></label>
          <input type="text"
                 name="subject_name"
                 id="subject_name"
                 class="form-control"
                 placeholder="การพัฒนาเว็บแอปพลิเคชัน"
                 value="<?= e($_POST['subject_name'] ?? $editItem['subject_name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="teacher_name" class="form-label">อาจารย์ผู้สอน</label>
          <input type="text"
                 name="teacher_name"
                 id="teacher_name"
                 class="form-control"
                 placeholder="อ.ธีรภัทร์ รัตนโชติ"
                 value="<?= e($_POST['teacher_name'] ?? $editItem['teacher_name'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
          <label for="room" class="form-label">ห้องเรียน / ห้องปฏิบัติการ</label>
          <input type="text"
                 name="room"
                 id="room"
                 class="form-control mono"
                 placeholder="Lab 301"
                 value="<?= e($_POST['room'] ?? $editItem['room'] ?? '') ?>">
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup)) ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       GROUP FILTER & VIEW TOGGLE BAR
       ==================================================================== -->
  <div class="scheduler-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 py-2 px-3 mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="fw-semibold text-navy d-inline-flex align-items-center gap-1" style="font-size: .875rem;">
        <?= icon('users', 15) ?> <span>กลุ่มเรียน:</span>
      </span>
      <div class="d-flex gap-1 flex-wrap">
        <?php foreach ($availableGroups as $grp): ?>
          <a href="<?= url('admin/classes?group=' . urlencode($grp) . '&view=' . $viewMode) ?>"
             class="btn-group-chip <?= $selectedGroup === $grp ? 'is-active' : '' ?>">
            <?= e($grp) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <span class="mono text-muted d-none d-md-inline" style="font-size: 11.5px;">[TIP] ชี้ที่คาบเพื่อแก้ไข/ลบ หรือลากเพื่อจัดตาราง</span>
      <div class="d-inline-flex border" style="border-radius: 0; padding: 2px; background: var(--c-bg-alt);">
        <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup) . '&view=grid') ?>"
           class="view-mode-btn <?= $viewMode === 'grid' ? 'is-active' : '' ?>" title="มุมมองผังตาราง">
          <?= icon('grid', 14) ?> <span>ผังตาราง</span>
        </a>
        <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup) . '&view=list') ?>"
           class="view-mode-btn <?= $viewMode === 'list' ? 'is-active' : '' ?>" title="มุมมองรายการ">
          <?= icon('news', 14) ?> <span>รายการ</span>
        </a>
      </div>
    </div>
  </div>

  <?php if ($viewMode === 'grid'): ?>
    <!-- Mobile/Tablet Day Selector Tabs (< 1024px) -->
    <div class="admin-scheduler-day-tabs d-flex d-lg-none mb-3" id="adminSchedulerDayTabs">
      <?php for ($d = 1; $d <= 5; $d++): ?>
        <button type="button" class="admin-scheduler-day-tab <?= $d === 1 ? 'is-active' : '' ?>" data-target-day="<?= $d ?>">
          วัน<?= TH_DAYS[$d] ?>
        </button>
      <?php endfor; ?>
    </div>

    <!-- ====================================================================
         INTERACTIVE DRAG-AND-DROP SCHEDULER GRID
         ==================================================================== -->
    <div class="timetable-admin-wrap mb-3">
      <table class="timetable-admin-table"
             id="schedulerGrid"
             data-csrf="<?= e(csrf_token()) ?>"
             data-group="<?= e($selectedGroup) ?>"
             data-endpoint="<?= url('admin/classes') ?>"
             data-periods="<?= e(json_encode($periods, JSON_UNESCAPED_UNICODE)) ?>">
        <thead>
          <?php renderSchedulerThead($periods); ?>
        </thead>
        <tbody>
          <?php renderSchedulerTbody($grid, $selectedGroup); ?>
        </tbody>
      </table>
    </div>

  <?php else: ?>
    <!-- ====================================================================
         TRADITIONAL LIST VIEW (SPLIT BY DAY OF THE WEEK)
         ==================================================================== -->
    <div class="admin-card">
      <div class="admin-card-head d-flex justify-content-between align-items-center">
        <h3>รายวิชาทั้งหมดของกลุ่ม <?= e($selectedGroup) ?></h3>
        <span class="mono text-muted" style="font-size: 13px;">จัดแบ่งตามวันในสัปดาห์</span>
      </div>

      <?php
      $groupSlots = array_values(array_filter($allSlots, fn($s) => $s['class_group'] === $selectedGroup));
      $daysMap = [1 => 'วันจันทร์', 2 => 'วันอังคาร', 3 => 'วันพุธ', 4 => 'วันพฤหัสบดี', 5 => 'วันศุกร์'];
      ?>

      <?php if (empty($groupSlots)): ?>
        <div class="text-center py-5 text-muted">
          <?= icon('info', 24) ?>
          <p class="mt-2 mb-0">ยังไม่มีข้อมูลคาบเรียนสำหรับกลุ่ม <?= e($selectedGroup) ?></p>
        </div>
      <?php else: ?>
        <?php foreach ($daysMap as $dNum => $dName): ?>
          <?php
          $daySlots = array_values(array_filter($groupSlots, fn($s) => (int) $s['day_of_week'] === $dNum));
          usort($daySlots, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
          ?>
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom" style="border-bottom: 2px solid var(--c-navy) !important;">
              <span class="badge-count" style="font-size: 13px;"><?= count($daySlots) ?> วิชา</span>
              <h4 class="m-0 text-navy fw-bold" style="font-size: 1.15rem;"><?= $dName ?></h4>
            </div>

            <?php if (empty($daySlots)): ?>
              <p class="text-muted small py-2 ps-2 mb-0">ไม่มีการเรียนการสอนในวันนี้</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th style="width: 140px;">เวลา</th>
                      <th>รหัสวิชา / รายวิชา</th>
                      <th>อาจารย์ผู้สอน</th>
                      <th style="width: 110px;">ห้องเรียน</th>
                      <th class="text-end" style="width: 110px;">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($daySlots as $s): ?>
                      <tr>
                        <td class="mono fw-semibold text-navy" style="font-size: 14px;">
                          <?= hm($s['time_start']) ?> – <?= hm($s['time_end']) ?>
                        </td>
                        <td>
                          <span class="mono text-accent-ink fw-bold d-block" style="font-size: 13px;"><?= e($s['subject_code']) ?></span>
                          <span class="fw-semibold text-navy" style="font-size: 15px;"><?= e($s['subject_name']) ?></span>
                        </td>
                        <td>
                          <span style="font-size: 14px;"><?= e($s['teacher_name'] ?: '-') ?></span>
                        </td>
                        <td>
                          <span class="mono fw-medium" style="font-size: 13.5px;"><?= e($s['room'] ?: '-') ?></span>
                        </td>
                        <td class="text-end">
                          <div class="d-inline-flex gap-1">
                            <a href="<?= url('admin/classes?action=edit&id=' . (int) $s['id'] . '&group=' . urlencode($selectedGroup)) ?>"
                               class="btn btn-action-icon btn-line" title="แก้ไข">
                              <?= icon('pencil', 15) ?>
                            </a>
                            <form method="POST" action="<?= url('admin/classes') ?>" class="d-inline m-0">
                              <?= csrf_field() ?>
                              <input type="hidden" name="sub_action" value="delete">
                              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                              <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
                              <button type="submit" class="btn btn-action-icon btn-danger-line" title="ลบ" data-confirm="ยืนยันการลบคาบเรียนนี้?">
                                <?= icon('trash', 15) ?>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<!-- ========================================================================
     MODAL: EDIT SCHEDULE PERIODS (เวลาคาบเรียนมาตรฐาน สอศ.)
     ======================================================================== -->
<div class="modal fade" id="periodsModal" tabindex="-1" aria-labelledby="periodsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="periodsModalLabel">ตั้งค่าคาบเวลาเรียน (Schedule Periods Configuration)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save_periods">
        <input type="hidden" name="current_group" value="<?= e($selectedGroup) ?>">

        <div class="modal-body">
          <p class="text-muted small mb-3">
            กำหนดการแบ่งคาบเรียน เวลาเริ่มต้นและสิ้นสุดของแต่ละคาบ รวมถึงคาบพิเศษเช่น พักกลางวัน หรือกิจกรรมหน้าเสาธง
          </p>
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="tablePeriodsModal">
              <thead class="table-light">
                <tr>
                  <th style="width: 65px;">คาบที่</th>
                  <th style="width: 105px;">เวลาเริ่ม</th>
                  <th style="width: 105px;">เวลาสิ้นสุด</th>
                  <th style="width: 140px;">ประเภท</th>
                  <th>ชื่อระบุ (ถ้ามี)</th>
                  <th style="width: 45px;" class="text-center">ลบ</th>
                </tr>
              </thead>
              <tbody id="periodsTbody">
                <?php foreach ($periods as $idx => $p): ?>
                  <tr>
                    <td>
                      <input type="number" name="p_num[]" class="form-control form-control-sm mono" value="<?= (int) $p['num'] ?>" required>
                    </td>
                    <td>
                      <input type="time" name="p_start[]" class="form-control form-control-sm mono" value="<?= e($p['start']) ?>" required>
                    </td>
                    <td>
                      <input type="time" name="p_end[]" class="form-control form-control-sm mono" value="<?= e($p['end']) ?>" required>
                    </td>
                    <td>
                      <select name="p_type[]" class="form-select form-select-sm">
                        <option value="class" <?= ($p['type'] ?? '') === 'class' ? 'selected' : '' ?>>คาบเรียนปกติ</option>
                        <option value="lunch" <?= ($p['type'] ?? '') === 'lunch' ? 'selected' : '' ?>>พักกลางวัน</option>
                        <option value="flag" <?= ($p['type'] ?? '') === 'flag' ? 'selected' : '' ?>>หน้าเสาธง/โฮมรูม</option>
                      </select>
                    </td>
                    <td>
                      <input type="text" name="p_name[]" class="form-control form-control-sm" placeholder="เช่น กิจกรรมหน้าเสาธง" value="<?= e($p['name'] ?? '') ?>">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-danger-line p-0 btn-del-period" style="width: 30px; height: 30px; font-size: 13px; line-height: 28px;" title="ลบคาบนี้">✕</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button type="button" class="btn btn-sm btn-line mt-2 py-1 px-3 fw-semibold" id="btnAddPeriod">
            <?= icon('plus', 15) ?> <span>เพิ่มคาบใหม่</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy">บันทึกการตั้งค่าคาบเวลา</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================================================
     MODAL: ADD / EDIT CLASS SLOT
     ======================================================================== -->
<div class="modal fade" id="slotModal" tabindex="-1" aria-labelledby="slotModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="slotModalTitle">เพิ่มคาบเรียนใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>" id="slotModalForm">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save">
        <input type="hidden" name="id" id="modalSlotId" value="">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <label for="modalClassGroup" class="form-label">กลุ่มเรียน / ชั้นปี <span class="req">*</span></label>
              <input type="text"
                     name="class_group"
                     id="modalClassGroup"
                     class="form-control"
                     placeholder="ปวช.2/1"
                     value="<?= e($selectedGroup) ?>"
                     required>
            </div>

            <div class="col-12 col-md-4">
              <label for="modalDayOfWeek" class="form-label">วันในสัปดาห์ <span class="req">*</span></label>
              <select name="day_of_week" id="modalDayOfWeek" class="form-select" required>
                <?php for ($d = 1; $d <= 5; $d++): ?>
                  <option value="<?= $d ?>">วัน<?= TH_DAYS[$d] ?></option>
                <?php endfor; ?>
              </select>
            </div>

            <div class="col-6 col-md-2">
              <label for="modalTimeStart" class="form-label">เวลาเริ่ม <span class="req">*</span></label>
              <input type="time"
                     name="time_start"
                     id="modalTimeStart"
                     class="form-control mono"
                     value="08:30"
                     required>
            </div>

            <div class="col-6 col-md-2">
              <label for="modalTimeEnd" class="form-label">เวลาสิ้นสุด <span class="req">*</span></label>
              <input type="time"
                     name="time_end"
                     id="modalTimeEnd"
                     class="form-control mono"
                     value="10:30"
                     required>
            </div>

            <div class="col-12 col-md-4">
              <label for="modalSubjectCode" class="form-label">รหัสวิชา <span class="req">*</span></label>
              <input type="text"
                     name="subject_code"
                     id="modalSubjectCode"
                     class="form-control mono"
                     placeholder="30204-2001"
                     required>
            </div>

            <div class="col-12 col-md-8">
              <label for="modalSubjectName" class="form-label">ชื่อรายวิชา <span class="req">*</span></label>
              <input type="text"
                     name="subject_name"
                     id="modalSubjectName"
                     class="form-control"
                     placeholder="การพัฒนาเว็บแอปพลิเคชัน"
                     required>
            </div>

            <div class="col-12 col-md-6">
              <label for="modalTeacherName" class="form-label">อาจารย์ผู้สอน</label>
              <input type="text"
                     name="teacher_name"
                     id="modalTeacherName"
                     class="form-control"
                     placeholder="อ.ธีรภัทร์ รัตนโชติ">
            </div>

            <div class="col-12 col-md-6">
              <label for="modalRoom" class="form-label">ห้องเรียน / ห้องปฏิบัติการ</label>
              <input type="text"
                     name="room"
                     id="modalRoom"
                     class="form-control mono"
                     placeholder="Lab 301">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy" id="modalSubmitBtn">
            <?= icon('check', 16) ?>
            <span>บันทึกข้อมูล</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= url('assets/js/admin-scheduler.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/admin-scheduler.js') ? filemtime(ROOT_PATH . '/assets/js/admin-scheduler.js') : '1' ?>"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
