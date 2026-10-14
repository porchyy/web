<?php
/**
 * Admin - จัดการภาพแบนเนอร์และข้อความประชาสัมพันธ์หน้าแรก (Refined Editorial Brutalism)
 * Standard: STYLE.md & RULES.md
 */
$adminNav = 'hero';
$pageTitle = 'จัดการภาพแบนเนอร์และข้อความหน้าแรก';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/SettingService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';

AuthService::requireLogin();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        // Hero title with <br> support
        $heroTitle = trim((string) ($_POST['hero_title'] ?? ''));
        if ($heroTitle === '') {
            throw new InvalidArgumentException('กรุณาระบุข้อความพาดหัวหลัก');
        }
        SettingService::set('hero_title', clean_html($heroTitle));

        // Maintain backward compatibility for hero_title_1 / hero_title_2
        $parts = preg_split('/<br\s*\/?>/i', $heroTitle, 2);
        SettingService::set('hero_title_1', clean_text(trim($parts[0] ?? $heroTitle)));
        SettingService::set('hero_title_2', clean_text(trim($parts[1] ?? '')));

        SettingService::set('hero_intro', clean_html(parse_markdown(trim((string) ($_POST['hero_intro'] ?? '')))));
        SettingService::set('hero_caption', clean_text(trim((string) ($_POST['hero_caption'] ?? ''))));

        // Metrics
        SettingService::set('stat_employment', clean_text(trim((string) ($_POST['stat_employment'] ?? '96%'))));

        // Image upload
        $newImage = upload_image('hero_image');
        if ($newImage) {
            $oldImage = SettingService::get('hero_image');
            delete_upload($oldImage);
            SettingService::set('hero_image', $newImage);
        }

        flash('บันทึกการตั้งค่าหน้าแรกและภาพแบนเนอร์สำเร็จแล้ว');
        redirect('admin/hero.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Current values
$heroTitle = SettingService::get('hero_title', '');
if (!$heroTitle) {
    $h1 = SettingService::get('hero_title_1', 'เทคโนโลยี');
    $h2 = SettingService::get('hero_title_2', 'ธุรกิจดิจิทัล');
    $heroTitle = trim($h1 . '<br>' . $h2, '<br>');
}

$heroIntro   = SettingService::get('hero_intro', 'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล');
$heroCaption = SettingService::get('hero_caption', 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์');
$heroImage   = SettingService::get('hero_image', 'uploads/seed/hero-lab.jpg');

$statEmploy  = SettingService::get('stat_employment', '96%');
$profCount   = ScheduleService::getProfessionalCourseCount();
$teachCount  = count(FacultyService::getAllTeachers());

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="mb-4">
  <p class="eyebrow eyebrow-muted mb-1">ตั้งค่าหน้าแรก</p>
  <h1 style="font-size: 1.75rem; margin: 0;">ภาพแบนเนอร์และข้อมูลประชาสัมพันธ์หลัก</h1>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="POST" action="<?= url('admin/hero.php') ?>" enctype="multipart/form-data" id="form-hero-settings">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Left Column: Hero Image Preview & Styled Dropzone -->
    <div class="col-12 col-lg-5">
      <div class="admin-card">
        <div class="admin-card-head">
          <h3>ภาพแบนเนอร์หลักปัจจุบัน</h3>
          <span class="mono text-muted" style="font-size: 13px;">สัดส่วน 4:3 / 16:9</span>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">ภาพแบนเนอร์หลักและอัปโหลดภาพใหม่</label>
          <div class="image-upload-wrapper is-landscape"
               data-image-upload
               data-current-src="<?= $heroImage && file_exists(ROOT_PATH . '/' . $heroImage) ? url($heroImage) : '' ?>">
            <div class="image-upload-preview">
              <?php if ($heroImage && file_exists(ROOT_PATH . '/' . $heroImage)): ?>
                <img id="heroPreview" src="<?= url($heroImage) ?>" class="preview-img" alt="ภาพแบนเนอร์หน้าแรกปัจจุบัน">
                <span class="preview-badge badge-current">รูปปัจจุบัน</span>
              <?php else: ?>
                <div class="preview-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                  <span>ยังไม่มีภาพ</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="image-upload-dropzone" role="button" tabindex="0" aria-label="คลิกเพื่อเลือกไฟล์รูปภาพ หรือลากไฟล์มาวาง">
              <input type="file"
                     name="hero_image"
                     id="hero_image"
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
                  <strong>คลิกเพื่อเลือกรูปภาพใหม่</strong> หรือลากไฟล์มาวางที่นี่
                </div>
                <div class="dropzone-hint">
                  รองรับไฟล์ JPG, PNG หรือ WebP (ขนาดสูงสุด 5 MB · สัดส่วน 4:3 หรือ 16:9)
                </div>
                <div class="dropzone-file-selected d-none">
                  <span class="file-name mono"></span>
                  <button type="button" class="btn-clear-file" title="ล้างรูปที่เลือก">ยกเลิก</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="hero_caption" class="form-label">คำบรรยายใต้ภาพประชาสัมพันธ์</label>
          <input type="text"
                 name="hero_caption"
                 id="hero_caption"
                 class="form-control"
                 value="<?= e($heroCaption) ?>">
          <div class="form-text">ข้อความอธิบายใต้ภาพสไตล์ทางการและเป็นทางการ</div>
        </div>
      </div>
    </div>

    <!-- Right Column: Editorial Text & Single Merged Title -->
    <div class="col-12 col-lg-7">
      <div class="admin-card">
        <div class="admin-card-head">
          <h3>ข้อความพาดหัวหลักและข้อมูลแนะนำ</h3>
        </div>

        <!-- Merged Single Hero Title -->
        <div class="mb-4">
          <label for="hero_title" class="form-label fw-semibold">ข้อความพาดหัวหลัก <span class="req">*</span></label>
          <div id="quill-hero-title-editor"
               class="quill-editorial-editor"
               data-target-input="hero_title"
               data-type="title"
               data-placeholder="พิมพ์ข้อความพาดหัวหลัก เช่น เทคโนโลยี ธุรกิจดิจิทัล..."></div>
          <textarea name="hero_title"
                    id="hero_title"
                    class="d-none"
                    required><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></textarea>
          <div class="form-text">สามารถกด Enter เพื่อขึ้นบรรทัดใหม่ได้โดยตรง ระบบจะจัดการตัดคำและเว้นวรรคอย่างเหมาะสม</div>
        </div>

        <!-- EDITORIAL RICH TEXT BOX WITH QUILL -->
        <div class="mb-3">
          <label for="hero_intro" class="form-label">คำอธิบายแนะนำแผนกวิชา <span class="req">*</span></label>
          <div id="quill-hero-editor"
               class="quill-editorial-editor"
               data-target-input="hero_intro"
               data-placeholder="พิมพ์คำอธิบายแนะนำแผนกวิชาที่นี่..."></div>
          <textarea name="hero_intro"
                    id="hero_intro"
                    class="d-none"
                    required><?= htmlspecialchars($heroIntro, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="admin-card-head mt-4">
          <h3>ตัวเลขสถิติและข้อมูลแสดงผลหน้าแรก</h3>
        </div>

        <div class="mb-3">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="stat_employment" class="form-label">อัตราการมีงานทำของผู้สำเร็จการศึกษา</label>
              <input type="text" name="stat_employment" id="stat_employment" class="form-control mono" placeholder="เช่น 96%" value="<?= e($statEmploy) ?>">
              <div class="form-text">ระบุเปอร์เซ็นต์อัตราการมีงานทำหรือศึกษาต่อ</div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-end align-items-center gap-2">
          <button type="button" class="btn btn-line d-inline-flex align-items-center gap-1" id="btnOpenHeroPreview" data-bs-toggle="modal" data-bs-target="#heroPreviewModal">
            <?= icon('eye', 16) ?> <span>ดูตัวอย่างหน้าจริง</span>
          </button>
          <button type="submit" class="btn btn-navy">
            <?= icon('check', 16) ?>
            <span>บันทึกการเปลี่ยนแปลง</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- FULL PREVIEW MODAL WITH DESKTOP / MOBILE SWITCHER -->
<div class="modal fade" id="heroPreviewModal" tabindex="-1" aria-labelledby="heroPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="heroPreviewModalLabel">
          <?= icon('eye', 18) ?> แสดงตัวอย่างหน้าหลักจริง
        </h5>
        
        <div class="d-flex align-items-center gap-3">
          <!-- Viewport Switcher -->
          <div class="preview-viewport-toggle">
            <button type="button" class="preview-viewport-btn is-active" id="btnPreviewDesktop" data-viewport="desktop">
              Desktop
            </button>
            <button type="button" class="preview-viewport-btn" id="btnPreviewMobile" data-viewport="mobile">
              Mobile
            </button>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
      </div>

      <div class="modal-body bg-light p-3 p-md-4">
        <!-- Frame Container -->
        <div class="preview-container-frame is-desktop" id="heroPreviewFrame">
          <section class="hero bg-white p-4 p-md-5">
            <div class="container-fluid">
              <div class="row align-items-center gy-4">
                <div class="col-12 col-lg-6">
                  <div class="hero-label">
                    <p class="eyebrow mb-2">
                      <span><?= e(SITE_NAME_EN) ?></span>
                    </p>
                  </div>
                  <h1 class="hero-title mb-3" id="modalHeroTitle">
                    <?= clean_html(nl2br($heroTitle)) ?>
                  </h1>
                  <span class="rule" aria-hidden="true"></span>
                  <div class="hero-intro" id="modalHeroIntro">
                    <?= clean_html(parse_markdown($heroIntro)) ?>
                  </div>
                  <dl class="hero-meta mt-4">
                    <div>
                      <dt>ระดับการศึกษา</dt>
                      <dd>เปิดรับสมัคร ปวช. และ ปวส.</dd>
                    </div>
                    <div>
                      <dt>รูปแบบการสอน</dt>
                      <dd>เน้นฝึกปฏิบัติและโครงงานจริง</dd>
                    </div>
                  </dl>
                </div>
                <div class="col-12 col-lg-6 hero-figure">
                  <div class="marks">
                    <figure class="figure w-100 m-0">
                      <div class="figure-img">
                        <img id="modalHeroImg" src="<?= url($heroImage) ?>" alt="ภาพตัวอย่าง" class="w-100" style="aspect-ratio: 4/3; object-fit: cover;">
                      </div>
                      <figcaption class="figcap" id="modalHeroFigcap">
                        <span id="modalHeroCaption"><?= e($heroCaption) ?></span>
                      </figcaption>
                    </figure>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">ปิดหน้าต่างตัวอย่าง</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= url('assets/js/admin-hero.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
