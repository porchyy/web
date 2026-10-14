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

$incidentId = 'INC-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));

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
    <div class="max-w-720 p-4 p-md-5 bg-white border" style="border-left: 4px solid var(--c-navy) !important;">
      <div class="d-flex align-items-center gap-3 mb-3">
        <?= icon('alert', 28) ?>
        <h2 style="font-size: 1.25rem; margin: 0;">คำแนะนำสำหรับผู้ใช้งาน</h2>
      </div>

      <ul class="mb-4 ps-3" style="line-height: 1.8; color: var(--c-ink);">
        <?php if ($code === 404): ?>
          <li>ตรวจสอบการสะกดของที่อยู่เว็บ (URL) บนแถบที่อยู่ของเบราว์เซอร์</li>
          <li>ใช้เมนูด้านบนหรือด้านล่างเพื่อนำทางไปยังส่วนอื่น ๆ ของเว็บไซต์</li>
        <?php elseif ($code === 403): ?>
          <li>หากคุณเป็นอาจารย์หรือเจ้าหน้าที่ กรุณาเข้าสู่ระบบด้วยบัญชีที่มีสิทธิ์</li>
          <li>ติดต่อผู้ดูแลระบบหลักหากจำเป็นต้องปรับเปลี่ยนระดับสิทธิ์การใช้งาน</li>
        <?php elseif ($code === 419): ?>
          <li>หน้าเว็บเปิดทิ้งไว้นานเกินไป ทำให้รหัสยืนยันความปลอดภัยหมดอายุ</li>
          <li>กรุณากดปุ่มย้อนกลับ รีเฟรชหน้าเว็บ แล้วกรอกข้อมูลส่งใหม่อีกครั้ง</li>
        <?php else: ?>
          <li>ลองรีเฟรชหน้าเว็บใหม่อีกครั้งในอีกสักครู่</li>
          <li>หากปัญหายังคงเกิดขึ้น แจ้งรหัสอ้างอิงด้านล่างแก่เจ้าหน้าที่ไอที</li>
        <?php endif; ?>
      </ul>

      <div class="p-3 mb-4 rounded mono" style="background: var(--c-bg-alt); font-size: 13.5px; border: 1px dashed var(--c-line);">
        <span class="text-muted">Incident Reference Code:</span>
        <strong class="text-navy ms-2"><?= $incidentId ?></strong>
        <span class="text-muted ms-3">Timestamp: <?= date('Y-m-d H:i:s') ?></span>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('index') ?>" class="btn btn-navy">
          <?= icon('home', 16) ?>
          <span>กลับสู่หน้าหลัก</span>
        </a>
        <button type="button" class="btn btn-line" onclick="window.history.back()">
          <?= icon('arrow-left', 16) ?>
          <span>ย้อนกลับหน้าเดิม</span>
        </button>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
