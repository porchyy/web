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
