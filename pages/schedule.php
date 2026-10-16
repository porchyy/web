<?php
/**
 * SCHEDULE - ตารางเรียนและตารางกิจกรรม
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/ScheduleService.php';

$activeNav = 'schedule';

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
$cleanGroup = str_replace(['/', '\\', ' '], '-', $selectedGroup);

$exportFileName = ($tab === 'class') ? "ตารางเรียน_กลุ่ม_{$cleanGroup}" : "ตารางกิจกรรมแผนก";
$pageTitle = ($tab === 'class') ? "ตารางเรียน กลุ่ม {$selectedGroup}" : 'ตารางกิจกรรมแผนก';

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

<div class="page-head" style="padding-bottom: var(--s-3);">
  <div class="container-xl">
    <p class="eyebrow">
      <span>ตารางเรียนและกิจกรรม</span>
    </p>
    <h1>ตารางเรียนและตารางกิจกรรม</h1>
    <p class="lead-text mb-0">
      ตรวจสอบเวลาเรียนรายวิชาตามมาตรฐานหลักสูตร สอศ. และติดตามกำหนดการกิจกรรม เสวนา อบรมเชิงปฏิบัติการประจำภาคเรียน
    </p>
  </div>
</div>

<section class="section" id="schedule-root" style="padding-top: var(--s-3);" data-export-file-name="<?= e($exportFileName) ?>" data-selected-group="<?= e($selectedGroup) ?>" data-is-class-tab="<?= $tab === 'class' ? '1' : '0' ?>">
  <div class="<?= $tab === 'class' ? 'container-fluid px-3 px-xl-4 px-xxl-5' : 'container-xl' ?>">

    <!-- Schedule Control Toolbar (Responsive Full-Width Card) -->
    <div class="schedule-control-card">
      <!-- Upper Section: Group Dropdown & Tabs Switcher (Horizontal on Desktop) -->
      <div class="scc-header-row">
        <div class="scc-group-row">
          <form method="get" action="<?= url('schedule') ?>" class="d-flex align-items-center gap-2 m-0 flex-wrap" id="form-schedule-group">
            <input type="hidden" name="tab" value="class">
            <label for="select-schedule-group" class="scc-label">
              <?= icon('users', 16) ?> ระดับชั้น / กลุ่มเรียน
            </label>
            <div class="scc-select-wrap">
              <select id="select-schedule-group" name="group" class="scc-select" onchange="this.form.submit()">
                <?php foreach ($availableGroups as $grp): ?>
                  <option value="<?= e($grp) ?>" <?= $selectedGroup === $grp ? 'selected' : '' ?>>
                    <?= e($grp) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>

        <div class="scc-tabs-row" role="tablist" aria-label="สลับประเภทตาราง">
          <a href="<?= url('schedule?tab=class' . ($selectedGroup ? '&group=' . urlencode($selectedGroup) : '')) ?>" class="scc-tab <?= $tab === 'class' ? 'is-active' : '' ?>" <?= $tab === 'class' ? 'aria-current="page"' : '' ?>>
            <?= icon('book', 18) ?>
            <span>ตารางเรียนรายสัปดาห์</span>
          </a>
          <a href="<?= url('schedule?tab=activity') ?>" class="scc-tab <?= $tab === 'activity' ? 'is-active' : '' ?>" <?= $tab === 'activity' ? 'aria-current="page"' : '' ?>>
            <?= icon('calendar', 18) ?>
            <span>ตารางกิจกรรมแผนก</span>
          </a>
        </div>
      </div>

      <!-- Lower Section: View Label & Action Buttons (Side-by-side on Desktop) -->
      <div class="scc-bottom-row">
        <div class="scc-action-buttons ms-auto"
             data-tab="<?= e($tab) ?>"
             data-filename="<?= e($exportFileName) ?>"
             data-group="<?= e($selectedGroup) ?>">
          <button type="button" class="scc-btn scc-btn-print" id="btn-export-print" onclick="exportSchedulePrint()" title="พิมพ์ตาราง">
            <?= icon('printer', 16) ?>
            <span>พิมพ์ตารางเรียน</span>
          </button>
          <button type="button" class="scc-btn scc-btn-pdf" id="btn-export-pdf" onclick="exportSchedulePDF()" title="บันทึกเป็น PDF พร้อมตั้งชื่อไฟล์">
            <?= icon('file-pdf', 16) ?>
            <span>บันทึกเป็น PDF</span>
          </button>
          <button type="button" class="scc-btn scc-btn-png" id="btn-export-png" onclick="exportSchedulePNG()" title="บันทึกเป็นรูปภาพ PNG">
            <?= icon('image', 16) ?>
            <span>บันทึกเป็นรูปภาพ (PNG)</span>
          </button>
        </div>
      </div>
    </div>

    <?php if ($tab === 'class'): ?>
      <!-- ==================================================================
           CLASS TIMETABLE VIEW (มาตรฐาน สอศ.)
           ================================================================== -->

      <!-- Print-Only Header Metadata -->
      <div class="print-metadata-header d-none">
        <span>สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล — ตารางเรียนระดับชั้น: <?= e($selectedGroup) ?></span>
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
                  <div class="slot-card-mobile p-3 bg-white border js-slot-card <?= $cardClass ?>"
                       role="button"
                       tabindex="0"
                       data-code="<?= e($slot['subject_code']) ?>"
                       data-name="<?= e($slot['subject_name']) ?>"
                       data-type="<?= e($slot['course_type'] ?? 'regular') ?>"
                       data-credit="<?= (int) ($slot['credit'] ?? 3) ?>"
                       data-teacher="<?= e($slot['teacher_name'] ?: '-') ?>"
                       data-room="<?= e($slot['room'] ?: '-') ?>"
                       data-time="<?= hm($slot['time_start']) ?> – <?= hm($slot['time_end']) ?>"
                       data-day="วัน<?= TH_DAYS[$d] ?>"
                       data-group="<?= e($selectedGroup) ?>"
                       title="คลิกเพื่อดูรายละเอียดรายวิชา">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <div class="d-flex align-items-center gap-1">
                        <span class="mono fw-bold slot-code"><?= e($slot['subject_code']) ?></span>
                        <span class="badge badge-sm <?= ($slot['course_type'] ?? 'regular') === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="font-size: 10px; padding: 2px 5px;">
                          <?= ($slot['course_type'] ?? 'regular') === 'professional' ? 'วิชาชีพ' : 'วิชาสามัญ' ?>
                        </span>
                      </div>
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
                <th>
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
                    <?php if ($isDayToday): ?>
                      <span class="badge-today-pill">วันนี้</span>
                    <?php endif; ?>
                  </div>
                </td>
                <?php foreach ($grid[$d] as $cell): ?>
                  <?php if ($cell['type'] === 'flag'): ?>
                    <td colspan="<?= $cell['colspan'] ?>" class="slot-flag">
                      <div class="special-slot-inner">
                        <div class="special-icon"><?= icon('flag', 14) ?></div>
                        <div class="special-title"><?= e($cell['title']) ?></div>
                        </div>
                    </td>
                  <?php elseif ($cell['type'] === 'lunch'): ?>
                    <td colspan="<?= $cell['colspan'] ?>" class="slot-lunch">
                      <div class="special-slot-inner">
                        <div class="special-icon"><?= icon('clock', 14) ?></div>
                        <div class="special-title"><?= e($cell['title']) ?></div>
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
                      <div class="cell-slot js-slot-card"
                           role="button"
                           tabindex="0"
                           data-code="<?= e($s['subject_code']) ?>"
                           data-name="<?= e($s['subject_name']) ?>"
                           data-type="<?= e($s['course_type'] ?? 'regular') ?>"
                           data-credit="<?= (int) ($s['credit'] ?? 3) ?>"
                           data-teacher="<?= e($s['teacher_name'] ?: '-') ?>"
                           data-room="<?= e($s['room'] ?: '-') ?>"
                           data-time="<?= hm($s['time_start']) ?>–<?= hm($s['time_end']) ?>"
                           data-day="วัน<?= TH_DAYS[$d] ?>"
                           data-group="<?= e($selectedGroup) ?>"
                           title="คลิกเพื่อดูรายละเอียดรายวิชา">
                        <div class="slot-head d-flex justify-content-between align-items-center">
                          <div class="d-flex align-items-center gap-1">
                            <span class="mono fw-bold slot-code"><?= e($s['subject_code']) ?></span>
                            <span class="badge badge-sm <?= ($s['course_type'] ?? 'regular') === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="font-size: 9px; padding: 1px 4px; font-weight: normal;">
                              <?= ($s['course_type'] ?? 'regular') === 'professional' ? 'วิชาชีพ' : 'สามัญ' ?>
                            </span>
                          </div>
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
          <div class="empty-state">
            <div class="empty-state-icon" aria-hidden="true">
              <?= icon('calendar', 28) ?>
            </div>
            <p class="empty-state-title">ยังไม่มีกำหนดการกิจกรรมในขณะนี้</p>
            <p class="empty-state-desc">ยังไม่มีการเพิ่มกิจกรรมหรือกำหนดการของสาขาวิชาในระบบ</p>
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

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailModal" tabindex="-1" aria-labelledby="courseDetailModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="courseDetailModalTitle">
          <span class="mono fw-bold text-navy" id="detailSubjectCode"></span>
          <span class="badge" id="detailCourseType"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body">
        <h4 class="fw-bold mb-3" id="detailSubjectName"></h4>
        <dl class="row g-2 mb-0" style="font-size: 14.5px;">
          <dt class="col-4 text-muted">จำนวนหน่วยกิต</dt>
          <dd class="col-8 mono fw-semibold" id="detailCredit">-</dd>

          <dt class="col-4 text-muted">วันและเวลาเรียน</dt>
          <dd class="col-8 mono" id="detailTime">-</dd>

          <dt class="col-4 text-muted">อาจารย์ผู้สอน</dt>
          <dd class="col-8" id="detailTeacher">-</dd>

          <dt class="col-4 text-muted">ห้องเรียน / แล็บ</dt>
          <dd class="col-8 mono" id="detailRoom">-</dd>

          <dt class="col-4 text-muted">กลุ่มเรียน</dt>
          <dd class="col-8" id="detailGroup">-</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" defer></script>
<script src="<?= url('assets/js/page-schedule.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/page-schedule.js') ?>" defer></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
