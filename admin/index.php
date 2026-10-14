<?php
/**
 * Admin Dashboard - ภาพรวมระบบ (Refined Editorial Brutalism)
 */
$adminNav = 'dashboard';
$pageTitle = 'แดชบอร์ดภาพรวม';

require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../includes/services/NewsService.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';
require_once __DIR__ . '/../includes/services/AuthService.php';

// Metrics
$cntNews       = (int) q("SELECT COUNT(*) FROM news")->fetchColumn();
$cntClasses    = (int) q("SELECT COUNT(*) FROM class_schedule")->fetchColumn();
$cntActivities = (int) q("SELECT COUNT(*) FROM activities")->fetchColumn();
$cntTeachers   = (int) q("SELECT COUNT(*) FROM teachers")->fetchColumn();
$cntClub       = (int) q("SELECT COUNT(*) FROM club_members")->fetchColumn();
$cntUsers      = (int) q("SELECT COUNT(*) FROM users")->fetchColumn();

// Recent news
$recentNews = q("
  SELECT n.*, c.name as category_name
  FROM news n
  JOIN news_categories c ON n.category_id = c.id
  ORDER BY n.id DESC
  LIMIT 5
")->fetchAll();

// Upcoming events
$upcomingActs = q("
  SELECT * FROM activities
  ORDER BY event_date ASC
  LIMIT 5
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">แผงควบคุมหลัก</p>
    <h1 style="font-size: 1.75rem; margin: 0;">ภาพรวมระบบบริหารจัดการ</h1>
  </div>
</div>

<!-- Stat Grid -->
<div class="stat-grid">
  <div class="stat-box">
    <span class="num"><?= $cntNews ?></span>
    <span class="label">ข่าวสารทั้งหมด</span>
  </div>
  <div class="stat-box">
    <span class="num"><?= $cntClasses ?></span>
    <span class="label">คาบเรียนในตาราง</span>
  </div>
  <div class="stat-box">
    <span class="num"><?= $cntActivities ?></span>
    <span class="label">กิจกรรมและปฏิทิน</span>
  </div>
  <div class="stat-box">
    <span class="num"><?= $cntTeachers ?> / <?= $cntClub ?></span>
    <span class="label">อาจารย์ / สมาชิกชมรม</span>
  </div>
</div>

<div class="row g-4">
  <!-- Left column: Quick Actions & News -->
  <div class="col-12 col-lg-7">
    <div class="admin-card">
      <div class="admin-card-head">
        <h3>เมนูจัดการหลัก</h3>
        <span class="mono text-muted" style="font-size: 13px;">ศูนย์ควบคุม 7 เมนู</span>
      </div>
      <div class="row g-2">
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/hero.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('image', 16) ?> ภาพประชาสัมพันธ์
          </a>
        </div>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/news.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('news', 16) ?> จัดการข่าวสาร
          </a>
        </div>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/classes.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('book', 16) ?> จัดการตารางเรียน
          </a>
        </div>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/activities.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('calendar', 16) ?> ตารางกิจกรรม
          </a>
        </div>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/teachers.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('user', 16) ?> จัดการอาจารย์
          </a>
        </div>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/club.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('flag', 16) ?> จัดการชมรม
          </a>
        </div>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
        <div class="col-6 col-md-4">
          <a href="<?= url('admin/users.php') ?>" class="btn btn-line w-100 justify-content-start text-start" style="font-size: .875rem;">
            <?= icon('users', 16) ?> ผู้ใช้งานระบบ
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-head d-flex justify-content-between align-items-center">
        <h3>ข่าวสารล่าสุดในระบบ</h3>
        <a href="<?= url('admin/news.php?action=new') ?>" class="btn btn-sm btn-navy">
          <?= icon('plus', 14) ?> เพิ่มข่าว
        </a>
      </div>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ชื่อข่าว</th>
              <th>หมวดหมู่</th>
              <th style="width: 130px;">วันที่</th>
              <th class="text-end" style="width: 70px;">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentNews as $newsItem): ?>
              <tr>
                <td>
                  <span class="fw-bold d-block text-truncate text-navy" style="max-width: 260px;">
                    <?= e($newsItem['title']) ?>
                  </span>
                </td>
                <td>
                  <span class="tag" style="font-size: 12px;"><?= e($newsItem['category_name']) ?></span>
                </td>
                <td class="mono" style="font-size: 13px;">
                  <?= thai_date($newsItem['published_at']) ?>
                </td>
                <td class="text-end">
                  <a href="<?= url('admin/news.php?action=edit&id=' . (int) $newsItem['id']) ?>"
                     class="btn btn-action-icon btn-line"
                     title="แก้ไข">
                    <?= icon('pencil', 16) ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right column: Upcoming Activities & System Specs -->
  <div class="col-12 col-lg-5">
    <div class="admin-card">
      <div class="admin-card-head d-flex justify-content-between align-items-center">
        <h3>กำหนดการกิจกรรม</h3>
        <a href="<?= url('admin/activities.php') ?>" class="link-more" style="font-size: .875rem;">
          <span>จัดการ</span> <?= icon('arrow-right', 12) ?>
        </a>
      </div>
      <ul class="list-unstyled mb-0">
        <?php if (empty($upcomingActs)): ?>
          <li class="py-3 text-muted text-center">ไม่มีกิจกรรม</li>
        <?php else: ?>
          <?php foreach ($upcomingActs as $actItem): ?>
            <li class="py-2 border-bottom d-flex justify-content-between align-items-start">
              <div>
                <span class="fw-bold d-block text-navy" style="font-size: .9375rem;"><?= e($actItem['title']) ?></span>
                <span class="mono text-muted" style="font-size: 12.5px;">
                  <?= thai_date($actItem['event_date']) ?>
                  <?= !empty($actItem['location']) ? ' · ' . e($actItem['location']) : '' ?>
                </span>
              </div>
              <?php $cd = activity_countdown_badge($actItem['event_date'], $actItem['event_date_end'] ?? null); ?>
              <span class="<?= e($cd['class']) ?>" style="font-size: 12px;"><?= e($cd['text']) ?></span>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
