<?php
/**
 * Admin Panel Header
 * Enforces authentication and provides admin navigation
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/AuthService.php';

$currentUser = AuthService::requireLogin();
$adminNav = $adminNav ?? '';
$pageTitle = $pageTitle ?? 'ระบบจัดการ';
$flash = flash();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= e($pageTitle) ?> - ระบบจัดการ <?= e(SITE_NAME) ?></title>

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
  <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500;600&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Quill Rich Text Editor (Snow Theme) -->
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

  <!-- Custom Design Tokens & Admin CSS -->
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>?v=<?= file_exists(ROOT_PATH . '/assets/css/site.css') ? filemtime(ROOT_PATH . '/assets/css/site.css') : '1' ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>?v=<?= file_exists(ROOT_PATH . '/assets/css/admin.css') ? filemtime(ROOT_PATH . '/assets/css/admin.css') : '1' ?>">
</head>
<body style="padding-bottom: 40px; background-color: var(--c-bg-alt);">

<header class="admin-header py-2">
  <div class="container-xl d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= url('admin') ?>" class="brand text-white text-decoration-none">
        <img src="<?= url('assets/favicon/apple-touch-icon.png') ?>" alt="DBT" class="brand-favicon" width="38" height="38">
        <div class="brand-text">
          <span class="brand-name text-white">ระบบจัดการหลังบ้าน</span>
          <span class="brand-sub" style="color: var(--c-blue-300);"><?= e(SITE_NAME) ?></span>
        </div>
      </a>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="text-white-50 d-none d-md-inline mono" style="font-size: 13.5px;">
        <?= icon('user', 14) ?> <?= e($currentUser['full_name'] ?? $currentUser['username'] ?? 'Admin') ?>
      </span>
      <a href="<?= url('index') ?>" class="btn btn-sm btn-navy" target="_blank" title="ดูหน้าเว็บจริง">
        <?= icon('external', 14) ?>
        <span class="d-none d-sm-inline">ดูหน้าเว็บ</span>
      </a>
      <form method="POST" action="<?= url('logout') ?>" class="d-inline m-0">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-line text-white border-white-50" title="ออกจากระบบ">
          <?= icon('log-out', 14) ?>
          <span class="d-none d-sm-inline">ออกจากระบบ</span>
        </button>
      </form>
    </div>
  </div>
</header>

<!-- Admin Modules Strip -->
<nav class="admin-nav-strip" aria-label="เมนูจัดการหลังบ้าน">
  <div class="container-xl">
    <ul>
      <li>
        <a href="<?= url('admin') ?>" <?= $adminNav === 'dashboard' ? 'aria-current="page"' : '' ?>>
          <?= icon('grid', 16) ?> แดชบอร์ด
        </a>
      </li>
      <li>
        <a href="<?= url('admin/hero') ?>" <?= $adminNav === 'hero' ? 'aria-current="page"' : '' ?>>
          <?= icon('image', 16) ?> ภาพประชาสัมพันธ์
        </a>
      </li>
      <li>
        <a href="<?= url('admin/news') ?>" <?= $adminNav === 'news' ? 'aria-current="page"' : '' ?>>
          <?= icon('news', 16) ?> ข่าวสาร
        </a>
      </li>
      <li>
        <a href="<?= url('admin/classes') ?>" <?= $adminNav === 'classes' ? 'aria-current="page"' : '' ?>>
          <?= icon('book', 16) ?> ตารางเรียน
        </a>
      </li>
      <li>
        <a href="<?= url('admin/activities') ?>" <?= $adminNav === 'activities' ? 'aria-current="page"' : '' ?>>
          <?= icon('calendar', 16) ?> ตารางกิจกรรม
        </a>
      </li>
      <li>
        <a href="<?= url('admin/teachers') ?>" <?= $adminNav === 'teachers' ? 'aria-current="page"' : '' ?>>
          <?= icon('user', 16) ?> อาจารย์
        </a>
      </li>
      <li>
        <a href="<?= url('admin/club') ?>" <?= $adminNav === 'club' ? 'aria-current="page"' : '' ?>>
          <?= icon('flag', 16) ?> สมาชิกชมรม
        </a>
      </li>
      <li>
        <a href="<?= url('admin/users' . (($currentUser['role'] ?? '') !== 'admin' ? '?action=edit&id=' . (int) $currentUser['id'] : '')) ?>" <?= $adminNav === 'users' ? 'aria-current="page"' : '' ?>>
          <?= icon('users', 16) ?> <?= (($currentUser['role'] ?? '') === 'admin') ? 'ผู้ใช้งาน' : 'ข้อมูลส่วนตัว' ?>
        </a>
      </li>
      <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
      <li>
        <a href="<?= url('admin/settings') ?>" <?= $adminNav === 'settings' ? 'aria-current="page"' : '' ?>>
          <?= icon('settings', 16) ?> ตั้งค่าระบบ
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="<?= !empty($containerFluid) ? 'container-fluid px-3 px-xl-4 px-xxl-5' : 'container-xl' ?> pt-4">
  <?php if ($flash): ?>
    <div class="alert-line <?= $flash['type'] === 'error' ? 'is-error' : '' ?> mb-4" role="alert">
      <?= icon($flash['type'] === 'error' ? 'alert' : 'check', 18) ?>
      <div><?= e($flash['msg']) ?></div>
    </div>
  <?php endif; ?>
