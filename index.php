<?php
/**
 * 01 / HOME — หน้าหลัก แผนกเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$activeNav = 'home';
$pageTitle = 'หน้าหลัก';

// Fetch settings
$heroTitle1 = setting('hero_title_1', 'เทคโนโลยี');
$heroTitle2 = setting('hero_title_2', 'ธุรกิจดิจิทัล');
$heroIntro  = setting('hero_intro', 'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล');
$heroImage   = setting('hero_image', 'uploads/seed/hero-lab.jpg');
$heroCaption = setting('hero_caption', 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์');

$currCode    = setting('curriculum_code', 'DBT-2567-V02');

// Dynamic metrics according to User Story 5
$cntTeachers     = (int) q("SELECT COUNT(*) FROM teachers")->fetchColumn();
$cntNews         = (int) q("SELECT COUNT(*) FROM news")->fetchColumn();
$cntUpcomingActs = (int) q("SELECT COUNT(*) FROM activities WHERE event_date >= CURDATE()")->fetchColumn();

// Fetch latest news (top 3)
$latestNews = q("
  SELECT n.*, c.name as category_name
  FROM news n
  JOIN news_categories c ON n.category_id = c.id
  ORDER BY n.published_at DESC, n.id DESC
  LIMIT 3
")->fetchAll();

// Fetch next upcoming activity
$nextActivity = q("
  SELECT * FROM activities
  WHERE event_date >= CURDATE()
  ORDER BY event_date ASC
  LIMIT 1
")->fetch();

include __DIR__ . '/includes/header.php';
?>

<!-- ========================================================================
     HERO SECTION — Editorial Technology Style
     ======================================================================== -->
<section class="hero">
  <div class="container-xl">
    <div class="row align-items-center gy-5">
      
      <div class="col-12 col-lg-6">
        <div class="hero-label">
          <p class="eyebrow">
            <span>01</span>
            <span class="sep">/</span>
            <span>DIGITAL BUSINESS TECHNOLOGY</span>
          </p>
        </div>

        <h1 class="hero-title">
          <span class="line"><?= e($heroTitle1) ?></span>
          <span class="line line-2"><?= e($heroTitle2) ?></span>
        </h1>

        <span class="rule" aria-hidden="true"></span>

        <p class="hero-intro">
          <?= nl2br(e($heroIntro)) ?>
        </p>

        <dl class="hero-meta">
          <div>
            <dt>CURRICULUM SPEC</dt>
            <dd class="mono"><?= e($currCode) ?></dd>
          </div>
          <div>
            <dt>STUDENT STATUS</dt>
            <dd>เปิดรับสมัคร ปวช. / ปวส.</dd>
          </div>
          <div>
            <dt>PRIMARY FOCUS</dt>
            <dd>Full-Stack & Biz Data</dd>
          </div>
          <div>
            <dt>LEARNING METHOD</dt>
            <dd>Project & Lab Based</dd>
          </div>
        </dl>
      </div>

      <div class="col-12 col-lg-6 hero-figure">
        <div class="marks">
          <figure class="figure w-100">
            <div class="figure-img">
              <?php if ($heroImage && file_exists(ROOT_PATH . '/' . $heroImage)): ?>
                <img src="<?= url($heroImage) ?>" alt="ห้องปฏิบัติการเทคโนโลยีธุรกิจดิจิทัล" width="1200" height="900" loading="eager">
              <?php else: ?>
                <div class="monogram monogram-lg" style="aspect-ratio: 4/3;">DBT // LAB</div>
              <?php endif; ?>
            </div>
            <figcaption class="figcap">
              <span><?= e($heroCaption) ?></span>
              <span class="mono">FIG 01.1 // LAB DEPT</span>
            </figcaption>
          </figure>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================================
     DATA SUMMARY STRIP — Technical Academic Metrics
     ======================================================================== -->
<section class="data-strip" aria-label="ข้อมูลเชิงสถิติของแผนก">
  <div class="container-xl">
    <ul>
      <li>
        <span class="data-num"><?= $cntTeachers ?><small>ท่าน</small></span>
        <span class="data-label">คณาจารย์ประจำแผนกวิชา</span>
      </li>
      <li>
        <span class="data-num"><?= $cntNews ?><small>เรื่อง</small></span>
        <span class="data-label">ข่าวสารและประกาศเผยแพร่</span>
      </li>
      <li>
        <span class="data-num"><?= $cntUpcomingActs ?><small>กิจกรรม</small></span>
        <span class="data-label">กิจกรรมและโครงการเร็ว ๆ นี้</span>
      </li>
    </ul>
  </div>
</section>

<!-- ========================================================================
     INDEX DIRECTORY — Structured Section Navigation
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">INDEX DIRECTORY</p>
        <h2>สารบัญแผนกวิชา</h2>
      </div>
      <span class="mono text-muted d-none d-md-inline" style="font-size: 11px;">02 – 05 SECTIONS</span>
    </div>

    <ul class="index-list">
      <li>
        <a href="<?= url('about.php') ?>">
          <span class="index-num">02</span>
          <div>
            <span class="index-title">เกี่ยวกับแผนกวิชา</span>
            <span class="index-desc">วิสัยทัศน์ สมรรถนะหลักสูตร หัวหน้าแผนก และช่องทางการติดต่อ</span>
          </div>
          <span class="mono text-muted d-none d-md-block">ABOUT & CONTACT</span>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('news.php') ?>">
          <span class="index-num">03</span>
          <div>
            <span class="index-title">ข่าวสารและประกาศ</span>
            <span class="index-desc">อัปเดตวิชาการ ข่าวรับสมัคร กิจกรรม และผลงานนักศึกษา</span>
          </div>
          <span class="mono text-muted d-none d-md-block">NEWS & UPDATES</span>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('schedule.php') ?>">
          <span class="index-num">04</span>
          <div>
            <span class="index-title">ตารางเรียนและกิจกรรม</span>
            <span class="index-desc">ตารางสอนรายสัปดาห์ ปฏิทินกิจกรรม และกำหนดการสำคัญ</span>
          </div>
          <span class="mono text-muted d-none d-md-block">TIMETABLE & EVENTS</span>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('teachers.php') ?>">
          <span class="index-num">05</span>
          <div>
            <span class="index-title">อาจารย์และชมรม</span>
            <span class="index-desc">ทำเนียบคณาจารย์ผู้สอน และคณะกรรมการชมรมวิชาชีพนักศึกษา</span>
          </div>
          <span class="mono text-muted d-none d-md-block">FACULTY & CLUB</span>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
    </ul>
  </div>
</section>

<!-- ========================================================================
     LATEST NEWS PREVIEW — Editorial 2-column Grid
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">03 / LATEST UPDATES</p>
        <h2>ข่าวสารล่าสุด</h2>
      </div>
      <a href="<?= url('news.php') ?>" class="link-more">
        <span>ดูข่าวทั้งหมด</span>
        <?= icon('arrow-right', 16) ?>
      </a>
    </div>

    <div class="news-list">
      <?php foreach ($latestNews as $item): ?>
        <article class="news-row">
          <div>
            <div class="news-meta">
              <span class="news-cat"><?= e($item['category_name']) ?></span>
              <span class="mono text-muted" style="font-size: 12px;"><?= mono_date($item['published_at']) ?></span>
            </div>
            <h3>
              <a href="<?= url('news.php?id=' . $item['id']) ?>" class="text-navy text-decoration-none">
                <?= e($item['title']) ?>
              </a>
            </h3>
            <p class="text-muted mb-0" style="font-size: .875rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?= e($item['summary']) ?>
            </p>
          </div>
          <div class="thumb">
            <?php if (!empty($item['image_path']) && file_exists(ROOT_PATH . '/' . $item['image_path'])): ?>
              <img src="<?= url($item['image_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
            <?php else: ?>
              <div class="monogram"><?= e(SITE_SHORT) ?></div>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================================================================
     NEXT UPCOMING ACTIVITY STRIP
     ======================================================================== -->
<?php if ($nextActivity): ?>
<section class="section" style="padding-block: var(--s-6);">
  <div class="container-xl">
    <div class="p-4 p-md-5" style="background: var(--c-blue-100); border-left: 4px solid var(--c-navy);">
      <div class="row align-items-center gy-3">
        <div class="col-12 col-md-8">
          <?php $nextCd = activity_countdown_badge($nextActivity['event_date'], $nextActivity['event_date_end'] ?? null); ?>
          <p class="eyebrow mb-1">
            <span>NEXT UPCOMING EVENT</span>
            <span class="<?= e($nextCd['class']) ?>"><?= e($nextCd['text']) ?></span>
            <?php if (!empty($nextActivity['badge_text'])): ?>
              <span class="badge-line"><?= e($nextActivity['badge_text']) ?></span>
            <?php endif; ?>
          </p>
          <h3 class="mb-2" style="font-size: 1.35rem;"><?= e($nextActivity['title']) ?></h3>
          <p class="mb-0 text-muted" style="font-size: .9375rem;">
            <?= e($nextActivity['description'] ?: 'กิจกรรมพัฒนาทักษะวิชาชีพและเสริมสร้างสมรรถนะนักศึกษา') ?>
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <div class="mono text-navy font-weight-bold" style="font-size: 1.125rem;">
            <?= thai_date($nextActivity['event_date'], true) ?>
          </div>
          <?php if (!empty($nextActivity['location'])): ?>
            <div class="text-muted" style="font-size: .875rem;">
              <?= icon('map-pin', 14) ?> <?= e($nextActivity['location']) ?>
            </div>
          <?php endif; ?>
          <a href="<?= url('schedule.php?tab=activity') ?>" class="btn btn-navy btn-sm mt-3">
            <span>ดูกิจกรรมทั้งหมด</span>
            <?= icon('arrow-right', 14) ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
