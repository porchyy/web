<?php
/**
 * Admin - จัดการผู้ใช้งานระบบ (Refined Editorial Brutalism & Productivity)
 */
$adminNav = 'users';
$pageTitle = 'จัดการผู้ใช้งานระบบ';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';

$currentUser = AuthService::requireLogin();
$isAdmin = ($currentUser['role'] === 'admin');

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: ($isAdmin ? 'list' : 'edit');
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: ($isAdmin ? null : (int) $currentUser['id']);

if (!$isAdmin) {
    // Non-admin may only edit their own account
    $action = 'edit';
    $userId = (int) $currentUser['id'];
}

$error = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        if (!$isAdmin) {
            flash('เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถลบบัญชีผู้ใช้ได้', 'error');
            redirect('admin/index.php');
        }
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            try {
                AuthService::deleteUser($delId, (int) $currentUser['id']);
                flash('ลบผู้ใช้งานเรียบร้อยแล้ว');
            } catch (Exception $e) {
                flash(public_error_message($e), 'error');
            }
        }
        redirect('admin/users.php');
    }

    if ($subAction === 'save') {
        $editId   = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$isAdmin) {
            $editId = (int) $currentUser['id'];
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $role     = $isAdmin ? trim((string) ($_POST['role'] ?? 'editor')) : ($currentUser['role'] ?? 'editor');

        if ($username === '' || $fullName === '') {
            $error = 'กรุณากรอกชื่อผู้ใช้และชื่อ-สกุล';
        } elseif (!$editId && $password === '') {
            $error = 'กรุณากำหนดรหัสผ่านสำหรับผู้ใช้ใหม่';
        } elseif ($password !== '' && strlen($password) < 6) {
            $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
        } else {
            // Check username uniqueness
            $chk = q("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $editId ?: 0])->fetch();
            if ($chk) {
                $error = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น';
            } else {
                try {
                    AuthService::saveUser([
                        'id'        => $editId,
                        'username'  => clean_text($username),
                        'password'  => $password,
                        'full_name' => clean_text($fullName),
                        'role'      => $role,
                    ]);

                    flash($editId ? 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว' : 'เพิ่มผู้ใช้งานใหม่เรียบร้อยแล้ว');
                    redirect($isAdmin ? 'admin/users.php' : 'admin/index.php');
                } catch (Exception $e) {
                    $error = public_error_message($e);
                }
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $userId) {
    $editItem = AuthService::getUserById($userId);
}

// Fetch all users
$allUsers = $isAdmin ? AuthService::getAllUsers() : [];

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1"><?= $isAdmin ? 'จัดการผู้ใช้งานระบบ' : 'ข้อมูลส่วนตัว' ?></p>
    <h1 style="font-size: 1.75rem; margin: 0;"><?= $isAdmin ? 'จัดการผู้ใช้งานระบบ' : 'แก้ไขข้อมูลส่วนตัวและรหัสผ่าน' ?></h1>
  </div>
  <?php if ($isAdmin): ?>
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
  <?php else: ?>
    <a href="<?= url('admin/index.php') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้าแดชบอร์ด</span>
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
  <div class="admin-card mx-auto" style="max-width: 760px;">
    <div class="admin-card-head">
      <h3><?= $action === 'edit' ? 'แก้ไขผู้ใช้งาน #' . (int) $editItem['id'] : 'เพิ่มผู้ใช้งานใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/users.php') ?>" id="form-user-editor">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label for="input-user-username" class="form-label">ชื่อผู้ใช้งาน <span class="req">*</span></label>
          <input type="text"
                 name="username"
                 id="input-user-username"
                 class="form-control mono"
                 placeholder="เช่น admin หรือ editor01"
                 value="<?= e($_POST['username'] ?? $editItem['username'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="input-user-fullname" class="form-label">ชื่อ-นามสกุลจริง <span class="req">*</span></label>
          <input type="text"
                 name="full_name"
                 id="input-user-fullname"
                 class="form-control"
                 placeholder="เช่น อ.สมชาย ใจดี"
                 value="<?= e($_POST['full_name'] ?? $editItem['full_name'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-6">
          <label for="input-user-password" class="form-label">
            รหัสผ่าน <?= $action === 'edit' ? '<span class="text-muted">(เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</span>' : '<span class="req">*</span>' ?>
          </label>
          <div class="pw-field">
            <input type="password"
                   name="password"
                   id="input-user-password"
                   class="form-control"
                   placeholder="••••••••"
                   minlength="6"
                   <?= $action === 'new' ? 'required' : '' ?>>
            <button type="button" class="pw-toggle" aria-label="แสดงรหัสผ่าน">
              <?= icon('eye', 18) ?>
            </button>
          </div>
          <div class="form-text">ความยาวอย่างน้อย 6 ตัวอักษร</div>
        </div>

        <div class="col-12 col-md-6">
          <?php if ($isAdmin): ?>
            <label for="select-user-role" class="form-label">ระดับสิทธิ์</label>
            <select name="role" id="select-user-role" class="form-select">
              <option value="admin" <?= ($_POST['role'] ?? $editItem['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (ผู้ดูแลระบบสูงสุด)</option>
              <option value="editor" <?= ($_POST['role'] ?? $editItem['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor (เจ้าหน้าที่แก้ไขข้อมูล)</option>
            </select>
          <?php else: ?>
            <label class="form-label">ระดับสิทธิ์</label>
            <div>
              <span class="tag"><?= e($editItem['role'] ?? 'editor') ?></span>
              <div class="form-text">เฉพาะผู้ดูแลระบบหลักที่สามารถปรับเปลี่ยนสิทธิ์ได้</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url($isAdmin ? 'admin/users.php' : 'admin/index.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       USERS LIST TABLE (PRODUCTIVITY RESTYLED)
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head d-flex justify-content-between align-items-center">
      <h3>รายชื่อผู้ใช้งานทั้งหมด (<?= count($allUsers) ?> บัญชี)</h3>
      <span class="mono text-muted" style="font-size: 13px;">ระบบจัดการสิทธิ์ผู้ดูแล</span>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 70px;">ID</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-สกุล</th>
            <th style="width: 140px;">ระดับสิทธิ์</th>
            <th style="width: 140px;">วันที่สร้าง</th>
            <th class="text-end" style="width: 100px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allUsers as $userItem): ?>
            <tr>
              <td class="mono">#<?= (int) $userItem['id'] ?></td>
              <td>
                <span class="mono fw-bold text-navy"><?= e($userItem['username']) ?></span>
                <?php if ((int) $userItem['id'] === (int) $currentUser['id']): ?>
                  <span class="badge-active ms-1" style="font-size: 12px;">บัญชีของคุณ</span>
                <?php endif; ?>
              </td>
              <td><?= e($userItem['full_name']) ?></td>
              <td>
                <span class="tag" style="font-size: 12px;"><?= e($userItem['role']) ?></span>
              </td>
              <td class="mono" style="font-size: 13px;">
                <?= thai_date($userItem['created_at']) ?>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="<?= url('admin/users.php?action=edit&id=' . (int) $userItem['id']) ?>"
                     class="btn btn-action-icon btn-line"
                     title="แก้ไข">
                    <?= icon('pencil', 16) ?>
                  </a>
                  <?php if ((int) $userItem['id'] !== (int) $currentUser['id']): ?>
                    <form method="POST" action="<?= url('admin/users.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $userItem['id'] ?>">
                      <button type="submit"
                              class="btn btn-action-icon btn-danger-line"
                              title="ลบ"
                              data-confirm="ยืนยันการลบผู้ใช้นี้?">
                        <?= icon('trash', 16) ?>
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
