<?php
/**
 * Admin — จัดการตารางเรียน
 */
$adminNav = 'classes';
$pageTitle = 'จัดการตารางเรียน';

require_once __DIR__ . '/../includes/admin-header.php';

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$slotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error  = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            q("DELETE FROM class_schedule WHERE id = ?", [$delId]);
            flash('ลบคาบเรียนออกจากตารางเรียบร้อยแล้ว');
        }
        redirect('admin/classes.php');
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

        if (!$dayOfWeek || $timeStart === '' || $timeEnd === '' || $subjectName === '') {
            $error = 'กรุณากรอกข้อมูลวัน เวลา และชื่อวิชาให้ครบถ้วน';
        } else {
            if ($editId) {
                q("
                  UPDATE class_schedule
                  SET day_of_week = ?, time_start = ?, time_end = ?, subject_code = ?, subject_name = ?, teacher_name = ?, room = ?, class_group = ?
                  WHERE id = ?
                ", [$dayOfWeek, $timeStart, $timeEnd, $subjectCode, $subjectName, $teacherName, $room, $classGroup, $editId]);
                flash('แก้ไขข้อมูลคาบเรียนเรียบร้อยแล้ว');
            } else {
                q("
                  INSERT INTO class_schedule (day_of_week, time_start, time_end, subject_code, subject_name, teacher_name, room, class_group)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", [$dayOfWeek, $timeStart, $timeEnd, $subjectCode, $subjectName, $teacherName, $room, $classGroup]);
                flash('เพิ่มคาบเรียนลงในตารางเรียบร้อยแล้ว');
            }
            redirect('admin/classes.php');
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $slotId) {
    $editItem = q("SELECT * FROM class_schedule WHERE id = ?", [$slotId])->fetch();
}

// Fetch all slots
$allSlots = q("
  SELECT * FROM class_schedule
  ORDER BY day_of_week ASC, time_start ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // CLASS TIMETABLE</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการตารางเรียนรายสัปดาห์</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/classes.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มคาบเรียน</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/classes.php') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายการตารางเรียน</span>
    </a>
  <?php endif; ?>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
  <!-- ====================================================================
       ADD / EDIT FORM
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3><?= $action === 'edit' ? 'แก้ไขคาบเรียน #' . $editItem['id'] : 'เพิ่มคาบเรียนใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/classes.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label for="day_of_week" class="form-label">วันในสัปดาห์ <span class="req">*</span></label>
          <select name="day_of_week" id="day_of_week" class="form-select" required>
            <?php for ($d = 1; $d <= 5; $d++): ?>
              <?php
              $selected = (int) ($_POST['day_of_week'] ?? $editItem['day_of_week'] ?? 1) === $d ? 'selected' : '';
              ?>
              <option value="<?= $d ?>" <?= $selected ?>>วัน<?= TH_DAYS[$d] ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-6 col-md-4">
          <label for="time_start" class="form-label">เวลาเริ่มเรียน <span class="req">*</span></label>
          <input type="time"
                 name="time_start"
                 id="time_start"
                 class="form-control mono"
                 value="<?= e($_POST['time_start'] ?? $editItem['time_start'] ?? '08:30') ?>"
                 required>
        </div>

        <div class="col-6 col-md-4">
          <label for="time_end" class="form-label">เวลาสิ้นสุด <span class="req">*</span></label>
          <input type="time"
                 name="time_end"
                 id="time_end"
                 class="form-control mono"
                 value="<?= e($_POST['time_end'] ?? $editItem['time_end'] ?? '10:30') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="subject_code" class="form-label">รหัสวิชา</label>
          <input type="text"
                 name="subject_code"
                 id="subject_code"
                 class="form-control mono"
                 placeholder="30204-2001"
                 value="<?= e($_POST['subject_code'] ?? $editItem['subject_code'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-8">
          <label for="subject_name" class="form-label">ชื่อวิชา <span class="req">*</span></label>
          <input type="text"
                 name="subject_name"
                 id="subject_name"
                 class="form-control"
                 placeholder="การเขียนโปรแกรมเชิงวัตถุบนเว็บ"
                 value="<?= e($_POST['subject_name'] ?? $editItem['subject_name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="teacher_name" class="form-label">อาจารย์ผู้สอน</label>
          <input type="text"
                 name="teacher_name"
                 id="teacher_name"
                 class="form-control"
                 placeholder="ดร.ศิริพร บุญเสริมประสิทธิ์"
                 value="<?= e($_POST['teacher_name'] ?? $editItem['teacher_name'] ?? '') ?>">
        </div>

        <div class="col-6 col-md-4">
          <label for="room" class="form-label">ห้องเรียน / ห้องปฏิบัติการ</label>
          <input type="text"
                 name="room"
                 id="room"
                 class="form-control"
                 placeholder="Lab 301"
                 value="<?= e($_POST['room'] ?? $editItem['room'] ?? '') ?>">
        </div>

        <div class="col-6 col-md-4">
          <label for="class_group" class="form-label">กลุ่มเรียน / ชั้นปี</label>
          <input type="text"
                 name="class_group"
                 id="class_group"
                 class="form-control"
                 placeholder="ปวช.2/1"
                 value="<?= e($_POST['class_group'] ?? $editItem['class_group'] ?? 'ปวช.2/1') ?>">
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/classes.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       CLASS SLOTS TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>ตารางเรียนทั้งหมดในระบบ (<?= count($allSlots) ?> คาบ)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 110px;">วัน</th>
            <th style="width: 120px;">เวลา</th>
            <th>รหัสวิชา / รายวิชา</th>
            <th>อาจารย์ผู้สอน</th>
            <th>ห้อง</th>
            <th>กลุ่ม</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allSlots)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">ยังไม่มีคาบเรียนในตาราง</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allSlots as $s): ?>
              <tr>
                <td>
                  <span class="fw-bold text-navy">วัน<?= TH_DAYS[$s['day_of_week']] ?? $s['day_of_week'] ?></span>
                </td>
                <td class="mono" style="font-size: 12px;">
                  <?= hm($s['time_start']) ?> – <?= hm($s['time_end']) ?>
                </td>
                <td>
                  <span class="mono text-muted d-block" style="font-size: 11px;"><?= e($s['subject_code']) ?></span>
                  <span class="fw-bold"><?= e($s['subject_name']) ?></span>
                </td>
                <td><?= e($s['teacher_name']) ?></td>
                <td><span class="mono"><?= e($s['room']) ?></span></td>
                <td><span class="mono" style="font-size: 11px;"><?= e($s['class_group']) ?></span></td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('admin/classes.php?action=edit&id=' . $s['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                      <?= icon('pencil', 13) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/classes.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $s['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบคาบเรียนนี้?">
                        <?= icon('trash', 13) ?>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
