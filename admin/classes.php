<?php
declare(strict_types=1);

/**
 * Admin Timetable Management Controller
 * จัดการตารางเรียนแบบอัปโหลดรูปภาพ / ไฟล์
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';

AuthService::requireLogin();

$pageTitle = 'จัดการตารางเรียน';
$adminNav  = 'classes';
$error     = '';

// --------------------------------------------------------------------------
// POST Action Handlers
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    // อัปโหลดไฟล์ตารางเรียน
    if ($subAction === 'upload_asset') {
        $groupName = trim((string) ($_POST['group_name'] ?? ''));
        $title     = trim((string) ($_POST['title'] ?? ''));

        if ($groupName === '' || $title === '') {
            $error = 'กรุณาระบุกลุ่มเรียน/ระดับชั้น และชื่อหัวข้อตารางเรียนให้ครบถ้วน';
        } else {
            try {
                $filePath = upload_schedule_file('schedule_file');
                if (!$filePath) {
                    throw new RuntimeException('กรุณาเลือกไฟล์ตารางเรียน (รูปภาพ หรือ PDF)');
                }

                $category = (str_contains($groupName, 'ครู') || str_contains($groupName, 'อาจารย์')) ? 'teacher' : 'student';

                ScheduleService::saveScheduleImage([
                    'category'   => $category,
                    'group_name' => $groupName,
                    'title'      => $title,
                    'image_path' => $filePath,
                ]);

                flash('อัปโหลดไฟล์ตารางเรียนเรียบร้อยแล้ว');
                redirect('admin/classes');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    // ลบไฟล์ตารางเรียน
    elseif ($subAction === 'delete_asset') {
        $deleteId = trim((string) ($_POST['id'] ?? ''));
        if ($deleteId !== '') {
            ScheduleService::deleteScheduleImage($deleteId);
            flash('ลบไฟล์ตารางเรียนเรียบร้อยแล้ว');
            redirect('admin/classes');
        }
    }
}

// --------------------------------------------------------------------------
// Prepare View Data
// --------------------------------------------------------------------------
$allGroups      = ScheduleService::getAllClassGroups();
$scheduleAssets = ScheduleService::getScheduleImages();

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการตาราง</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการตารางเรียน</h1>
    <p class="text-muted small mb-0 mt-1">อัปโหลดไฟล์ตารางเรียน (รูปภาพ หรือ PDF) สำหรับแต่ละกลุ่มเรียน แสดงเป็นตารางรายการพร้อมภาพตัวอย่าง ให้นักเรียน/นักศึกษาเปิดดูหรือดาวน์โหลดได้ทันที</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('schedule') ?>" target="_blank" class="btn btn-sm btn-line d-inline-flex align-items-center gap-2">
      <?= icon('external', 14) ?>
      <span>ดูหน้าเว็บจริง</span>
    </a>
  </div>
</div>

<?php if ($error !== ''): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<!-- Form Card: Upload Schedule File -->
<div class="admin-card mb-4">
  <div class="admin-card-head">
    <h3 class="mb-0">อัปโหลดรูปภาพหรือไฟล์ตารางเรียน</h3>
    <span class="text-muted small">เลือกไฟล์รูปภาพ (JPG, PNG, WebP) หรือไฟล์ PDF ของตารางเรียนประจำกลุ่ม</span>
  </div>
  <form method="POST" action="<?= url('admin/classes') ?>" enctype="multipart/form-data" class="p-3">
    <?= csrf_field() ?>
    <input type="hidden" name="sub_action" value="upload_asset">

    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="scheduleGroup">ระดับชั้น / กลุ่มเรียน <span class="text-danger">*</span></label>
        <input type="text" name="group_name" id="scheduleGroup" class="form-control" list="groupSuggestions" placeholder="เช่น ปวช.1, ปวช.2, ปวช.3, ปวส.1 หรือ ปวช.2/1" required>
        <datalist id="groupSuggestions">
          <?php foreach ($allGroups as $grp): ?>
            <option value="<?= e($grp) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label fw-semibold" for="scheduleTitle">ชื่อหัวข้อตารางเรียน <span class="text-danger">*</span></label>
        <input type="text" name="title" id="scheduleTitle" class="form-control" placeholder="เช่น ตารางเรียน ระดับชั้น ปวช.1 ภาคเรียนที่ 1/2569" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold" for="scheduleFile">เลือกไฟล์ตารางเรียน (รูปภาพ JPG, PNG, WebP หรือ PDF) <span class="text-danger">*</span></label>
      <input type="file" name="schedule_file" id="scheduleFile" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="previewScheduleFile(this)">
      <div class="form-text">รองรับไฟล์รูปภาพ JPG, PNG, WebP หรือไฟล์ PDF ขนาดไม่เกิน 5 MB</div>
      <div id="fileUploadPreviewWrap" class="mt-2 d-none">
        <div class="small text-muted mb-1">ตัวอย่างไฟล์ที่เลือก:</div>
        <img id="fileUploadPreviewImg" src="" alt="ตัวอย่างรูปภาพ" style="max-height: 140px; border-radius: 6px; border: 1px solid #ddd; object-fit: contain;">
      </div>
    </div>

    <button type="submit" class="btn btn-navy d-flex align-items-center justify-content-center gap-2">
      <?= icon('upload', 16) ?>
      <span>อัปโหลดตารางเรียน</span>
    </button>
  </form>
</div>

<!-- List of Uploaded Schedules in Table View -->
<div class="admin-card">
  <div class="admin-card-head d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0">ตารางแสดงรายการไฟล์ตารางเรียน</h3>
      <span class="text-muted small">แสดงรายการตารางเรียนพร้อมรูปภาพตัวอย่างและสถานะ</span>
    </div>
    <span class="badge bg-light text-navy border fw-bold"><?= count($scheduleAssets) ?> รายการ</span>
  </div>

  <div class="p-3">
    <?php if (empty($scheduleAssets)): ?>
      <div class="empty-state p-5 text-center text-muted">
        <?= icon('file', 40) ?>
        <h4 class="mt-3">ยังไม่มีไฟล์ตารางเรียน</h4>
        <p>ใช้อัปโหลดฟอร์มด้านบนเพื่อแนบไฟล์รูปภาพหรือ PDF ตารางเรียนสำหรับแต่ละกลุ่ม</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 140px;">ระดับชั้น</th>
              <th style="width: 130px;">รูปตัวอย่าง</th>
              <th>ชื่อหัวข้อตารางเรียน</th>
              <th style="width: 110px;">ชนิดไฟล์</th>
              <th style="width: 160px;">วันที่อัปเดต</th>
              <th style="width: 150px;" class="text-center">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($scheduleAssets as $item): ?>
              <?php
                $filePath = $item['image_path'] ?? '';
                $isPdf    = str_ends_with(strtolower($filePath), '.pdf');
                $fileUrl  = url($filePath);
              ?>
              <tr>
                <td>
                  <span class="badge bg-navy text-white px-2 py-1 fs-6"><?= e($item['group_name'] ?? '-') ?></span>
                </td>
                <td>
                  <?php if (!$isPdf && !empty($filePath)): ?>
                    <a href="<?= e($fileUrl) ?>" target="_blank" title="คลิกเพื่อดูรูปภาพขนาดเต็ม" class="d-inline-block text-decoration-none">
                      <img src="<?= e($fileUrl) ?>" alt="<?= e($item['title'] ?? '') ?>" class="rounded border shadow-sm" style="width: 90px; height: 60px; object-fit: cover; display: block; transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                  <?php elseif ($isPdf): ?>
                    <a href="<?= e($fileUrl) ?>" target="_blank" class="d-inline-flex flex-column align-items-center justify-content-center rounded border bg-light text-decoration-none shadow-sm p-1" style="width: 90px; height: 60px;">
                      <?= icon('file-pdf', 20) ?>
                      <span class="text-danger fw-bold mt-1" style="font-size: 10px;">PDF</span>
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-bold text-navy"><?= e($item['title'] ?? '-') ?></div>
                  <div class="text-muted small"><?= e($filePath) ?></div>
                </td>
                <td>
                  <span class="badge bg-light text-muted border">
                    <?= icon($isPdf ? 'file-pdf' : 'image', 13) ?>
                    <?= $isPdf ? 'PDF' : 'รูปภาพ' ?>
                  </span>
                </td>
                <td class="text-muted small">
                  <?= thai_date($item['updated_at'] ?? 'now') ?>
                </td>
                <td class="text-center">
                  <div class="d-flex align-items-center justify-content-center gap-2">
                    <a href="<?= e($fileUrl) ?>" target="_blank" class="btn btn-sm btn-line d-inline-flex align-items-center gap-1" title="เปิดดูไฟล์">
                      <?= icon('external', 13) ?> ดูไฟล์
                    </a>
                    <form method="POST" action="<?= url('admin/classes') ?>" onsubmit="return confirm('ยืนยันลบไฟล์ตารางเรียนนี้?');" class="d-inline m-0">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete_asset">
                      <input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="ลบไฟล์">
                        <?= icon('trash', 14) ?>
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
</div>

<script>
function previewScheduleFile(input) {
  const wrap = document.getElementById('fileUploadPreviewWrap');
  const img = document.getElementById('fileUploadPreviewImg');
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        wrap.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    } else {
      wrap.classList.add('d-none');
    }
  } else {
    wrap.classList.add('d-none');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
