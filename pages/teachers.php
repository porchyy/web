<?php
/**
 * TEACHERS & CLUB - คณาจารย์และชมรมวิชาชีพ
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/FacultyService.php';

$activeNav = 'teachers';
$pageTitle = 'อาจารย์และชมรม';

send_cache_headers(60, 120);

// Fetch Faculty (Head first, then sorted by order)
$teachers = FacultyService::getAllTeachers();

// Fetch Student Club Members (Club Head first, then sorted by order)
$clubMembers = FacultyService::getAllClubMembers();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>บุคลากรและชมรมวิชาชีพ</span>
    </p>
    <h1>อาจารย์และชมรมวิชาชีพ</h1>
    <p class="lead-text">
      ทำเนียบคณาจารย์ผู้ทรงคุณวุฒิประจำแผนกวิชา และคณะกรรมการชมรมวิชาชีพเทคโนโลยีธุรกิจดิจิทัล
    </p>
  </div>
</div>

<!-- ========================================================================
     FACULTY DIRECTORY / คณาจารย์ประจำแผนก
     ======================================================================== -->
<section class="section">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow eyebrow-muted">คณาจารย์ผู้สอน</p>
        <h2>คณาจารย์ประจำแผนกวิชา</h2>
      </div>
      <span class="mono text-muted" style="font-size: 14px;"><?= count($teachers) ?> ท่าน</span>
    </div>

    <!-- Mobile list / Desktop grid -->
    <ul class="people-list people-grid">
      <?php foreach ($teachers as $teacher): ?>
        <li>
          <?= render_avatar($teacher['name'], $teacher['image_path'] ?? null) ?>

          <div>
            <?php if (!empty($teacher['is_head'])): ?>
              <span class="sub d-block text-accent-ink fw-semibold mb-1">★ หัวหน้าแผนกวิชา</span>
            <?php endif; ?>
            <h3><?= e($teacher['name']) ?></h3>
            <p><?= e($teacher['position']) ?></p>
            <?php if (!empty($teacher['specialization'])): ?>
              <p class="text-muted" style="font-size: .875rem; margin-top: 2px;">
                <?= e($teacher['specialization']) ?>
              </p>
            <?php endif; ?>
            <?php if (!empty($teacher['email'])): ?>
              <div class="mono mt-2" style="font-size: 13.5px;">
                <a href="mailto:<?= e($teacher['email']) ?>" class="text-navy"><?= e($teacher['email']) ?></a>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ========================================================================
     STUDENT CLUB DIRECTORY / สมาชิกชมรมวิชาชีพ
     ======================================================================== -->
<section class="section section-alt">
  <div class="container-xl">
    <div class="section-head">
      <div>
        <p class="eyebrow">ชมรมวิชาชีพ</p>
        <h2>คณะกรรมการชมรมวิชาชีพ</h2>
      </div>
      <span class="mono text-muted" style="font-size: 14px;"><?= count($clubMembers) ?> คน</span>
    </div>

    <ul class="people-list people-grid">
      <?php foreach ($clubMembers as $member): ?>
        <li>
          <?= render_avatar($member['name'], $member['image_path'] ?? null) ?>

          <div>
            <?php if (!empty($member['is_club_head'])): ?>
              <span class="sub d-block text-accent-ink fw-semibold mb-1">★ ประธานชมรมวิชาชีพ</span>
            <?php endif; ?>
            <h3><?= e($member['name']) ?></h3>
            <p><?= e($member['club_role']) ?></p>
            <?php if (!empty($member['student_id'])): ?>
              <div class="mono text-muted mt-1" style="font-size: 13.5px;">
                รหัสนักศึกษา: <?= e($member['student_id']) ?>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
