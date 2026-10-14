<?php
/**
 * Admin - จัดการสมาชิกชมรมวิชาชีพ (Hierarchical Tree Slot UI)
 */
$adminNav = 'club';
$pageTitle = 'จัดการสมาชิกชมรม';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';

AuthService::requireLogin();

$action   = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$memberId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error    = null;

// Handle Form Submissions & AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);


    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            FacultyService::deleteClubMember($delId);
            flash('ลบข้อมูลสมาชิกชมรมเรียบร้อยแล้ว');
        }
        redirect('admin/club');
    }

    if ($subAction === 'save') {
        $editId     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $studentId  = trim((string) ($_POST['student_id'] ?? ''));
        $name       = trim((string) ($_POST['name'] ?? ''));
        $clubRole   = trim((string) ($_POST['club_role'] ?? ''));
        $isClubHead = !empty($_POST['is_club_head']) ? 1 : 0;

        if ($name === '' || $clubRole === '') {
            $error = 'กรุณากรอกชื่อ-นามสกุล และตำแหน่งในชมรม';
        } else {
            try {
                $newImage = !empty($_FILES['image']['name']) ? upload_image('image') : null;

                FacultyService::saveClubMember([
                    'id'           => $editId,
                    'student_id'   => clean_text($studentId),
                    'name'         => clean_text($name),
                    'club_role'    => clean_text($clubRole),
                    'is_club_head' => $isClubHead,
                ], $newImage);

                flash($editId ? 'แก้ไขข้อมูลสมาชิกชมรมเรียบร้อยแล้ว' : 'เพิ่มสมาชิกชมรมใหม่เรียบร้อยแล้ว');
                redirect('admin/club');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $memberId) {
    $editItem = FacultyService::getClubMemberById($memberId);
}

// Fetch all members and segregate into President and Officers
$allMembers = FacultyService::getAllClubMembers();
$clubPresident = null;
$officerMembers = [];

foreach ($allMembers as $member) {
    if ($member['is_club_head'] && $clubPresident === null) {
        $clubPresident = $member;
    } else {
        $officerMembers[] = $member;
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการชมรมวิชาชีพ</p>
    <h1 style="font-size: 1.75rem; margin: 0;">ผังและรายชื่อคณะกรรมการชมรมวิชาชีพ</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/club?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มสมาชิกชมรม</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/club') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายชื่อสมาชิก</span>
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
      <h3><?= $action === 'edit' ? 'แก้ไขสมาชิกชมรม #' . (int) $editItem['id'] : 'เพิ่มสมาชิกชมรมใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/club') ?>" enctype="multipart/form-data" id="form-club-editor">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label for="input-club-student-id" class="form-label">รหัสนักเรียน/นักศึกษา</label>
          <input type="text"
                 name="student_id"
                 id="input-club-student-id"
                 class="form-control mono"
                 placeholder="66209010001"
                 value="<?= e($_POST['student_id'] ?? $editItem['student_id'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-4">
          <label for="input-club-name" class="form-label">ชื่อ-นามสกุล <span class="req">*</span></label>
          <input type="text"
                 name="name"
                 id="input-club-name"
                 class="form-control"
                 placeholder="นายกิตติภูมิ ทวีโชคประเสริฐ"
                 value="<?= e($_POST['name'] ?? $editItem['name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="input-club-role" class="form-label">ตำแหน่งในชมรม <span class="req">*</span></label>
          <input type="text"
                 name="club_role"
                 id="input-club-role"
                 class="form-control"
                 placeholder="ประธานชมรม / ฝ่ายวิชาการ"
                 value="<?= e($_POST['club_role'] ?? $editItem['club_role'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">รูปถ่ายสมาชิกชมรม (สัดส่วน 1:1 หรือ 4:5)</label>
          <div class="image-upload-wrapper is-portrait"
               data-image-upload
               data-current-src="<?= !empty($editItem['image_path']) ? url($editItem['image_path']) : '' ?>">
            <div class="image-upload-preview">
              <?php if (!empty($editItem['image_path']) && file_exists(ROOT_PATH . '/' . $editItem['image_path'])): ?>
                <img src="<?= url($editItem['image_path']) ?>" class="preview-img" alt="รูปถ่ายปัจจุบัน">
                <span class="preview-badge badge-current">รูปปัจจุบัน</span>
              <?php else: ?>
                <div class="preview-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>
                  </svg>
                  <span>ยังไม่มีรูปถ่าย</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="image-upload-dropzone" role="button" tabindex="0" aria-label="คลิกเพื่อเลือกไฟล์รูปภาพ หรือลากไฟล์มาวาง">
              <input type="file"
                     name="image"
                     id="input-club-image"
                     class="image-upload-input"
                     accept="image/jpeg,image/png,image/webp">
              <div class="dropzone-content">
                <div class="dropzone-icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                  </svg>
                </div>
                <div class="dropzone-text">
                  <strong>คลิกเพื่อเลือกรูปภาพ</strong> หรือลากไฟล์มาวางที่นี่
                </div>
                <div class="dropzone-hint">
                  รองรับไฟล์ JPG, PNG หรือ WebP (ขนาดสูงสุด 5 MB · สัดส่วน 1:1 หรือ 4:5)
                </div>
                <div class="dropzone-file-selected d-none">
                  <span class="file-name mono"></span>
                  <button type="button" class="btn-clear-file" title="ล้างรูปที่เลือก">ยกเลิก</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 pt-2">
          <div class="form-check form-switch d-inline-flex align-items-center gap-2">
            <input class="form-check-input"
                   type="checkbox"
                   role="switch"
                   name="is_club_head"
                   value="1"
                   id="input-club-is-head"
                   <?= !empty($_POST['is_club_head'] ?? $editItem['is_club_head'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="input-club-is-head">
              เป็นประธานชมรมวิชาชีพ
            </label>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/club') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       HIERARCHICAL TREE-LIKE SLOT VIEW
       ==================================================================== -->
  <div class="d-flex flex-column gap-4">

    <!-- ประธานชมรมวิชาชีพ -->
    <div class="tree-tier">
      <div class="tree-tier-header">
        <div>
          <strong class="text-navy" style="font-size: 1.05rem;">ประธานชมรมวิชาชีพ</strong>
        </div>
        <span class="text-muted" style="font-size: 12px;">ลากสมาชิกมาวางที่นี่เพื่อแต่งตั้งหรือสลับตำแหน่งประธานชมรม</span>
      </div>

      <?php if ($clubPresident): ?>
        <div class="tree-slot-card tree-slot-head is-head-target"
             data-head-dropzone
             data-id="<?= (int) $clubPresident['id'] ?>"
             style="max-width: 620px; margin: 0 auto;">
          <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 0;">
            <?php if (!empty($clubPresident['image_path']) && file_exists(ROOT_PATH . '/' . $clubPresident['image_path'])): ?>
              <img src="<?= url($clubPresident['image_path']) ?>" class="tree-slot-avatar" style="width: 56px; height: 56px;" alt="">
            <?php else: ?>
              <div class="tree-slot-avatar-fallback" style="width: 56px; height: 56px; font-size: 15px;">
                <?= initials($clubPresident['name']) ?>
              </div>
            <?php endif; ?>
            <div class="text-truncate">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="tree-slot-order-badge" style="background: var(--c-navy); color: #fff; border-color: var(--c-navy);">ประธานชมรม</span>
                <strong class="text-navy" style="font-size: 1.05rem;"><?= e($clubPresident['name']) ?></strong>
              </div>
              <div class="text-muted" style="font-size: 13.5px;"><?= e($clubPresident['club_role']) ?></div>
              <?php if (!empty($clubPresident['student_id'])): ?>
                <div class="mono text-muted" style="font-size: 12px;">รหัส: <?= e($clubPresident['student_id']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="d-flex gap-1 flex-shrink-0">
            <a href="<?= url('admin/club?action=edit&id=' . (int) $clubPresident['id']) ?>"
               class="btn btn-action-icon btn-line"
               title="แก้ไข">
              <?= icon('pencil', 17) ?>
            </a>
            <form method="POST" action="<?= url('admin/club') ?>" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="sub_action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $clubPresident['id'] ?>">
              <button type="submit"
                      class="btn btn-action-icon btn-danger-line"
                      title="ลบ"
                      data-confirm="ยืนยันการลบสมาชิกชมรมท่านนี้?">
                <?= icon('trash', 17) ?>
              </button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center py-4 border dashed text-muted is-head-target"
             data-head-dropzone
             style="border-radius: var(--radius); max-width: 620px; margin: 0 auto; background: var(--c-surface);">
          <p class="mb-2">ยังไม่มีการระบุตำแหน่งประธานชมรม (ลากสมาชิกมาวางที่นี่เพื่อแต่งตั้ง)</p>
          <a href="<?= url('admin/club?action=new') ?>" class="btn btn-sm btn-navy">
            <?= icon('plus', 14) ?>
            <span>เพิ่มหรือกำหนดประธานชมรม</span>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- CONNECTOR LINE -->
    <div class="tree-connector-line"></div>

    <!-- คณะกรรมการและสมาชิกชมรม (DRAGGABLE & IN-PLACE ADD) -->
    <div class="tree-tier">
      <div class="tree-tier-header">
        <div>
          <strong class="text-navy" style="font-size: 1.05rem;">คณะกรรมการและสมาชิกชมรม (<?= count($officerMembers) ?> คน)</strong>
        </div>
        <span class="text-muted" style="font-size: 12.5px;">ลากย้ายเพื่อจัดเรียงลำดับ</span>
      </div>

      <div class="tree-slot-grid"
           data-tree-grid
           data-reorder-endpoint="<?= url('api/admin/club') ?>"
           data-csrf="<?= csrf_token() ?>">
        <?php foreach ($officerMembers as $index => $member): ?>
          <div class="tree-slot-card"
               draggable="true"
               data-id="<?= (int) $member['id'] ?>">
            <div class="tree-slot-handle" title="ลากเพื่อย้ายลำดับ">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="5" cy="4" r="1.5"/><circle cx="11" cy="4" r="1.5"/>
                <circle cx="5" cy="8" r="1.5"/><circle cx="11" cy="8" r="1.5"/>
                <circle cx="5" cy="12" r="1.5"/><circle cx="11" cy="12" r="1.5"/>
              </svg>
            </div>

            <?php if (!empty($member['image_path']) && file_exists(ROOT_PATH . '/' . $member['image_path'])): ?>
              <img src="<?= url($member['image_path']) ?>" class="tree-slot-avatar" alt="">
            <?php else: ?>
              <div class="tree-slot-avatar-fallback">
                <?= initials($member['name']) ?>
              </div>
            <?php endif; ?>

            <div class="flex-grow-1" style="min-width: 0;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="tree-slot-order-badge">#<?= $index + 1 ?></span>
                <span class="fw-bold text-truncate text-navy" style="font-size: 14px;" title="<?= e($member['name']) ?>">
                  <?= e($member['name']) ?>
                </span>
              </div>
              <div class="text-muted text-truncate" style="font-size: 12.5px;" title="<?= e($member['club_role']) ?>">
                <?= e($member['club_role']) ?>
              </div>
              <?php if (!empty($member['student_id'])): ?>
                <div class="mono text-muted text-truncate" style="font-size: 11.5px;" title="<?= e($member['student_id']) ?>">
                  รหัส: <?= e($member['student_id']) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-1 flex-shrink-0">
              <a href="<?= url('admin/club?action=edit&id=' . (int) $member['id']) ?>"
                 class="btn btn-action-icon btn-line"
                 title="แก้ไข">
                <?= icon('pencil', 16) ?>
              </a>
              <form method="POST" action="<?= url('admin/club') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="sub_action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                <button type="submit"
                        class="btn btn-action-icon btn-danger-line"
                        title="ลบ"
                        data-confirm="ยืนยันการลบสมาชิกชมรมท่านนี้?">
                  <?= icon('trash', 16) ?>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- IN-PLACE ADD SLOT LINK -->
        <a href="<?= url('admin/club?action=new') ?>" class="tree-slot-empty" title="เพิ่มสมาชิกใหม่">
          <?= icon('plus', 16) ?>
          <span>เพิ่มสมาชิกชมรม</span>
        </a>
      </div>
    </div>

  </div>
<?php endif; ?>

<script src="<?= url('assets/js/admin-tree-slots.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
