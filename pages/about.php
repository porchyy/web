<?php
/**
 * เกี่ยวกับสาขา - สาขาเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';
require_once __DIR__ . '/../includes/services/SettingService.php';

$activeNav = 'about';
$pageTitle = 'เกี่ยวกับสาขา';

send_cache_headers(60, 120);

// Fetch Head of Department via FacultyService
$headTeacher = FacultyService::getHeadTeacher();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>เกี่ยวกับสาขาวิชา</span>
    </p>
    <h1>เกี่ยวกับสาขาวิชา</h1>
    <p class="lead-text">
      สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล มุ่งเน้นการสร้างนักปฏิบัติการด้านดิจิทัลที่มีทักษะการพัฒนาระบบเทคโนโลยีสารสนเทศ ควบคู่กับการเข้าใจกระบวนการทางธุรกิจสมัยใหม่ เพื่อตอบโจทย์ตลาดแรงงานในยุคเศรษฐกิจดิจิทัล
    </p>
  </div>
</div>

<!-- ========================================================================
     AREAS OF STUDY
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">ด้านการเรียนรู้</p>
        <h2>สมรรถนะหลักสูตรและด้านการเรียนรู้</h2>
      </div>
    </div>

    <ul class="offer-list">
      <li>
        <div>
          <h3>1. การพัฒนาเว็บและแอปพลิเคชัน (Web & App Development)</h3>
          <p>เรียนรู้การสร้างเว็บแอปพลิเคชันแบบ Full-Stack ด้วยภาษา PHP 8, Modern JavaScript, ระบบเชื่อมต่อฐานข้อมูลเชิงสัมพันธ์ และการออกแบบ RESTful API</p>
        </div>
      </li>
      <li>
        <div>
          <h3>2. การจัดการข้อมูลและฐานข้อมูลเชิงธุรกิจ (Database & Business Data)</h3>
          <p>การวิเคราะห์ข้อมูล การออกแบบโครงสร้างฐานข้อมูล SQL, การคิวรีข้อมูลเชิงลึก และการจัดทำแดชบอร์ดรายงานผลเชิงสถิติสำหรับผู้บริหาร</p>
        </div>
      </li>
      <li>
        <div>
          <h3>3. การตลาดดิจิทัลและพาณิชย์อิเล็กทรอนิกส์ (Digital Marketing & E-Commerce)</h3>
          <p>การบริหารจัดการร้านค้าออนไลน์ การสร้างคอนเทนต์เชิงกลยุทธ์ การวิเคราะห์พฤติกรรมผู้บริโภคผ่านเครื่องมือดิจิทัล และการตลาดบนแพลตฟอร์มโซเชียลมีเดีย</p>
        </div>
      </li>
      <li>
        <div>
          <h3>4. การออกแบบสื่อดิจิทัลและประสบการณ์ผู้ใช้ (UI/UX & Digital Media)</h3>
          <p>หลักการออกแบบอินเทอร์เฟซ การจัดวาง Typography, Visual Hierarchy, การทำ User Flow, และการผลิตสื่อกราฟิกมัลติมีเดียที่ตอบโจทย์การใช้งานจริง</p>
        </div>
      </li>
      <li>
        <div>
          <h3>5. ความมั่นคงปลอดภัยไซเบอร์และคลาวด์ (Cybersecurity & Cloud)</h3>
          <p>ทักษะพื้นฐานด้านความมั่นคงปลอดภัยบนเครือข่าย การป้องกันข้อมูลรั่วไหล การประยุกต์ใช้บริการคลาวด์คอมพิวติง และจริยธรรมดิจิทัล</p>
        </div>
      </li>
    </ul>
  </div>
</section>

<!-- ========================================================================
     CURRICULUM BREAKDOWN
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">หลักสูตรวิชาชีพ</p>
        <h2>ระดับการศึกษาและหลักสูตรที่เปิดสอน</h2>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-md-6">
        <div class="curriculum-card-actionable h-100 d-flex flex-column justify-content-between" id="curriculum-vocational">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-navy fw-bold" style="font-size: 14px;">หลักสูตร 3 ปี</span>
              <span class="badge-upcoming">รับผู้จบ ม.3</span>
            </div>
            <h3 style="font-size: 1.25rem;">ประกาศนียบัตรวิชาชีพ (ปวช.)</h3>
            <p class="text-muted" style="font-size: .9375rem;">
              หลักสูตรพื้นฐานเข้มข้น 3 ปี ปูพื้นฐานการเขียนโค้ด การสร้างเว็บไซต์ด้วยเทคโนโลยีมาตรฐาน การจัดการฐานข้อมูล และหลักการตลาดดิจิทัลเบื้องต้น
            </p>
            <ul class="text-muted" style="font-size: .875rem; padding-left: 1.25rem; margin-bottom: 0;">
              <li>การเขียนโปรแกรมเชิงวัตถุและโครงสร้างข้อมูลพื้นฐาน</li>
              <li>การจัดการฐานข้อมูลดิจิทัลและคิวรี SQL</li>
              <li>การออกแบบสื่อดิจิทัลและส่วนต่อประสานผู้ใช้ (UI/UX)</li>
              <li>โครงงานธุรกิจดิจิทัลจำลองก่อนสำเร็จการศึกษา</li>
            </ul>
          </div>
          <div class="mt-4 pt-3 border-top">
            <a href="<?= url('schedule?group=' . urlencode('ปวช.2/1')) ?>" class="btn btn-sm btn-navy d-inline-flex align-items-center gap-2">
              <span>ดูตารางเรียน ปวช.</span>
              <?= icon('arrow-right', 14) ?>
            </a>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="curriculum-card-actionable h-100 d-flex flex-column justify-content-between" id="curriculum-high-vocational">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-navy fw-bold" style="font-size: 14px;">หลักสูตร 2 ปี</span>
              <span class="badge-upcoming">รับผู้จบ ม.6 / ปวช.</span>
            </div>
            <h3 style="font-size: 1.25rem;">ประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.)</h3>
            <p class="text-muted" style="font-size: .9375rem;">
              หลักสูตรสมรรถนะวิชาชีพขั้นสูง 2 ปี ยกระดับสู่การเป็น Full-Stack Developer, Data Analyst และผู้ประกอบการนวัตกรรมดิจิทัล พร้อมทำงานในอุตสาหกรรมจริง
            </p>
            <ul class="text-muted" style="font-size: .875rem; padding-left: 1.25rem; margin-bottom: 0;">
              <li>สถาปัตยกรรม Web Application และ Cloud Services</li>
              <li>การวิเคราะห์ข้อมูลเชิงลึก (Business Intelligence)</li>
              <li>การพัฒนาแอปพลิเคชันบนอุปกรณ์พกพาและการเชื่อมต่อ API</li>
              <li>การฝึกงานเข้มข้นในสถานประกอบการ 1 ภาคเรียนเต็ม</li>
            </ul>
          </div>
          <div class="mt-4 pt-3 border-top">
            <a href="<?= url('schedule?group=' . urlencode('ปวส.1/1')) ?>" class="btn btn-sm btn-navy d-inline-flex align-items-center gap-2">
              <span>ดูตารางเรียน ปวส.</span>
              <?= icon('arrow-right', 14) ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     FACILITIES & LABS
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">สิ่งอำนวยความสะดวก</p>
        <h2>ห้องปฏิบัติการและสภาพแวดล้อมการเรียนรู้</h2>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="facility-card-static h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 13.5px; font-weight: 500;">LAB 301</span>
          <h4 style="font-size: 1.05rem;">Web Architecture Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">เครื่องคอมพิวเตอร์สเปกสูง 40 เครื่อง พร้อมระบบ Local Server และสภาพแวดล้อมจำลองสำหรับการพัฒนาเว็บแอปพลิเคชัน</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="facility-card-static h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 13.5px; font-weight: 500;">LAB 302</span>
          <h4 style="font-size: 1.05rem;">Database & Analytics Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">รองรับการจัดเก็บและการคิวรีข้อมูล และการวิเคราะห์ข้อมูลธุรกิจด้วยเครื่องมือ BI และระบบจัดการฐานข้อมูลเชิงสัมพันธ์</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="facility-card-static h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 13.5px; font-weight: 500;">LAB 303</span>
          <h4 style="font-size: 1.05rem;">Digital Media & UI/UX Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">ติดตั้งหน้าจอความละเอียดสูงและอุปกรณ์สำหรับออกแบบส่วนต่อประสาน, การตัดต่อสื่อ และงานกราฟิกดิจิทัล</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="facility-card-static h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 13.5px; font-weight: 500;">ROOM 306</span>
          <h4 style="font-size: 1.05rem;">Student Club & Coworking</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">พื้นที่บ่มเพาะโครงงานและศูนย์กลางกิจกรรมชมรมวิชาชีพ พร้อมอุปกรณ์ประชุมทางไกลและระบบเครือข่ายความเร็วสูง</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     INTERNSHIP & INDUSTRY PARTNERS
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">ความร่วมมือ</p>
        <h2>โครงการฝึกงานและเครือข่ายพันธมิตร</h2>
      </div>
    </div>

    <div class="p-4 bg-white border">
      <div class="row align-items-center gy-3">
        <div class="col-12 col-md-8">
          <h3 style="font-size: 1.2rem;">การเรียนรู้ผ่านประสบการณ์จริงในสถานประกอบการ</h3>
          <p class="text-muted mb-0" style="font-size: .9375rem;">
            สาขาฯ มีข้อตกลงความร่วมมือ (MOU) ร่วมกับบริษัทเทคโนโลยี ซอฟต์แวร์เฮาส์ ดิจิทัลเอเจนซี่ และหน่วยงานชั้นนำ นักศึกษาทุกคนในระดับ ปวช.3 และ ปวส.2 จะได้ออกฝึกปฏิบัติงานจริงในตำแหน่ง Junior Developer, Content Creator, Data Support หรือ E-Commerce Operations ตลอดภาคเรียน
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <div class="text-navy fw-bold" style="font-size: 2.25rem;">96%</div>
          <div class="text-muted" style="font-size: .875rem;">อัตรานักศึกษาได้รับการจ้างงานต่อหลังฝึกงาน</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     HEAD OF DEPARTMENT FEATURE
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">ผู้บริหารสาขาวิชา</p>
        <h2>หัวหน้าสาขาวิชา</h2>
      </div>
    </div>

    <?php if ($headTeacher): ?>
      <div class="person-feature">
        <div class="marks figure-img">
          <?php if (!empty($headTeacher['image_path']) && file_exists(ROOT_PATH . '/' . $headTeacher['image_path'])): ?>
            <img src="<?= url($headTeacher['image_path']) ?>" alt="<?= e($headTeacher['name']) ?>" loading="lazy">
          <?php else: ?>
            <div class="monogram monogram-lg" style="aspect-ratio: 3/4;">
              <?= initials($headTeacher['name']) ?>
            </div>
          <?php endif; ?>
        </div>

        <div>
          <span class="badge-active mb-2 d-inline-block">หัวหน้าสาขาวิชา</span>
          <h3 class="person-name"><?= e($headTeacher['name']) ?></h3>
          <p class="person-role"><?= e($headTeacher['position']) ?></p>

          <blockquote class="quote mb-4">
            "เป้าหมายสูงสุดของสาขาเรา คือการสร้างกำลังคนที่มีทักษะเทคโนโลยีที่จับต้องได้ มีวินัยในกระบวนการทำงาน และมีความคิดสร้างสรรค์ในการแก้ปัญหาธุรกิจจริง"
          </blockquote>

          <dl class="row gy-2 text-muted" style="font-size: .9375rem;">
            <dt class="col-sm-4 text-navy">ความเชี่ยวชาญเฉพาะทาง</dt>
            <dd class="col-sm-8"><?= e($headTeacher['specialization'] ?: 'เทคโนโลยีซอฟต์แวร์และการจัดการสารสนเทศ') ?></dd>

            <dt class="col-sm-4 text-navy">อีเมลติดต่อ</dt>
            <dd class="col-sm-8"><a href="mailto:<?= e($headTeacher['email']) ?>"><?= e($headTeacher['email']) ?></a></dd>
          </dl>

          <a href="<?= url('teachers.php') ?>" class="btn btn-navy btn-sm mt-2">
            <span>ดูทำเนียบคณาจารย์ทั้งหมด</span>
            <?= icon('arrow-right', 14) ?>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ========================================================================
     CONTACT INFORMATION
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">ติดต่อเรา</p>
        <h2>ช่องทางการติดต่อสาขาวิชา</h2>
      </div>
    </div>

    <ul class="contact-list">
      <li>
        <a href="tel:<?= e(SettingService::get('contact_phone', CONTACT_PHONE)) ?>">
          <?= icon('phone', 20) ?>
          <div>
            <span class="contact-k">โทรศัพท์</span>
            <span class="fw-bold"><?= e(SettingService::get('contact_phone', CONTACT_PHONE)) ?></span>
          </div>
          <?= icon('chevron-right', 18) ?>
        </a>
      </li>
      <li>
        <a href="mailto:<?= e(SettingService::get('contact_email', CONTACT_EMAIL)) ?>">
          <?= icon('mail', 20) ?>
          <div>
            <span class="contact-k">อีเมลติดต่อราชการ</span>
            <span class="fw-bold"><?= e(SettingService::get('contact_email', CONTACT_EMAIL)) ?></span>
          </div>
          <?= icon('chevron-right', 18) ?>
        </a>
      </li>
      <li>
        <a href="<?= e(SettingService::get('contact_facebook_url', CONTACT_FACEBOOK_URL)) ?>" target="_blank" rel="noopener noreferrer">
          <?= icon('facebook', 20) ?>
          <div>
            <span class="contact-k">แฟนเพจ Facebook</span>
            <span class="fw-bold"><?= e(SettingService::get('contact_facebook_name', CONTACT_FACEBOOK_NAME)) ?></span>
          </div>
          <?= icon('external', 18) ?>
        </a>
      </li>
      <li>
        <div class="contact-static">
          <?= icon('map-pin', 20) ?>
          <div>
            <span class="contact-k">สถานที่ตั้ง / ห้องพักอาจารย์</span>
            <span class="fw-bold"><?= e(SettingService::get('contact_address', CONTACT_ADDRESS)) ?></span>
          </div>
          <span class="text-muted" style="font-size: 13px;"><?= e(SettingService::get('contact_hours', CONTACT_HOURS)) ?></span>
        </div>
      </li>
    </ul>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
