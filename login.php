<?php
/**
 * 05 / LOGIN — เข้าสู่ระบบเจ้าหน้าที่
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to admin
if (is_logged_in()) {
    redirect('admin/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } elseif (attempt_login($username, $password)) {
        flash('เข้าสู่ระบบสำเร็จ ยินดีต้อนรับสู่แผงควบคุมหลังบ้าน');
        redirect('admin/index.php');
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

$pageTitle = 'เข้าสู่ระบบเจ้าหน้าที่';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <aside class="auth-aside">
    <div>
      <p class="eyebrow eyebrow-light">05 / AUTHENTICATION</p>
      <h2>ระบบบริหารจัดการข้อมูลดิจิทัล</h2>
      <p class="lead-text text-white-50 mt-3" style="max-width: 32ch;">
        สำหรับอาจารย์และเจ้าหน้าที่ดูแลระบบแผนกวิชาเทคโนโลยีธุรกิจดิจิทัล
      </p>
    </div>

    <div class="border-top pt-4 border-white-50 mono text-white-50" style="font-size: 12px;">
      <div>DEFAULT ACCESS // ADMIN</div>
      <div class="mt-1">SECURE HASH: BCRYPT // SESSION: HTTPONLY</div>
    </div>
  </aside>

  <section class="auth-main">
    <div class="auth-box">
      <div class="mb-4">
        <p class="eyebrow">STAFF LOGIN</p>
        <h1 style="font-size: 1.75rem;">เข้าสู่ระบบ</h1>
        <p class="text-muted" style="font-size: .875rem;">
          กรอกข้อมูลประจำตัวเพื่อเข้าสู่หน้าจัดการระบบ
        </p>
      </div>

      <?php if ($error): ?>
        <div class="alert-line is-error mb-4" role="alert">
          <?= icon('alert', 18) ?>
          <div><?= e($error) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('login.php') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
          <label for="username" class="form-label">
            ชื่อผู้ใช้งาน <span class="req">*</span>
          </label>
          <input type="text"
                 name="username"
                 id="username"
                 class="form-control"
                 placeholder="admin"
                 value="<?= e($_POST['username'] ?? '') ?>"
                 required
                 autofocus
                 autocomplete="username">
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">
            รหัสผ่าน <span class="req">*</span>
          </label>
          <div class="pw-field">
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control"
                   placeholder="••••••••"
                   required
                   autocomplete="current-password">
            <button type="button" class="pw-toggle" aria-label="แสดงรหัสผ่าน">
              <?= icon('eye', 18) ?>
            </button>
          </div>
          <div class="form-text mt-2 mono">ค่าเริ่มต้นทดสอบระบบ: admin / admin1234</div>
        </div>

        <button type="submit" class="btn btn-navy w-100">
          <?= icon('log-in', 18) ?>
          <span>เข้าสู่ระบบจัดการ</span>
        </button>
      </form>

      <div class="mt-4 pt-3 border-top text-center">
        <a href="<?= url('index.php') ?>" class="text-muted" style="font-size: .875rem;">
          ← กลับสู่หน้าหลักเว็บไซต์
        </a>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
