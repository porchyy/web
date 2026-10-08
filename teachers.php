<?php
/**
 * 05 / TEACHERS & CLUB — คณาจารย์และชมรมวิชาชีพ
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$activeNav = 'teachers';
$pageTitle = 'อาจารย์และชมรม';

// Fetch Faculty (Head first, then sorted by order)
$teachers = q("
  SELECT * FROM teachers
  ORDER BY is_head DESC, sort_order ASC, id ASC
")->fetchAll();

// Fetch Student Club Members (Club Head first, then sorted by order)
$clubMembers = q("
  SELECT * FROM club_members
  ORDER BY is_club_head DESC, sort_order ASC, id ASC
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>05</span>
      <span class="sep">/</span>
      <span>FACULTY & STUDENT CLUB</span>
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
        <p class="eyebrow eyebrow-muted">FACULTY DIRECTORY</p>
        <h2>คณาจารย์ประจำแผนกวิชา</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;"><?= count($teachers) ?> FACULTY MEMBERS</span>
    </div>

    <!-- Mobile list / Desktop grid -->
    <ul class="people-list people-grid">
      <?php foreach ($teachers as $t): ?>
        <li>
          <div class="avatar figure-img marks">
            <?php if (!empty($t['image_path']) && file_exists(ROOT_PATH . '/' . $t['image_path'])): ?>
              <img src="<?= url($t['image_path']) ?>" alt="<?= e($t['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="monogram"><?= initials($t['name']) ?></div>
            <?php endif; ?>
          </div>

          <div>
            <?php if ($t['is_head']): ?>
              <span class="sub d-block">★ HEAD OF DEPARTMENT</span>
            <?php endif; ?>
            <h3><?= e($t['name']) ?></h3>
            <p><?= e($t['position']) ?></p>
            <?php if (!empty($t['specialization'])): ?>
              <p class="text-muted" style="font-size: .8125rem; margin-top: 2px;">
                <?= e($t['specialization']) ?>
              </p>
            <?php endif; ?>
            <?php if (!empty($t['email'])): ?>
              <div class="mono mt-2" style="font-size: 11px;">
                <a href="mailto:<?= e($t['email']) ?>" class="text-navy"><?= e($t['email']) ?></a>
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
        <p class="eyebrow">STUDENT CLUB DIRECTORY</p>
        <h2>คณะกรรมการชมรมวิชาชีพ</h2>
      </div>
      <span class="mono text-muted" style="font-size: 11px;"><?= count($clubMembers) ?> CLUB OFFICERS</span>
    </div>

    <ul class="people-list people-grid">
      <?php foreach ($clubMembers as $m): ?>
        <li>
          <div class="avatar figure-img marks">
            <?php if (!empty($m['image_path']) && file_exists(ROOT_PATH . '/' . $m['image_path'])): ?>
              <img src="<?= url($m['image_path']) ?>" alt="<?= e($m['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="monogram"><?= initials($m['name']) ?></div>
            <?php endif; ?>
          </div>

          <div>
            <?php if ($m['is_club_head']): ?>
              <span class="sub d-block">★ CLUB PRESIDENT</span>
            <?php endif; ?>
            <h3><?= e($m['name']) ?></h3>
            <p><?= e($m['club_role']) ?></p>
            <?php if (!empty($m['student_id'])): ?>
              <div class="mono text-muted mt-1" style="font-size: 11px;">
                ID: <?= e($m['student_id']) ?>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
