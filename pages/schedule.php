<?php
/**
 * SCHEDULE - ตารางเรียนตามระดับชั้น และตารางกิจกรรมสาขาวิชา
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

// โหลดรายการไฟล์ตารางเรียนทั้งหมด
$allImageAssets = ScheduleService::getScheduleImages();
$defaultLevels  = ['ปวช.1', 'ปวช.2', 'ปวช.3', 'ปวส.1', 'ปวส.2'];

// รวบรวมระดับชั้นทั้งหมดจากรายการที่อัปโหลดไว้
$customGroups = [];
foreach ($allImageAssets as $item) {
    $grp = trim((string) ($item['group_name'] ?? ''));
    if ($grp !== '' && !in_array($grp, $defaultLevels, true)) {
        $customGroups[] = $grp;
    }
}
$availableLevels = array_merge($defaultLevels, array_unique($customGroups));

// ระดับชั้นที่เลือก (ค่าเริ่มต้นคือ 'all' เพื่อให้ดูได้ทุกระดับชั้นพร้อมกัน)
$queryGroup    = trim((string) (filter_input(INPUT_GET, 'group', FILTER_DEFAULT) ?? 'all'));
$selectedGroup = ($queryGroup === '' || $queryGroup === 'all') ? 'all' : $queryGroup;

$pageTitle = ($tab === 'class') ? 'ตารางเรียนตามระดับชั้น' : 'ตารางกิจกรรมสาขาวิชา';

// จัดเตรียมข้อมูลระดับชั้นที่จะแสดงผล
$displayLevels = ($selectedGroup === 'all') ? $availableLevels : [$selectedGroup];

// กิจกรรม
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
      ตรวจสอบตารางเรียนประจำภาคเรียนแยกตามระดับชั้น (ปวช.1 – ปวส.2) และติดตามกำหนดการกิจกรรมประจำภาคเรียน
    </p>
  </div>
</div>

<section class="section" style="padding-top: var(--s-3);">
  <div class="container-xl">

    <!-- Schedule Control Toolbar -->
    <div class="schedule-control-card mb-4">
      <div class="scc-header-row justify-content-between align-items-center flex-wrap gap-3">
        <!-- Main Tabs Switcher -->
        <div class="scc-tabs-row" role="tablist" aria-label="สลับประเภทตาราง">
          <a href="<?= url('schedule?tab=class' . ($selectedGroup !== 'all' ? '&group=' . urlencode($selectedGroup) : '')) ?>" class="scc-tab <?= $tab === 'class' ? 'is-active' : '' ?>">
            <?= icon('book', 18) ?>
            <span>ตารางเรียนตามระดับชั้น</span>
          </a>
          <a href="<?= url('schedule?tab=activity') ?>" class="scc-tab <?= $tab === 'activity' ? 'is-active' : '' ?>">
            <?= icon('calendar', 18) ?>
            <span>ตารางกิจกรรมสาขา</span>
          </a>
        </div>

        <!-- Grade Level Selector Pills (กรองดูตามระดับชั้น หรือดูทั้งหมด) -->
        <?php if ($tab === 'class'): ?>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small fw-semibold">ระดับชั้น:</span>
            <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="เลือกระดับชั้น">
              <a href="<?= url('schedule?tab=class&group=all') ?>" class="btn <?= $selectedGroup === 'all' ? 'btn-navy fw-semibold' : 'btn-line' ?>">
                ทั้งหมด
              </a>
              <?php foreach ($availableLevels as $lvl): ?>
                <a href="<?= url('schedule?tab=class&group=' . urlencode($lvl)) ?>" class="btn <?= $selectedGroup === $lvl ? 'btn-navy fw-semibold' : 'btn-line' ?>">
                  <?= e($lvl) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($tab === 'class'): ?>
      <!-- ==================================================================
           SCHEDULE IMAGES VIEW (ตารางเรียนแบบรูปภาพคมชัด)
           ================================================================== -->
      <div class="row g-4">
        <?php foreach ($displayLevels as $lvl): ?>
          <?php
            // ค้นหาไฟล์ตารางของระดับชั้นนี้
            $matchingItem = null;
            foreach ($allImageAssets as $item) {
              $grp = trim((string) ($item['group_name'] ?? ''));
              if ($grp === $lvl) {
                $matchingItem = $item;
                break;
              }
            }
            if (!$matchingItem) {
              foreach ($allImageAssets as $item) {
                $grp = trim((string) ($item['group_name'] ?? ''));
                if (str_starts_with($grp, $lvl) || str_contains($grp, $lvl)) {
                  $matchingItem = $item;
                  break;
                }
              }
            }

            $hasFile   = false;
            $fileUrl   = '';
            $isPdf     = false;
            $title     = 'ตารางเรียน ระดับชั้น ' . $lvl;
            $updatedAt = '';

            if ($matchingItem) {
              $filePath = $matchingItem['image_path'] ?? '';
              if ($filePath && is_file(ROOT_PATH . '/' . $filePath)) {
                $hasFile   = true;
                $fileUrl   = url($filePath);
                $isPdf     = str_ends_with(strtolower($filePath), '.pdf');
                $title     = $matchingItem['title'] ?? $title;
                $updatedAt = $matchingItem['updated_at'] ?? '';
              }
            }
          ?>
          <div class="col-12 <?= $selectedGroup === 'all' ? 'col-lg-6' : 'col-12' ?>">
            <div class="card h-100 border shadow-sm overflow-hidden bg-white d-flex flex-column justify-content-between" style="border-radius: var(--radius, 8px);">
              
              <!-- Card Header -->
              <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-navy text-white px-2 py-1 fs-6"><?= e($lvl) ?></span>
                  <strong class="text-navy" style="font-size: 1.05rem;"><?= e($title) ?></strong>
                </div>
                <?php if ($updatedAt): ?>
                  <span class="text-muted small" style="font-size: 12px;">
                    <?= icon('clock', 12) ?> อัปเดตเมื่อ <?= thai_date($updatedAt) ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Card Body: Image / Document Presentation -->
              <div class="card-body p-3 d-flex align-items-center justify-content-center bg-light" style="min-height: 280px;">
                <?php if ($hasFile && !$isPdf): ?>
                  <div class="schedule-img-container text-center position-relative w-100">
                    <img src="<?= e($fileUrl) ?>"
                         alt="<?= e($title) ?>"
                         class="img-fluid rounded border shadow-sm js-schedule-zoomable"
                         style="max-height: 520px; width: 100%; object-fit: contain; cursor: pointer; background: #fff;"
                         onclick="openScheduleModal('<?= e($fileUrl) ?>', '<?= e($title) ?>')"
                         title="คลิกเพื่อขยายดูรูปขนาดเต็ม">
                    <div class="schedule-img-hint mt-2 text-muted small">
                      <?= icon('search', 13) ?> คลิกที่รูปภาพเพื่อเปิดดูขนาดเต็ม
                    </div>
                  </div>
                <?php elseif ($hasFile && $isPdf): ?>
                  <div class="text-center py-4">
                    <div class="mb-3 text-danger"><?= icon('file-pdf', 54) ?></div>
                    <h5 class="fw-bold mb-1 text-navy">ไฟล์เอกสาร PDF</h5>
                    <p class="text-muted small mb-3">ตารางเรียนนี้ถูกแนบไว้ในรูปแบบเอกสาร PDF</p>
                    <a href="<?= e($fileUrl) ?>" target="_blank" class="btn btn-navy d-inline-flex align-items-center gap-2">
                      <?= icon('external', 14) ?> เปิดดูเอกสาร PDF
                    </a>
                  </div>
                <?php else: ?>
                  <div class="empty-state text-center py-4 text-muted">
                    <div class="mb-2 text-muted opacity-50"><?= icon('calendar', 42) ?></div>
                    <h5 class="fw-semibold text-navy mb-1">ยังไม่มีไฟล์ตารางเรียนสำหรับ <?= e($lvl) ?></h5>
                    <p class="small text-muted mb-0">เจ้าหน้าที่กำลังเตรียมข้อมูลตารางเรียน โปรดติดต่อแผนกวิชาเพื่อสอบถามเพิ่มเติม</p>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Card Footer: Actions -->
              <?php if ($hasFile): ?>
                <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div class="small text-muted">
                    <?= $isPdf ? icon('file-pdf', 14) . ' ไฟล์เอกสาร PDF' : icon('image', 14) . ' ไฟล์รูปภาพตารางเรียน' ?>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!$isPdf): ?>
                      <button type="button" class="btn btn-sm btn-line d-inline-flex align-items-center gap-1" onclick="openScheduleModal('<?= e($fileUrl) ?>', '<?= e($title) ?>')">
                        <?= icon('zoom-in', 13) ?> ขยายดูรูปเต็ม
                      </button>
                    <?php endif; ?>
                    <a href="<?= e($fileUrl) ?>" download class="btn btn-sm btn-navy d-inline-flex align-items-center gap-1">
                      <?= icon('download', 13) ?> ดาวน์โหลดตาราง
                    </a>
                  </div>
                </div>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
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
            <div class="month-group mb-5">
              <div class="month-head d-flex align-items-center gap-3 mb-3 border-bottom pb-2">
                <span class="badge bg-navy text-white fs-6 px-3 py-2"><?= TH_MONTHS[$monthNum] ?> <?= $yearThai ?></span>
                <span class="text-muted small"><?= count($items) ?> กิจกรรม</span>
              </div>

              <div class="row g-3">
                <?php foreach ($items as $ev): ?>
                  <?php
                    $badge = activity_countdown_badge($ev['event_date'], $ev['event_date_end'] ?? null);
                  ?>
                  <div class="col-12 col-md-6">
                    <div class="card h-100 border p-3 shadow-sm d-flex flex-column justify-content-between bg-white">
                      <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <span class="badge <?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                          <span class="text-muted small"><?= thai_date($ev['event_date']) ?></span>
                        </div>
                        <h4 class="h6 fw-bold mb-2"><?= e($ev['title']) ?></h4>
                        <?php if (!empty($ev['description'])): ?>
                          <p class="text-muted small mb-2"><?= nl2br(e($ev['description'])) ?></p>
                        <?php endif; ?>
                      </div>
                      
                      <div class="pt-2 border-top mt-2 text-muted small d-flex justify-content-between align-items-center">
                        <?php if (!empty($ev['location'])): ?>
                          <span><?= icon('map-pin', 12) ?> <?= e($ev['location']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($ev['action_url'])): ?>
                          <a href="<?= e($ev['action_url']) ?>" target="_blank" class="text-navy fw-semibold text-decoration-none">
                            รายละเอียด <?= icon('external', 12) ?>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Schedule Full Image Modal (Lightbox) -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-navy text-white py-2 px-3">
        <h5 class="modal-title fs-6 fw-bold text-truncate me-2" id="scheduleModalTitle">ตารางเรียน</h5>
        <div class="d-flex align-items-center gap-2">
          <a id="scheduleModalDownload" href="#" download class="btn btn-sm btn-light py-1 px-2 d-inline-flex align-items-center gap-1">
            <?= icon('download', 14) ?> <span>ดาวน์โหลด</span>
          </a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
      </div>
      <div class="modal-body p-2 bg-dark text-center" style="overflow-x: auto;">
        <img id="scheduleModalImg" src="" alt="ตารางเรียน" class="img-fluid" style="max-height: 85vh; object-fit: contain;">
      </div>
    </div>
  </div>
</div>

<script>
function openScheduleModal(imgUrl, title) {
  const modalImg = document.getElementById('scheduleModalImg');
  const modalTitle = document.getElementById('scheduleModalTitle');
  const modalDownload = document.getElementById('scheduleModalDownload');
  if (modalImg && modalTitle) {
    modalImg.src = imgUrl;
    modalTitle.textContent = title;
    modalDownload.href = imgUrl;
    const modalEl = document.getElementById('scheduleModal');
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
      window.open(imgUrl, '_blank');
    }
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
