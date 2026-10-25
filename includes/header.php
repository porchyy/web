<?php
/**
 * Global Header Component
 * Variables:
 *   $pageTitle (string, optional)
 *   $activeNav (string, e.g. 'home', 'about', 'news', 'schedule', 'teachers')
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/AuthService.php';

$activeNav = $activeNav ?? '';
$titleText = !empty($pageTitle)
    ? e($pageTitle) . ' - ' . e(SITE_NAME)
    : e(SITE_NAME) . ' (' . e(SITE_SHORT) . ') - ' . e(SITE_NAME_EN);
$flash = flash();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล - Digital Business Technology สถานศึกษายุคใหม่ เรียนรู้การพัฒนาเทคโนโลยีและธุรกิจดิจิทัลร่วมสมัย">
  
  <!-- Open Graph / Social Sharing -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
  <meta property="og:title" content="<?= $titleText ?>">
  <meta property="og:description" content="สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล - Digital Business Technology สถานศึกษายุคใหม่ เรียนรู้การพัฒนาเทคโนโลยีและธุรกิจดิจิทัลร่วมสมัย">
  <meta property="og:image" content="<?= url('uploads/seed/hero-lab.jpg') ?>">

  <title><?= $titleText ?></title>

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="<?= url('assets/favicon/apple-touch-icon.png') ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= url('assets/favicon/favicon-32x32.png') ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= url('assets/favicon/favicon-16x16.png') ?>">
  <link rel="shortcut icon" href="<?= url('assets/favicon/favicon.ico') ?>">
  <link rel="manifest" href="<?= url('assets/favicon/site.webmanifest') ?>">
  <meta name="theme-color" content="#17324D">

  <!-- Google Fonts: Kanit (Display/Headings), Prompt (Body/UI) & IBM Plex Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Custom Design System -->
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/site.css') ?>">
</head>
<body>

<a href="#main" class="skip-link">ข้ามไปยังเนื้อหาหลัก</a>

<header class="site-header">
  <div class="container-xl d-flex align-items-center justify-content-between gap-3">
    <a href="<?= url('index') ?>" class="brand flex-shrink-0" aria-label="กลับสู่หน้าหลัก สาขาเทคโนโลยีธุรกิจดิจิทัล">
      <img src="<?= url('assets/favicon/apple-touch-icon.png') ?>" alt="<?= e(SITE_SHORT) ?>" class="brand-favicon" width="38" height="38">
      <div class="brand-text">
        <span class="brand-name"><?= e(SITE_NAME) ?></span>
        <span class="brand-sub"><?= e(SITE_NAME_EN) ?></span>
      </div>
    </a>

    <nav class="nav-desktop" aria-label="เมนูหลัก">
      <ol>
        <li>
          <a href="<?= url('index') ?>" <?= $activeNav === 'home' ? 'aria-current="page"' : '' ?>>
            <?= icon('home', 16) ?>
            <span>หน้าหลัก</span>
          </a>
        </li>
        <li>
          <a href="<?= url('about') ?>" <?= $activeNav === 'about' ? 'aria-current="page"' : '' ?>>
            <?= icon('info', 16) ?>
            <span>เกี่ยวกับสาขา</span>
          </a>
        </li>
        <li>
          <a href="<?= url('news') ?>" <?= $activeNav === 'news' ? 'aria-current="page"' : '' ?>>
            <?= icon('news', 16) ?>
            <span>ข่าวสาร</span>
          </a>
        </li>
        <li>
          <a href="<?= url('schedule') ?>" <?= $activeNav === 'schedule' ? 'aria-current="page"' : '' ?>>
            <?= icon('calendar', 16) ?>
            <span>ตารางเรียนและกิจกรรม</span>
          </a>
        </li>
        <li>
          <a href="<?= url('teachers') ?>" <?= $activeNav === 'teachers' ? 'aria-current="page"' : '' ?>>
            <?= icon('users', 16) ?>
            <span>อาจารย์และชมรม</span>
          </a>
        </li>
      </ol>
    </nav>

    <div class="d-flex align-items-center gap-2 flex-shrink-0">
      <?php if (AuthService::isLoggedIn()): ?>
        <a href="<?= url('admin') ?>" class="header-action" title="เข้าสู่ระบบจัดการหลังบ้าน">
          <?= icon('grid', 16) ?>
          <span>จัดการระบบ</span>
        </a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="header-action" title="เข้าสู่ระบบเจ้าหน้าที่">
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
