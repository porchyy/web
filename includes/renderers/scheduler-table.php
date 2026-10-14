<?php
/**
 * DBT Timetable Header & Body Renderer Helpers
 * Shared between Admin View (admin/classes.php) and JSON API (api/admin/classes.php)
 */

if (!function_exists('renderSchedulerThead')) {
    function renderSchedulerThead(array $periods): void { ?>
        <tr>
          <th class="day-col">วัน / คาบ</th>
          <?php foreach ($periods as $p): ?>
            <th>
              <div class="period-name text-truncate" title="<?= e($p['name'] ?: 'คาบ ' . $p['num']) ?>">
                <?= e($p['name'] ?: 'คาบ ' . $p['num']) ?>
              </div>
              <div class="period-time mono text-muted"><?= e($p['label']) ?></div>
            </th>
          <?php endforeach; ?>
        </tr>
    <?php }
}

if (!function_exists('renderSchedulerTbody')) {
    function renderSchedulerTbody(array $grid, string $selectedGroup): void {
        for ($d = 1; $d <= 5; $d++): ?>
            <tr data-day="<?= $d ?>">
              <td class="day-cell">
                วัน<?= TH_DAYS[$d] ?>
              </td>
              <?php 
              $daySlots = $grid[$d] ?? [];
              foreach ($daySlots as $cell): 
              ?>
                <?php if ($cell['type'] === 'flag' || $cell['type'] === 'lunch'): ?>
                  <?php $s = $cell['slot']; ?>
                  <td colspan="<?= $cell['colspan'] ?>">
                    <div class="admin-slot-card admin-slot-card-special slot-type-<?= $cell['type'] ?>"
                         data-slot-id="<?= (int) $s['id'] ?>"
                         data-day="<?= $d ?>"
                         data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                         data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                         data-span="<?= $cell['colspan'] ?>"
                         data-period-num="<?= (int) $cell['period']['num'] ?>"
                         data-subject-code="<?= e($s['subject_code']) ?>"
                         data-subject-name="<?= e($s['subject_name']) ?>"
                         data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                         data-room="<?= e($s['room'] ?? '') ?>"
                         data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                         title="คลิกเพื่อแก้ไขข้อมูลคาบพิเศษ">
                      <div class="slot-resize-handle resize-handle handle-left resize-handle-left" data-side="left" title="ลากขอบซ้ายเพื่อปรับเวลาเริ่มต้น"></div>
                      <div class="slot-resize-handle resize-handle handle-right resize-handle-right" data-side="right" title="ลากขอบขวาเพื่อปรับเวลาสิ้นสุด"></div>
                      
                      <div class="card-head">
                        <span class="subject-code d-inline-flex align-items-center gap-1">
                          <?= icon($cell['type'] === 'flag' ? 'flag' : 'clock', 12) ?>
                          <span><?= $cell['type'] === 'flag' ? 'FLAG' : 'LUNCH' ?></span>
                        </span>
                        <span class="slot-drag-handle" title="ลากเพื่อย้ายคาบเรียน">⠿</span>
                      </div>
                      <div class="subject-name" title="<?= e($cell['title']) ?>">
                        <?= e($cell['title']) ?>
                      </div>
                      <div class="card-meta">
                        <span class="card-room text-muted"><?= $cell['type'] === 'flag' ? 'หน้าเสาธง / โฮมรูม' : 'พักรับประทานอาหาร' ?></span>
                        <span class="card-time mono">
                          <?php if ($cell['colspan'] > 1): ?>
                            (<?= $cell['colspan'] ?> คาบ)
                          <?php endif; ?>
                        </span>
                      </div>

                      <!-- Action Overlay -->
                      <div class="slot-hover-overlay">
                        <div class="slot-actions-bar position-absolute top-0 end-0 p-1 d-flex gap-1">
                          <button type="button"
                                  class="btn btn-sm bg-white border shadow-sm p-1 lh-1 text-primary btn-edit-slot-modal"
                                  data-id="<?= (int) $s['id'] ?>"
                                  data-day="<?= $d ?>"
                                  data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                                  data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                                  data-subject-code="<?= e($s['subject_code']) ?>"
                                  data-subject-name="<?= e($s['subject_name']) ?>"
                                  data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                                  data-room="<?= e($s['room'] ?? '') ?>"
                                  data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                                  title="แก้ไขข้อมูล">
                            <?= icon('pencil', 16) ?>
                          </button>
                          <form method="POST" action="<?= url('admin/classes') ?>" class="d-inline m-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="sub_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
                            <button type="submit" class="btn btn-sm bg-white border shadow-sm p-1 lh-1 text-danger" title="ลบ" data-confirm="ยืนยันการลบคาบ<?= $cell['type'] === 'flag' ? 'กิจกรรมหน้าเสาธง' : 'พักกลางวัน' ?>ของวัน<?= TH_DAYS[$d] ?>?">
                              <?= icon('trash', 16) ?>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                <?php elseif ($cell['type'] === 'class'): ?>
                  <?php $s = $cell['slot']; ?>
                  <td colspan="<?= $cell['colspan'] ?>">
                    <div class="admin-slot-card"
                         data-slot-id="<?= (int) $s['id'] ?>"
                         data-class-id="<?= (int) ($s['class_id'] ?? 0) ?>"
                         data-course-type="<?= e($s['course_type'] ?? 'regular') ?>"
                         data-day="<?= $d ?>"
                         data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                         data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                         data-span="<?= $cell['colspan'] ?>"
                         data-period-num="<?= (int) $cell['period']['num'] ?>"
                         data-subject-code="<?= e($s['subject_code']) ?>"
                         data-subject-name="<?= e($s['subject_name']) ?>"
                         data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                         data-room="<?= e($s['room'] ?? '') ?>"
                         data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                         title="คลิกเพื่อแก้ไขข้อมูลคาบเรียนนี้">
                      <div class="slot-resize-handle resize-handle handle-left resize-handle-left" data-side="left" title="ลากขอบซ้ายเพื่อปรับเวลาเริ่มต้น"></div>
                      <div class="slot-resize-handle resize-handle handle-right resize-handle-right" data-side="right" title="ลากขอบขวาเพื่อปรับเวลาสิ้นสุด"></div>
                      
                      <div class="card-head d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1">
                          <span class="subject-code" title="<?= e($s['subject_code']) ?>"><?= e($s['subject_code']) ?></span>
                          <span class="badge badge-sm <?= ($s['course_type'] ?? 'regular') === 'professional' ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="font-size: 9px; padding: 1px 4px; font-weight: normal; border-radius: 2px;">
                            <?= ($s['course_type'] ?? 'regular') === 'professional' ? 'วิชาชีพ' : 'สามัญ' ?>
                          </span>
                        </div>
                        <span class="slot-drag-handle" title="ลากเพื่อย้ายคาบเรียน">⠿</span>
                      </div>
                      <div class="subject-name" title="<?= e($s['subject_name']) ?>">
                        <?= e($s['subject_name']) ?>
                      </div>
                      <div class="card-meta">
                        <span class="card-room" title="<?= e($s['room'] ?: '-') ?>">
                          <?= e($s['room'] ?: '-') ?><?= !empty($s['teacher_name']) ? ' · ' . e($s['teacher_name']) : '' ?>
                        </span>
                        <span class="card-time mono">
                          <?php if ($cell['colspan'] > 1): ?>
                            (<?= $cell['colspan'] ?> คาบ)
                          <?php endif; ?>
                        </span>
                      </div>

                      <!-- Action Overlay -->
                      <div class="slot-hover-overlay">
                        <div class="slot-actions-bar position-absolute top-0 end-0 p-1 d-flex gap-1">
                          <button type="button"
                                  class="btn btn-sm bg-white border shadow-sm p-1 lh-1 text-primary btn-edit-slot-modal"
                                  data-id="<?= (int) $s['id'] ?>"
                                  data-class-id="<?= (int) ($s['class_id'] ?? 0) ?>"
                                  data-day="<?= $d ?>"
                                  data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                                  data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                                  data-subject-code="<?= e($s['subject_code']) ?>"
                                  data-subject-name="<?= e($s['subject_name']) ?>"
                                  data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                                  data-room="<?= e($s['room'] ?? '') ?>"
                                  data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                                  title="แก้ไขข้อมูล">
                            <?= icon('pencil', 16) ?>
                          </button>
                          <button type="button"
                                  class="btn btn-sm bg-white border shadow-sm p-1 lh-1 text-success btn-duplicate-slot-modal"
                                  data-id="<?= (int) $s['id'] ?>"
                                  data-class-id="<?= (int) ($s['class_id'] ?? 0) ?>"
                                  data-day="<?= $d ?>"
                                  data-time-start="<?= e(substr($s['time_start'], 0, 5)) ?>"
                                  data-time-end="<?= e(substr($s['time_end'], 0, 5)) ?>"
                                  data-subject-code="<?= e($s['subject_code']) ?>"
                                  data-subject-name="<?= e($s['subject_name']) ?>"
                                  data-teacher-name="<?= e($s['teacher_name'] ?? '') ?>"
                                  data-room="<?= e($s['room'] ?? '') ?>"
                                  data-group="<?= e($s['class_group'] ?? $selectedGroup) ?>"
                                  title="ทำซ้ำคาบเรียนนี้ไปยังวัน/เวลาอื่น">
                            <?= icon('copy', 16) ?>
                          </button>
                          <form method="POST" action="<?= url('admin/classes') ?>" class="d-inline m-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="sub_action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <input type="hidden" name="class_group" value="<?= e($selectedGroup) ?>">
                            <button type="submit" class="btn btn-sm bg-white border shadow-sm p-1 lh-1 text-danger" title="ลบ" data-confirm="ยืนยันการลบคาบเรียนนี้?">
                              <?= icon('trash', 16) ?>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                <?php else: ?>
                  <td class="admin-slot-empty btn-open-slot-modal"
                      data-dropzone="true"
                      data-day="<?= $d ?>"
                      data-time-start="<?= e(substr($cell['period']['start'], 0, 5)) ?>"
                      data-time-end="<?= e(substr($cell['period']['end'], 0, 5)) ?>"
                      data-period-num="<?= (int) $cell['period']['num'] ?>"
                      data-group="<?= e($selectedGroup) ?>"
                      title="คลิกเพื่อเพิ่มคาบเรียน ณ เวลานี้">
                    <div class="empty-slot-inner">
                      <span class="btn-slot-add">
                        <?= icon('plus', 13) ?>
                      </span>
                      <span class="empty-slot-label">เพิ่มคาบ</span>
                    </div>
                  </td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
        <?php endfor;
    }
}
