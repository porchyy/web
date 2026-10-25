<?php
/**
 * หน้าหลัก - สาขาเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/services/SettingService.php';
require_once __DIR__ . '/includes/services/NewsService.php';
require_once __DIR__ . '/includes/services/ScheduleService.php';

$activeNav = 'home';
$pageTitle = 'หน้าหลัก';

header('Cache-Control: no-cache, must-revalidate');

// Fetch settings
$heroTitle   = SettingService::get('hero_title', '');
if (!$heroTitle) {
    $heroTitle1 = SettingService::get('hero_title_1', 'เทคโนโลยี');
    $heroTitle2 = SettingService::get('hero_title_2', 'ธุรกิจดิจิทัล');
    $heroTitle  = trim($heroTitle1 . '<br>' . $heroTitle2, '<br>');
}
$heroIntro   = SettingService::get('hero_intro', 'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล');
$heroImage   = SettingService::get('hero_image', 'uploads/seed/hero-lab.jpg');
$heroCaption = SettingService::get('hero_caption', 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์');

// Metrics from SettingService (dynamic or manual configured via Admin)
$homeStats = SettingService::getHomeStats();

// Fetch latest news via NewsService
$latestNews = NewsService::getLatest(3);

// Fetch next upcoming activity via ScheduleService
$nextActivity = ScheduleService::getNextUpcomingActivity();

$availableGroups = ScheduleService::getAvailableGroups();
$queryGroup = trim((string) ($_GET['group'] ?? ''));
$selectedGroup = in_array($queryGroup, $availableGroups, true) ? $queryGroup : ($availableGroups[0] ?? '');
$timetable = ScheduleService::getTimetable($selectedGroup);
$previewDay = (int) date('N') <= 5 ? (int) date('N') : 1;
$previewSchedule = ScheduleService::getScheduleByDay($selectedGroup)[$previewDay] ?? [];

include __DIR__ . '/includes/header.php';
?>

<!-- ========================================================================
     HERO SECTION
     ======================================================================== -->
<section class="hero">
  <div class="container-xl">
    <div class="row align-items-center gy-5">
      
      <div class="col-12 col-lg-6">
        <div class="hero-label">
          <p class="eyebrow">
            <span><?= e(SITE_NAME_EN) ?></span>
          </p>
        </div>

        <h1 class="hero-title">
          <?= clean_html(nl2br($heroTitle)) ?>
        </h1>

        <span class="rule" aria-hidden="true"></span>

        <div class="hero-intro">
          <?= clean_html(parse_markdown($heroIntro)) ?>
        </div>

        <dl class="hero-meta">
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
          <figure class="figure w-100">
            <div class="figure-img">
              <?php if ($heroImage && file_exists(ROOT_PATH . '/' . $heroImage)): ?>
                <img src="<?= url($heroImage) ?>" alt="ห้องปฏิบัติการเทคโนโลยีธุรกิจดิจิทัล" width="1200" height="900" loading="eager">
              <?php else: ?>
                <div class="monogram monogram-lg" style="aspect-ratio: 4/3;"><?= e(SITE_SHORT) ?> // LAB</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($heroCaption)): ?>
              <figcaption class="figcap">
                <span><?= e($heroCaption) ?></span>
              </figcaption>
            <?php endif; ?>
          </figure>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================================
     DATA SUMMARY STRIP
     ======================================================================== -->
<section class="data-strip" aria-label="ข้อมูลเชิงสถิติของสาขา">
  <div class="container-xl">
    <ul>
      <?php foreach ($homeStats['items'] as $stat): ?>
        <li>
          <span class="data-num">
            <?= e($stat['num']) ?><?php if (!empty($stat['unit'])): ?><small><?= e($stat['unit']) ?></small><?php endif; ?>
          </span>
          <span class="data-label"><?= e($stat['label']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ========================================================================
     INDEX DIRECTORY
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">หมวดหมู่เนื้อหา</p>
        <h2>สารบัญสาขาวิชา</h2>
      </div>
    </div>

    <ul class="index-list">
      <li>
        <a href="<?= url('about') ?>">
          <div>
            <span class="index-title">เกี่ยวกับสาขาวิชา</span>
            <span class="index-desc">วิสัยทัศน์ สมรรถนะหลักสูตร หัวหน้าสาขา และช่องทางการติดต่อ</span>
          </div>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('news') ?>">
          <div>
            <span class="index-title">ข่าวสารและประกาศ</span>
            <span class="index-desc">อัปเดตวิชาการ ข่าวรับสมัคร กิจกรรม และผลงานนักศึกษา</span>
          </div>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('schedule') ?>">
          <div>
            <span class="index-title">ตารางเรียนและกิจกรรม</span>
            <span class="index-desc">ตารางสอนรายสัปดาห์ ปฏิทินกิจกรรม และกำหนดการสำคัญ</span>
          </div>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
      <li>
        <a href="<?= url('teachers') ?>">
          <div>
            <span class="index-title">อาจารย์และชมรมวิชาชีพ</span>
            <span class="index-desc">ทำเนียบคณาจารย์ผู้สอน และคณะกรรมการชมรมวิชาชีพนักศึกษา</span>
          </div>
          <?= icon('arrow-right', 20) ?>
        </a>
      </li>
    </ul>
  </div>
</section>

<section class="section section-alt" aria-labelledby="homeTimetableTitle">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">ตารางเรียน</p>
        <h2 id="homeTimetableTitle">ตารางเรียนประจำกลุ่ม</h2>
      </div>
      <a class="link-more" href="<?= e(url('schedule?group=' . urlencode($selectedGroup))) ?>">ดูตารางเรียนทั้งหมด <?= icon('arrow-right', 16) ?></a>
    </div>
    <?php if ($availableGroups): ?>
      <form method="get" action="<?= url('') ?>#homeTimetableTitle" class="d-flex flex-wrap align-items-end gap-2 mb-4">
        <div>
          <label for="homeTimetableGroup" class="form-label">กลุ่มเรียน</label>
          <select id="homeTimetableGroup" name="group" class="form-select">
            <?php foreach ($availableGroups as $group): ?>
              <option value="<?= e($group) ?>" <?= $group === $selectedGroup ? 'selected' : '' ?>><?= e($group) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-navy">แสดงตาราง</button>
      </form>
    <?php endif; ?>
    <?php if ($timetable['display_mode'] === 'image' && ScheduleService::hasTimetableImage($timetable)): ?>
      <?php require __DIR__ . '/includes/renderers/timetable-image.php'; ?>
    <?php elseif ($timetable['display_mode'] === 'image'): ?>
      <p class="text-muted mb-0" role="status">รูปตารางเรียนไม่พร้อมใช้งาน กรุณาติดต่อเจ้าหน้าที่เพื่ออัปเดตรูปตารางเรียน</p>
    <?php else: ?>
      <h3 class="h5 mb-3">วัน<?= TH_DAYS[$previewDay] ?><?= $selectedGroup !== '' ? ' · ' . e($selectedGroup) : '' ?></h3>
      <?php if ($previewSchedule): ?>
        <div class="table-responsive">
          <table class="table bg-white align-middle">
            <caption>ตารางเรียนวัน<?= TH_DAYS[$previewDay] ?> กลุ่ม <?= e($selectedGroup) ?></caption>
            <thead><tr><th scope="col">เวลา</th><th scope="col">รายวิชา</th><th scope="col">ห้องเรียน</th></tr></thead>
            <tbody>
              <?php foreach ($previewSchedule as $slot): ?>
                <tr>
                  <td class="mono text-nowrap"><?= hm($slot['time_start']) ?>–<?= hm($slot['time_end']) ?></td>
                  <td><?= e($slot['subject_name']) ?></td>
                  <td><?= e($slot['room'] ?: '—') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted mb-0">ยังไม่มีคาบเรียนสำหรับวันนี้ ดูตารางรายสัปดาห์ได้ที่หน้าตารางเรียน</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ========================================================================
     LATEST NEWS PREVIEW
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">อัปเดตล่าสุด</p>
        <h2>ข่าวสารและประกาศล่าสุด</h2>
      </div>
      <a href="<?= url('news') ?>" class="link-more">
        <span>ดูข่าวทั้งหมด</span>
        <?= icon('arrow-right', 16) ?>
      </a>
    </div>

    <div class="news-list">
      <?php foreach ($latestNews as $item): ?>
        <article class="news-row position-relative">
          <div>
            <div class="news-meta">
              <span class="news-cat"><?= e($item['category_name']) ?></span>
              <span class="text-muted" style="font-size: 13.5px;"><?= thai_date($item['published_at']) ?></span>
            </div>
            <h3>
              <a href="<?= url('news?id=' . $item['id']) ?>" class="stretched-link text-navy text-decoration-none">
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
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="eyebrow mb-0">กิจกรรมเร็ว ๆ นี้</span>
            <span class="<?= e($nextCd['class']) ?>"><?= e($nextCd['text']) ?></span>
          </div>
          <h3 class="mb-2" style="font-size: 1.35rem;"><?= e($nextActivity['title']) ?></h3>
          <p class="mb-0 text-muted" style="font-size: .9375rem;">
            <?= e($nextActivity['description'] ?: 'กิจกรรมพัฒนาทักษะวิชาชีพและเสริมสร้างสมรรถนะนักศึกษา') ?>
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <div class="text-navy fw-bold" style="font-size: 1.125rem;">
            <?= thai_date($nextActivity['event_date'], true) ?>
          </div>
          <?php if (!empty($nextActivity['location'])): ?>
            <div class="text-muted" style="font-size: .875rem;">
              <?= icon('map-pin', 14) ?> <?= e($nextActivity['location']) ?>
            </div>
          <?php endif; ?>
          <a href="<?= url('schedule?tab=activity') ?>" class="btn btn-navy btn-sm mt-3">
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
