<?php
declare(strict_types=1);

/**
 * Admin Timetable Management Controller
 * จัดการตารางเรียนแบบอัปโหลดรูปภาพ / แก้ไขจากตารางโดยตรง
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

    // บันทึก / แก้ไขข้อมูลตารางเรียน (จากตารางหรือโมดอล)
    if ($subAction === 'save_asset') {
        $editId    = trim((string) ($_POST['id'] ?? ''));
        $groupName = trim((string) ($_POST['group_name'] ?? ''));
        $title     = trim((string) ($_POST['title'] ?? ''));

        if ($groupName === '' || $title === '') {
            $error = 'กรุณาระบุระดับชั้น/กลุ่มเรียน และชื่อหัวข้อตารางเรียนให้ครบถ้วน';
        } else {
            try {
                $filePath = upload_schedule_file('schedule_file');
                if (!$filePath && $editId === '') {
                    throw new RuntimeException('กรุณาเลือกไฟล์รูปภาพหรือ PDF ของตารางเรียน');
                }

                $category = (str_contains($groupName, 'ครู') || str_contains($groupName, 'อาจารย์')) ? 'teacher' : 'student';

                $data = [
                    'category'   => $category,
                    'group_name' => $groupName,
                    'title'      => $title,
                ];

                if ($editId !== '') {
                    $data['id'] = $editId;
                }
                if ($filePath) {
                    $data['image_path'] = $filePath;
                }

                ScheduleService::saveScheduleImage($data);

                flash($editId !== '' ? 'บันทึกการแก้ไขตารางเรียนเรียบร้อยแล้ว' : 'เพิ่มตารางเรียนใหม่เรียบร้อยแล้ว');
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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการตาราง</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการตารางเรียน</h1>
    <p class="text-muted small mb-0 mt-1">สามารถแก้ไข เปลี่ยนรูปภาพ หรือเพิ่มตารางเรียนได้จากตารางด้านล่างโดยตรง</p>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-navy d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
      <?= icon('plus', 16) ?>
      <span>เพิ่มตารางเรียน</span>
    </button>
    <a href="<?= url('schedule') ?>" target="_blank" class="btn btn-line d-inline-flex align-items-center gap-2">
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

<!-- List of Uploaded Schedules in Table View (Single Unified View) -->
<div class="admin-card">
  <div class="admin-card-head d-flex justify-content-between align-items-center">
    <div>
      <h3 class="mb-0">ตารางแสดงรายการไฟล์ตารางเรียน</h3>
      <span class="text-muted small">จัดการและแก้ไขไฟล์ตารางเรียนประจำแต่ละระดับชั้น</span>
    </div>
    <span class="badge-count"><?= count($scheduleAssets) ?> รายการ</span>
  </div>

  <?php if (empty($scheduleAssets)): ?>
    <div class="empty-state p-5 text-center text-muted">
      <?= icon('file', 40) ?>
      <h4 class="mt-3">ยังไม่มีไฟล์ตารางเรียน</h4>
      <p>กดปุ่ม "เพิ่มตารางเรียน" ด้านบนเพื่อเริ่มอัปโหลดรูปภาพตารางเรียน</p>
      <button type="button" class="btn btn-navy btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        <?= icon('plus', 14) ?> เพิ่มตารางเรียนใหม่
      </button>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 130px;">ระดับชั้น</th>
            <th style="width: 100px;">รูปตัวอย่าง</th>
            <th>ชื่อหัวข้อตารางเรียน</th>
            <th style="width: 110px;">ชนิดไฟล์</th>
            <th style="width: 140px;">วันที่อัปเดต</th>
            <th class="text-end" style="width: 120px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($scheduleAssets as $item): ?>
            <?php
              $itemId   = (string) ($item['id'] ?? '');
              $grpName  = (string) ($item['group_name'] ?? '');
              $ttl      = (string) ($item['title'] ?? '');
              $filePath = (string) ($item['image_path'] ?? '');
              $isPdf    = str_ends_with(strtolower($filePath), '.pdf');
              $fileUrl  = url($filePath);
            ?>
            <tr>
              <td>
                <span class="badge-count fw-bold" style="font-size: 13px;"><?= e($grpName ?: '-') ?></span>
              </td>
              <td>
                <?php if (!$isPdf && !empty($filePath)): ?>
                  <a href="<?= e($fileUrl) ?>" target="_blank" title="คลิกเพื่อดูรูปภาพขนาดเต็ม" class="d-inline-block text-decoration-none">
                    <img src="<?= e($fileUrl) ?>" alt="<?= e($ttl) ?>" class="rounded border" style="width: 75px; height: 50px; object-fit: cover; display: block; transition: transform 0.15s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                  </a>
                <?php elseif ($isPdf): ?>
                  <a href="<?= e($fileUrl) ?>" target="_blank" class="d-inline-flex flex-column align-items-center justify-content-center rounded border bg-light text-decoration-none p-1" style="width: 75px; height: 50px;" title="เปิดเอกสาร PDF">
                    <?= icon('file-pdf', 18) ?>
                    <span class="text-danger fw-bold" style="font-size: 9px;">PDF</span>
                  </a>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="fw-bold d-block text-navy" style="font-size: 14.5px;"><?= e($ttl ?: '-') ?></span>
                <span class="text-muted d-block" style="font-size: 12.5px;"><?= $isPdf ? 'ไฟล์เอกสาร PDF' : 'ไฟล์รูปภาพตารางเรียน' ?></span>
              </td>
              <td>
                <span class="tag" style="font-size: 12px;"><?= $isPdf ? 'PDF' : 'รูปภาพ' ?></span>
              </td>
              <td style="font-family: var(--font-body); font-size: 13px;">
                <span class="text-navy"><?= thai_date($item['updated_at'] ?? 'now') ?></span>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <!-- ปุ่มดูไฟล์ -->
                  <a href="<?= e($fileUrl) ?>" target="_blank" class="btn btn-action-icon btn-line" title="ดูไฟล์">
                    <?= icon('external', 16) ?>
                  </a>

                  <!-- ปุ่มแก้ไข / เปลี่ยนรูปภาพ -->
                  <button type="button"
                          class="btn btn-action-icon btn-line"
                          onclick="openEditScheduleModal(<?= htmlspecialchars(json_encode([
                            'id'         => $itemId,
                            'group_name' => $grpName,
                            'title'      => $ttl,
                            'image_path' => $filePath,
                            'file_url'   => $fileUrl,
                            'is_pdf'     => $isPdf,
                          ]), ENT_QUOTES, 'UTF-8') ?>)"
                          title="แก้ไข / เปลี่ยนรูป">
                    <?= icon('pencil', 16) ?>
                  </button>

                  <!-- ปุ่มลบ -->
                  <form method="POST" action="<?= url('admin/classes') ?>" onsubmit="return confirm('ยืนยันลบไฟล์ตารางเรียนนี้?');" class="d-inline m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sub_action" value="delete_asset">
                    <input type="hidden" name="id" value="<?= e($itemId) ?>">
                    <button type="submit" class="btn btn-action-icon btn-danger-line" title="ลบ">
                      <?= icon('trash', 16) ?>
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

<!-- =========================================================================
     Modal 1: แก้ไขข้อมูล / เปลี่ยนรูปภาพตารางเรียน (Edit Modal)
     ========================================================================= -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-navy text-white py-3 px-4">
        <h5 class="modal-title fw-bold" id="editScheduleModalTitle">แก้ไขตารางเรียน</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save_asset">
        <input type="hidden" name="id" id="editScheduleId" value="">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="editScheduleGroup">ระดับชั้น / กลุ่มเรียน <span class="text-danger">*</span></label>
            <input type="text" name="group_name" id="editScheduleGroup" class="form-control" list="groupSuggestions" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="editScheduleTitle">ชื่อหัวข้อตารางเรียน <span class="text-danger">*</span></label>
            <input type="text" name="title" id="editScheduleTitle" class="form-control" required>
          </div>

          <!-- Current Image Preview -->
          <div class="mb-3">
            <label class="form-label fw-semibold d-block">รูปภาพ / ไฟล์ปัจจุบัน</label>
            <div id="editCurrentFileWrap" class="p-2 border rounded bg-light text-center">
              <img id="editCurrentImg" src="" alt="ภาพปัจจุบัน" style="max-height: 120px; object-fit: contain; display: inline-block;">
              <div id="editCurrentPdf" class="d-none py-2 text-danger">
                <?= icon('file-pdf', 32) ?>
                <div class="small fw-bold mt-1">ไฟล์เอกสาร PDF</div>
              </div>
            </div>
          </div>

          <!-- Upload New File -->
          <div class="mb-3">
            <label class="form-label fw-semibold" for="editScheduleFile">เลือกไฟล์ใหม่เพื่อแทนที่ (JPG, PNG, WebP หรือ PDF)</label>
            <input type="file" name="schedule_file" id="editScheduleFile" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="previewEditFile(this)">
            <div class="form-text text-muted small">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรูปภาพเดิม (ขนาดไม่เกิน 5 MB)</div>
            <div id="editFilePreviewWrap" class="mt-2 d-none">
              <div class="small text-muted mb-1">ตัวอย่างรูปใหม่ที่เลือก:</div>
              <img id="editFilePreviewImg" src="" alt="ตัวอย่างใหม่" style="max-height: 120px; border-radius: 6px; border: 1px solid #ddd; object-fit: contain;">
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-line btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy btn-sm d-inline-flex align-items-center gap-1">
            <?= icon('check', 14) ?> บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================================
     Modal 2: เพิ่มตารางเรียนใหม่ (Add Modal)
     ========================================================================= -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-navy text-white py-3 px-4">
        <h5 class="modal-title fw-bold" id="addScheduleModalTitle">เพิ่มตารางเรียนใหม่</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save_asset">
        <input type="hidden" name="id" value="">

        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="addScheduleGroup">ระดับชั้น / กลุ่มเรียน <span class="text-danger">*</span></label>
            <input type="text" name="group_name" id="addScheduleGroup" class="form-control" list="groupSuggestions" placeholder="เช่น ปวช.1 หรือ ปวช.2/1" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="addScheduleTitle">ชื่อหัวข้อตารางเรียน <span class="text-danger">*</span></label>
            <input type="text" name="title" id="addScheduleTitle" class="form-control" placeholder="เช่น ตารางเรียน ระดับชั้น ปวช.1 ภาคเรียนที่ 1/2569" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="addScheduleFile">เลือกไฟล์ตารางเรียน (รูปภาพ JPG, PNG, WebP หรือ PDF) <span class="text-danger">*</span></label>
            <input type="file" name="schedule_file" id="addScheduleFile" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="previewAddFile(this)">
            <div class="form-text text-muted small">รองรับไฟล์รูปภาพ JPG, PNG, WebP หรือไฟล์ PDF ขนาดไม่เกิน 5 MB</div>
            <div id="addFilePreviewWrap" class="mt-2 d-none">
              <div class="small text-muted mb-1">ตัวอย่างไฟล์ที่เลือก:</div>
              <img id="addFilePreviewImg" src="" alt="ตัวอย่าง" style="max-height: 120px; border-radius: 6px; border: 1px solid #ddd; object-fit: contain;">
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-line btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy btn-sm d-inline-flex align-items-center gap-1">
            <?= icon('upload', 14) ?> บันทึกและอัปโหลด
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<datalist id="groupSuggestions">
  <?php foreach ($allGroups as $grp): ?>
    <option value="<?= e($grp) ?>"></option>
  <?php endforeach; ?>
</datalist>

<script>
function openEditScheduleModal(item) {
  document.getElementById('editScheduleId').value = item.id || '';
  document.getElementById('editScheduleGroup').value = item.group_name || '';
  document.getElementById('editScheduleTitle').value = item.title || '';
  
  const currentImg = document.getElementById('editCurrentImg');
  const currentPdf = document.getElementById('editCurrentPdf');
  const previewWrap = document.getElementById('editFilePreviewWrap');
  previewWrap.classList.add('d-none');
  document.getElementById('editScheduleFile').value = '';

  if (item.is_pdf) {
    currentImg.classList.add('d-none');
    currentPdf.classList.remove('d-none');
  } else if (item.file_url) {
    currentImg.src = item.file_url;
    currentImg.classList.remove('d-none');
    currentPdf.classList.add('d-none');
  } else {
    currentImg.classList.add('d-none');
    currentPdf.classList.add('d-none');
  }

  const modalEl = document.getElementById('editScheduleModal');
  if (window.bootstrap && bootstrap.Modal) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

function previewEditFile(input) {
  const wrap = document.getElementById('editFilePreviewWrap');
  const img = document.getElementById('editFilePreviewImg');
  if (input.files && input.files[0] && input.files[0].type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      wrap.classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    wrap.classList.add('d-none');
  }
}

function previewAddFile(input) {
  const wrap = document.getElementById('addFilePreviewWrap');
  const img = document.getElementById('addFilePreviewImg');
  if (input.files && input.files[0] && input.files[0].type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      wrap.classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    wrap.classList.add('d-none');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
