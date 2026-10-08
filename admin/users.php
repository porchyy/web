<?php
/**
 * Admin — จัดการผู้ใช้งานระบบ
 */
$adminNav = 'users';
$pageTitle = 'จัดการผู้ใช้งานระบบ';

require_once __DIR__ . '/../includes/admin-header.php';
require_role('admin');

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error  = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            if ($delId === (int) $currentUser['id']) {
                flash('ไม่สามารถลบบัญชีผู้ใช้ที่กำลังเข้าสู่ระบบอยู่ได้', 'error');
            } else {
                q("DELETE FROM users WHERE id = ?", [$delId]);
                flash('ลบผู้ใช้งานเรียบร้อยแล้ว');
            }
        }
        redirect('admin/users.php');
    }

    if ($subAction === 'save') {
        $editId   = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $role     = trim((string) ($_POST['role'] ?? 'admin'));

        if ($username === '' || $fullName === '') {
            $error = 'กรุณากรอกชื่อผู้ใช้และชื่อ-สกุล';
        } elseif (!$editId && $password === '') {
            $error = 'กรุณากำหนดรหัสผ่านสำหรับผู้ใช้ใหม่';
        } else {
            // Check username uniqueness
            $chk = q("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $editId ?: 0])->fetch();
            if ($chk) {
                $error = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น';
            } else {
                if ($editId) {
                    if ($password !== '') {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        q("
                          UPDATE users
                          SET username = ?, password_hash = ?, full_name = ?, role = ?
                          WHERE id = ?
                        ", [$username, $hash, $fullName, $role, $editId]);
                    } else {
                        q("
                          UPDATE users
                          SET username = ?, full_name = ?, role = ?
                          WHERE id = ?
                        ", [$username, $fullName, $role, $editId]);
                    }
                    flash('แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว');
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    q("
                      INSERT INTO users (username, password_hash, full_name, role)
                      VALUES (?, ?, ?, ?)
                    ", [$username, $hash, $fullName, $role]);
                    flash('เพิ่มผู้ใช้งานใหม่เรียบร้อยแล้ว');
                }
                redirect('admin/users.php');
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $userId) {
    $editItem = q("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
}

// Fetch all users
$allUsers = q("SELECT * FROM users ORDER BY id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // SYSTEM USERS</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการผู้ใช้งานระบบ</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/users.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มผู้ใช้งาน</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/users.php') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายชื่อผู้ใช้</span>
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
  <div class="admin-card" style="max-width: 600px;">
    <div class="admin-card-head">
      <h3><?= $action === 'edit' ? 'แก้ไขผู้ใช้งาน #' . $editItem['id'] : 'เพิ่มผู้ใช้งานใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/users.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label for="username" class="form-label">ชื่อผู้ใช้งาน (Username) <span class="req">*</span></label>
        <input type="text"
               name="username"
               id="username"
               class="form-control mono"
               value="<?= e($_POST['username'] ?? $editItem['username'] ?? '') ?>"
               required>
      </div>

      <div class="mb-3">
        <label for="full_name" class="form-label">ชื่อ-นามสกุลจริง <span class="req">*</span></label>
        <input type="text"
               name="full_name"
               id="full_name"
               class="form-control"
               value="<?= e($_POST['full_name'] ?? $editItem['full_name'] ?? '') ?>"
               required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">
          รหัสผ่าน <?= $action === 'edit' ? '<span class="text-muted">(เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</span>' : '<span class="req">*</span>' ?>
        </label>
        <div class="pw-field">
          <input type="password"
                 name="password"
                 id="password"
                 class="form-control"
                 placeholder="••••••••"
                 <?= $action === 'new' ? 'required' : '' ?>>
          <button type="button" class="pw-toggle" aria-label="แสดงรหัสผ่าน">
            <?= icon('eye', 18) ?>
          </button>
        </div>
      </div>

      <div class="mb-4">
        <label for="role" class="form-label">ระดับสิทธิ์</label>
        <select name="role" id="role" class="form-select">
          <option value="admin" <?= ($_POST['role'] ?? $editItem['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (ผู้ดูแลระบบสูงสุด)</option>
          <option value="editor" <?= ($_POST['role'] ?? $editItem['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor (เจ้าหน้าที่แก้ไขข้อมูล)</option>
        </select>
      </div>

      <div class="pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/users.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       USERS LIST TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>รายชื่อผู้ใช้งานทั้งหมด (<?= count($allUsers) ?> บัญชี)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 60px;">ID</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-สกุล</th>
            <th>ระดับสิทธิ์</th>
            <th>วันที่สร้าง</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allUsers as $u): ?>
            <tr>
              <td class="mono">#<?= $u['id'] ?></td>
              <td>
                <span class="mono fw-bold text-navy"><?= e($u['username']) ?></span>
                <?php if ((int) $u['id'] === (int) $currentUser['id']): ?>
                  <span class="badge-soon ms-1" style="font-size: 9px;">YOU</span>
                <?php endif; ?>
              </td>
              <td><?= e($u['full_name']) ?></td>
              <td>
                <span class="tag"><?= e($u['role']) ?></span>
              </td>
              <td class="mono" style="font-size: 12px;">
                <?= mono_date($u['created_at']) ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="<?= url('admin/users.php?action=edit&id=' . $u['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                    <?= icon('pencil', 13) ?>
                  </a>
                  <?php if ((int) $u['id'] !== (int) $currentUser['id']): ?>
                    <form method="POST" action="<?= url('admin/users.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบผู้ใช้นี้?">
                        <?= icon('trash', 13) ?>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
