<?php
declare(strict_types=1);

/**
 * Component: Modals for Class Schedule & Courses Management
 * โมดอลจัดการคาบเวลาเรียน, โมดอลจัดคาบเรียน (Slot), และโมดอลทะเบียนรายวิชา (Course)
 */
?>

<!-- Modal: Periods Configuration -->
<div class="modal fade" id="periodsModal" tabindex="-1" aria-labelledby="periodsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="periodsModalLabel">ตั้งค่าคาบเวลาเรียน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save_periods">
        <input type="hidden" name="current_group" value="<?= e($selectedGroup) ?>">

        <div class="modal-body">
          <p class="text-muted small mb-3">
            กำหนดการแบ่งคาบเรียน เวลาเริ่มต้นและสิ้นสุดของแต่ละคาบ รวมถึงคาบพิเศษเช่น พักกลางวัน หรือกิจกรรมหน้าเสาธง
          </p>
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="tablePeriodsModal">
              <thead class="table-light">
                <tr>
                  <th style="width: 65px;">คาบที่</th>
                  <th style="width: 105px;">เวลาเริ่ม</th>
                  <th style="width: 105px;">เวลาสิ้นสุด</th>
                  <th style="width: 140px;">ประเภท</th>
                  <th>ชื่อระบุ (ถ้ามี)</th>
                  <th style="width: 45px;" class="text-center">ลบ</th>
                </tr>
              </thead>
              <tbody id="periodsTbody">
                <?php foreach ($periods as $idx => $p): ?>
                  <tr>
                    <td>
                      <input type="number" name="p_num[]" class="form-control form-control-sm mono" value="<?= (int) $p['num'] ?>" required>
                    </td>
                    <td>
                      <input type="time" name="p_start[]" class="form-control form-control-sm mono" value="<?= e($p['start']) ?>" required>
                    </td>
                    <td>
                      <input type="time" name="p_end[]" class="form-control form-control-sm mono" value="<?= e($p['end']) ?>" required>
                    </td>
                    <td>
                      <select name="p_type[]" class="form-select form-select-sm">
                        <option value="class" <?= ($p['type'] ?? '') === 'class' ? 'selected' : '' ?>>คาบเรียนปกติ</option>
                        <option value="lunch" <?= ($p['type'] ?? '') === 'lunch' ? 'selected' : '' ?>>พักกลางวัน</option>
                        <option value="flag" <?= ($p['type'] ?? '') === 'flag' ? 'selected' : '' ?>>หน้าเสาธง/โฮมรูม</option>
                      </select>
                    </td>
                    <td>
                      <input type="text" name="p_name[]" class="form-control form-control-sm" placeholder="เช่น กิจกรรมหน้าเสาธง" value="<?= e($p['name'] ?? '') ?>">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-danger-line p-0 btn-del-period" style="width: 30px; height: 30px; font-size: 13px; line-height: 28px;" title="ลบคาบนี้">✕</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button type="button" class="btn btn-sm btn-line mt-2 py-1 px-3 fw-semibold" id="btnAddPeriod">
            <?= icon('plus', 15) ?> <span>เพิ่มคาบใหม่</span>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy">บันทึกการตั้งค่าคาบเวลา</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Class Slot Editor -->
<div class="modal fade" id="slotModal" tabindex="-1" aria-labelledby="slotModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="slotModalTitle">เพิ่มคาบเรียนใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes') ?>" id="slotModalForm">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save">
        <input type="hidden" name="id" id="modalSlotId" value="">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <label for="modalClassGroup" class="form-label">กลุ่มเรียน / ชั้นปี <span class="req">*</span></label>
              <input type="text"
                     name="class_group"
                     id="modalClassGroup"
                     class="form-control"
                     list="modalClassGroupList"
                     placeholder="ปวช.2/1"
                     value="<?= e($selectedGroup) ?>"
                     required>
              <datalist id="modalClassGroupList">
                <?php foreach ($availableGroups as $grp): ?>
                  <option value="<?= e($grp) ?>"></option>
                <?php endforeach; ?>
              </datalist>
            </div>

            <div class="col-12 col-md-4">
              <label for="modalDayOfWeek" class="form-label">วันในสัปดาห์ <span class="req">*</span></label>
              <select name="day_of_week" id="modalDayOfWeek" class="form-select" required>
                <?php for ($d = 1; $d <= 5; $d++): ?>
                  <option value="<?= $d ?>">วัน<?= TH_DAYS[$d] ?></option>
                <?php endfor; ?>
              </select>
            </div>

            <div class="col-6 col-md-2">
              <label for="modalTimeStart" class="form-label">เวลาเริ่ม <span class="req">*</span></label>
              <input type="time"
                     name="time_start"
                     id="modalTimeStart"
                     class="form-control mono"
                     value="08:30"
                     required>
            </div>

            <div class="col-6 col-md-2">
              <label for="modalTimeEnd" class="form-label">เวลาสิ้นสุด <span class="req">*</span></label>
              <input type="time"
                     name="time_end"
                     id="modalTimeEnd"
                     class="form-control mono"
                     value="10:30"
                     required>
            </div>

            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="modalCourseSearch" class="form-label mb-0 fw-semibold text-navy">เลือกรายวิชาจากทะเบียนหลักสูตร</label>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnToggleNewCourseInline" style="font-size: 13px;">
                  <?= icon('plus', 13) ?> กำหนดรหัส/ชื่อวิชาเอง
                </button>
              </div>
              <div class="course-combobox-wrap position-relative" id="courseComboboxWrap">
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><?= icon('search', 14) ?></span>
                  <input type="text"
                         id="modalCourseSearch"
                         class="form-control border-start-0 border-end-0"
                         placeholder="พิมพ์รหัส หรือชื่อวิชา เพื่อค้นหาและเลือกทันที..."
                         autocomplete="off">
                  <button type="button" class="btn btn-outline-secondary btn-course-clear d-none" id="btnClearCourseSearch" aria-label="ล้างการค้นหา" title="ล้างการค้นหา">
                    ✕
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-course-dropdown" id="btnToggleCourseCombobox" aria-label="แสดงรายวิชาทั้งหมด" title="แสดงรายวิชาทั้งหมด">
                    <span class="combobox-chevron" style="font-size: 10px;">▼</span>
                  </button>
                </div>
                <!-- Native hidden select for form submission and standard state sync -->
                <select name="class_id" id="modalClassId" class="d-none">
                  <option value="">-- เลือกจากทะเบียนรายวิชา หรือกรอกเองด้านล่าง --</option>
                  <?php if (!empty($recommendedCourses)): ?>
                    <optgroup label="วิชาแนะนำสำหรับกลุ่ม <?= e($selectedGroup) ?> (<?= e($smartGroup) ?>)">
                      <?php foreach ($recommendedCourses as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
                                data-code="<?= e($c['subject_code']) ?>"
                                data-name="<?= e($c['subject_name']) ?>"
                                data-teacher="<?= e($c['default_teacher'] ?? '') ?>"
                                data-room="<?= e($c['default_room'] ?? '') ?>"
                                data-credit="<?= (int) $c['credit'] ?>"
                                data-type="<?= e($c['course_type']) ?>">
                          <?= e($c['subject_code']) ?>: <?= e($c['subject_name']) ?> (<?= $c['credit'] ?> นก. / <?= $c['course_type'] === 'professional' ? 'วิชาชีพ' : 'วิชาสามัญ' ?>)
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                  <?php if (!empty($otherCourses)): ?>
                    <optgroup label="รายวิชาอื่น ๆ ในแผนก">
                      <?php foreach ($otherCourses as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
                                data-code="<?= e($c['subject_code']) ?>"
                                data-name="<?= e($c['subject_name']) ?>"
                                data-teacher="<?= e($c['default_teacher'] ?? '') ?>"
                                data-room="<?= e($c['default_room'] ?? '') ?>"
                                data-credit="<?= (int) $c['credit'] ?>"
                                data-type="<?= e($c['course_type']) ?>">
                          <?= e($c['subject_code']) ?>: <?= e($c['subject_name']) ?> (<?= $c['credit'] ?> นก. / <?= $c['course_type'] === 'professional' ? 'วิชาชีพ' : 'วิชาสามัญ' ?>)
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                </select>

                <!-- Searchable Dropdown Popup -->
                <div class="course-combobox-menu shadow-sm" id="courseComboboxMenu" style="display: none;">
                  <div class="course-combobox-group-header course-combobox-search-header d-none" id="courseComboboxSearchHeader">
                    ผลการค้นหารายวิชา
                  </div>
                  <?php if (!empty($recommendedCourses)): ?>
                    <div class="course-combobox-group-header section-header text-navy fw-bold">
                      วิชาแนะนำสำหรับกลุ่ม <?= e($selectedGroup) ?>
                    </div>
                    <?php foreach ($recommendedCourses as $c): ?>
                      <div class="course-combobox-item"
                           role="button"
                           tabindex="0"
                           data-id="<?= (int) $c['id'] ?>"
                           data-code="<?= e($c['subject_code']) ?>"
                           data-name="<?= e($c['subject_name']) ?>"
                           data-teacher="<?= e($c['default_teacher'] ?? '') ?>"
                           data-room="<?= e($c['default_room'] ?? '') ?>"
                           data-credit="<?= (int) $c['credit'] ?>"
                           data-type="<?= e($c['course_type']) ?>"
                           data-search="<?= e(mb_strtolower($c['subject_code'] . ' ' . $c['subject_name'] . ' ' . ($c['default_teacher'] ?? '') . ' ' . ($c['default_room'] ?? ''))) ?>">
                        <div class="course-combobox-info">
                          <div>
                            <strong class="mono text-navy course-code"><?= e($c['subject_code']) ?></strong>
                            <span class="ms-1 course-title"><?= e($c['subject_name']) ?></span>
                          </div>
                          <?php if (!empty($c['default_teacher']) || !empty($c['default_room'])): ?>
                            <div class="small text-muted" style="font-size: 11.5px; line-height: 1.3;">
                              <?= e($c['default_teacher'] ?? '') ?><?= !empty($c['default_room']) ? ' · ห้อง ' . e($c['default_room']) : '' ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <span class="badge <?= $c['course_type'] === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="font-size: 10px;">
                          <?= $c['course_type'] === 'professional' ? 'วิชาชีพ' : 'สามัญ' ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  <?php if (!empty($otherCourses)): ?>
                    <div class="course-combobox-group-header section-header text-muted">
                      รายวิชาอื่น ๆ ในแผนก
                    </div>
                    <?php foreach ($otherCourses as $c): ?>
                      <div class="course-combobox-item"
                           role="button"
                           tabindex="0"
                           data-id="<?= (int) $c['id'] ?>"
                           data-code="<?= e($c['subject_code']) ?>"
                           data-name="<?= e($c['subject_name']) ?>"
                           data-teacher="<?= e($c['default_teacher'] ?? '') ?>"
                           data-room="<?= e($c['default_room'] ?? '') ?>"
                           data-credit="<?= (int) $c['credit'] ?>"
                           data-type="<?= e($c['course_type']) ?>"
                           data-search="<?= e(mb_strtolower($c['subject_code'] . ' ' . $c['subject_name'] . ' ' . ($c['default_teacher'] ?? '') . ' ' . ($c['default_room'] ?? ''))) ?>">
                        <div class="course-combobox-info">
                          <div>
                            <strong class="mono text-navy course-code"><?= e($c['subject_code']) ?></strong>
                            <span class="ms-1 course-title"><?= e($c['subject_name']) ?></span>
                          </div>
                          <?php if (!empty($c['default_teacher']) || !empty($c['default_room'])): ?>
                            <div class="small text-muted" style="font-size: 11.5px; line-height: 1.3;">
                              <?= e($c['default_teacher'] ?? '') ?><?= !empty($c['default_room']) ? ' · ห้อง ' . e($c['default_room']) : '' ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <span class="badge <?= $c['course_type'] === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="font-size: 10px;">
                          <?= $c['course_type'] === 'professional' ? 'วิชาชีพ' : 'สามัญ' ?>
                        </span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  <div class="p-3 text-center text-muted small d-none" id="courseComboboxNoResult">
                    ไม่พบรายวิชาที่ค้นหา — สามารถคลิก "กำหนดรหัส/ชื่อวิชาเอง" เพื่อกรอกโดยตรง
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4" id="rowSubjectCode">
              <label for="modalSubjectCode" class="form-label">รหัสวิชา</label>
              <input type="text"
                     name="subject_code"
                     id="modalSubjectCode"
                     class="form-control mono"
                     placeholder="เช่น 20204-2001">
            </div>

            <div class="col-12 col-md-8" id="rowSubjectName">
              <label for="modalSubjectName" class="form-label">ชื่อรายวิชา <span class="req">*</span></label>
              <input type="text"
                     name="subject_name"
                     id="modalSubjectName"
                     class="form-control"
                     placeholder="เช่น การพัฒนาเว็บแอปพลิเคชัน"
                     required>
            </div>

            <div class="col-12 col-md-6">
              <label for="modalTeacherName" class="form-label">อาจารย์ผู้สอน (เฉพาะกลุ่มนี้)</label>
              <input type="text"
                     name="teacher_name"
                     id="modalTeacherName"
                     class="form-control"
                     placeholder="อ.ธีรภัทร์ รัตนโชติ">
            </div>

            <div class="col-12 col-md-6">
              <label for="modalRoom" class="form-label">ห้องเรียน / ห้องปฏิบัติการ (เฉพาะกลุ่มนี้)</label>
              <input type="text"
                     name="room"
                     id="modalRoom"
                     class="form-control mono"
                     placeholder="Lab 301">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy" id="modalSubmitBtn">
            <?= icon('check', 16) ?>
            <span>บันทึกข้อมูล</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Master Course Editor -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="courseModalTitle">เพิ่มรายวิชาใหม่ในหลักสูตร</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <form method="POST" action="<?= url('admin/classes?tab=catalog') ?>" id="formCourseModal">
        <?= csrf_field() ?>
        <input type="hidden" name="sub_action" value="save_course">
        <input type="hidden" name="id" id="courseModalId" value="">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-5">
              <label for="courseModalCode" class="form-label">รหัสวิชา <span class="req">*</span></label>
              <input type="text" name="subject_code" id="courseModalCode" class="form-control mono" placeholder="เช่น 20204-2001" required>
            </div>
            <div class="col-12 col-md-7">
              <label for="courseModalType" class="form-label">ประเภทรายวิชา <span class="req">*</span></label>
              <select name="course_type" id="courseModalType" class="form-select" required>
                <option value="professional">วิชาชีพเฉพาะ (Professional)</option>
                <option value="regular">วิชาสามัญ / พื้นฐาน (Regular)</option>
              </select>
            </div>
            <div class="col-12">
              <label for="courseModalName" class="form-label">ชื่อรายวิชา <span class="req">*</span></label>
              <input type="text" name="subject_name" id="courseModalName" class="form-control" placeholder="เช่น การเขียนโปรแกรมบนเว็บ" required>
            </div>
            <div class="col-12 col-md-4">
              <label for="courseModalCredit" class="form-label">จำนวนหน่วยกิต</label>
              <input type="number" name="credit" id="courseModalCredit" class="form-control mono" min="1" max="6" value="3" required>
            </div>
            <div class="col-12 col-md-8">
              <label for="courseModalTeacher" class="form-label">อาจารย์ผู้สอน</label>
              <input type="text" name="default_teacher" id="courseModalTeacher" class="form-control" placeholder="เช่น อ.กฤษณะ เขมาภิรักษ์">
            </div>
            <div class="col-12">
              <label for="courseModalRoom" class="form-label">ห้องเรียน / แล็บ</label>
              <input type="text" name="default_room" id="courseModalRoom" class="form-control mono" placeholder="เช่น Lab 432">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-navy" id="courseModalSubmitBtn">
            <?= icon('check', 16) ?> <span>บันทึกรายวิชา</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
