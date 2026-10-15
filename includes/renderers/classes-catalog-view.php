<?php
declare(strict_types=1);

/**
 * Component: Courses Master Catalog View
 * ทะเบียนรายวิชาหลักสูตรและตัวกรองหมวดหมู่รหัสวิชา
 */
?>
<div class="admin-card">
  <div class="admin-card-head d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h3 class="mb-1">รายวิชาในหลักสูตรทั้งหมด</h3>
      <p class="text-muted small mb-0">รหัสวิชา ชื่อวิชา ประเภทหลักสูตร และอาจารย์/ห้องเรียน</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="position-relative" style="width: 240px;">
        <input type="text" id="catalogSearchInput" class="form-control form-control-sm ps-4" placeholder="ค้นหารหัส หรือชื่อวิชา...">
        <span class="position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="pointer-events: none;">
          <?= icon('search', 14) ?>
        </span>
      </div>
      <select class="form-select form-select-sm" style="width: auto;" onchange="location.href='<?= url('admin/classes.php?tab=catalog&prefix=' . urlencode($filterPrefix) . '&type=') ?>' + this.value">
        <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>ประเภททั้งหมด</option>
        <option value="professional" <?= $filterType === 'professional' ? 'selected' : '' ?>>เฉพาะวิชาชีพ</option>
        <option value="regular" <?= $filterType === 'regular' ? 'selected' : '' ?>>เฉพาะวิชาสามัญ</option>
      </select>
    </div>
  </div>

  <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center gap-2">
    <span class="text-muted small fw-semibold me-1">หมวดรหัส:</span>
    <a href="<?= url('admin/classes.php?tab=catalog&type=' . urlencode($filterType)) ?>"
       class="btn btn-sm <?= $filterPrefix === 'all' ? 'btn-navy' : 'btn-line' ?>">
      ทั้งหมด (<?= count($allCourses) ?>)
    </a>
    <?php foreach ($prefixes as $pfx): ?>
      <?php
      $cleanPfx = rtrim($pfx, '-');
      $pfxCount = count(array_filter($allCourses, fn($c) => str_starts_with($c['subject_code'], $cleanPfx)));
      ?>
      <a href="<?= url('admin/classes.php?tab=catalog&prefix=' . urlencode($cleanPfx) . '&type=' . urlencode($filterType)) ?>"
         class="btn btn-sm <?= $filterPrefix === $cleanPfx ? 'btn-navy' : 'btn-line' ?>">
        <?= e($cleanPfx) ?> (<?= $pfxCount ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tableCoursesCatalog">
      <thead class="table-light">
        <tr>
          <th style="width: 130px;">รหัสวิชา</th>
          <th>ชื่อรายวิชา</th>
          <th style="width: 120px;">ประเภท</th>
          <th style="width: 90px;" class="text-center">หน่วยกิต</th>
          <th>ผู้สอน</th>
          <th>ห้องเรียน</th>
          <th style="width: 110px;" class="text-end">การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($catalogCourses)): ?>
          <tr id="emptyCatalogRow">
            <td colspan="7" class="text-center py-5 text-muted">
              <?= icon('book', 32) ?>
              <p class="mt-2 mb-0">ไม่พบรายวิชาตามเงื่อนไขที่เลือก</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($catalogCourses as $c): ?>
            <tr class="catalog-course-row">
              <td>
                <span class="mono fw-bold text-navy"><?= e($c['subject_code']) ?></span>
              </td>
              <td>
                <span class="fw-semibold"><?= e($c['subject_name']) ?></span>
              </td>
              <td>
                <span class="badge <?= $c['course_type'] === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>">
                  <?= $c['course_type'] === 'professional' ? 'วิชาชีพ' : 'วิชาสามัญ' ?>
                </span>
              </td>
              <td class="text-center mono fw-medium">
                <?= (int) $c['credit'] ?> นก.
              </td>
              <td>
                <span class="text-muted"><?= e($c['default_teacher'] ?: '-') ?></span>
              </td>
              <td>
                <span class="mono text-muted"><?= e($c['default_room'] ?: '-') ?></span>
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <button type="button"
                          class="btn btn-sm btn-line btn-edit-course"
                          data-id="<?= (int) $c['id'] ?>"
                          data-code="<?= e($c['subject_code']) ?>"
                          data-name="<?= e($c['subject_name']) ?>"
                          data-type="<?= e($c['course_type']) ?>"
                          data-teacher="<?= e($c['default_teacher'] ?? '') ?>"
                          data-room="<?= e($c['default_room'] ?? '') ?>"
                          data-credit="<?= (int) $c['credit'] ?>"
                          title="แก้ไขวิชานี้">
                    <?= icon('pencil', 13) ?>
                  </button>
                  <form method="POST" action="<?= url('admin/classes.php?tab=catalog') ?>" class="d-inline m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sub_action" value="delete_course">
                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger-line" title="ลบวิชานี้" data-confirm="ยืนยันการลบวิชา <?= e($c['subject_code']) ?> <?= e($c['subject_name']) ?>?">
                      <?= icon('trash', 13) ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('catalogSearchInput');
  const rows = document.querySelectorAll('.catalog-course-row');
  if (searchInput && rows.length > 0) {
    searchInput.addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      let matchCount = 0;
      rows.forEach(tr => {
        const text = tr.textContent.toLowerCase();
        const matches = text.includes(q);
        tr.style.display = matches ? '' : 'none';
        if (matches) matchCount++;
      });
      let emptyMsg = document.getElementById('catalogSearchEmptyRow');
      if (matchCount === 0) {
        if (!emptyMsg) {
          emptyMsg = document.createElement('tr');
          emptyMsg.id = 'catalogSearchEmptyRow';
          const emptyTd = document.createElement('td');
          emptyTd.colSpan = 7;
          emptyTd.className = 'text-center py-4 text-muted';
          emptyTd.textContent = 'ไม่พบรายวิชาที่ตรงกับคำค้นหา';
          emptyMsg.appendChild(emptyTd);
          document.querySelector('#tableCoursesCatalog tbody').appendChild(emptyMsg);
        }
        emptyMsg.style.display = '';
      } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
      }
    });
  }
});
</script>
