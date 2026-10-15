<?php
declare(strict_types=1);

/**
 * Admin Timetable & Courses Controller
 * จัดการตารางเรียนรายสัปดาห์ & ทะเบียนรายวิชา
 *
 * Architecture:
 * - Controller: admin/classes.php
 * - Catalog Component: includes/renderers/classes-catalog-view.php
 * - Scheduler Component: includes/renderers/scheduler-table.php
 * - Modals Component: includes/renderers/classes-modals.php
 * - Domain Service: includes/services/ScheduleService.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';
require_once __DIR__ . '/../includes/renderers/scheduler-table.php';

AuthService::requireLogin();

$pageTitle = 'ตารางเรียนและหลักสูตร';
$adminNav  = 'classes';
$error     = '';

// --------------------------------------------------------------------------
// POST Action Handlers
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'save') {
        $editId      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $classId     = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT) ?: null;
        $dayOfWeek   = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
        $timeStart   = trim((string) ($_POST['time_start'] ?? ''));
        $timeEnd     = trim((string) ($_POST['time_end'] ?? ''));
        $subjectCode = trim((string) ($_POST['subject_code'] ?? ''));
        $subjectName = trim((string) ($_POST['subject_name'] ?? ''));
        $teacherName = trim((string) ($_POST['teacher_name'] ?? ''));
        $room        = trim((string) ($_POST['room'] ?? ''));
        $classGroup  = trim((string) ($_POST['class_group'] ?? ''));

        if ($classId && ($subjectCode === '' || $subjectName === '')) {
            $masterClass = ScheduleService::getClassById($classId);
            if ($masterClass) {
                $subjectCode = $subjectCode !== '' ? $subjectCode : $masterClass['subject_code'];
                $subjectName = $subjectName !== '' ? $subjectName : $masterClass['subject_name'];
                $teacherName = $teacherName !== '' ? $teacherName : ($masterClass['default_teacher'] ?? '');
                $room        = $room !== '' ? $room : ($masterClass['default_room'] ?? '');
            }
        }

        $validationError = match (true) {
            !$dayOfWeek || $dayOfWeek < 1 || $dayOfWeek > 5 => 'วันในสัปดาห์ไม่ถูกต้อง (ต้องเป็นวันจันทร์-ศุกร์)',
            $timeStart === '' || $timeEnd === '' => 'กรุณาระบุเวลาเริ่มต้นและสิ้นสุดของคาบเรียน',
            strtotime("1970-01-01 $timeStart") >= strtotime("1970-01-01 $timeEnd") => 'เวลาเริ่มต้นต้องมาก่อนเวลาสิ้นสุด',
            $subjectCode === '' || $subjectName === '' => 'กรุณาระบุรหัสวิชาและชื่อรายวิชา',
            $classGroup === '' => 'กรุณาระบุกลุ่มเรียน เช่น ปวช.2/1 หรือ ปวส.1/2',
            default => null,
        };

        if ($validationError !== null) {
            $error = $validationError;
        } else {
            try {
                ScheduleService::saveClassSlot([
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

                flash($editId ? 'แก้ไขข้อมูลคาบเรียนเรียบร้อยแล้ว' : 'เพิ่มคาบเรียนลงในตารางเรียบร้อยแล้ว');
                redirect('admin/classes?group=' . urlencode($classGroup));
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    if ($subAction === 'save_course') {
        $courseId       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $subjectCode    = trim((string) ($_POST['subject_code'] ?? ''));
        $subjectName    = trim((string) ($_POST['subject_name'] ?? ''));
        $courseType     = in_array($_POST['course_type'] ?? '', ['professional', 'regular'], true) ? $_POST['course_type'] : 'professional';
        $defaultTeacher = trim((string) ($_POST['default_teacher'] ?? ''));
        $defaultRoom    = trim((string) ($_POST['default_room'] ?? ''));
        $credit         = (int) ($_POST['credit'] ?? 3);

        if ($subjectCode === '' || $subjectName === '') {
            $error = 'กรุณาระบุรหัสวิชาและชื่อวิชา';
        } else {
            try {
                ScheduleService::saveClass([
                    'id'              => $courseId,
                    'subject_code'    => $subjectCode,
                    'subject_name'    => $subjectName,
                    'course_type'     => $courseType,
                    'default_teacher' => $defaultTeacher,
                    'default_room'    => $defaultRoom,
                    'credit'          => $credit,
                ]);
                flash($courseId ? 'แก้ไขข้อมูลรายวิชาเรียบร้อยแล้ว' : 'เพิ่มรายวิชาใหม่ในระบบเรียบร้อยแล้ว');
                redirect('admin/classes?tab=catalog');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    if ($subAction === 'delete_course') {
        $courseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($courseId) {
            try {
                ScheduleService::deleteClass($courseId);
                flash('ลบรายวิชาออกจากระบบเรียบร้อยแล้ว');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        redirect('admin/classes?tab=catalog');
    }

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $grp   = trim((string) ($_POST['class_group'] ?? ''));
        if ($delId) {
            ScheduleService::deleteClassSlot($delId);
            flash('ลบคาบเรียนออกจากตารางเรียบร้อยแล้ว');
        }
        redirect('admin/classes' . ($grp ? '?group=' . urlencode($grp) : ''));
    }

    if ($subAction === 'restore_default_special_slots') {
        $grp = trim((string) ($_POST['class_group'] ?? ''));
        if ($grp !== '') {
            $restoredCount = ScheduleService::restoreDefaultSpecialSlots($grp);
            flash("กู้คืนคาบกิจกรรมมาตรฐาน (เสาธง/พักกลางวัน) เรียบร้อยแล้ว ({$restoredCount} รายการ)");
        }
        redirect('admin/classes' . ($grp ? '?group=' . urlencode($grp) : ''));
    }

    if ($subAction === 'save_periods') {
        $pNums   = $_POST['p_num'] ?? [];
        $pStarts = $_POST['p_start'] ?? [];
        $pEnds   = $_POST['p_end'] ?? [];
        $pTypes  = $_POST['p_type'] ?? [];
        $pNames  = $_POST['p_name'] ?? [];

        $newPeriods = [];
        $invalidTime = false;
        foreach ($pNums as $idx => $num) {
            $s = trim((string) ($pStarts[$idx] ?? ''));
            $e = trim((string) ($pEnds[$idx] ?? ''));
            if ($s !== '' && $e !== '') {
                if (strtotime("1970-01-01 $s") >= strtotime("1970-01-01 $e")) {
                    $invalidTime = true;
                    continue;
                }
                $newPeriods[] = [
                    'num'   => (int) $num,
                    'start' => $s,
                    'end'   => $e,
                    'type'  => $pTypes[$idx] ?? 'class',
                    'name'  => trim((string) ($pNames[$idx] ?? '')),
                ];
            }
        }

        if ($invalidTime) {
            flash('บางช่วงเวลาเริ่มต้นมากกว่าหรือเท่ากับเวลาสิ้นสุด จึงถูกข้ามการบันทึก', 'error');
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

$tab      = filter_input(INPUT_GET, 'tab', FILTER_DEFAULT) ?: 'scheduler';
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

// Timetable Grid and Period Settings
$gridData = ScheduleService::getTimetableGrid($selectedGroup);
$periods  = $gridData['periods'];
$grid     = $gridData['grid'];

// Master Courses Catalog Data
$allCourses = ScheduleService::getClasses();
$prefixes   = ScheduleService::getClassPrefixes();
$smartGroup = str_contains($selectedGroup, 'ปวช') ? 'ปวช' : 'ปวส';

// Group courses for Smart Level Filtering in slot modal
$recommendedCourses = [];
$otherCourses       = [];
foreach ($allCourses as $c) {
    $code = $c['subject_code'];
    $isMatch = ($smartGroup === 'ปวช')
        ? (str_starts_with($code, '20204-') || str_starts_with($code, '20000-'))
        : (str_starts_with($code, '30204-') || str_starts_with($code, '30000-'));

    if ($isMatch) {
        $recommendedCourses[] = $c;
    } else {
        $otherCourses[] = $c;
    }
}

// Catalog filter parameters
$filterPrefix   = filter_input(INPUT_GET, 'prefix', FILTER_DEFAULT) ?: 'all';
$filterType     = filter_input(INPUT_GET, 'type', FILTER_DEFAULT) ?: 'all';
$catalogCourses = ScheduleService::getClasses($filterPrefix, $filterType);

// Preset parameters for slot addition
$presetDay   = filter_input(INPUT_GET, 'preset_day', FILTER_VALIDATE_INT);
$presetStart = filter_input(INPUT_GET, 'preset_start', FILTER_DEFAULT);
$presetEnd   = filter_input(INPUT_GET, 'preset_end', FILTER_DEFAULT);

$editItem = ($action === 'edit' && $slotId) ? ScheduleService::getClassSlotById($slotId) : null;
$allSlots = ScheduleService::getAllClassSlots();
?>

<!-- Header & Toolbar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <p class="eyebrow eyebrow-muted mb-0">ตารางเรียนและหลักสูตร</p>
    <h1 style="font-size: 1.5rem; margin: 0; font-family: var(--font-heading); font-weight: 600;">
      <?= $tab === 'catalog' ? 'ทะเบียนรายวิชา' : 'ระบบจัดการและจัดตารางเรียนรายสัปดาห์' ?>
    </h1>
  </div>
  <div class="d-flex align-items-center gap-2">
    <?php if ($tab === 'catalog'): ?>
      <button type="button" class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#courseModal" id="btnOpenNewCourseModal">
        <?= icon('plus', 16) ?>
        <span>เพิ่มรายวิชาใหม่</span>
      </button>
    <?php else: ?>
      <?php if ($action === 'new' || $action === 'edit'): ?>
        <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup)) ?>" class="btn btn-line">
          <?= icon('arrow-left', 16) ?>
          <span>กลับหน้าตารางเรียน</span>
        </a>
      <?php else: ?>
        <button type="button" class="btn btn-line" data-bs-toggle="modal" data-bs-target="#restoreSpecialSlotsModal" title="กู้คืนคาบกิจกรรมมาตรฐาน (หน้าเสาธงและพักกลางวัน)">
          <?= icon('refresh', 16) ?>
          <span>กู้คืนคาบกิจกรรมมาตรฐาน</span>
        </button>
        <button type="button" class="btn btn-line" data-bs-toggle="modal" data-bs-target="#periodsModal">
          <?= icon('clock', 16) ?>
          <span>ตั้งค่าคาบเวลาเรียน</span>
        </button>
        <button type="button" class="btn btn-navy btn-open-slot-modal" data-group="<?= e($selectedGroup) ?>">
          <?= icon('plus', 16) ?>
          <span>เพิ่มคาบเรียน</span>
        </button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Topic Selector: Sharp Cornered Editorial Buttons (Same Style as Rest of Web) -->
<nav class="d-flex align-items-center gap-2 mb-4" aria-label="หมวดหมู่การทำงาน">
  <a class="btn btn-sm <?= $tab === 'scheduler' ? 'btn-navy' : 'btn-line' ?> py-2 px-3 fw-semibold d-inline-flex align-items-center gap-2 rounded-0"
     href="<?= url('admin/classes?tab=scheduler' . ($selectedGroup ? '&group=' . urlencode($selectedGroup) : '')) ?>"
     style="border-radius: 0 !important;">
    <?= icon('calendar', 16) ?> <span>ผังตารางเรียน</span>
  </a>
  <a class="btn btn-sm <?= $tab === 'catalog' ? 'btn-navy' : 'btn-line' ?> py-2 px-3 fw-semibold d-inline-flex align-items-center gap-2 rounded-0"
     href="<?= url('admin/classes?tab=catalog') ?>"
     style="border-radius: 0 !important;">
    <?= icon('book', 16) ?> <span>ทะเบียนรายวิชา</span>
    <span class="badge <?= $tab === 'catalog' ? 'bg-white text-dark' : 'bg-light text-muted border' ?> ms-1 rounded-0" style="font-size: 11px; border-radius: 0 !important;"><?= count($allCourses) ?></span>
  </a>
</nav>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<?php if ($tab === 'catalog'): ?>
  <!-- Render Courses Master Catalog Component -->
  <?php require_once __DIR__ . '/../includes/renderers/classes-catalog-view.php'; ?>
<?php else: ?>
  <?php if ($action === 'new' || $action === 'edit'): ?>
    <!-- Full-Page Form: Add / Edit Slot Fallback -->
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
            <input type="text" name="class_group" id="class_group" class="form-control" placeholder="ปวช.2/1" value="<?= e($_POST['class_group'] ?? $editItem['class_group'] ?? $selectedGroup) ?>" required>
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
            <input type="time" name="time_start" id="time_start" class="form-control mono" value="<?= e(substr($_POST['time_start'] ?? $editItem['time_start'] ?? $presetStart ?? '08:30', 0, 5)) ?>" required>
          </div>
          <div class="col-6 col-md-2">
            <label for="time_end" class="form-label">เวลาสิ้นสุด <span class="req">*</span></label>
            <input type="time" name="time_end" id="time_end" class="form-control mono" value="<?= e(substr($_POST['time_end'] ?? $editItem['time_end'] ?? $presetEnd ?? '10:30', 0, 5)) ?>" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="subject_code" class="form-label">รหัสวิชา <span class="req">*</span></label>
            <input type="text" name="subject_code" id="subject_code" class="form-control mono" placeholder="30204-2001" value="<?= e($_POST['subject_code'] ?? $editItem['subject_code'] ?? '') ?>" required>
          </div>
          <div class="col-12 col-md-8">
            <label for="subject_name" class="form-label">ชื่อรายวิชา <span class="req">*</span></label>
            <input type="text" name="subject_name" id="subject_name" class="form-control" placeholder="การพัฒนาเว็บแอปพลิเคชัน" value="<?= e($_POST['subject_name'] ?? $editItem['subject_name'] ?? '') ?>" required>
          </div>
          <div class="col-12 col-md-6">
            <label for="teacher_name" class="form-label">อาจารย์ผู้สอน</label>
            <input type="text" name="teacher_name" id="teacher_name" class="form-control" placeholder="อ.ธีรภัทร์ รัตนโชติ" value="<?= e($_POST['teacher_name'] ?? $editItem['teacher_name'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label for="room" class="form-label">ห้องเรียน / ห้องปฏิบัติการ</label>
            <input type="text" name="room" id="room" class="form-control mono" placeholder="Lab 301" value="<?= e($_POST['room'] ?? $editItem['room'] ?? '') ?>">
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
    <!-- Scheduler Toolbar -->
    <div class="scheduler-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 py-2 px-3 mb-3">
      <div class="d-flex align-items-center gap-2">
        <label for="adminClassGroupSelect" class="fw-semibold text-navy d-inline-flex align-items-center gap-1 mb-0" style="font-size: .875rem; cursor: pointer;">
          <?= icon('users', 15) ?> <span>กลุ่มเรียน:</span>
        </label>
        <select id="adminClassGroupSelect"
                class="form-select form-select-sm"
                style="width: auto; min-width: 170px; cursor: pointer;"
                onchange="location.href='<?= url('admin/classes?view=' . $viewMode . '&group=') ?>' + encodeURIComponent(this.value)">
          <?php foreach ($availableGroups as $grp): ?>
            <option value="<?= e($grp) ?>" <?= $selectedGroup === $grp ? 'selected' : '' ?>>
              <?= e($grp) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="d-inline-flex gap-1">
          <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup) . '&view=grid') ?>"
             class="btn btn-sm <?= $viewMode === 'grid' ? 'btn-navy shadow-sm' : 'btn-outline-secondary bg-white' ?> d-inline-flex align-items-center gap-1 rounded-0"
             title="มุมมองผังตาราง">
            <?= icon('grid', 14) ?> <span>ผังตาราง</span>
          </a>
          <a href="<?= url('admin/classes?group=' . urlencode($selectedGroup) . '&view=list') ?>"
             class="btn btn-sm <?= $viewMode === 'list' ? 'btn-navy shadow-sm' : 'btn-outline-secondary bg-white' ?> d-inline-flex align-items-center gap-1 rounded-0"
             title="มุมมองรายการ">
            <?= icon('news', 14) ?> <span>รายการ</span>
          </a>
        </div>
      </div>
    </div>

    <?php if ($viewMode === 'grid'): ?>
      <div class="admin-scheduler-day-tabs d-flex d-lg-none mb-3" id="adminSchedulerDayTabs">
        <?php for ($d = 1; $d <= 5; $d++): ?>
          <button type="button" class="admin-scheduler-day-tab <?= $d === 1 ? 'is-active' : '' ?>" data-target-day="<?= $d ?>">
            วัน<?= TH_DAYS[$d] ?>
          </button>
        <?php endfor; ?>
      </div>

      <div class="timetable-admin-wrap mb-3">
        <div id="schedulerLivePreview" class="scheduler-live-preview d-none">
          <div class="scheduler-live-preview-badge"></div>
        </div>
        <table class="timetable-admin-table"
               id="schedulerGrid"
               data-csrf="<?= e(csrf_token()) ?>"
               data-group="<?= e($selectedGroup) ?>"
               data-endpoint="<?= url('api/admin/classes') ?>"
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
      <!-- Traditional List View -->
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
<?php endif; ?>

<!-- Render Modals Component -->
<?php require_once __DIR__ . '/../includes/renderers/classes-modals.php'; ?>

<?php
$schedulerScripts = [
    'assets/js/modules/scheduler-ui.js',
    'assets/js/modules/scheduler-api.js',
    'assets/js/modules/scheduler-dnd.js',
    'assets/js/modules/scheduler-resize.js',
    'assets/js/modules/scheduler-periods.js',
    'assets/js/modules/scheduler-modals.js',
    'assets/js/modules/scheduler-combobox.js',
    'assets/js/admin-scheduler.js',
];
foreach ($schedulerScripts as $scriptRel):
    $fullPath = ROOT_PATH . '/' . $scriptRel;
    $ver = file_exists($fullPath) ? filemtime($fullPath) : '1';
?>
<script src="<?= url($scriptRel) ?>?v=<?= $ver ?>"></script>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
