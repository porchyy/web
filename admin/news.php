<?php
/**
 * Admin - จัดการข่าวสารและประกาศ (Refined Editorial Brutalism & Productivity)
 * Standard: STYLE.md & RULES.md
 */
$adminNav = 'news';
$pageTitle = 'จัดการข่าวสารและประกาศ';

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/AuthService.php';
require_once __DIR__ . '/../includes/services/NewsService.php';

AuthService::requireLogin();

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$newsId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error  = null;

// Categories for dropdown
$categories = NewsService::getCategories();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            NewsService::delete($delId);
            flash('ลบข่าวสารเรียบร้อยแล้ว');
        }
        redirect('admin/news.php');
    }

    if ($subAction === 'save') {
        $editId      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $title       = clean_text($_POST['title'] ?? '');
        $summary     = clean_text($_POST['summary'] ?? '');
        $content     = trim((string) ($_POST['content'] ?? ''));
        $isFeatured  = !empty($_POST['is_featured']) ? 1 : 0;

        // Auto-assign published_at (no manual input required)
        if ($editId) {
            $existing = NewsService::getById($editId);
            $publishedAt = $existing['published_at'] ?? date('Y-m-d');
        } else {
            $publishedAt = date('Y-m-d');
        }

        if ($title === '' || !$categoryId || $content === '') {
            $error = 'กรุณากรอกหัวข้อข่าว หมวดหมู่ และเนื้อหาให้ครบถ้วน';
        } else {
            try {
                $newImage = upload_image('image');
                NewsService::save([
                    'id'           => $editId,
                    'category_id'  => $categoryId,
                    'title'        => $title,
                    'summary'      => $summary,
                    'content'      => $content,
                    'is_featured'  => $isFeatured,
                    'published_at' => $publishedAt,
                ], $newImage);

                flash($editId ? 'แก้ไขข่าวสารเรียบร้อยแล้ว' : 'เพิ่มข่าวสารใหม่เรียบร้อยแล้ว');
                redirect('admin/news.php');
            } catch (Exception $e) {
                $error = public_error_message($e);
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $newsId) {
    $editItem = NewsService::getById($newsId);
}

// Fetch list
$allNews = NewsService::getAll();

require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">จัดการข่าวสาร</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการข่าวสารและประกาศ</h1>
  </div>
  <div class="d-flex gap-2">
    <?php if ($action === 'list'): ?>
      <a href="<?= url('admin/news.php?action=new') ?>" class="btn btn-navy">
        <?= icon('plus', 16) ?>
        <span>เพิ่มข่าวสารใหม่</span>
      </a>
    <?php else: ?>
      <a href="<?= url('admin/news.php') ?>" class="btn btn-sm btn-line">
        <?= icon('arrow-left', 16) ?>
        <span>กลับหน้ารายการข่าว</span>
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert-line is-error mb-4" role="alert">
    <?= icon('alert', 18) ?>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
  <!-- ====================================================================
       CREATE / EDIT FORM
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3><?= $action === 'edit' ? 'แก้ไขข่าวสาร #' . (int) $editItem['id'] : 'สร้างข่าวสารใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/news.php') ?>" enctype="multipart/form-data" id="form-news-editor">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-7">
          <label for="input-news-title" class="form-label">หัวข้อข่าว <span class="req">*</span></label>
          <input type="text"
                 name="title"
                 id="input-news-title"
                 class="form-control fw-bold"
                 placeholder="ระบุหัวข้อข่าวสารหรือประกาศสำคัญ"
                 value="<?= e($_POST['title'] ?? $editItem['title'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-5">
          <label for="input-news-category" class="form-label">หมวดหมู่ <span class="req">*</span></label>
          <div class="input-group">
            <select name="category_id" id="input-news-category" class="form-select" required>
              <option value="">-- เลือกหมวดหมู่ --</option>
              <?php foreach ($categories as $c): ?>
                <?php
                $selected = (int) ($_POST['category_id'] ?? $editItem['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '';
                ?>
                <option value="<?= (int) $c['id'] ?>" <?= $selected ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-line btn-sm" id="btnQuickAddCat" data-api-url="<?= url('api/admin/news-category') ?>" title="เพิ่มหมวดหมู่ใหม่ทันที">
              <?= icon('plus', 16) ?> <span>เพิ่มหมวด</span>
            </button>
          </div>
        </div>

        <div class="col-12">
          <div class="form-check form-switch d-inline-flex align-items-center gap-2">
            <input class="form-check-input"
                   type="checkbox"
                   role="switch"
                   name="is_featured"
                   value="1"
                   id="input-news-featured"
                   <?= !empty($_POST['is_featured'] ?? $editItem['is_featured'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="input-news-featured">
              ปักหมุดเป็นข่าวเด่น
            </label>
          </div>
        </div>

        <div class="col-12">
          <label for="input-news-summary" class="form-label">บทสรุปย่อ</label>
          <textarea name="summary"
                    id="input-news-summary"
                    rows="2"
                    class="form-control"
                    placeholder="สรุปเนื้อหาสำคัญสั้น ๆ ไม่เกิน 2-3 บรรทัด"><?= e($_POST['summary'] ?? $editItem['summary'] ?? '') ?></textarea>
          <div class="form-text">ข้อความเกริ่นนำที่จะแสดงในการ์ดรายการข่าวหน้าแรก</div>
        </div>

        <div class="col-12 mb-4">
          <label for="input-news-content" class="form-label">เนื้อหาข่าวฉบับเต็ม <span class="req">*</span></label>
          <div id="quill-news-editor"
               class="quill-editorial-editor"
               data-target-input="input-news-content"
               data-preview-target="modalNewsContent"
               data-placeholder="พิมพ์หรือวางเนื้อหาข่าวสารที่นี่..."></div>
          <textarea name="content"
                    id="input-news-content"
                    class="d-none"
                    required><?= htmlspecialchars($_POST['content'] ?? $editItem['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- SIDE-BY-SIDE IMAGE UPLOAD WRAPPER -->
        <div class="col-12">
          <label class="form-label fw-semibold">รูปภาพประกอบข่าว (สัดส่วน 16:9 หรือ 4:3)</label>
          <div class="image-upload-wrapper is-landscape is-side-by-side"
               data-image-upload
               data-current-src="<?= !empty($editItem['image_path']) ? url($editItem['image_path']) : '' ?>">
            <div class="image-upload-preview">
              <?php if (!empty($editItem['image_path']) && file_exists(ROOT_PATH . '/' . $editItem['image_path'])): ?>
                <img id="newsPreviewImg" src="<?= url($editItem['image_path']) ?>" class="preview-img" alt="รูปประกอบข่าวปัจจุบัน">
                <span class="preview-badge badge-current">รูปปัจจุบัน</span>
              <?php else: ?>
                <div class="preview-placeholder">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                  <span>ยังไม่มีรูปภาพ</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="image-upload-dropzone" role="button" tabindex="0" aria-label="คลิกเพื่อเลือกไฟล์รูปภาพ หรือลากไฟล์มาวาง">
              <input type="file"
                     name="image"
                     id="input-news-image"
                     class="image-upload-input"
                     accept="image/jpeg,image/png,image/webp">
              <div class="dropzone-content">
                <div class="dropzone-icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                  </svg>
                </div>
                <div class="dropzone-text">
                  <strong>คลิกเพื่อเลือกรูปภาพประกอบข่าว</strong> หรือลากไฟล์มาวางที่นี่
                </div>
                <div class="dropzone-hint">
                  รองรับไฟล์ JPG, PNG หรือ WebP (ขนาดสูงสุด 5 MB)
                </div>
                <div class="dropzone-file-selected d-none">
                  <span class="file-prefix">เลือกแล้ว:</span>
                  <span class="file-name mono" title=""></span>
                  <span class="file-size mono"></span>
                  <button type="button" class="btn-clear-file" title="ล้างรูปที่เลือก" aria-label="ล้างรูปที่เลือก"><?= icon('x', 14) ?></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end align-items-center gap-2">
        <a href="<?= url('admin/news.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="button" class="btn btn-line d-inline-flex align-items-center gap-1" id="btnOpenNewsPreview" data-bs-toggle="modal" data-bs-target="#newsPreviewModal">
          <?= icon('eye', 16) ?> <span>ดูตัวอย่างหน้าจริง</span>
        </button>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

  <!-- FULL PREVIEW MODAL FOR NEWS -->
  <div class="modal fade" id="newsPreviewModal" tabindex="-1" aria-labelledby="newsPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center">
          <h5 class="modal-title" id="newsPreviewModalLabel">
            <?= icon('eye', 18) ?> แสดงตัวอย่างข่าวสารจริง
          </h5>
          <div class="d-flex align-items-center gap-3">
            <div class="preview-viewport-toggle">
              <button type="button" class="preview-viewport-btn is-active" id="btnNewsPreviewDesktop">
                Desktop
              </button>
              <button type="button" class="preview-viewport-btn" id="btnNewsPreviewMobile">
                Mobile
              </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
          </div>
        </div>

        <div class="modal-body bg-light p-3 p-md-4">
          <div class="preview-container-frame is-desktop" id="newsPreviewFrame">
            <article class="p-4 p-md-5 bg-white" style="max-width: 800px; margin: 0 auto;">
              <header class="mb-4">
                <span class="tag d-inline-block mb-2" id="modalNewsCat">หมวดหมู่</span>
                <h1 class="heading-editorial mb-3" id="modalNewsTitle" style="font-size: 2rem;">หัวข้อข่าว</h1>
                <div class="meta-line">
                  <span class="mono"><?= thai_date(date('Y-m-d')) ?></span>
                  <span class="dot"></span>
                  <span>สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล</span>
                </div>
              </header>

              <figure class="figure mb-4 w-100" id="modalNewsFigWrap">
                <div class="figure-img">
                  <img id="modalNewsImg" src="<?= !empty($editItem['image_path']) ? url($editItem['image_path']) : '' ?>" class="w-100" style="aspect-ratio: 16/9; object-fit: cover;" alt="">
                </div>
              </figure>

              <div class="lead-text fw-medium text-navy mb-3" id="modalNewsSummary"></div>
              <div class="article-body" id="modalNewsContent" style="font-size: 1rem; line-height: 1.45;"></div>
            </article>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">ปิดหน้าต่างตัวอย่าง</button>
        </div>
      </div>
    </div>
  </div>



<?php else: ?>
  <!-- ====================================================================
       NEWS LIST TABLE (PRODUCTIVITY RESTYLED)
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h3 class="mb-0">รายการข่าวสารทั้งหมด (<?= count($allNews) ?>)</h3>
      <div class="admin-search-wrap">
        <div class="admin-search-group">
          <span class="admin-search-prefix" aria-hidden="true"><?= icon('search', 14) ?></span>
          <input type="text"
                 id="newsSearchInput"
                 class="admin-search-input"
                 placeholder="ค้นหาข่าว หรือหมวดหมู่..."
                 autocomplete="off"
                 role="combobox"
                 aria-autocomplete="list"
                 aria-expanded="false"
                 aria-controls="newsAutocompleteList">
          <button type="button"
                  id="btnNewsClearSearch"
                  class="admin-search-clear"
                  title="ล้างคำค้นหา"
                  aria-label="ล้างคำค้นหา"
                  style="display: none;">
            <?= icon('close', 13) ?>
          </button>
        </div>
        <ul id="newsAutocompleteList" class="admin-autocomplete-dropdown" role="listbox" hidden></ul>
      </div>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 54px;">รูป</th>
            <th>หัวข้อข่าว</th>
            <th style="width: 140px;">หมวดหมู่</th>
            <th style="width: 130px;">วันที่</th>
            <th class="text-center" style="width: 90px;">สถานะเด่น</th>
            <th class="text-end" style="width: 130px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allNews)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีข่าวสารในระบบ</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allNews as $newsItem): ?>
              <tr class="news-list-row"
                  data-title="<?= e($newsItem['title']) ?>"
                  data-meta="<?= e($newsItem['category_name']) ?>">
                <td>
                  <?php if (!empty($newsItem['image_path']) && file_exists(ROOT_PATH . '/' . $newsItem['image_path'])): ?>
                    <img src="<?= url($newsItem['image_path']) ?>" class="table-thumb" alt="">
                  <?php else: ?>
                    <div class="table-thumb d-grid place-items-center text-muted" style="font-size: 11px;">ไม่มีรูป</div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold d-block text-navy" style="font-size: 14.5px;"><?= e($newsItem['title']) ?></span>
                  <span class="text-muted d-block text-truncate" style="max-width: 420px; font-size: 13px;">
                    <?= e($newsItem['summary']) ?>
                  </span>
                </td>
                <td>
                  <span class="tag" style="font-size: 12px;"><?= e($newsItem['category_name']) ?></span>
                </td>
                <td style="font-family: var(--font-body); font-size: 13px;">
                  <span class="text-navy"><?= thai_date($newsItem['published_at']) ?></span>
                </td>
                <td class="text-center">
                  <?php if ($newsItem['is_featured']): ?>
                    <span class="badge-active" style="font-size: 12px;">ข่าวเด่น</span>
                  <?php else: ?>
                    <span class="text-muted mono" style="font-size: 12px;">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('news?id=' . (int) $newsItem['id']) ?>"
                       target="_blank"
                       class="btn btn-action-icon btn-line"
                       title="ดูหน้าเว็บ">
                      <?= icon('external', 16) ?>
                    </a>
                    <a href="<?= url('admin/news.php?action=edit&id=' . (int) $newsItem['id']) ?>"
                       class="btn btn-action-icon btn-line"
                       title="แก้ไข">
                      <?= icon('pencil', 16) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/news.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $newsItem['id'] ?>">
                      <button type="submit"
                              class="btn btn-action-icon btn-danger-line"
                              title="ลบ"
                              data-confirm="ยืนยันการลบข่าวสารนี้?">
                        <?= icon('trash', 16) ?>
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
<?php endif; ?>

<script src="<?= url('assets/js/admin-search-autocomplete.js') ?>"></script>
<script src="<?= url('assets/js/admin-news.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
