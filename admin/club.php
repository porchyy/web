<?php
/**
 * Admin — จัดการสมาชิกชมรมวิชาชีพ
 */
$adminNav = 'club';
$pageTitle = 'จัดการสมาชิกชมรม';

require_once __DIR__ . '/../includes/admin-header.php';

$action   = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$memberId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error    = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            $m = q("SELECT image_path FROM club_members WHERE id = ?", [$delId])->fetch();
            if ($m) {
                delete_upload($m['image_path']);
                q("DELETE FROM club_members WHERE id = ?", [$delId]);
                flash('ลบข้อมูลสมาชิกชมรมเรียบร้อยแล้ว');
            }
        }
        redirect('admin/club.php');
    }

    if ($subAction === 'save') {
        $editId     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $studentId  = trim((string) ($_POST['student_id'] ?? ''));
        $name       = trim((string) ($_POST['name'] ?? ''));
        $clubRole   = trim((string) ($_POST['club_role'] ?? ''));
        $sortOrder  = (int) ($_POST['sort_order'] ?? 0);
        $isClubHead = !empty($_POST['is_club_head']) ? 1 : 0;

        if ($name === '' || $clubRole === '') {
            $error = 'กรุณากรอกชื่อ-นามสกุล และบทบาทในชมรม';
        } else {
            try {
                // If set as head, unset other heads
                if ($isClubHead) {
                    q("UPDATE club_members SET is_club_head = 0 WHERE id != ?", [$editId ?: 0]);
                }

                $newImage = upload_image('image');

                if ($editId) {
                    if ($newImage) {
                        $old = q("SELECT image_path FROM club_members WHERE id = ?", [$editId])->fetch();
                        if ($old) delete_upload($old['image_path']);
                        q("
                          UPDATE club_members
                          SET student_id = ?, name = ?, club_role = ?, is_club_head = ?, image_path = ?, sort_order = ?
                          WHERE id = ?
                        ", [$studentId, $name, $clubRole, $isClubHead, $newImage, $sortOrder, $editId]);
                    } else {
                        q("
                          UPDATE club_members
                          SET student_id = ?, name = ?, club_role = ?, is_club_head = ?, sort_order = ?
                          WHERE id = ?
                        ", [$studentId, $name, $clubRole, $isClubHead, $sortOrder, $editId]);
                    }
                    flash('แก้ไขข้อมูลสมาชิกชมรมเรียบร้อยแล้ว');
                } else {
                    q("
                      INSERT INTO club_members (student_id, name, club_role, is_club_head, image_path, sort_order)
                      VALUES (?, ?, ?, ?, ?, ?)
                    ", [$studentId, $name, $clubRole, $isClubHead, $newImage, $sortOrder]);
                    flash('เพิ่มสมาชิกชมรมใหม่เรียบร้อยแล้ว');
                }
                redirect('admin/club.php');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $memberId) {
    $editItem = q("SELECT * FROM club_members WHERE id = ?", [$memberId])->fetch();
}

// Fetch all members
$allMembers = q("
  SELECT * FROM club_members
  ORDER BY is_club_head DESC, sort_order ASC, id ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // STUDENT CLUB</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการสมาชิกชมรมวิชาชีพ</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/club.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มสมาชิกชมรม</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/club.php') ?>" class="btn btn-line">
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
      <h3><?= $action === 'edit' ? 'แก้ไขสมาชิกชมรม #' . $editItem['id'] : 'เพิ่มสมาชิกชมรมใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/club.php') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <label for="student_id" class="form-label">รหัสนักเรียน/นักศึกษา</label>
          <input type="text"
                 name="student_id"
                 id="student_id"
                 class="form-control mono"
                 placeholder="66209010001"
                 value="<?= e($_POST['student_id'] ?? $editItem['student_id'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-4">
          <label for="name" class="form-label">ชื่อ-นามสกุล <span class="req">*</span></label>
          <input type="text"
                 name="name"
                 id="name"
                 class="form-control"
                 placeholder="นายกิตติภูมิ ทวีโชคประเสริฐ"
                 value="<?= e($_POST['name'] ?? $editItem['name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="club_role" class="form-label">ตำแหน่งในชมรม <span class="req">*</span></label>
          <input type="text"
                 name="club_role"
                 id="club_role"
                 class="form-control"
                 placeholder="ประธานชมรม / ฝ่ายวิชาการ"
                 value="<?= e($_POST['club_role'] ?? $editItem['club_role'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="image" class="form-label">รูปถ่ายสมาชิก (สี่เหลี่ยมจัตุรัส 1:1)</label>
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
                   name="is_club_head"
                   value="1"
                   id="is_club_head"
                   <?= !empty($_POST['is_club_head'] ?? $editItem['is_club_head'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="is_club_head">
              เป็นหัวหน้าชมรม
            </label>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/club.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       CLUB MEMBERS LIST TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>รายชื่อสมาชิกชมรมทั้งหมด (<?= count($allMembers) ?> คน)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 50px;">รูป</th>
            <th>รหัสนักศึกษา</th>
            <th>ชื่อ-สกุล</th>
            <th>ตำแหน่งในชมรม</th>
            <th class="text-center">หัวหน้า</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allMembers)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีข้อมูลสมาชิกชมรมในระบบ</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allMembers as $m): ?>
              <tr>
                <td>
                  <?php if (!empty($m['image_path']) && file_exists(ROOT_PATH . '/' . $m['image_path'])): ?>
                    <img src="<?= url($m['image_path']) ?>" class="table-thumb" alt="">
                  <?php else: ?>
                    <div class="table-thumb d-grid place-items-center text-muted mono" style="font-size: 10px;">
                      <?= initials($m['name']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="mono" style="font-size: 12px;"><?= e($m['student_id'] ?: '-') ?></td>
                <td>
                  <span class="fw-bold"><?= e($m['name']) ?></span>
                </td>
                <td><?= e($m['club_role']) ?></td>
                <td class="text-center">
                  <?php if ($m['is_club_head']): ?>
                    <span class="badge-soon" style="font-size: 10px;">PRESIDENT</span>
                  <?php else: ?>
                    <span class="text-muted mono">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('admin/club.php?action=edit&id=' . $m['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                      <?= icon('pencil', 13) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/club.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $m['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบสมาชิกชมรมท่านนี้?">
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
