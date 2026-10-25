<?php
$hasImage = ScheduleService::hasTimetableImage($timetable);
$formGroup = $failedPresentationSave ? (string) ($_POST['class_group'] ?? '') : $selectedGroup;
$formAlt = $failedPresentationSave ? (string) ($_POST['image_alt'] ?? '') : $timetable['image_alt'];
$isImageMode = $presentationMode === 'image';
?>
<form id="timetablePresentationForm" class="timetable-presentation mb-3" method="post" action="<?= url('admin/classes?group=' . urlencode($selectedGroup)) ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="sub_action" value="save_timetable">
  <input type="hidden" name="display_mode" id="timetableDisplayMode" value="<?= e($presentationMode) ?>">
  <div class="scheduler-toolbar timetable-mode-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 py-2 px-3 mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <label for="adminClassGroupSelect" class="fw-semibold text-navy mb-0 small">กลุ่มเรียน:</label>
      <select id="adminClassGroupSelect" class="form-select form-select-sm w-auto" data-group-url="<?= e(url('admin/classes?view=' . $viewMode . '&group=')) ?>">
        <?php foreach ($availableGroups as $group): ?>
          <option value="<?= e($group) ?>" <?= $group === $selectedGroup ? 'selected' : '' ?>><?= e($group) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (!in_array($action, ['new', 'edit'], true)): ?>
        <div class="d-inline-flex gap-1" data-timetable-mode-panel="manual" <?= $isImageMode ? 'hidden' : '' ?>>
          <a href="<?= e(url('admin/classes?group=' . urlencode($selectedGroup) . '&view=grid&editor_mode=manual')) ?>" class="btn btn-sm <?= $viewMode === 'grid' ? 'btn-navy' : 'btn-line' ?>" title="มุมมองผังตาราง">
            <?= icon('grid', 14) ?> ผังตาราง
          </a>
          <a href="<?= e(url('admin/classes?group=' . urlencode($selectedGroup) . '&view=list&editor_mode=manual')) ?>" class="btn btn-sm <?= $viewMode === 'list' ? 'btn-navy' : 'btn-line' ?>" title="มุมมองรายการ">
            <?= icon('news', 14) ?> รายการ
          </a>
        </div>
      <?php endif; ?>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="small fw-semibold text-navy" id="timetablePresentationTitle">รูปแบบตารางเรียน</span>
      <div class="d-inline-flex gap-1" role="group" aria-labelledby="timetablePresentationTitle">
        <button type="button" class="btn btn-sm <?= !$isImageMode ? 'btn-navy' : 'btn-line' ?>" data-timetable-mode-choice="manual" aria-controls="timetableManualPanel" aria-pressed="<?= !$isImageMode ? 'true' : 'false' ?>">
          <?= icon('grid', 14) ?> จัดตารางเอง
        </button>
        <button type="button" class="btn btn-sm <?= $isImageMode ? 'btn-navy' : 'btn-line' ?>" data-timetable-mode-choice="image" aria-controls="timetableImageFields" aria-pressed="<?= $isImageMode ? 'true' : 'false' ?>">
          <?= icon('image', 14) ?> อัปโหลดรูป
        </button>
      </div>
      <button class="btn btn-sm btn-navy" type="submit">บันทึกรูปแบบ</button>
    </div>
  </div>
  <p class="small text-muted mb-3" id="timetableModeStatus" role="status">เลือกโหมดเพื่อจัดการตาราง แล้วกดบันทึกรูปแบบเพื่อเปลี่ยนการแสดงผลบนหน้าเว็บ</p>
  <fieldset data-timetable-mode-panel="manual" <?= $isImageMode ? 'hidden disabled' : '' ?>>
    <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
    <input type="hidden" name="image_alt" value="<?= e($timetable['image_alt']) ?>">
  </fieldset>
  <fieldset id="timetableImageFields" class="admin-card" data-timetable-mode-panel="image" <?= !$isImageMode ? 'hidden disabled' : '' ?>>
    <legend class="h5 mb-3">รูปตารางเรียน</legend>
    <?php if ($timetable['display_mode'] === 'image' && !$hasImage): ?>
      <p class="alert-line is-error" role="alert">ไม่พบไฟล์รูปตารางเรียน กรุณาอัปโหลดรูปใหม่หรือเลือกจัดตารางเองแล้วบันทึก</p>
    <?php endif; ?>
    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <label for="timetableClassGroup" class="form-label">กลุ่มเรียน / เพิ่มกลุ่มใหม่</label>
        <input id="timetableClassGroup" name="class_group" class="form-control mb-2" value="<?= e($formGroup) ?>" maxlength="50" required>
        <p class="form-text mb-3">พิมพ์ชื่อกลุ่มใหม่เพื่อสร้างตารางแยกจากกลุ่มปัจจุบัน</p>
        <label for="timetableImageAlt" class="form-label">คำอธิบายรูปตารางเรียน</label>
        <input id="timetableImageAlt" name="image_alt" class="form-control" maxlength="255" value="<?= e($formAlt) ?>" placeholder="เช่น ตารางเรียน ปวช.2/1 ภาคเรียนที่ 2">
      </div>
      <div class="col-12 col-lg-8">
        <div class="image-upload-wrapper is-landscape" data-image-upload data-current-src="<?= $hasImage ? e(url($timetable['image_path'])) : '' ?>">
          <div class="image-upload-dropzone" role="button" tabindex="0" aria-labelledby="timetableUploadLabel" aria-describedby="timetableUploadHelp">
            <div class="image-upload-preview">
              <?php if ($hasImage): ?>
                <img class="preview-img" src="<?= e(url($timetable['image_path'])) ?>" alt="<?= e($timetable['image_alt'] ?: 'ตารางเรียน ' . $selectedGroup) ?>">
              <?php else: ?>
                <p class="preview-placeholder text-muted mb-0"><?= icon('image', 32) ?><span>ยังไม่มีรูปตารางเรียน</span></p>
              <?php endif; ?>
            </div>
            <div class="dropzone-content">
              <span id="timetableUploadLabel" class="fw-semibold text-navy">คลิกเพื่อเลือกรูป หรือลากรูปมาวาง</span>
              <input id="timetableImage" name="timetable_image" type="file" class="image-upload-input" accept="image/jpeg,image/png,image/webp" aria-labelledby="timetableUploadLabel" aria-describedby="timetableUploadHelp">
              <p id="timetableUploadHelp" class="form-text m-0">JPG, PNG หรือ WebP ไม่เกิน 5 MB<br>รูปตัวอย่างจะเผยแพร่เมื่อบันทึก</p>
              <div class="dropzone-file-selected d-none"><span class="file-name"></span> <span class="file-size"></span></div>
            </div>
          </div>
          <button type="button" class="btn btn-sm btn-line btn-clear-file d-none align-self-center">ยกเลิกรูปที่เลือก</button>
        </div>
      </div>
    </div>
  </fieldset>
</form>
<?php if ($hasImage): ?>
  <form method="post" action="<?= url('admin/classes?group=' . urlencode($selectedGroup)) ?>" class="mb-3" data-timetable-mode-panel="image" <?= !$isImageMode ? 'hidden' : '' ?>>
    <?= csrf_field() ?>
    <input type="hidden" name="sub_action" value="save_timetable">
    <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
    <input type="hidden" name="display_mode" value="manual">
    <input type="hidden" name="image_alt" value="<?= e($timetable['image_alt']) ?>">
    <button type="submit" name="remove_image" value="1" class="btn btn-sm btn-danger-line" data-confirm="ลบรูปตารางเรียนของกลุ่มนี้และกลับไปใช้ตารางที่จัดเอง?">ลบรูปที่บันทึกไว้</button>
  </form>
<?php endif; ?>
