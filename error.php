<?php
/**
 * Error Page - หน้าแจ้งเตือนข้อผิดพลาดระบบ
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$code = (int) filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT) ?: 404;
if (!in_array($code, [400, 403, 404, 419, 500], true)) {
    $code = 404;
}

http_response_code($code);

$errorTitles = [
    400 => 'คำขอไม่ถูกต้อง (Bad Request)',
    403 => 'การเข้าถึงถูกปฏิเสธ (Access Denied)',
    404 => 'ไม่พบหน้าที่คุณต้องการ (Page Not Found)',
    419 => 'เซสชันหรือแบบฟอร์มหมดอายุ (Session Expired)',
    500 => 'ระบบขัดข้องชั่วคราว (Internal Server Error)',
];

$errorDescriptions = [
    400 => 'ข้อมูลที่ส่งมาไม่ถูกต้องหรือไม่สมบูรณ์ กรุณาตรวจสอบและลองใหม่อีกครั้ง',
    403 => 'คุณไม่มีสิทธิ์ในการเข้าถึงส่วนนี้ บัญชีของคุณอาจต้องการสิทธิ์ระดับผู้ดูแลระบบ (Admin) ในการเข้าถึง',
    404 => 'หน้าที่คุณกำลังค้นหาไม่มีอยู่ ลิงก์อาจเปลี่ยนแปลงหรือถูกย้ายที่อยู่แล้ว กรุณาตรวจสอบ URL อีกครั้ง',
    419 => 'รหัสความปลอดภัยสำหรับแบบฟอร์ม (CSRF Token) หมดอายุ กรุณารีเฟรชหน้าเว็บและส่งข้อมูลใหม่อีกครั้ง',
    500 => 'เกิดข้อผิดพลาดในการประมวลผลของระบบ ระบบได้บันทึกรายงานไว้เรียบร้อยแล้ว ทีมงานกำลังดำเนินการตรวจสอบ',
];

$pageTitle = $code . ' - ' . ($errorTitles[$code] ?? 'เกิดข้อผิดพลาด');
include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow text-danger">
      <span>HTTP ERROR <?= $code ?></span>
    </p>
    <h1><?= e($errorTitles[$code] ?? 'เกิดข้อผิดพลาด') ?></h1>
    <p class="lead-text">
      <?= e($errorDescriptions[$code] ?? '') ?>
    </p>
  </div>
</div>

<section class="section">
  <div class="container-xl">
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= url('index') ?>" class="btn btn-navy">
        <?= icon('home', 16) ?>
        <span>กลับสู่หน้าหลัก</span>
      </a>
      <button type="button" class="btn btn-line" onclick="window.history.back()">
        <?= icon('arrow-left', 16) ?>
        <span>ย้อนกลับหน้าเดิม</span>
      </button>
      <?php if ($code === 403): ?>
        <a href="<?= url('login') ?>" class="btn btn-line">
          <?= icon('user', 16) ?>
          <span>เข้าสู่ระบบ</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
