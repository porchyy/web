<?php
/**
 * Admin System Settings Page
 * Manage Department Metadata, Contact Info, Social Links & Stats
 * Standard: STYLE.md & RULES.md
 */
$adminNav = 'settings';
$pageTitle = 'ตั้งค่าระบบและการติดต่อ';

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/services/SettingService.php';

// Only administrators may modify system settings
if (($currentUser['role'] ?? '') !== 'admin') {
    flash('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect('admin');
}

$errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $settingsToSave = [
        // General
        'site_name'             => trim($_POST['site_name'] ?? ''),
        'site_name_en'          => trim($_POST['site_name_en'] ?? ''),
        'site_short'            => trim($_POST['site_short'] ?? ''),
        'institute_name'        => trim($_POST['institute_name'] ?? ''),
        'curriculum_code'       => trim($_POST['curriculum_code'] ?? ''),

        // Contacts & Location
        'contact_phone'         => trim($_POST['contact_phone'] ?? ''),
        'contact_email'         => trim($_POST['contact_email'] ?? ''),
        'contact_address'       => trim($_POST['contact_address'] ?? ''),
        'contact_hours'         => trim($_POST['contact_hours'] ?? ''),

        // Social Media
        'contact_facebook_name' => trim($_POST['contact_facebook_name'] ?? ''),
        'contact_facebook_url'  => trim($_POST['contact_facebook_url'] ?? ''),

        // Metrics Mode
        'stat_mode'             => in_array($_POST['stat_mode'] ?? '', ['dynamic', 'manual'], true) ? $_POST['stat_mode'] : 'dynamic',
        'stat_courses'          => trim($_POST['stat_courses'] ?? '18'),
        'stat_labs'             => trim($_POST['stat_labs'] ?? '04'),
        'stat_employment'       => trim($_POST['stat_employment'] ?? '96%'),
    ];

    if ($settingsToSave['site_name'] === '') {
        $errors[] = 'กรุณาระบุชื่อแผนกวิชา (ภาษาไทย)';
    }

    if (empty($errors)) {
        foreach ($settingsToSave as $k => $v) {
            SettingService::set($k, $v);
        }
        flash('success', 'บันทึกการตั้งค่าระบบเรียบร้อยแล้ว');
        redirect('admin/settings');
    }
}

// Current values
$settings = SettingService::getAll();

$siteName            = $settings['site_name'] ?? SITE_NAME;
$siteNameEn          = $settings['site_name_en'] ?? SITE_NAME_EN;
$siteShort           = $settings['site_short'] ?? SITE_SHORT;
$instituteName       = $settings['institute_name'] ?? INSTITUTE_NAME;
$curriculumCode      = $settings['curriculum_code'] ?? 'DBT-2567-V02';

$contactPhone        = $settings['contact_phone'] ?? CONTACT_PHONE;
$contactEmail        = $settings['contact_email'] ?? CONTACT_EMAIL;
$contactAddress      = $settings['contact_address'] ?? CONTACT_ADDRESS;
$contactHours        = $settings['contact_hours'] ?? CONTACT_HOURS;

$contactFbName       = $settings['contact_facebook_name'] ?? CONTACT_FACEBOOK_NAME;
$contactFbUrl        = $settings['contact_facebook_url'] ?? CONTACT_FACEBOOK_URL;

$statMode            = $settings['stat_mode'] ?? 'dynamic';
$statCourses         = $settings['stat_courses'] ?? '18';
$statLabs            = $settings['stat_labs'] ?? '04';
$statEmployment      = $settings['stat_employment'] ?? '96%';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">การจัดการระบบส่วนกลาง</p>
    <h1 style="font-size: 1.75rem; margin: 0;">ตั้งค่าระบบและการติดต่อ</h1>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div>
      <strong>พบข้อผิดพลาด:</strong>
      <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<form method="post" action="<?= url('admin/settings') ?>" class="admin-form-settings">
  <?= csrf_field() ?>

  <!-- Tabs Navigation -->
  <ul class="nav nav-tabs border-bottom mb-4" id="settingsTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active font-heading fw-semibold text-navy py-2 px-3 rounded-0" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
        <?= icon('info', 16) ?> ข้อมูลทั่วไป
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link font-heading fw-semibold text-navy py-2 px-3 rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
        <?= icon('phone', 16) ?> ช่องทางการติดต่อและที่ตั้ง
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link font-heading fw-semibold text-navy py-2 px-3 rounded-0" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">
        <?= icon('facebook', 16) ?> โซเชียลมีเดีย
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link font-heading fw-semibold text-navy py-2 px-3 rounded-0" id="metrics-tab" data-bs-toggle="tab" data-bs-target="#metrics" type="button" role="tab" aria-controls="metrics" aria-selected="false">
        <?= icon('grid', 16) ?> สถิติหน้าแรก
      </button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="settingsTabContent">
    
    <!-- Tab 1: ข้อมูลทั่วไป -->
    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
      <div class="admin-card">
        <div class="admin-card-head">
          <h2>ข้อมูลแผนกวิชาและสถานศึกษา</h2>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label" for="site_name">ชื่อแผนกวิชา (ภาษาไทย) <span class="req">*</span></label>
            <input type="text" id="site_name" name="site_name" class="form-control" value="<?= e($siteName) ?>" required>
            <div class="form-text">แสดงผลเป็นชื่อหลักบนหัวเว็บและเอกสารราชการ</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="site_name_en">ชื่อแผนกวิชา (ภาษาอังกฤษ)</label>
            <input type="text" id="site_name_en" name="site_name_en" class="form-control" value="<?= e($siteNameEn) ?>">
            <div class="form-text">เช่น Digital Business Technology</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="site_short">ชื่อย่อแผนกวิชา</label>
            <input type="text" id="site_short" name="site_short" class="form-control mono" value="<?= e($siteShort) ?>">
            <div class="form-text">เช่น DBT</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="institute_name">ชื่อสถานศึกษา / วิทยาลัย</label>
            <input type="text" id="institute_name" name="institute_name" class="form-control" value="<?= e($instituteName) ?>">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="curriculum_code">รหัสหลักสูตรมาตรฐาน</label>
            <input type="text" id="curriculum_code" name="curriculum_code" class="form-control mono" value="<?= e($curriculumCode) ?>">
            <div class="form-text">แสดงในส่วนข้อมูลการศึกษาหน้าแรกและเอกสารตารางเรียน</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 2: ช่องทางการติดต่อและที่ตั้ง -->
    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
      <div class="admin-card">
        <div class="admin-card-head">
          <h2>ช่องทางการติดต่อและสถานที่ตั้ง</h2>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label" for="contact_phone">หมายเลขโทรศัพท์</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control mono" value="<?= e($contactPhone) ?>">
            <div class="form-text">เช่น 02-000-0000 หรือ 08X-XXX-XXXX</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="contact_email">อีเมลติดต่อ</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control mono" value="<?= e($contactEmail) ?>">
            <div class="form-text">สำหรับผู้สนใจติดต่อสอบถามข้อมูลหลักสูตร</div>
          </div>

          <div class="col-12">
            <label class="form-label" for="contact_address">สถานที่ตั้ง / ห้องพักครู</label>
            <textarea id="contact_address" name="contact_address" class="form-control" rows="2"><?= e($contactAddress) ?></textarea>
            <div class="form-text">ระบุอาคาร ชั้น และห้องปฏิบัติการสำหรับติดต่อราชการ</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="contact_hours">เวลาทำการ</label>
            <input type="text" id="contact_hours" name="contact_hours" class="form-control" value="<?= e($contactHours) ?>">
            <div class="form-text">เช่น จันทร์–ศุกร์ 08:00–16:30 น.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 3: โซเชียลมีเดีย -->
    <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
      <div class="admin-card">
        <div class="admin-card-head">
          <h2>แฟนเพจและการสื่อสารสังคมออนไลน์</h2>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label" for="contact_facebook_name">ชื่อแฟนเพจ Facebook</label>
            <input type="text" id="contact_facebook_name" name="contact_facebook_name" class="form-control" value="<?= e($contactFbName) ?>">
            <div class="form-text">ชื่อที่ปรากฏในส่วนท้ายเว็บ (Footer) และหน้าติดต่อ</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="contact_facebook_url">ลิงก์ URL แฟนเพจ Facebook</label>
            <input type="url" id="contact_facebook_url" name="contact_facebook_url" class="form-control mono" value="<?= e($contactFbUrl) ?>">
            <div class="form-text">เช่น https://facebook.com/your-page-id</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 4: สถิติหน้าแรก -->
    <div class="tab-pane fade" id="metrics" role="tabpanel" aria-labelledby="metrics-tab">
      <div class="admin-card">
        <div class="admin-card-head">
          <h2>แถบข้อมูลสถิติหน้าแรก</h2>
        </div>

        <div class="row g-3" id="statsFields">
          <div class="col-12 col-md-4">
            <label class="form-label" for="stat_courses">จำนวนหลักสูตรวิชาชีพ</label>
            <input type="text" id="stat_courses" name="stat_courses" class="form-control mono" value="<?= e($statCourses) ?>">
            <div class="form-text">เช่น 18 (รายวิชา)</div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label" for="stat_labs">ห้องปฏิบัติการคอมพิวเตอร์</label>
            <input type="text" id="stat_labs" name="stat_labs" class="form-control mono" value="<?= e($statLabs) ?>">
            <div class="form-text">เช่น 04 (ห้อง)</div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label" for="stat_employment">อัตราการได้งานทำ</label>
            <input type="text" id="stat_employment" name="stat_employment" class="form-control mono" value="<?= e($statEmployment) ?>">
            <div class="form-text">เช่น 96%</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex justify-content-end gap-3 mt-4">
    <button type="submit" class="btn btn-navy">
      <?= icon('check', 18) ?> บันทึกการเปลี่ยนแปลง
    </button>
  </div>
</form>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
