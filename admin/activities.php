<?php
/**
 * Admin — จัดการตารางกิจกรรม
 */
$adminNav = 'activities';
$pageTitle = 'จัดการตารางกิจกรรม';

require_once __DIR__ . '/../includes/admin-header.php';

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$actId  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error  = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            q("DELETE FROM activities WHERE id = ?", [$delId]);
            flash('ลบกิจกรรมเรียบร้อยแล้ว');
        }
        redirect('admin/activities.php');
    }

    if ($subAction === 'save') {
        $editId       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title        = trim((string) ($_POST['title'] ?? ''));
        $description  = trim((string) ($_POST['description'] ?? ''));
        $eventDate    = trim((string) ($_POST['event_date'] ?? ''));
        $eventDateEnd = trim((string) ($_POST['event_date_end'] ?? '')) ?: null;
        $timeStart    = trim((string) ($_POST['time_start'] ?? '')) ?: null;
        $timeEnd      = trim((string) ($_POST['time_end'] ?? '')) ?: null;
        $location     = trim((string) ($_POST['location'] ?? ''));
        $badgeText    = trim((string) ($_POST['badge_text'] ?? ''));
        $actionUrl    = trim((string) ($_POST['action_url'] ?? '')) ?: null;

        if ($title === '' || $eventDate === '') {
            $error = 'กรุณากรอกชื่อกิจกรรมและวันที่จัดกิจกรรม';
        } else {
            if ($editId) {
                q("
                  UPDATE activities
                  SET title = ?, description = ?, event_date = ?, event_date_end = ?, time_start = ?, time_end = ?, location = ?, badge_text = ?, action_url = ?
                  WHERE id = ?
                ", [$title, $description, $eventDate, $eventDateEnd, $timeStart, $timeEnd, $location, $badgeText, $actionUrl, $editId]);
                flash('แก้ไขข้อมูลกิจกรรมเรียบร้อยแล้ว');
            } else {
                q("
                  INSERT INTO activities (title, description, event_date, event_date_end, time_start, time_end, location, badge_text, action_url)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$title, $description, $eventDate, $eventDateEnd, $timeStart, $timeEnd, $location, $badgeText, $actionUrl]);
                flash('เพิ่มกิจกรรมใหม่เรียบร้อยแล้ว');
            }
            redirect('admin/activities.php');
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $actId) {
    $editItem = q("SELECT * FROM activities WHERE id = ?", [$actId])->fetch();
}

// Fetch all activities
$allActivities = q("
  SELECT * FROM activities
  ORDER BY event_date DESC, id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // ACTIVITIES & CALENDAR</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการตารางกิจกรรมแผนก</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/activities.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มกิจกรรมใหม่</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/activities.php') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายการกิจกรรม</span>
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
      <h3><?= $action === 'edit' ? 'แก้ไขกิจกรรม #' . $editItem['id'] : 'เพิ่มกิจกรรมใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/activities.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-8">
          <label for="title" class="form-label">ชื่อกิจกรรม / งานเสวนา <span class="req">*</span></label>
          <input type="text"
                 name="title"
                 id="title"
                 class="form-control"
                 placeholder="DBT Hackathon 2026"
                 value="<?= e($_POST['title'] ?? $editItem['title'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="badge_text" class="form-label">ป้ายสถานะ (Badge Text)</label>
          <input type="text"
                 name="badge_text"
                 id="badge_text"
                 class="form-control mono"
                 placeholder="SOON, UPCOMING, OPEN"
                 value="<?= e($_POST['badge_text'] ?? $editItem['badge_text'] ?? 'UPCOMING') ?>">
        </div>

        <div class="col-12 col-md-4">
          <label for="event_date" class="form-label">วันที่เริ่มจัดกิจกรรม <span class="req">*</span></label>
          <input type="date"
                 name="event_date"
                 id="event_date"
                 class="form-control mono"
                 value="<?= e($_POST['event_date'] ?? $editItem['event_date'] ?? date('Y-m-d')) ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="event_date_end" class="form-label">วันที่สิ้นสุดกิจกรรม (กรณีจัดหลายวัน)</label>
          <input type="date"
                 name="event_date_end"
                 id="event_date_end"
                 class="form-control mono"
                 value="<?= e($_POST['event_date_end'] ?? $editItem['event_date_end'] ?? '') ?>">
        </div>

        <div class="col-6 col-md-2">
          <label for="time_start" class="form-label">เวลาเริ่ม</label>
          <input type="time"
                 name="time_start"
                 id="time_start"
                 class="form-control mono"
                 value="<?= e($_POST['time_start'] ?? $editItem['time_start'] ?? '09:00') ?>">
        </div>

        <div class="col-6 col-md-2">
          <label for="time_end" class="form-label">เวลาสิ้นสุด</label>
          <input type="time"
                 name="time_end"
                 id="time_end"
                 class="form-control mono"
                 value="<?= e($_POST['time_end'] ?? $editItem['time_end'] ?? '16:00') ?>">
        </div>

        <div class="col-12 col-md-6">
          <label for="location" class="form-label">สถานที่จัดงาน</label>
          <input type="text"
                 name="location"
                 id="location"
                 class="form-control"
                 placeholder="อาคารวิทยบริการ ชั้น 3 / หอประชุมใหญ่"
                 value="<?= e($_POST['location'] ?? $editItem['location'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
          <label for="action_url" class="form-label">ลิงก์ลงทะเบียน / เอกสารแนบ (Action URL)</label>
          <input type="url"
                 name="action_url"
                 id="action_url"
                 class="form-control mono"
                 placeholder="https://forms.gle/... หรือ https://..."
                 value="<?= e($_POST['action_url'] ?? $editItem['action_url'] ?? '') ?>">
        </div>

        <div class="col-12">
          <label for="description" class="form-label">รายละเอียดกิจกรรม</label>
          <textarea name="description"
                    id="description"
                    rows="3"
                    class="form-control"><?= e($_POST['description'] ?? $editItem['description'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/activities.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       ACTIVITIES LIST TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>รายการกิจกรรมทั้งหมด (<?= count($allActivities) ?> กิจกรรม)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 120px;">วันที่</th>
            <th>ชื่อกิจกรรม</th>
            <th>เวลา</th>
            <th>สถานที่</th>
            <th>ป้ายกำกับ</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allActivities)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีกิจกรรมในระบบ</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allActivities as $a): ?>
              <tr>
                <td class="mono" style="font-size: 12px;">
                  <?= mono_date($a['event_date']) ?>
                  <?php if (!empty($a['event_date_end'])): ?>
                    <span class="d-block text-muted" style="font-size: 10px;">– <?= mono_date($a['event_date_end']) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold d-block"><?= e($a['title']) ?></span>
                  <?php if (!empty($a['description'])): ?>
                    <span class="text-muted text-truncate d-block" style="max-width: 320px; font-size: 12px;">
                      <?= e($a['description']) ?>
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($a['action_url'])): ?>
                    <a href="<?= e($a['action_url']) ?>" target="_blank" rel="noopener" class="text-navy mono" style="font-size: 11px;">
                      <?= icon('external', 11) ?> ลิงก์แนบ
                    </a>
                  <?php endif; ?>
                </td>
                <td class="mono" style="font-size: 12px;">
                  <?= hm($a['time_start']) ?><?= !empty($a['time_end']) ? '–' . hm($a['time_end']) : '' ?>
                </td>
                <td><?= e($a['location']) ?></td>
                <td>
                  <?php $adminCd = activity_countdown_badge($a['event_date'], $a['event_date_end'] ?? null); ?>
                  <span class="<?= e($adminCd['class']) ?>" style="font-size: 10px;"><?= e($adminCd['text']) ?></span>
                  <?php if (!empty($a['badge_text'])): ?>
                    <span class="badge-line" style="font-size: 10px;"><?= e($a['badge_text']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('admin/activities.php?action=edit&id=' . $a['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                      <?= icon('pencil', 13) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/activities.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $a['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบกิจกรรมนี้?">
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
