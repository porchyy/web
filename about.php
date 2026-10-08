<?php
/**
 * 02 / ABOUT — เกี่ยวกับแผนกเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$activeNav = 'about';
$pageTitle = 'เกี่ยวกับแผนก';

// Fetch Head of Department
$headTeacher = q("
  SELECT * FROM teachers
  WHERE is_head = 1
  LIMIT 1
")->fetch();

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>02</span>
      <span class="sep">/</span>
      <span>ABOUT THE DEPARTMENT</span>
    </p>
    <h1>เกี่ยวกับแผนกวิชา</h1>
    <p class="lead-text">
      แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล มุ่งเน้นการสร้างนักปฏิบัติการด้านดิจิทัลที่มีทักษะการพัฒนาระบบเทคโนโลยีสารสนเทศ ควบคู่กับการเข้าใจกระบวนการทางธุรกิจสมัยใหม่ เพื่อตอบโจทย์ตลาดแรงงานในยุคเศรษฐกิจดิจิทัล
    </p>
  </div>
</div>

<!-- ========================================================================
     AREAS OF STUDY / แผนกมีอะไรบ้าง (A.01 – A.05)
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">CURRICULUM MODULES</p>
        <h2>แผนกมีอะไรบ้าง : ด้านการเรียนรู้</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;">5 CORE DOMAINS</span>
    </div>

    <ul class="offer-list">
      <li>
        <span class="offer-code">A.01</span>
        <div>
          <h3>การพัฒนาเว็บและแอปพลิเคชัน (Web & App Development)</h3>
          <p>เรียนรู้การสร้างเว็บแอปพลิเคชันแบบ Full-Stack ด้วยภาษา PHP 8, Modern JavaScript, ระบบเชื่อมต่อฐานข้อมูลเชิงสัมพันธ์ และการออกแบบ RESTful API</p>
        </div>
      </li>
      <li>
        <span class="offer-code">A.02</span>
        <div>
          <h3>การจัดการข้อมูลและฐานข้อมูลเชิงธุรกิจ (Database & Business Data)</h3>
          <p>การวิเคราะห์ข้อมูล การออกแบบโครงสร้างฐานข้อมูล SQL, การคิวรีข้อมูลเชิงลึก และการจัดทำแดชบอร์ดรายงานผลเชิงสถิติสำหรับผู้บริหาร</p>
        </div>
      </li>
      <li>
        <span class="offer-code">A.03</span>
        <div>
          <h3>การตลาดดิจิทัลและพาณิชย์อิเล็กทรอนิกส์ (Digital Marketing & E-Commerce)</h3>
          <p>การบริหารจัดการร้านค้าออนไลน์ การสร้างคอนเทนต์เชิงกลยุทธ์ การวิเคราะห์พฤติกรรมผู้บริโภคผ่านเครื่องมือดิจิทัล และการตลาดบนแพลตฟอร์มโซเชียลมีเดีย</p>
        </div>
      </li>
      <li>
        <span class="offer-code">A.04</span>
        <div>
          <h3>การออกแบบสื่อดิจิทัลและประสบการณ์ผู้ใช้ (UI/UX & Digital Media)</h3>
          <p>หลักการออกแบบอินเทอร์เฟซ การจัดวาง Typography, Visual Hierarchy, การทำ User Flow, และการผลิตสื่อกราฟิกมัลติมีเดียที่ตอบโจทย์การใช้งานจริง</p>
        </div>
      </li>
      <li>
        <span class="offer-code">A.05</span>
        <div>
          <h3>ความมั่นคงปลอดภัยไซเบอร์และคลาวด์ (Cybersecurity & Cloud)</h3>
          <p>ทักษะพื้นฐานด้านความมั่นคงปลอดภัยบนเครือข่าย การป้องกันข้อมูลรั่วไหล การประยุกต์ใช้บริการคลาวด์คอมพิวติง และจริยธรรมดิจิทัล</p>
        </div>
      </li>
    </ul>
  </div>
</section>

<!-- ========================================================================
     CURRICULUM BREAKDOWN / โครงสร้างหลักสูตร ปวช. และ ปวส.
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">PROGRAM OFFERINGS</p>
        <h2>ระดับการศึกษาและหลักสูตรที่เปิดสอน</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;">VOCATIONAL CURRICULUM</span>
    </div>

    <div class="row g-4">
      <div class="col-12 col-md-6">
        <div class="p-4 bg-white border h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="mono text-navy font-weight-bold" style="font-size: 13px;">LEVEL 01 // 3 YEARS</span>
            <span class="badge-soon">รับผู้จบ ม.3</span>
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
      </div>

      <div class="col-12 col-md-6">
        <div class="p-4 bg-white border h-100">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="mono text-navy font-weight-bold" style="font-size: 13px;">LEVEL 02 // 2 YEARS</span>
            <span class="badge-soon">รับผู้จบ ม.6 / ปวช.</span>
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
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     FACILITIES & LABS / ห้องปฏิบัติการและสิ่งอำนวยความสะดวก
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">FACILITIES & INFRASTRUCTURE</p>
        <h2>ห้องปฏิบัติการและสภาพแวดล้อมการเรียนรู้</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;">4 COMPUTING LABS</span>
    </div>

    <div class="row g-3">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="p-3 bg-white border h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 11px;">LAB 301 // DEV WORKSPACE</span>
          <h4 style="font-size: 1.05rem;">Web Architecture Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">เครื่องคอมพิวเตอร์สเปกสูง 40 เครื่อง พร้อมระบบ Local Server และสภาพแวดล้อมจำลอง Docker สำหรับการพัฒนาเว็บแอปพลิเคชัน</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="p-3 bg-white border h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 11px;">LAB 302 // DATA WORKSPACE</span>
          <h4 style="font-size: 1.05rem;">Database & Analytics Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">รองรับการจัดเก็บและการคิวรี Big Data และการวิเคราะห์ข้อมูลธุรกิจด้วยเครื่องมือ BI และระบบจัดการฐานข้อมูลเชิงสัมพันธ์</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="p-3 bg-white border h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 11px;">LAB 303 // CREATIVE LAB</span>
          <h4 style="font-size: 1.05rem;">Digital Media & UI/UX Lab</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">ติดตั้งหน้าจอความละเอียดสูงและอุปกรณ์อินพุตดิจิทัลสำหรับการออกแบบส่วนต่อประสาน, การตัดต่อสื่อ และงานกราฟิกสารคดี</p>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="p-3 bg-white border h-100">
          <span class="mono text-muted d-block mb-1" style="font-size: 11px;">ROOM 306 // CLUB & INCUBATOR</span>
          <h4 style="font-size: 1.05rem;">Student Club & Coworking</h4>
          <p class="text-muted mb-0" style="font-size: .875rem;">พื้นที่บ่มเพาะโครงงานและศูนย์กลางกิจกรรมชมรมวิชาชีพ พร้อมอุปกรณ์ประชุมทางไกลและระบบเชื่อมต่อเครือข่ายความเร็วสูง</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     INTERNSHIP & INDUSTRY PARTNERS / การฝึกงานและความร่วมมือ
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">INDUSTRY COLLABORATION</p>
        <h2>โครงการฝึกงานและเครือข่ายพันธมิตร</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;">INTERNSHIP PROGRAM</span>
    </div>

    <div class="p-4 bg-white border">
      <div class="row align-items-center gy-3">
        <div class="col-12 col-md-8">
          <h3 style="font-size: 1.2rem;">การเรียนรู้ผ่านประสบการณ์จริงในสถานประกอบการ</h3>
          <p class="text-muted mb-0" style="font-size: .9375rem;">
            แผนกฯ มีข้อตกลงความร่วมมือ (MOU) ร่วมกับบริษัทเทคโนโลยี ซอฟต์แวร์เฮาส์ ดิจิทัลเอเจนซี่ และหน่วยงานภาครัฐกว่า 20 องค์กร นักศึกษาทุกคนในระดับ ปวช.3 และ ปวส.2 จะได้ออกฝึกปฏิบัติงานจริงในตำแหน่ง Junior Developer, Content Creator, Data Support หรือ E-Commerce Operations ตลอดภาคเรียน
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <div class="mono text-navy font-weight-bold" style="font-size: 2rem;">96%</div>
          <div class="text-muted" style="font-size: .875rem;">อัตรานักศึกษาได้รับการจ้างงานต่อหลังฝึกงาน</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     HEAD OF DEPARTMENT FEATURE / หัวหน้าแผนก
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">HEAD OF DEPARTMENT</p>
        <h2>หัวหน้าแผนกวิชา</h2>
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
          <span class="eyebrow mb-1">DEPARTMENT LEADERSHIP</span>
          <h3 class="person-name"><?= e($headTeacher['name']) ?></h3>
          <p class="person-role"><?= e($headTeacher['position']) ?></p>

          <blockquote class="quote mb-4">
            "เป้าหมายสูงสุดของแผนกเรา คือการสร้างกำลังคนที่มีทักษะเทคโนโลยีที่จับต้องได้ มีวินัยในกระบวนการทำงาน และมีความคิดสร้างสรรค์ในการแก้ปัญหาธุรกิจจริง"
          </blockquote>

          <dl class="row gy-2 text-muted" style="font-size: .9375rem;">
            <dt class="col-sm-3 mono" style="font-size: 11px; text-transform: uppercase;">SPECIALIZATION</dt>
            <dd class="col-sm-9"><?= e($headTeacher['specialization'] ?: 'เทคโนโลยีซอฟต์แวร์และการจัดการสารสนเทศ') ?></dd>

            <dt class="col-sm-3 mono" style="font-size: 11px; text-transform: uppercase;">EMAIL CONTACT</dt>
            <dd class="col-sm-9 mono"><a href="mailto:<?= e($headTeacher['email']) ?>"><?= e($headTeacher['email']) ?></a></dd>
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
     CONTACT INFORMATION / ช่องทางการติดต่อ
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">DIRECT CONTACT</p>
        <h2>ช่องทางการติดต่อแผนก</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;">OFFICE HOURS</span>
    </div>

    <ul class="contact-list">
      <li>
        <a href="tel:<?= e(setting('contact_phone', CONTACT_PHONE)) ?>">
          <?= icon('phone', 20) ?>
          <div>
            <span class="contact-k">โทรศัพท์</span>
            <span class="fw-bold"><?= e(setting('contact_phone', CONTACT_PHONE)) ?></span>
          </div>
          <?= icon('chevron-right', 18) ?>
        </a>
      </li>
      <li>
        <a href="mailto:<?= e(setting('contact_email', CONTACT_EMAIL)) ?>">
          <?= icon('mail', 20) ?>
          <div>
            <span class="contact-k">อีเมลติดต่อราชการ</span>
            <span class="fw-bold"><?= e(setting('contact_email', CONTACT_EMAIL)) ?></span>
          </div>
          <?= icon('chevron-right', 18) ?>
        </a>
      </li>
      <li>
        <a href="<?= e(setting('contact_facebook_url', CONTACT_FACEBOOK_URL)) ?>" target="_blank" rel="noopener noreferrer">
          <?= icon('facebook', 20) ?>
          <div>
            <span class="contact-k">แฟนเพจ Facebook</span>
            <span class="fw-bold"><?= e(setting('contact_facebook_name', CONTACT_FACEBOOK_NAME)) ?></span>
          </div>
          <?= icon('external', 18) ?>
        </a>
      </li>
      <li>
        <div class="contact-static">
          <?= icon('map-pin', 20) ?>
          <div>
            <span class="contact-k">สถานที่ตั้ง / ห้องพักอาจารย์</span>
            <span class="fw-bold"><?= e(setting('contact_address', CONTACT_ADDRESS)) ?></span>
          </div>
          <span class="mono text-muted" style="font-size: 12px;"><?= e(setting('contact_hours', CONTACT_HOURS)) ?></span>
        </div>
      </li>
    </ul>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
