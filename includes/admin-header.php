<?php
/**
 * Admin Panel Header
 * Enforces authentication and provides admin navigation
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

require_login();
$currentUser = current_user();
$adminNav = $adminNav ?? '';
$pageTitle = $pageTitle ?? 'ระบบจัดการ';
$flash = flash();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= e($pageTitle) ?> — ระบบจัดการ <?= e(SITE_NAME) ?></title>

  <!-- Google Fonts: Anuphan & IBM Plex Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Custom Design Tokens & Admin CSS -->
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body style="padding-bottom: 40px; background-color: var(--c-bg-alt);">

<header class="admin-header py-2">
  <div class="container-xl d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= url('admin/index.php') ?>" class="brand text-white text-decoration-none">
        <div class="brand-mark">DBT</div>
        <div class="brand-text">
          <span class="brand-name text-white">ระบบจัดการหลังบ้าน</span>
          <span class="brand-sub" style="color: var(--c-blue-300);"><?= e(SITE_NAME) ?></span>
        </div>
      </a>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="text-white-50 d-none d-md-inline mono" style="font-size: 12px;">
        <?= icon('user', 14) ?> <?= e($currentUser['full_name'] ?? $currentUser['username'] ?? 'Admin') ?>
      </span>
      <a href="<?= url('index.php') ?>" class="btn btn-sm btn-navy" target="_blank" title="ดูหน้าเว็บจริง">
        <?= icon('external', 14) ?>
        <span class="d-none d-sm-inline">ดูหน้าเว็บ</span>
      </a>
      <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-line text-white border-white-50" title="ออกจากระบบ">
        <?= icon('log-out', 14) ?>
        <span class="d-none d-sm-inline">ออกจากระบบ</span>
      </a>
    </div>
  </div>
</header>

<!-- Admin Modules Strip -->
<nav class="admin-nav-strip" aria-label="เมนูจัดการหลังบ้าน">
  <div class="container-xl">
    <ul>
      <li>
        <a href="<?= url('admin/index.php') ?>" <?= $adminNav === 'dashboard' ? 'aria-current="page"' : '' ?>>
          <?= icon('grid', 16) ?> แดชบอร์ด
        </a>
      </li>
      <li>
        <a href="<?= url('admin/hero.php') ?>" <?= $adminNav === 'hero' ? 'aria-current="page"' : '' ?>>
          <?= icon('image', 16) ?> รูป Hero
        </a>
      </li>
      <li>
        <a href="<?= url('admin/news.php') ?>" <?= $adminNav === 'news' ? 'aria-current="page"' : '' ?>>
          <?= icon('news', 16) ?> ข่าวสาร
        </a>
      </li>
      <li>
        <a href="<?= url('admin/classes.php') ?>" <?= $adminNav === 'classes' ? 'aria-current="page"' : '' ?>>
          <?= icon('book', 16) ?> ตารางเรียน
        </a>
      </li>
      <li>
        <a href="<?= url('admin/activities.php') ?>" <?= $adminNav === 'activities' ? 'aria-current="page"' : '' ?>>
          <?= icon('calendar', 16) ?> ตารางกิจกรรม
        </a>
      </li>
      <li>
        <a href="<?= url('admin/teachers.php') ?>" <?= $adminNav === 'teachers' ? 'aria-current="page"' : '' ?>>
          <?= icon('user', 16) ?> อาจารย์
        </a>
      </li>
      <li>
        <a href="<?= url('admin/club.php') ?>" <?= $adminNav === 'club' ? 'aria-current="page"' : '' ?>>
          <?= icon('flag', 16) ?> สมาชิกชมรม
        </a>
      </li>
      <li>
        <a href="<?= url('admin/users.php') ?>" <?= $adminNav === 'users' ? 'aria-current="page"' : '' ?>>
          <?= icon('users', 16) ?> ผู้ใช้งาน
        </a>
      </li>
    </ul>
  </div>
</nav>

<div class="container-xl pt-4">
  <?php if ($flash): ?>
    <div class="alert-line <?= $flash['type'] === 'error' ? 'is-error' : '' ?> mb-4" role="alert">
      <?= icon($flash['type'] === 'error' ? 'alert' : 'check', 18) ?>
      <div><?= e($flash['msg']) ?></div>
    </div>
  <?php endif; ?>
