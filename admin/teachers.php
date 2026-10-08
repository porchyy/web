<?php
/**
 * Admin — จัดการรายชื่ออาจารย์ในแผนก
 */
$adminNav = 'teachers';
$pageTitle = 'จัดการรายชื่ออาจารย์';

require_once __DIR__ . '/../includes/admin-header.php';

$action    = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$teacherId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error     = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            delete_record_with_asset('teachers', $delId);
            flash('ลบข้อมูลอาจารย์เรียบร้อยแล้ว');
        }
        redirect('admin/teachers.php');
    }

    if ($subAction === 'save') {
        $editId         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name           = trim((string) ($_POST['name'] ?? ''));
        $position       = trim((string) ($_POST['position'] ?? ''));
        $specialization = trim((string) ($_POST['specialization'] ?? ''));
        $email          = trim((string) ($_POST['email'] ?? ''));
        $sortOrder      = (int) ($_POST['sort_order'] ?? 0);
        $isHead         = !empty($_POST['is_head']) ? 1 : 0;

        if ($name === '' || $position === '') {
            $error = 'กรุณากรอกชื่อ-นามสกุล และตำแหน่งทางวิชาการ';
        } else {
            try {
                // If set as head, unset others if needed
                if ($isHead) {
                    q("UPDATE teachers SET is_head = 0 WHERE id != ?", [$editId ?: 0]);
                }

                $newImage = upload_image('image');

                if ($editId) {
                    if ($newImage) {
                        $old = q("SELECT image_path FROM teachers WHERE id = ?", [$editId])->fetch();
                        if ($old) delete_upload($old['image_path']);
                        q("
                          UPDATE teachers
                          SET name = ?, position = ?, specialization = ?, email = ?, image_path = ?, is_head = ?, sort_order = ?
                          WHERE id = ?
                        ", [$name, $position, $specialization, $email, $newImage, $isHead, $sortOrder, $editId]);
                    } else {
                        q("
                          UPDATE teachers
                          SET name = ?, position = ?, specialization = ?, email = ?, is_head = ?, sort_order = ?
                          WHERE id = ?
                        ", [$name, $position, $specialization, $email, $isHead, $sortOrder, $editId]);
                    }
                    flash('แก้ไขข้อมูลอาจารย์เรียบร้อยแล้ว');
                } else {
                    q("
                      INSERT INTO teachers (name, position, specialization, email, image_path, is_head, sort_order)
                      VALUES (?, ?, ?, ?, ?, ?, ?)
                    ", [$name, $position, $specialization, $email, $newImage, $isHead, $sortOrder]);
                    flash('เพิ่มข้อมูลอาจารย์ใหม่เรียบร้อยแล้ว');
                }
                redirect('admin/teachers.php');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $teacherId) {
    $editItem = q("SELECT * FROM teachers WHERE id = ?", [$teacherId])->fetch();
}

// Fetch all teachers
$allTeachers = q("
  SELECT * FROM teachers
  ORDER BY is_head DESC, sort_order ASC, id ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // FACULTY MEMBERS</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการรายชื่ออาจารย์ในแผนก</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/teachers.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มอาจารย์</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/teachers.php') ?>" class="btn btn-line">
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
      <h3><?= $action === 'edit' ? 'แก้ไขข้อมูลอาจารย์ #' . $editItem['id'] : 'เพิ่มอาจารย์ใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/teachers.php') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label for="name" class="form-label">ชื่อ-นามสกุล (พร้อมคำนำหน้า/ยศวิชาการ) <span class="req">*</span></label>
          <input type="text"
                 name="name"
                 id="name"
                 class="form-control"
                 placeholder="ดร.ศิริพร บุญเสริมประสิทธิ์"
                 value="<?= e($_POST['name'] ?? $editItem['name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="position" class="form-label">ตำแหน่ง / หน้าที่รับผิดชอบ <span class="req">*</span></label>
          <input type="text"
                 name="position"
                 id="position"
                 class="form-control"
                 placeholder="หัวหน้าแผนกวิชา / ครูชำนาญการ"
                 value="<?= e($_POST['position'] ?? $editItem['position'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-8">
          <label for="specialization" class="form-label">ความเชี่ยวชาญเฉพาะทาง</label>
          <input type="text"
                 name="specialization"
                 id="specialization"
                 class="form-control"
                 placeholder="Web Architecture, Database Design & Cloud"
                 value="<?= e($_POST['specialization'] ?? $editItem['specialization'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-4">
          <label for="email" class="form-label">อีเมลติดต่อ</label>
          <input type="email"
                 name="email"
                 id="email"
                 class="form-control mono"
                 placeholder="teacher@institution.ac.th"
                 value="<?= e($_POST['email'] ?? $editItem['email'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
          <label for="image" class="form-label">รูปถ่ายอาจารย์ (ภาพบุคคล อัตราส่วน 4:5)</label>
          <input type="file"
                 name="image"
                 id="image"
                 class="form-control"
                 accept="image/jpeg,image/png,image/webp">
          <?php if (!empty($editItem['image_path'])): ?>
            <div class="mt-2 text-muted" style="font-size: 12px;">
              รูปเดิม: <code><?= e($editItem['image_path']) ?></code>
            </div>
          <?php endif; ?>
        </div>

        <div class="col-6 col-md-3">
          <label for="sort_order" class="form-label">ลำดับการแสดงผล</label>
          <input type="number"
                 name="sort_order"
                 id="sort_order"
                 class="form-control mono"
                 value="<?= (int) ($_POST['sort_order'] ?? $editItem['sort_order'] ?? 0) ?>">
        </div>

        <div class="col-6 col-md-3 d-flex align-items-center pt-md-4">
          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   name="is_head"
                   value="1"
                   id="is_head"
                   <?= !empty($_POST['is_head'] ?? $editItem['is_head'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="is_head">
              เป็นหัวหน้าแผนก
            </label>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/teachers.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       TEACHERS LIST TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>รายชื่ออาจารย์ทั้งหมดในแผนก (<?= count($allTeachers) ?> ท่าน)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 50px;">รูป</th>
            <th>ชื่อ-สกุล</th>
            <th>ตำแหน่ง</th>
            <th>ความเชี่ยวชาญ</th>
            <th class="text-center">หัวหน้า</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allTeachers)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีข้อมูลอาจารย์ในระบบ</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allTeachers as $t): ?>
              <tr>
                <td>
                  <?php if (!empty($t['image_path']) && file_exists(ROOT_PATH . '/' . $t['image_path'])): ?>
                    <img src="<?= url($t['image_path']) ?>" class="table-thumb" alt="">
                  <?php else: ?>
                    <div class="table-thumb d-grid place-items-center text-muted mono" style="font-size: 10px;">
                      <?= initials($t['name']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold d-block"><?= e($t['name']) ?></span>
                  <?php if (!empty($t['email'])): ?>
                    <span class="mono text-muted" style="font-size: 11px;"><?= e($t['email']) ?></span>
                  <?php endif; ?>
                </td>
                <td><?= e($t['position']) ?></td>
                <td>
                  <span class="text-muted d-block text-truncate" style="max-width: 260px; font-size: 12px;">
                    <?= e($t['specialization'] ?: '-') ?>
                  </span>
                </td>
                <td class="text-center">
                  <?php if ($t['is_head']): ?>
                    <span class="badge-soon" style="font-size: 10px;">HEAD</span>
                  <?php else: ?>
                    <span class="text-muted mono">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('admin/teachers.php?action=edit&id=' . $t['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                      <?= icon('pencil', 13) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/teachers.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $t['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบอาจารย์ท่านนี้?">
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
