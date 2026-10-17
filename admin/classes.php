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

<!-- Schedule Items Standard Admin Table (Unified Design with News & Activities) -->
<div class="admin-card mb-4">
  <div class="admin-card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-2">
      <h3 class="mb-0">รายการไฟล์ตารางเรียนทั้งหมด</h3>
      <span class="badge-count" id="classesCountBadge"><?= count($scheduleAssets) ?> รายการ</span>
    </div>
    
    <!-- Unified Search Bar -->
    <div class="admin-search-wrap">
      <div class="admin-search-group">
        <span class="admin-search-prefix" aria-hidden="true"><?= icon('search', 14) ?></span>
        <input type="text"
               id="classesSearchInput"
               class="admin-search-input"
               placeholder="ค้นหาระดับชั้น หรือชื่อหัวข้อ..."
               autocomplete="off"
               aria-label="ค้นหาตารางเรียน">
        <button type="button"
                id="btnClassesClearSearch"
                class="admin-search-clear"
                title="ล้างคำค้นหา"
                aria-label="ล้างคำค้นหา"
                style="display: none;">
          <?= icon('close', 13) ?>
        </button>
      </div>
    </div>
  </div>

  <!-- Filter Pills / Tabs -->
  <div class="px-3 pt-3 pb-3 border-bottom bg-light d-flex flex-wrap align-items-center gap-2">
    <span class="text-muted small fw-semibold me-1">กรองระดับชั้น:</span>
    <button type="button" class="btn btn-sm btn-navy btn-filter-pill is-active py-1 px-3" data-filter="all" style="min-height: 32px; font-size: 13px;">
      ทั้งหมด (<?= count($scheduleAssets) ?>)
    </button>
    <button type="button" class="btn btn-sm btn-line btn-filter-pill py-1 px-3" data-filter="ปวช" style="min-height: 32px; font-size: 13px;">
      ระดับ ปวช.
    </button>
    <button type="button" class="btn btn-sm btn-line btn-filter-pill py-1 px-3" data-filter="ปวส" style="min-height: 32px; font-size: 13px;">
      ระดับ ปวส.
    </button>
    <button type="button" class="btn btn-sm btn-line btn-filter-pill py-1 px-3" data-filter="อาจารย์" style="min-height: 32px; font-size: 13px;">
      คณาจารย์
    </button>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 120px;">ระดับชั้น</th>
          <th style="width: 76px;" class="text-center">รูปตัวอย่าง</th>
          <th>ชื่อหัวข้อตารางเรียน</th>
          <th style="width: 110px;">ชนิดไฟล์</th>
          <th style="width: 140px;">วันที่อัปเดต</th>
          <th class="text-end" style="width: 140px;">จัดการ</th>
        </tr>
      </thead>
      <tbody id="scheduleTableBody">
        <?php if (empty($scheduleAssets)): ?>
          <tr id="emptyRow">
            <td colspan="6" class="text-center py-5 text-muted">
              <?= icon('file', 36) ?>
              <div class="mt-2 fw-semibold">ยังไม่มีไฟล์ตารางเรียนในระบบ</div>
              <div class="small">คลิกปุ่ม "+ เพิ่มตารางเรียน" ด้านบนเพื่อเริ่มอัปโหลด</div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($scheduleAssets as $item): ?>
            <?php
              $itemId   = (string) ($item['id'] ?? '');
              $grpName  = (string) ($item['group_name'] ?? '');
              $ttl      = (string) ($item['title'] ?? '');
              $filePath = (string) ($item['image_path'] ?? '');
              $isPdf    = str_ends_with(strtolower($filePath), '.pdf');
              $fileUrl  = url($filePath);
              $hasFile  = !empty($filePath) && is_file(ROOT_PATH . '/' . $filePath);
              $itemJson = htmlspecialchars(json_encode([
                'id'         => $itemId,
                'group_name' => $grpName,
                'title'      => $ttl,
                'image_path' => $filePath,
                'file_url'   => $fileUrl,
                'is_pdf'     => $isPdf,
              ]), ENT_QUOTES, 'UTF-8');

              // Distinguish grade level badge colors for quick scanning
              $grpLower = mb_strtolower($grpName);
              if (str_contains($grpLower, 'ปวช')) {
                  $badgeBg = '#e0f2fe';
                  $badgeColor = '#0369a1';
                  $badgeBorder = '#bae6fd';
              } elseif (str_contains($grpLower, 'ปวส')) {
                  $badgeBg = '#ede9fe';
                  $badgeColor = '#6d28d9';
                  $badgeBorder = '#ddd6fe';
              } elseif (str_contains($grpLower, 'อาจารย์') || str_contains($grpLower, 'ครู') || str_contains($grpLower, 'คณาจารย์')) {
                  $badgeBg = '#fef3c7';
                  $badgeColor = '#92400e';
                  $badgeBorder = '#fde68a';
              } else {
                  $badgeBg = '#f1f5f9';
                  $badgeColor = '#334155';
                  $badgeBorder = '#cbd5e1';
              }
            ?>
            <tr class="schedule-row"
                data-group="<?= e(mb_strtolower($grpName)) ?>"
                data-title="<?= e(mb_strtolower($ttl)) ?>">
              
              <!-- ระดับชั้น -->
              <td>
                <span class="badge-count fw-bold" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeBorder ?>; font-size: 12.5px; padding: 4px 9px;">
                  <?= e($grpName ?: '-') ?>
                </span>
              </td>

              <!-- รูปตัวอย่าง (คลิกเปิด Lightbox ได้) -->
              <td class="text-center">
                <?php if ($hasFile && !$isPdf): ?>
                  <a href="javascript:void(0)"
                     onclick="openPreviewLightbox('<?= e($fileUrl) ?>', '<?= e(addslashes($ttl)) ?>')"
                     title="คลิกเพื่อดูรูปขยาย">
                    <img src="<?= e($fileUrl) ?>"
                         alt="<?= e($ttl) ?>"
                         class="table-thumb"
                         style="cursor: zoom-in; object-fit: cover; border: 1px solid #cbd5e1; background: #ffffff; transition: transform 0.15s ease;"
                         onmouseover="this.style.transform='scale(1.1)'"
                         onmouseout="this.style.transform='scale(1)'">
                  </a>
                <?php elseif ($hasFile && $isPdf): ?>
                  <a href="<?= e($fileUrl) ?>" target="_blank" class="table-thumb d-inline-flex align-items-center justify-content-center text-danger" title="เปิดไฟล์ PDF" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <?= icon('file-pdf', 24) ?>
                  </a>
                <?php else: ?>
                  <div class="table-thumb d-inline-flex align-items-center justify-content-center text-muted" style="font-size: 11px; border: 1px solid #e2e8f0; background: #f8fafc;">
                    ไม่มีรูป
                  </div>
                <?php endif; ?>
              </td>

              <!-- ชื่อหัวข้อตารางเรียน -->
              <td>
                <span class="fw-bold d-block text-navy" style="font-size: 14.5px;"><?= e($ttl ?: '-') ?></span>
                <span class="text-muted d-block small mt-1">
                  <?= $isPdf ? 'ไฟล์เอกสารตารางเรียน PDF' : 'ไฟล์รูปภาพตารางเรียน' ?>
                  <?php if ($filePath): ?>
                    &middot; <span class="mono text-truncate" style="max-width: 320px; display: inline-block; vertical-align: bottom; font-size: 11.5px; color: #64748b;"><?= e(basename($filePath)) ?></span>
                  <?php endif; ?>
                </span>
              </td>

              <!-- ชนิดไฟล์ -->
              <td>
                <?php if ($isPdf): ?>
                  <span class="tag" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">PDF</span>
                <?php else: ?>
                  <span class="tag" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">รูปภาพ</span>
                <?php endif; ?>
              </td>

              <!-- วันที่อัปเดต -->
              <td style="font-size: 13px;">
                <span class="text-navy fw-medium"><?= thai_date($item['updated_at'] ?? 'now') ?></span>
              </td>

              <!-- จัดการ -->
              <td class="text-end">
                <div class="d-inline-flex align-items-center gap-1">
                  <!-- ดูไฟล์ -->
                  <a href="<?= e($fileUrl) ?>"
                     target="_blank"
                     class="btn btn-action-icon btn-line"
                     title="ดูไฟล์จริง / เปิดแท็บใหม่">
                    <?= icon('external', 16) ?>
                  </a>

                  <!-- แก้ไข / เปลี่ยนรูปภาพ -->
                  <button type="button"
                          class="btn btn-action-icon btn-line"
                          onclick="openEditScheduleModal(<?= $itemJson ?>)"
                          title="แก้ไขข้อมูล / เปลี่ยนรูปภาพ">
                    <?= icon('pencil', 16) ?>
                  </button>

                  <!-- ลบ -->
                  <form method="POST" action="<?= url('admin/classes') ?>" onsubmit="return confirm('ยืนยันลบไฟล์ตารางเรียนนี้?');" class="d-inline m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sub_action" value="delete_asset">
                    <input type="hidden" name="id" value="<?= e($itemId) ?>">
                    <button type="submit" class="btn btn-action-icon btn-danger-line" title="ลบตารางเรียน">
                      <?= icon('trash', 16) ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        <tr id="noResultsRow" style="display: none;">
          <td colspan="6" class="text-center py-4 text-muted">
            ไม่พบตารางเรียนที่ตรงกับคำค้นหาหรือตัวกรอง
          </td>
        </tr>
      </tbody>
    </table>
  </div>
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

<!-- =========================================================================
     Modal 3: ดูรูปภาพตารางเรียนขนาดเต็ม (Image Lightbox Modal)
     ========================================================================= -->
<div class="modal fade" id="previewImageModal" tabindex="-1" aria-labelledby="previewImageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border shadow-lg overflow-hidden" style="border-radius: var(--radius-lg, 12px);">
      <div class="modal-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #17324d !important; border-bottom: 2px solid var(--c-accent, #e5993e);">
        <h5 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2" id="previewImageModalLabel" style="color: #ffffff !important; font-size: 1.1rem;">
          <span style="color: #ffffff !important; display: inline-flex;"><?= icon('image', 20) ?></span>
          <span id="previewLightboxTitle" style="color: #ffffff !important; font-weight: 700;">ดูตัวอย่างตารางเรียน</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
          <a href="#" id="previewLightboxExternal" target="_blank" class="btn btn-sm d-inline-flex align-items-center gap-1" title="เปิดดูในแท็บใหม่" style="color: #ffffff !important; background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.4) !important; min-height: 34px; padding: 4px 12px; font-size: 13px; text-decoration: none;">
            <span style="color: #ffffff !important; display: inline-flex;"><?= icon('external', 14) ?></span>
            <span style="color: #ffffff !important;">เปิดแท็บใหม่</span>
          </a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด" style="opacity: 0.9;"></button>
        </div>
      </div>
      <div class="modal-body p-3 p-md-4 text-center d-flex align-items-center justify-content-center" style="min-height: 480px; max-height: 82vh; overflow: auto; background-color: #f1f5f9 !important;">
        <img id="previewLightboxImg" src="" alt="ภาพตารางเรียน" class="img-fluid rounded border shadow-sm" style="max-height: 78vh; object-fit: contain; background: #ffffff; padding: 4px;">
      </div>
      <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between align-items-center">
        <span class="text-muted small">คลิก "เปิดแท็บใหม่" หากต้องการพิมพ์หรือดาวน์โหลดไฟล์ขนาดเต็ม</span>
        <button type="button" class="btn btn-sm btn-navy px-3" data-bs-dismiss="modal" style="min-height: 34px;">ปิดหน้าต่าง</button>
      </div>
    </div>
  </div>
</div>

<datalist id="groupSuggestions">
  <?php foreach ($allGroups as $grp): ?>
    <option value="<?= e($grp) ?>"></option>
  <?php endforeach; ?>
</datalist>

<script>
// Filter & Live Search Functionality
let activeFilter = 'all';
const searchInput = document.getElementById('classesSearchInput');
const clearSearchBtn = document.getElementById('btnClassesClearSearch');
const filterButtons = document.querySelectorAll('.btn-filter-pill');
const tableRows = document.querySelectorAll('.schedule-row');
const noResultsRow = document.getElementById('noResultsRow');
const countBadge = document.getElementById('classesCountBadge');

function applyFilterAndSearch() {
  const query = (searchInput ? searchInput.value.trim().toLowerCase() : '');
  if (clearSearchBtn) {
    clearSearchBtn.style.display = query !== '' ? 'inline-flex' : 'none';
  }

  let visibleCount = 0;

  tableRows.forEach(row => {
    const grp = row.getAttribute('data-group') || '';
    const ttl = row.getAttribute('data-title') || '';
    
    // Check filter pill
    let matchesFilter = false;
    if (activeFilter === 'all') {
      matchesFilter = true;
    } else if (activeFilter === 'ปวช' && grp.includes('ปวช')) {
      matchesFilter = true;
    } else if (activeFilter === 'ปวส' && grp.includes('ปวส')) {
      matchesFilter = true;
    } else if (activeFilter === 'อาจารย์' && (grp.includes('อาจารย์') || grp.includes('ครู') || grp.includes('คณาจารย์') || ttl.includes('อาจารย์') || ttl.includes('ครู'))) {
      matchesFilter = true;
    }

    // Check search query
    let matchesSearch = true;
    if (query !== '') {
      matchesSearch = grp.includes(query) || ttl.includes(query);
    }

    if (matchesFilter && matchesSearch) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  if (noResultsRow) {
    noResultsRow.style.display = (visibleCount === 0 && tableRows.length > 0) ? '' : 'none';
  }
  if (countBadge) {
    countBadge.textContent = visibleCount + ' รายการ';
  }
}

if (searchInput) {
  searchInput.addEventListener('input', applyFilterAndSearch);
}
if (clearSearchBtn) {
  clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    applyFilterAndSearch();
    searchInput.focus();
  });
}

filterButtons.forEach(btn => {
  btn.addEventListener('click', function() {
    filterButtons.forEach(b => {
      b.classList.remove('btn-navy', 'is-active');
      b.classList.add('btn-line');
    });
    this.classList.remove('btn-line');
    this.classList.add('btn-navy', 'is-active');
    activeFilter = this.getAttribute('data-filter') || 'all';
    applyFilterAndSearch();
  });
});

function openPreviewLightbox(imgUrl, title) {
  const modalEl = document.getElementById('previewImageModal');
  const imgEl = document.getElementById('previewLightboxImg');
  const titleEl = document.getElementById('previewLightboxTitle');
  const extEl = document.getElementById('previewLightboxExternal');
  
  if (imgEl) imgEl.src = imgUrl;
  if (titleEl) titleEl.textContent = title || 'ดูตัวอย่างตารางเรียน';
  if (extEl) extEl.href = imgUrl;

  if (window.bootstrap && bootstrap.Modal) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

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

