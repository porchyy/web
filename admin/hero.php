<?php
/**
 * Admin — จัดการรูปภาพ Hero และข้อความหน้าแรก
 */
$adminNav = 'hero';
$pageTitle = 'จัดการรูป Hero และข้อมูลหน้าแรก';

require_once __DIR__ . '/../includes/admin-header.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        // Text settings
        set_setting('hero_title_1', trim((string) ($_POST['hero_title_1'] ?? '')));
        set_setting('hero_title_2', trim((string) ($_POST['hero_title_2'] ?? '')));
        set_setting('hero_intro', trim((string) ($_POST['hero_intro'] ?? '')));
        set_setting('hero_caption', trim((string) ($_POST['hero_caption'] ?? '')));
        set_setting('stat_courses', trim((string) ($_POST['stat_courses'] ?? '')));
        set_setting('stat_labs', trim((string) ($_POST['stat_labs'] ?? '')));
        set_setting('stat_employment', trim((string) ($_POST['stat_employment'] ?? '')));
        set_setting('curriculum_code', trim((string) ($_POST['curriculum_code'] ?? '')));

        // Image upload
        $newImage = upload_image('hero_image');
        if ($newImage) {
            $oldImage = setting('hero_image');
            delete_upload($oldImage);
            set_setting('hero_image', $newImage);
        }

        flash('บันทึกการตั้งค่าหน้าแรกและรูปภาพ Hero สำเร็จแล้ว');
        redirect('admin/hero.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Current values
$heroTitle1  = setting('hero_title_1', 'เทคโนโลยี');
$heroTitle2  = setting('hero_title_2', 'ธุรกิจดิจิทัล');
$heroIntro   = setting('hero_intro', 'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล');
$heroCaption = setting('hero_caption', 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์');
$heroImage   = setting('hero_image', 'uploads/seed/hero-lab.jpg');

$statCourses = setting('stat_courses', '18');
$statLabs    = setting('stat_labs', '04');
$statEmploy  = setting('stat_employment', '96%');
$currCode    = setting('curriculum_code', 'DBT-2567-V02');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // HERO & HOMEPAGE</p>
    <h1 style="font-size: 1.75rem; margin: 0;">เปลี่ยนรูปภาพ Hero และข้อมูลหน้าแรก</h1>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="POST" action="<?= url('admin/hero.php') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Left Column: Hero Image Preview & Upload -->
    <div class="col-12 col-lg-5">
      <div class="admin-card">
        <div class="admin-card-head">
          <h3>รูปภาพ Hero ปัจจุบัน</h3>
          <span class="mono text-muted" style="font-size: 11px;">RATIO 4:3 / 16:9</span>
        </div>

        <div class="figure-img marks mb-3" style="aspect-ratio: 4/3; background: var(--c-blue-100);">
          <?php if ($heroImage && file_exists(ROOT_PATH . '/' . $heroImage)): ?>
            <img id="heroPreview" src="<?= url($heroImage) ?>" alt="รูป Hero ปัจจุบัน" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <div id="heroPreview" class="monogram monogram-lg" style="height: 100%;">DBT // HERO</div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label for="hero_image" class="form-label">อัปโหลดรูปภาพ Hero ใหม่</label>
          <input type="file"
                 name="hero_image"
                 id="hero_image"
                 class="form-control"
                 accept="image/jpeg,image/png,image/webp"
                 data-preview-target="heroPreview">
          <div class="form-text">รองรับ JPG, PNG หรือ WebP ขนาดไม่เกิน 5 MB (แนะนำภาพแนวนอน สไตล์ห้องเรียนหรือห้องแล็บจริง)</div>
        </div>

        <div class="p-3 bg-white border mt-3">
          <div class="mono text-muted mb-1" style="font-size: 11px;">REAL-TIME TEXT PREVIEW</div>
          <p id="previewIntro" class="mb-2" style="font-size: .875rem; color: var(--c-navy); line-height: 1.5;"><?= e($heroIntro) ?></p>
          <div class="text-muted" style="font-size: 11px;">
            <strong>Caption:</strong> <span id="previewCaption"><?= e($heroCaption) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Text & Metrics -->
    <div class="col-12 col-lg-7">
      <div class="admin-card">
        <div class="admin-card-head">
          <h3>ข้อความพาดหัว Hero</h3>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <label for="hero_title_1" class="form-label">พาดหัว บรรทัดที่ 1</label>
            <input type="text"
                   name="hero_title_1"
                   id="hero_title_1"
                   class="form-control font-weight-bold"
                   value="<?= e($heroTitle1) ?>"
                   required>
          </div>
          <div class="col-12 col-md-6">
            <label for="hero_title_2" class="form-label">พาดหัว บรรทัดที่ 2</label>
            <input type="text"
                   name="hero_title_2"
                   id="hero_title_2"
                   class="form-control font-weight-bold"
                   value="<?= e($heroTitle2) ?>"
                   required>
          </div>
        </div>

        <div class="mb-3">
          <label for="hero_intro" class="form-label">คำอธิบายแนะนำแผนก (Intro Paragraph)</label>
          <textarea name="hero_intro"
                    id="hero_intro"
                    rows="3"
                    class="form-control"
                    required><?= e($heroIntro) ?></textarea>
        </div>

        <div class="mb-3">
          <label for="hero_caption" class="form-label">คำบรรยายใต้ภาพ Hero (Editorial Caption)</label>
          <input type="text"
                 name="hero_caption"
                 id="hero_caption"
                 class="form-control"
                 value="<?= e($heroCaption) ?>">
          <div class="form-text">ข้อความอธิบายใต้ภาพสไตล์สิ่งพิมพ์วิชาการ</div>
        </div>

        <div class="admin-card-head mt-4">
          <h3>ตัวเลขสถิติบนแถบ Data Strip</h3>
        </div>

        <div class="row g-3">
          <div class="col-6 col-md-3">
            <label for="stat_courses" class="form-label">จำนวนวิชา</label>
            <input type="text" name="stat_courses" id="stat_courses" class="form-control mono" value="<?= e($statCourses) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label for="stat_labs" class="form-label">ห้องแล็บ</label>
            <input type="text" name="stat_labs" id="stat_labs" class="form-control mono" value="<?= e($statLabs) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label for="stat_employment" class="form-label">อัตรามีงานทำ</label>
            <input type="text" name="stat_employment" id="stat_employment" class="form-control mono" value="<?= e($statEmploy) ?>">
          </div>
          <div class="col-6 col-md-3">
            <label for="curriculum_code" class="form-label">รหัสหลักสูตร</label>
            <input type="text" name="curriculum_code" id="curriculum_code" class="form-control mono" value="<?= e($currCode) ?>">
          </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
          <button type="submit" class="btn btn-navy">
            <?= icon('check', 16) ?>
            <span>บันทึกการเปลี่ยนแปลง</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const introInput = document.getElementById('hero_intro');
  const captionInput = document.getElementById('hero_caption');
  const previewIntro = document.getElementById('previewIntro');
  const previewCaption = document.getElementById('previewCaption');
  if (introInput && previewIntro) {
    introInput.addEventListener('input', () => { previewIntro.textContent = introInput.value; });
  }
  if (captionInput && previewCaption) {
    captionInput.addEventListener('input', () => { previewCaption.textContent = captionInput.value; });
  }
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
