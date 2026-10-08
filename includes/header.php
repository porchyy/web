<?php
/**
 * Global Header Component
 * Variables:
 *   $pageTitle (string, optional)
 *   $activeNav (string, e.g. 'home', 'about', 'news', 'schedule', 'teachers')
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$activeNav = $activeNav ?? '';
$titleText = !empty($pageTitle)
    ? e($pageTitle) . ' — ' . e(SITE_NAME)
    : e(SITE_NAME) . ' (' . e(SITE_SHORT) . ') — ' . e(SITE_NAME_EN);
$flash = flash();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล — Digital Business Technology สถานศึกษายุคใหม่ เรียนรู้การพัฒนาเทคโนโลยีและธุรกิจดิจิทัลร่วมสมัย">
  <title><?= $titleText ?></title>

  <!-- Google Fonts: Anuphan & IBM Plex Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Custom Design System -->
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>">
</head>
<body>

<a href="#main" class="skip-link">ข้ามไปยังเนื้อหาหลัก</a>

<header class="site-header">
  <div class="container-xl d-flex align-items-center justify-content-between gap-3">
    <a href="<?= url('index.php') ?>" class="brand flex-shrink-0" aria-label="กลับสู่หน้าหลัก แผนกเทคโนโลยีธุรกิจดิจิทัล">
      <div class="brand-mark" aria-hidden="true">DBT</div>
      <div class="brand-text">
        <span class="brand-name"><?= e(SITE_NAME) ?></span>
        <span class="brand-sub"><?= e(SITE_NAME_EN) ?></span>
      </div>
    </a>

    <nav class="nav-desktop" aria-label="เมนูหลัก">
      <ol>
        <li>
          <a href="<?= url('index.php') ?>" <?= $activeNav === 'home' ? 'aria-current="page"' : '' ?>>
            <span class="nav-num">01 /</span> หน้าหลัก
          </a>
        </li>
        <li>
          <a href="<?= url('about.php') ?>" <?= $activeNav === 'about' ? 'aria-current="page"' : '' ?>>
            <span class="nav-num">02 /</span> เกี่ยวกับแผนก
          </a>
        </li>
        <li>
          <a href="<?= url('news.php') ?>" <?= $activeNav === 'news' ? 'aria-current="page"' : '' ?>>
            <span class="nav-num">03 /</span> ข่าวสาร
          </a>
        </li>
        <li>
          <a href="<?= url('schedule.php') ?>" <?= $activeNav === 'schedule' ? 'aria-current="page"' : '' ?>>
            <span class="nav-num">04 /</span> ตารางเรียนและกิจกรรม
          </a>
        </li>
        <li>
          <a href="<?= url('teachers.php') ?>" <?= $activeNav === 'teachers' ? 'aria-current="page"' : '' ?>>
            <span class="nav-num">05 /</span> อาจารย์และชมรม
          </a>
        </li>
      </ol>
    </nav>

    <div class="d-flex align-items-center gap-2 flex-shrink-0">
      <?php if (is_logged_in()): ?>
        <a href="<?= url('admin/index.php') ?>" class="header-action" title="เข้าสู่ระบบจัดการหลังบ้าน">
          <?= icon('grid', 16) ?>
          <span>จัดการระบบ</span>
        </a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="header-action" title="เข้าสู่ระบบเจ้าหน้าที่">
          <?= icon('log-in', 16) ?>
          <span>เข้าสู่ระบบ</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php if ($flash): ?>
  <div class="container-xl pt-3">
    <div class="alert-line <?= $flash['type'] === 'error' ? 'is-error' : '' ?>" role="alert">
      <?= icon($flash['type'] === 'error' ? 'alert' : 'check', 18) ?>
      <div><?= e($flash['msg']) ?></div>
    </div>
  </div>
<?php endif; ?>

<main id="main">
