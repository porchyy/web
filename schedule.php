<?php
/**
 * 04 / SCHEDULE — ตารางเรียนและตารางกิจกรรม
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$activeNav = 'schedule';
$pageTitle = 'ตารางเรียนและกิจกรรม';

$tab = filter_input(INPUT_GET, 'tab', FILTER_DEFAULT) ?: 'class';
if (!in_array($tab, ['class', 'activity'], true)) {
    $tab = 'class';
}

// Current day of week (1=Mon ... 7=Sun)
$currentDayNum = (int) date('N');
$defaultDay = ($currentDayNum >= 1 && $currentDayNum <= 5) ? $currentDayNum : 1;

// Fetch Class Schedule (Mon-Fri)
$classSchedule = q("
  SELECT * FROM class_schedule
  ORDER BY day_of_week ASC, time_start ASC
")->fetchAll();

// Group schedule by day
$scheduleByDay = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
foreach ($classSchedule as $slot) {
    $day = (int) $slot['day_of_week'];
    if (isset($scheduleByDay[$day])) {
        $scheduleByDay[$day][] = $slot;
    }
}

// Fetch Activities
$activities = q("
  SELECT * FROM activities
  ORDER BY event_date ASC, time_start ASC
")->fetchAll();

// Group activities by Year-Month
$activitiesByMonth = [];
foreach ($activities as $act) {
    $ym = date('Y-m', strtotime($act['event_date']));
    $activitiesByMonth[$ym][] = $act;
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>04</span>
      <span class="sep">/</span>
      <span>TIMETABLE & CALENDAR</span>
    </p>
    <h1>ตารางเรียนและตารางกิจกรรม</h1>
    <p class="lead-text">
      ตรวจสอบเวลาเรียนรายวิชาตามหลักสูตร และติดตามกำหนดการกิจกรรม เสวนา อบรมเชิงปฏิบัติการประจำภาคเรียน
    </p>

    <!-- Segmented Control Switcher -->
    <div class="segmented mt-4" role="tablist" aria-label="สลับประเภทตาราง">
      <a href="<?= url('schedule.php?tab=class') ?>" <?= $tab === 'class' ? 'aria-current="page"' : '' ?>>
        <?= icon('book', 16) ?>
        <span>ตารางเรียนรายสัปดาห์</span>
      </a>
      <a href="<?= url('schedule.php?tab=activity') ?>" <?= $tab === 'activity' ? 'aria-current="page"' : '' ?>>
        <?= icon('calendar', 16) ?>
        <span>ตารางกิจกรรมแผนก</span>
      </a>
    </div>
  </div>
</div>

<section class="section">
  <div class="container-xl">

    <?php if ($tab === 'class'): ?>
      <!-- ==================================================================
           CLASS TIMETABLE VIEW
           ================================================================== -->
      
      <!-- Mobile View (360-430px): Day Selector + Vertical Timeline Slots -->
      <div class="d-md-none">
        <div class="day-tabs" role="tablist" aria-label="เลือกวันสำหรับตารางเรียน">
          <?php for ($d = 1; $d <= 5; $d++): ?>
            <button type="button"
                    class="day-tab"
                    role="tab"
                    data-day="<?= $d ?>"
                    aria-selected="<?= $d === $defaultDay ? 'true' : 'false' ?>">
              <span><?= TH_DAYS_SHORT[$d] ?></span>
              <span class="d"><?= TH_DAYS[$d] ?></span>
              <?php if ($d === $currentDayNum): ?>
                <span class="today-dot" title="วันนี้"></span>
              <?php endif; ?>
            </button>
          <?php endfor; ?>
        </div>

        <?php for ($d = 1; $d <= 5; $d++): ?>
          <div class="slot-group" data-day="<?= $d ?>" <?= $d !== $defaultDay ? 'hidden' : '' ?>>
            <div class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <span class="fw-bold text-navy">วัน<?= TH_DAYS[$d] ?></span>
              <span class="mono text-muted" style="font-size: 11px;"><?= count($scheduleByDay[$d]) ?> รายวิชา</span>
            </div>

            <?php if (empty($scheduleByDay[$d])): ?>
              <div class="slot-empty text-center py-4 text-muted">
                ไม่มีชั่วโมงเรียนในวันนี้
              </div>
            <?php else: ?>
              <ul class="slot-list">
                <?php foreach ($scheduleByDay[$d] as $slot): ?>
                  <li class="slot">
                    <div class="slot-time">
                      <span class="start"><?= hm($slot['time_start']) ?></span>
                      <span class="end"><?= hm($slot['time_end']) ?></span>
                    </div>
                    <div>
                      <span class="code"><?= e($slot['subject_code']) ?> · <?= e($slot['room']) ?></span>
                      <h3><?= e($slot['subject_name']) ?></h3>
                      <div class="text-muted" style="font-size: .875rem;">
                        <?= icon('user', 14) ?> <?= e($slot['teacher_name']) ?>
                        <span class="ms-2 mono" style="font-size: 11px;">(<?= e($slot['class_group']) ?>)</span>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Desktop View (>=768px): Full Weekly Grid Table -->
      <div class="d-none d-md-block overflow-x-auto">
        <table class="week-table">
          <thead>
            <tr>
              <?php for ($d = 1; $d <= 5; $d++): ?>
                <th class="<?= $d === $currentDayNum ? 'is-today' : '' ?>">
                  <span class="mono">DAY 0<?= $d ?></span>
                  <span>วัน<?= TH_DAYS[$d] ?></span>
                  <?php if ($d === $currentDayNum): ?>
                    <span class="badge-soon">TODAY</span>
                  <?php endif; ?>
                </th>
              <?php endfor; ?>
            </tr>
          </thead>
          <tbody>
            <tr>
              <?php for ($d = 1; $d <= 5; $d++): ?>
                <td>
                  <?php if (empty($scheduleByDay[$d])): ?>
                    <div class="text-muted text-center py-4" style="font-size: 12px;">- ไม่มีคาบเรียน -</div>
                  <?php else: ?>
                    <?php foreach ($scheduleByDay[$d] as $slot): ?>
                      <div class="cell-slot">
                        <span class="t"><?= hm($slot['time_start']) ?> – <?= hm($slot['time_end']) ?></span>
                        <div class="s"><?= e($slot['subject_name']) ?></div>
                        <div class="r">
                          <span class="mono text-navy font-weight-bold"><?= e($slot['subject_code']) ?></span>
                          <span class="mx-1">·</span>
                          <span><?= e($slot['room']) ?></span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 11px;">
                          <?= e($slot['teacher_name']) ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </td>
              <?php endfor; ?>
            </tr>
          </tbody>
        </table>
      </div>

    <?php else: ?>
      <!-- ==================================================================
           ACTIVITY CALENDAR VIEW
           ================================================================== -->
      <div class="max-w-720">
        <?php if (empty($activitiesByMonth)): ?>
          <div class="text-center py-5 text-muted">
            <?= icon('calendar', 40) ?>
            <p class="mt-3">ยังไม่มีกำหนดการกิจกรรมในขณะนี้</p>
          </div>
        <?php else: ?>
          <?php foreach ($activitiesByMonth as $ym => $items): ?>
            <?php
            $timeObj = strtotime($ym . '-01');
            $monthNum = (int) date('n', $timeObj);
            $yearThai = (int) date('Y', $timeObj) + 543;
            ?>
            <div class="month-group">
              <div class="month-head">
                <h2><?= TH_MONTHS[$monthNum] ?> <?= $yearThai ?></h2>
                <span class="mono text-muted" style="font-size: 12px;"><?= count($items) ?> กิจกรรม</span>
              </div>

              <ul class="event-list">
                <?php foreach ($items as $ev): ?>
                  <?php
                  $tEv = strtotime($ev['event_date']);
                  $dayVal = date('j', $tEv);
                  $dayWeek = TH_DAYS[(int) date('N', $tEv)];
                  $isUpcoming = strtotime($ev['event_date']) >= strtotime('today');
                  ?>
                  <li class="event">
                    <div class="event-date">
                      <span class="day"><?= sprintf('%02d', $dayVal) ?></span>
                      <span class="wd">วัน<?= $dayWeek ?></span>
                    </div>
                    <div>
                      <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        <h3><?= e($ev['title']) ?></h3>
                        <?php if (!empty($ev['badge_text'])): ?>
                          <span class="badge-soon"><?= e($ev['badge_text']) ?></span>
                        <?php elseif ($isUpcoming): ?>
                          <span class="badge-soon">UPCOMING</span>
                        <?php endif; ?>
                      </div>

                      <?php if (!empty($ev['description'])): ?>
                        <p><?= nl2br(e($ev['description'])) ?></p>
                      <?php endif; ?>

                      <div class="meta-line">
                        <?php if (!empty($ev['time_start'])): ?>
                          <span class="mono">
                            <?= icon('clock', 13) ?>
                            <?= hm($ev['time_start']) ?><?= !empty($ev['time_end']) ? ' – ' . hm($ev['time_end']) : '' ?> น.
                          </span>
                        <?php endif; ?>
                        <?php if (!empty($ev['location'])): ?>
                          <span class="dot"></span>
                          <span>
                            <?= icon('map-pin', 13) ?> <?= e($ev['location']) ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
