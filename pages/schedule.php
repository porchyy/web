<?php
/**
 * SCHEDULE - ตารางเรียนและตารางกิจกรรม
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';

$activeNav = 'schedule';
$pageTitle = 'ตารางเรียนและกิจกรรม';

send_cache_headers(60, 120);

$tab = filter_input(INPUT_GET, 'tab', FILTER_DEFAULT) ?: 'class';
if (!in_array($tab, ['class', 'activity'], true)) {
    $tab = 'class';
}

// Current day & time for live highlighting
$currentDayNum = (int) date('N'); // 1=Mon .. 7=Sun
$currentTime   = date('H:i:s');
$defaultDay    = ($currentDayNum >= 1 && $currentDayNum <= 5) ? $currentDayNum : 1;

// Available class groups
$availableGroups = ScheduleService::getAvailableGroups();
if (empty($availableGroups)) {
    $availableGroups = ['ปวช.2/1'];
}
$queryGroup = trim((string) (filter_input(INPUT_GET, 'group', FILTER_DEFAULT) ?? ''));
$selectedGroup = in_array($queryGroup, $availableGroups, true) ? $queryGroup : $availableGroups[0];

// Fetch Schedule Grid & Periods (สอศ. Standard)
$gridData      = ScheduleService::getTimetableGrid($selectedGroup);
$periods       = $gridData['periods'];
$grid          = $gridData['grid'];
$scheduleByDay = ScheduleService::getScheduleByDay($selectedGroup);

// Determine current class slot ID and next upcoming class slot ID
$currentSlotId = null;
$nextSlotId    = null;

if ($currentDayNum >= 1 && $currentDayNum <= 5) {
    // Weekday: Look for current class and next upcoming class today
    $upcomingToday = [];
    foreach ($scheduleByDay[$currentDayNum] ?? [] as $slot) {
        $sStart = $slot['time_start'];
        $sEnd   = $slot['time_end'];
        $code   = strtoupper(trim($slot['subject_code']));
        $name   = trim($slot['subject_name']);
        $isSpecial = ($code === 'FLAG' || $code === 'LUNCH' || str_contains($name, 'เสาธง') || str_contains($name, 'พัก'));

        if ($currentTime >= $sStart && $currentTime <= $sEnd) {
            $currentSlotId = (int) $slot['id'];
        } elseif ($currentTime < $sStart && !$isSpecial) {
            $upcomingToday[] = $slot;
        }
    }
    if (!empty($upcomingToday)) {
        usort($upcomingToday, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
        $nextSlotId = (int) $upcomingToday[0]['id'];
    } else {
        // If classes for today ended, find the first class of the next school day
        for ($nextD = $currentDayNum + 1; $nextD <= 5; $nextD++) {
            $nextDaySlots = array_filter($scheduleByDay[$nextD] ?? [], function($s) {
                $code = strtoupper(trim($s['subject_code']));
                $name = trim($s['subject_name']);
                return !($code === 'FLAG' || $code === 'LUNCH' || str_contains($name, 'เสาธง') || str_contains($name, 'พัก'));
            });
            if (!empty($nextDaySlots)) {
                usort($nextDaySlots, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
                $firstNext = reset($nextDaySlots);
                $nextSlotId = (int) $firstNext['id'];
                break;
            }
        }
    }
} else {
    // Weekend: Next class is the first normal class on Monday
    $mondaySlots = array_filter($scheduleByDay[1] ?? [], function($s) {
        $code = strtoupper(trim($s['subject_code']));
        $name = trim($s['subject_name']);
        return !($code === 'FLAG' || $code === 'LUNCH' || str_contains($name, 'เสาธง') || str_contains($name, 'พัก'));
    });
    if (!empty($mondaySlots)) {
        usort($mondaySlots, fn($a, $b) => strcmp($a['time_start'], $b['time_start']));
        $firstMon = reset($mondaySlots);
        $nextSlotId = (int) $firstMon['id'];
    }
}

// Fetch Activities
$activitiesByMonth = ScheduleService::getActivitiesGroupedByMonth();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>ตารางเรียนและกิจกรรม</span>
    </p>
    <h1>ตารางเรียนและตารางกิจกรรม</h1>
    <p class="lead-text">
      ตรวจสอบเวลาเรียนรายวิชาตามมาตรฐานหลักสูตร สอศ. และติดตามกำหนดการกิจกรรม เสวนา อบรมเชิงปฏิบัติการประจำภาคเรียน
    </p>

    <!-- Segmented Control Switcher -->
    <div class="segmented mt-4" role="tablist" aria-label="สลับประเภทตาราง">
      <a href="<?= url('schedule?tab=class' . ($selectedGroup ? '&group=' . urlencode($selectedGroup) : '')) ?>" <?= $tab === 'class' ? 'aria-current="page"' : '' ?>>
        <?= icon('book', 16) ?>
        <span>ตารางเรียนรายสัปดาห์</span>
      </a>
      <a href="<?= url('schedule?tab=activity') ?>" <?= $tab === 'activity' ? 'aria-current="page"' : '' ?>>
        <?= icon('calendar', 16) ?>
        <span>ตารางกิจกรรมแผนก</span>
      </a>
    </div>
  </div>
</div>

<section class="section">
  <div class="<?= $tab === 'class' ? 'container-fluid px-3 px-xl-4 px-xxl-5' : 'container-xl' ?>">

    <?php if ($tab === 'class'): ?>
      <!-- ==================================================================
           CLASS TIMETABLE VIEW (มาตรฐาน สอศ.)
           ================================================================== -->
      
      <!-- Group Filter & Print Bar -->
      <div class="group-select-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-white border">
        <form method="get" action="<?= url('schedule') ?>" class="d-flex align-items-center gap-2 m-0 flex-wrap" id="form-schedule-group">
          <input type="hidden" name="tab" value="class">
          <label for="select-schedule-group" class="fw-semibold text-navy mb-0" style="font-size: .875rem;">
            <?= icon('users', 16) ?> ระดับชั้น / กลุ่มเรียน:
          </label>
          <select id="select-schedule-group" name="group" class="form-select form-select-sm" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
            <?php foreach ($availableGroups as $grp): ?>
              <option value="<?= e($grp) ?>" <?= $selectedGroup === $grp ? 'selected' : '' ?>>
                <?= e($grp) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>

        <button type="button" class="btn btn-sm btn-line btn-print" onclick="window.print()">
          <?= icon('book', 14) ?> พิมพ์ / บันทึกตารางเรียน
        </button>
      </div>

      <!-- Print-Only Header Metadata -->
      <div class="print-metadata-header d-none">
        <span>แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล — ตารางเรียนระดับชั้น: <?= e($selectedGroup) ?></span>
        <span>พิมพ์เมื่อ: <?= date('d/m/Y H:i') ?> น.</span>
      </div>

      <!-- Mobile View (360-430px): Day Selector + Vertical Timeline Slots -->
      <div class="d-md-none">
        <div class="day-tabs mb-3" role="tablist" aria-label="เลือกวันสำหรับตารางเรียน">
          <?php for ($d = 1; $d <= 5; $d++): ?>
            <button type="button"
                    class="day-tab <?= $d === $defaultDay ? 'is-active' : '' ?> <?= $d === $currentDayNum ? 'is-today' : '' ?>"
                    role="tab"
                    data-day="<?= $d ?>"
                    aria-selected="<?= $d === $defaultDay ? 'true' : 'false' ?>">
              <span><?= TH_DAYS_SHORT[$d] ?></span>
              <span class="d"><?= TH_DAYS[$d] ?></span>
            </button>
          <?php endfor; ?>
        </div>

        <?php for ($d = 1; $d <= 5; $d++): ?>
          <div class="slot-group <?= $d === $currentDayNum ? 'is-today' : '' ?>" data-day="<?= $d ?>" <?= $d !== $defaultDay ? 'hidden' : '' ?>>
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 <?= $d === $currentDayNum ? 'bg-today-highlight' : '' ?>" style="border-radius: var(--radius);">
              <h3 class="mb-0" style="font-size: 1.125rem;">ตารางเรียนวัน<?= TH_DAYS[$d] ?></h3>
            </div>

            <?php if (empty($scheduleByDay[$d])): ?>
              <div class="p-4 bg-white border text-center text-muted" style="border-radius: var(--radius);">
                ไม่มีคาบเรียนในวัน<?= TH_DAYS[$d] ?>
              </div>
            <?php else: ?>
              <div class="d-flex flex-column gap-3">
                <?php foreach ($scheduleByDay[$d] as $slot): ?>
                  <?php
                  $slotId = (int) $slot['id'];
                  $isCurrent = ($d === $currentDayNum && $slotId === $currentSlotId);
                  $isNext    = ($d === $currentDayNum && $slotId === $nextSlotId);
                  $cardClass = $isCurrent ? 'is-current-class' : ($isNext ? 'is-next-class' : '');
                  ?>
                  <div class="slot-card-mobile p-3 bg-white border <?= $cardClass ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="mono fw-bold slot-code"><?= e($slot['subject_code']) ?></span>
                      <span class="mono text-muted slot-time"><?= hm($slot['time_start']) ?> – <?= hm($slot['time_end']) ?></span>
                    </div>
                    <div class="fw-bold mb-2 slot-name"><?= e($slot['subject_name']) ?></div>
                    <div class="d-flex justify-content-between align-items-center text-muted slot-meta">
                      <span><?= icon('user', 12) ?> <?= e($slot['teacher_name'] ?: '-') ?></span>
                      <span class="mono"><?= icon('map-pin', 12) ?> <?= e($slot['room'] ?: '-') ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Desktop View (>=768px): Official สอศ. Table (Days as rows, Periods as columns, colspan deduplication) -->
      <div class="d-none d-md-block timetable-wrap-user">
        <table class="week-table">
          <thead>
            <tr>
              <th class="col-day">
                <div class="period-head-inner">
                  <span>วัน / คาบ</span>
                </div>
              </th>
              <?php foreach ($periods as $p): ?>
                <?php
                $isCurrentP = ($currentTime >= $p['start'] . ':00' && $currentTime <= $p['end'] . ':00' && $currentDayNum >= 1 && $currentDayNum <= 5);
                ?>
                <th class="<?= $isCurrentP ? 'is-current-period' : '' ?>">
                  <div class="period-head-inner">
                    <div class="period-name"><?= e($p['name'] ?: 'คาบ ' . $p['num']) ?></div>
                    <div class="period-time mono"><?= e($p['label']) ?></div>
                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php for ($d = 1; $d <= 5; $d++): ?>
              <?php $isDayToday = ($d === $currentDayNum); ?>
              <tr class="<?= $isDayToday ? 'is-today' : '' ?>">
                <td class="col-day">
                  <div class="day-label-inner">
                    <span>วัน<?= TH_DAYS[$d] ?></span>
                  </div>
                </td>
                <?php foreach ($grid[$d] as $cell): ?>
                  <?php if ($cell['type'] === 'flag'): ?>
                    <td colspan="<?= $cell['colspan'] ?>" class="slot-flag">
                      <div class="special-slot-inner">
                        <div class="special-icon"><?= icon('flag', 14) ?></div>
                        <div class="special-title"><?= e($cell['title']) ?></div>
                        <div class="special-time mono"><?= hm($cell['slot']['time_start']) ?>–<?= hm($cell['slot']['time_end']) ?></div>
                      </div>
                    </td>
                  <?php elseif ($cell['type'] === 'lunch'): ?>
                    <td colspan="<?= $cell['colspan'] ?>" class="slot-lunch">
                      <div class="special-slot-inner">
                        <div class="special-icon"><?= icon('clock', 14) ?></div>
                        <div class="special-title"><?= e($cell['title']) ?></div>
                        <div class="special-time mono"><?= hm($cell['slot']['time_start']) ?>–<?= hm($cell['slot']['time_end']) ?></div>
                      </div>
                    </td>
                  <?php elseif ($cell['type'] === 'class'): ?>
                    <?php
                    $s = $cell['slot'];
                    $slotId = (int) $s['id'];
                    $isCurrent = ($isDayToday && $slotId === $currentSlotId);
                    $isNext    = ($isDayToday && $slotId === $nextSlotId);
                    $highlightClass = $isCurrent ? 'is-current-class' : ($isNext ? 'is-next-class' : '');
                    ?>
                    <td colspan="<?= $cell['colspan'] ?>" class="slot-class <?= $highlightClass ?>">
                      <div class="cell-slot">
                        <div class="slot-head">
                          <span class="mono fw-bold slot-code"><?= e($s['subject_code']) ?></span>
                          <span class="mono slot-time"><?= hm($s['time_start']) ?>–<?= hm($s['time_end']) ?></span>
                        </div>
                        <div class="s" title="<?= e($s['subject_name']) ?>">
                          <?= e($s['subject_name']) ?>
                        </div>
                        <div class="r">
                          <span class="teacher text-truncate" title="<?= e($s['teacher_name'] ?: '-') ?>"><?= icon('user', 11) ?> <?= e($s['teacher_name'] ?: '-') ?></span>
                          <span class="mono room fw-semibold"><?= icon('map-pin', 11) ?> <?= e($s['room'] ?: '-') ?></span>
                        </div>
                      </div>
                    </td>
                  <?php else: ?>
                    <td colspan="1" class="slot-empty">
                      <div class="empty-slot-inner">
                        <span class="empty-dash">-</span>
                      </div>
                    </td>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tr>
            <?php endfor; ?>
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
            $timeObj  = strtotime($ym . '-01');
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
                        <?php $cd = activity_countdown_badge($ev['event_date'], $ev['event_date_end'] ?? null); ?>
                        <span class="<?= e($cd['class']) ?>"><?= e($cd['text']) ?></span>
                        <?php if (!empty($ev['badge_text'])): ?>
                          <span class="badge-line"><?= e($ev['badge_text']) ?></span>
                        <?php endif; ?>
                      </div>

                      <?php if (!empty($ev['description'])): ?>
                        <p><?= nl2br(e($ev['description'])) ?></p>
                      <?php endif; ?>

                      <div class="meta-line">
                        <?php if (!empty($ev['event_date_end'])): ?>
                          <span class="mono">
                            <?= icon('calendar', 13) ?>
                            <?= thai_date($ev['event_date']) ?> – <?= thai_date($ev['event_date_end']) ?>
                          </span>
                          <span class="dot"></span>
                        <?php endif; ?>
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

                      <?php if (!empty($ev['action_url'])): ?>
                        <div class="mt-2">
                          <a href="<?= e($ev['action_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-line">
                            <?= icon('external', 13) ?> ข้อมูลเพิ่มเติม / ลงทะเบียน
                          </a>
                        </div>
                      <?php endif; ?>
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

<!-- Script for Mobile Tab Switching -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.day-tab');
  const slotGroups = document.querySelectorAll('.slot-group');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const day = tab.getAttribute('data-day');
      tabs.forEach(t => {
        t.classList.remove('is-active');
        t.setAttribute('aria-selected', 'false');
      });
      tab.classList.add('is-active');
      tab.setAttribute('aria-selected', 'true');

      slotGroups.forEach(grp => {
        if (grp.getAttribute('data-day') === day) {
          grp.removeAttribute('hidden');
        } else {
          grp.setAttribute('hidden', '');
        }
      });
    });
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
