<?php
/** Shared by the homepage preview and full public timetable. */
?>
<figure class="timetable-image mb-3">
  <a href="<?= e(url($timetable['image_path'])) ?>" target="_blank" rel="noopener" aria-label="เปิดรูปตารางเรียนขนาดเต็ม">
    <img src="<?= e(url($timetable['image_path'])) ?>" alt="<?= e($timetable['image_alt'] ?: 'ตารางเรียนกลุ่ม ' . $selectedGroup) ?>" class="d-block w-100" style="height: auto; object-fit: contain;" loading="lazy">
  </a>
  <figcaption class="small text-muted mt-2">ตารางเรียนกลุ่ม <?= e($selectedGroup) ?> · เปิดรูปเพื่อดูขนาดเต็ม</figcaption>
</figure>
