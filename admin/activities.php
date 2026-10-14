<?php
/**
 * Admin - จัดการตารางกิจกรรม (Editorial Modal & Fast Management)
 * Standard: STYLE.md & RULES.md
 */
$adminNav = 'activities';
$pageTitle = 'จัดการตารางกิจกรรม';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';

AuthService::requireLogin();

$error = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            ScheduleService::deleteActivity($delId);
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
        $actionUrl    = trim((string) ($_POST['action_url'] ?? '')) ?: null;

        if ($title === '' || $eventDate === '') {
            $error = 'กรุณากรอกชื่อกิจกรรมและวันที่จัดกิจกรรม';
        } else {
            try {
                ScheduleService::saveActivity([
                    'id'             => $editId,
                    'title'          => $title,
                    'description'    => $description,
                    'event_date'     => $eventDate,
                    'event_date_end' => $eventDateEnd,
                    'time_start'     => $timeStart,
                    'time_end'       => $timeEnd,
                    'location'       => $location,
                    'action_url'     => $actionUrl,
                ]);

                flash($editId ? 'แก้ไขข้อมูลกิจกรรมเรียบร้อยแล้ว' : 'เพิ่มกิจกรรมใหม่เรียบร้อยแล้ว');
                redirect('admin/activities.php');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch all activities
$allActivities = ScheduleService::getAllActivities();

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการกิจกรรม</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการตารางกิจกรรมแผนก</h1>
  </div>
  <div>
    <button type="button" class="btn btn-navy" id="btnOpenAddActivityModal">
      <?= icon('plus', 16) ?>
      <span>เพิ่มกิจกรรมใหม่</span>
    </button>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<!-- ====================================================================
     ACTIVITIES LIST TABLE (PRODUCTIVITY RESTYLED)
     ==================================================================== -->
<div class="admin-card">
  <div class="admin-card-head d-flex justify-content-between align-items-center">
    <h3>รายการกิจกรรมทั้งหมด (<?= count($allActivities) ?> กิจกรรม)</h3>
    <span class="mono text-muted" style="font-size: 13px;">กำหนดการและปฏิทินงาน</span>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 150px;">วันที่จัดงาน</th>
          <th>ชื่อกิจกรรม</th>
          <th style="width: 130px;">เวลา</th>
          <th>สถานที่</th>
          <th class="text-center" style="width: 110px;">สถานะ</th>
          <th class="text-end" style="width: 110px;">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($allActivities)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <?= icon('info', 20) ?>
              <div class="mt-2">ยังไม่มีกิจกรรมในระบบ คลิก "เพิ่มกิจกรรมใหม่" เพื่อเริ่มสร้าง</div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($allActivities as $item): ?>
            <?php
            $isPast = strtotime($item['event_date']) < strtotime(date('Y-m-d'));
            $isToday = $item['event_date'] === date('Y-m-d');
            ?>
            <tr>
              <td class="mono fw-semibold text-navy">
                <?= thai_date($item['event_date']) ?>
                <?php if ($item['event_date_end']): ?>
                  <span class="d-block text-muted" style="font-size: 11.5px;">– <?= thai_date($item['event_date_end']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <span class="fw-bold d-block text-navy" style="font-size: 14.5px;"><?= e($item['title']) ?></span>
                <?php if (!empty($item['description'])): ?>
                  <span class="text-muted d-block text-truncate" style="max-width: 380px; font-size: 12.5px;">
                    <?= e($item['description']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td class="mono" style="font-size: 13px;">
                <?php if ($item['time_start']): ?>
                  <?= hm($item['time_start']) ?> – <?= hm($item['time_end']) ?>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size: 13.5px;"><?= e($item['location'] ?: '-') ?></span>
              </td>
              <td class="text-center">
                <?php if ($isToday): ?>
                  <span class="badge-active" style="font-size: 12px;">วันนี้</span>
                <?php elseif ($isPast): ?>
                  <span class="text-muted mono" style="font-size: 12px;">เสร็จสิ้น</span>
                <?php else: ?>
                  <span class="tag" style="font-size: 12px;">เร็ว ๆ นี้</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <?php if (!empty($item['action_url'])): ?>
                    <a href="<?= e($item['action_url']) ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-action-icon btn-line"
                       title="เปิดลิงก์ภายนอก">
                      <?= icon('external', 15) ?>
                    </a>
                  <?php endif; ?>
                  <button type="button"
                          class="btn btn-action-icon btn-line btn-edit-activity"
                          data-id="<?= (int) $item['id'] ?>"
                          data-title="<?= e($item['title']) ?>"
                          data-event-date="<?= e($item['event_date']) ?>"
                          data-event-date-end="<?= e($item['event_date_end'] ?? '') ?>"
                          data-time-start="<?= e(substr($item['time_start'] ?? '', 0, 5)) ?>"
                          data-time-end="<?= e(substr($item['time_end'] ?? '', 0, 5)) ?>"
                          data-location="<?= e($item['location'] ?? '') ?>"
                          data-action-url="<?= e($item['action_url'] ?? '') ?>"
                          data-description="<?= e($item['description'] ?? '') ?>"
                          title="แก้ไข">
                    <?= icon('pencil', 15) ?>
                  </button>
                  <form method="POST" action="<?= url('admin/activities.php') ?>" class="d-inline m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sub_action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                    <button type="submit"
                            class="btn btn-action-icon btn-danger-line"
                            title="ลบ"
                            data-confirm="ยืนยันการลบกิจกรรม '<?= e($item['title']) ?>'?">
                      <?= icon('trash', 15) ?>
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

<!-- ====================================================================
     EDITORIAL MODAL: ADD / EDIT ACTIVITY
     ==================================================================== -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="activityModalLabel">เพิ่มกิจกรรมใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>

      <form method="POST" action="<?= url('admin/activities.php') ?>" id="formActivityModal">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save">
        <input type="hidden" name="id" id="act_modal_id" value="">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="act_title" class="form-label">ชื่อกิจกรรม / งานเสวนา / การอบรม <span class="req">*</span></label>
              <input type="text"
                     name="title"
                     id="act_title"
                     class="form-control fw-bold"
                     placeholder="เช่น DBT Hackathon 2026: นวัตกรรมธุรกิจดิจิทัล"
                     required>
            </div>

            <div class="col-12 col-md-6">
              <label for="act_event_date" class="form-label">วันที่จัดกิจกรรม <span class="req">*</span></label>
              <input type="date"
                     name="event_date"
                     id="act_event_date"
                     class="form-control mono"
                     value="<?= date('Y-m-d') ?>"
                     required>
            </div>

            <div class="col-12 col-md-6">
              <label for="act_event_date_end" class="form-label">วันที่สิ้นสุด (กรณีจัดหลายวัน)</label>
              <input type="date"
                     name="event_date_end"
                     id="act_event_date_end"
                     class="form-control mono">
            </div>

            <div class="col-6 col-md-3">
              <label for="act_time_start" class="form-label">เวลาเริ่ม</label>
              <input type="time"
                     name="time_start"
                     id="act_time_start"
                     class="form-control mono"
                     value="09:00">
            </div>

            <div class="col-6 col-md-3">
              <label for="act_time_end" class="form-label">เวลาสิ้นสุด</label>
              <input type="time"
                     name="time_end"
                     id="act_time_end"
                     class="form-control mono"
                     value="16:00">
            </div>

            <div class="col-12 col-md-6">
              <label for="act_location" class="form-label">สถานที่จัดงาน</label>
              <input type="text"
                     name="location"
                     id="act_location"
                     class="form-control"
                     placeholder="อาคารวิทยบริการ ชั้น 3 / หอประชุมใหญ่">
            </div>

            <div class="col-12">
              <label for="act_action_url" class="form-label">ลิงก์ลงทะเบียน / เอกสารเพิ่มเติม</label>
              <input type="url"
                     name="action_url"
                     id="act_action_url"
                     class="form-control mono"
                     placeholder="https://forms.gle/... หรือ https://...">
            </div>

            <div class="col-12">
              <label for="act_description" class="form-label">รายละเอียดกิจกรรม</label>
              <textarea name="description"
                        id="act_description"
                        rows="3"
                        class="form-control"
                        placeholder="ระบุวัตถุประสงค์ กลุ่มเป้าหมาย หรือรายละเอียดกำหนดการ"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-sm btn-navy">
            <?= icon('check', 16) ?> <span>บันทึกกิจกรรม</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('activityModal');
  if (!modalEl) return;
  const bsModal = new bootstrap.Modal(modalEl);

  const modalTitle = document.getElementById('activityModalLabel');
  const inputId = document.getElementById('act_modal_id');
  const inputTitle = document.getElementById('act_title');
  const inputEventDate = document.getElementById('act_event_date');
  const inputEventDateEnd = document.getElementById('act_event_date_end');
  const inputTimeStart = document.getElementById('act_time_start');
  const inputTimeEnd = document.getElementById('act_time_end');
  const inputLocation = document.getElementById('act_location');
  const inputActionUrl = document.getElementById('act_action_url');
  const inputDesc = document.getElementById('act_description');

  // Open for New
  const btnAdd = document.getElementById('btnOpenAddActivityModal');
  if (btnAdd) {
    btnAdd.addEventListener('click', () => {
      modalTitle.textContent = 'เพิ่มกิจกรรมใหม่';
      inputId.value = '';
      inputTitle.value = '';
      inputEventDate.value = '<?= date('Y-m-d') ?>';
      inputEventDateEnd.value = '';
      inputTimeStart.value = '09:00';
      inputTimeEnd.value = '16:00';
      inputLocation.value = '';
      inputActionUrl.value = '';
      inputDesc.value = '';
      bsModal.show();
    });
  }

  // Open for Edit
  document.querySelectorAll('.btn-edit-activity').forEach(btn => {
    btn.addEventListener('click', () => {
      modalTitle.textContent = 'แก้ไขกิจกรรม #' + btn.dataset.id;
      inputId.value = btn.dataset.id || '';
      inputTitle.value = btn.dataset.title || '';
      inputEventDate.value = btn.dataset.eventDate || '';
      inputEventDateEnd.value = btn.dataset.eventDateEnd || '';
      inputTimeStart.value = btn.dataset.timeStart || '';
      inputTimeEnd.value = btn.dataset.timeEnd || '';
      inputLocation.value = btn.dataset.location || '';
      inputActionUrl.value = btn.dataset.actionUrl || '';
      inputDesc.value = btn.dataset.description || '';
      bsModal.show();
    });
  });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
