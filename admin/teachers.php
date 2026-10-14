<?php
/**
 * Admin - จัดการรายชื่ออาจารย์ในแผนก (Hierarchical Tree Slot UI)
 */
$adminNav = 'teachers';
$pageTitle = 'จัดการรายชื่ออาจารย์';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';

AuthService::requireLogin();

$action    = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$teacherId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error     = null;

// Handle Form Submissions & AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'reorder') {
        $orderJson = $_POST['order_ids'] ?? '[]';
        $orderIds = json_decode($orderJson, true) ?: [];
        if (!empty($orderIds)) {
            FacultyService::reorderTeachers($orderIds);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }

    if ($subAction === 'swap_head') {
        $newHeadId = filter_input(INPUT_POST, 'new_head_id', FILTER_VALIDATE_INT);
        $success = false;
        if ($newHeadId) {
            $success = FacultyService::setHeadTeacher($newHeadId);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success]);
        exit;
    }

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            FacultyService::deleteTeacher($delId);
            flash('ลบข้อมูลอาจารย์เรียบร้อยแล้ว');
        }
        redirect('admin/teachers');
    }

    if ($subAction === 'save') {
        $editId         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name           = clean_text($_POST['name'] ?? '');
        $position       = clean_text($_POST['position'] ?? '');
        $specialization = clean_text($_POST['specialization'] ?? '');
        $email          = clean_text($_POST['email'] ?? '');
        $isHead         = !empty($_POST['is_head']) ? 1 : 0;

        if ($name === '' || $position === '') {
            $error = 'กรุณากรอกชื่อ-นามสกุล และตำแหน่งทางวิชาการ';
        } else {
            try {
                $newImage = upload_image('image');
                FacultyService::saveTeacher([
                    'id'             => $editId,
                    'name'           => $name,
                    'position'       => $position,
                    'specialization' => $specialization,
                    'email'          => $email,
                    'is_head'        => $isHead,
                ], $newImage);

                flash($editId ? 'แก้ไขข้อมูลอาจารย์เรียบร้อยแล้ว' : 'เพิ่มข้อมูลอาจารย์ใหม่เรียบร้อยแล้ว');
                redirect('admin/teachers');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $teacherId) {
    $editItem = FacultyService::getTeacherById($teacherId);
}

// Fetch all teachers and segregate into Head and Faculty
$allTeachers = FacultyService::getAllTeachers();
$headTeacher = null;
$facultyMembers = [];

foreach ($allTeachers as $teacher) {
    if ($teacher['is_head'] && $headTeacher === null) {
        $headTeacher = $teacher;
    } else {
        $facultyMembers[] = $teacher;
    }
}

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการคณาจารย์</p>
    <h1 style="font-size: 1.75rem; margin: 0;">ผังและรายชื่ออาจารย์ในแผนก</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/teachers?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มอาจารย์</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/teachers') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายชื่ออาจารย์</span>
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
      <h3><?= $action === 'edit' ? 'แก้ไขข้อมูลอาจารย์ #' . (int) $editItem['id'] : 'เพิ่มอาจารย์ใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/teachers') ?>" enctype="multipart/form-data" id="form-teacher-editor">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label for="input-teacher-name" class="form-label">ชื่อ-นามสกุล (พร้อมคำนำหน้า/ตำแหน่ง) <span class="req">*</span></label>
          <input type="text"
                 name="name"
                 id="input-teacher-name"
                 class="form-control"
                 placeholder="ดร.ศิริพร บุญเสริมประสิทธิ์"
                 value="<?= e($_POST['name'] ?? $editItem['name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="input-teacher-position" class="form-label">ตำแหน่ง / หน้าที่รับผิดชอบ <span class="req">*</span></label>
          <input type="text"
                 name="position"
                 id="input-teacher-position"
                 class="form-control"
                 placeholder="หัวหน้าแผนกวิชา / ครูชำนาญการ"
                 value="<?= e($_POST['position'] ?? $editItem['position'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-8">
          <label for="input-teacher-specialization" class="form-label">ความเชี่ยวชาญเฉพาะทาง</label>
          <input type="text"
                 name="specialization"
                 id="input-teacher-specialization"
                 class="form-control"
                 placeholder="Web Architecture, Database Design & Cloud"
                 value="<?= e($_POST['specialization'] ?? $editItem['specialization'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-4">
          <label for="input-teacher-email" class="form-label">อีเมลติดต่อ</label>
          <input type="email"
                 name="email"
                 id="input-teacher-email"
                 class="form-control mono"
                 placeholder="teacher@institution.ac.th"
                 value="<?= e($_POST['email'] ?? $editItem['email'] ?? '') ?>">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">รูปถ่ายอาจารย์ (ภาพบุคคล สัดส่วนแนะนำ 4:5)</label>
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
                     id="input-teacher-image"
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
                  รองรับไฟล์ JPG, PNG หรือ WebP (ขนาดสูงสุด 5 MB · สัดส่วน 4:5)
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
                   name="is_head"
                   value="1"
                   id="input-teacher-is-head"
                   <?= !empty($_POST['is_head'] ?? $editItem['is_head'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="input-teacher-is-head">
              เป็นหัวหน้าแผนกวิชา
            </label>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/teachers') ?>" class="btn btn-line">ยกเลิก</a>
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

    <!-- หัวหน้าแผนกวิชา -->
    <div class="tree-tier">
      <div class="tree-tier-header">
        <div>
          <strong class="text-navy" style="font-size: 1.05rem;">หัวหน้าแผนกวิชา</strong>
        </div>
        <span class="text-muted" style="font-size: 12px;">ลากอาจารย์มาวางที่นี่เพื่อแต่งตั้งหรือสลับตำแหน่งหัวหน้า</span>
      </div>

      <?php if ($headTeacher): ?>
        <div class="tree-slot-card tree-slot-head is-head-target"
             data-head-dropzone
             data-id="<?= (int) $headTeacher['id'] ?>"
             style="max-width: 620px; margin: 0 auto;">
          <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 0;">
            <?php if (!empty($headTeacher['image_path']) && file_exists(ROOT_PATH . '/' . $headTeacher['image_path'])): ?>
              <img src="<?= url($headTeacher['image_path']) ?>" class="tree-slot-avatar" style="width: 56px; height: 56px;" alt="">
            <?php else: ?>
              <div class="tree-slot-avatar-fallback" style="width: 56px; height: 56px; font-size: 15px;">
                <?= initials($headTeacher['name']) ?>
              </div>
            <?php endif; ?>
            <div class="text-truncate">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="tree-slot-order-badge" style="background: var(--c-navy); color: #fff; border-color: var(--c-navy);">หัวหน้าแผนก</span>
                <strong class="text-navy" style="font-size: 1.05rem;"><?= e($headTeacher['name']) ?></strong>
              </div>
              <div class="text-muted" style="font-size: 13.5px;"><?= e($headTeacher['position']) ?></div>
              <?php if (!empty($headTeacher['email'])): ?>
                <div class="mono text-muted" style="font-size: 12px;"><?= e($headTeacher['email']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="d-flex gap-1 flex-shrink-0">
            <a href="<?= url('admin/teachers?action=edit&id=' . (int) $headTeacher['id']) ?>"
               class="btn btn-action-icon btn-line"
               title="แก้ไข">
              <?= icon('pencil', 17) ?>
            </a>
            <form method="POST" action="<?= url('admin/teachers') ?>" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="sub_action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $headTeacher['id'] ?>">
              <button type="submit"
                      class="btn btn-action-icon btn-danger-line"
                      title="ลบ"
                      data-confirm="ยืนยันการลบอาจารย์ท่านนี้?">
                <?= icon('trash', 17) ?>
              </button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center py-4 border dashed text-muted is-head-target"
             data-head-dropzone
             style="border-radius: var(--radius); max-width: 620px; margin: 0 auto; background: var(--c-surface);">
          <p class="mb-2">ยังไม่มีการระบุตำแหน่งหัวหน้าแผนกวิชา (ลากอาจารย์มาวางที่นี่เพื่อแต่งตั้ง)</p>
          <a href="<?= url('admin/teachers?action=new') ?>" class="btn btn-sm btn-navy">
            <?= icon('plus', 14) ?>
            <span>เพิ่มหรือกำหนดหัวหน้าแผนก</span>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- CONNECTOR LINE -->
    <div class="tree-connector-line"></div>

    <!-- คณะครูและบุคลากรทางการศึกษา (DRAGGABLE & IN-PLACE ADD) -->
    <div class="tree-tier">
      <div class="tree-tier-header">
        <div>
          <strong class="text-navy" style="font-size: 1.05rem;">คณะครูและบุคลากรทางการศึกษา (<?= count($facultyMembers) ?> ท่าน)</strong>
        </div>
        <span class="text-muted" style="font-size: 12.5px;">ลากย้ายเพื่อจัดเรียงลำดับ</span>
      </div>

      <div class="tree-slot-grid"
           data-tree-grid
           data-reorder-endpoint="<?= url('admin/teachers') ?>"
           data-csrf="<?= csrf_token() ?>">
        <?php foreach ($facultyMembers as $index => $teacher): ?>
          <div class="tree-slot-card"
               draggable="true"
               data-id="<?= (int) $teacher['id'] ?>">
            <div class="tree-slot-handle" title="ลากเพื่อย้ายลำดับ">
              <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                <circle cx="5" cy="4" r="1.5"/><circle cx="11" cy="4" r="1.5"/>
                <circle cx="5" cy="8" r="1.5"/><circle cx="11" cy="8" r="1.5"/>
                <circle cx="5" cy="12" r="1.5"/><circle cx="11" cy="12" r="1.5"/>
              </svg>
            </div>

            <?php if (!empty($teacher['image_path']) && file_exists(ROOT_PATH . '/' . $teacher['image_path'])): ?>
              <img src="<?= url($teacher['image_path']) ?>" class="tree-slot-avatar" alt="">
            <?php else: ?>
              <div class="tree-slot-avatar-fallback">
                <?= initials($teacher['name']) ?>
              </div>
            <?php endif; ?>

            <div class="flex-grow-1" style="min-width: 0;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="tree-slot-order-badge">#<?= $index + 1 ?></span>
                <span class="fw-bold text-truncate text-navy" style="font-size: 14px;" title="<?= e($teacher['name']) ?>">
                  <?= e($teacher['name']) ?>
                </span>
              </div>
              <div class="text-muted text-truncate" style="font-size: 12.5px;" title="<?= e($teacher['position']) ?>">
                <?= e($teacher['position']) ?>
              </div>
              <?php if (!empty($teacher['specialization'])): ?>
                <div class="text-muted text-truncate" style="font-size: 11.5px;" title="<?= e($teacher['specialization']) ?>">
                  <?= e($teacher['specialization']) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-1 flex-shrink-0">
              <a href="<?= url('admin/teachers?action=edit&id=' . (int) $teacher['id']) ?>"
                 class="btn btn-action-icon btn-line"
                 title="แก้ไข">
                <?= icon('pencil', 16) ?>
              </a>
              <form method="POST" action="<?= url('admin/teachers') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="sub_action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $teacher['id'] ?>">
                <button type="submit"
                        class="btn btn-action-icon btn-danger-line"
                        title="ลบ"
                        data-confirm="ยืนยันการลบอาจารย์ท่านนี้?">
                  <?= icon('trash', 16) ?>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- IN-PLACE ADD SLOT LINK -->
        <a href="<?= url('admin/teachers?action=new') ?>" class="tree-slot-empty" title="เพิ่มอาจารย์ใหม่">
          <?= icon('plus', 16) ?>
          <span>เพิ่มอาจารย์ในแผนก</span>
        </a>
      </div>
    </div>

  </div>
<?php endif; ?>

<script src="<?= url('assets/js/admin-tree-slots.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
