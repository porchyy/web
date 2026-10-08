<?php
/**
 * Admin — จัดการข่าวสารและประกาศ
 */
$adminNav = 'news';
$pageTitle = 'จัดการข่าวสารและประกาศ';

require_once __DIR__ . '/../includes/admin-header.php';

$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT) ?: 'list';
$newsId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error  = null;

// Categories for dropdown
$categories = q("SELECT * FROM news_categories ORDER BY id ASC")->fetchAll();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $subAction = filter_input(INPUT_POST, 'sub_action', FILTER_DEFAULT);

    if ($subAction === 'delete') {
        $delId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($delId) {
            delete_record_with_asset('news', $delId);
            flash('ลบข่าวสารเรียบร้อยแล้ว');
        }
        redirect('admin/news.php');
    }

    if ($subAction === 'save') {
        $editId      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $title       = trim((string) ($_POST['title'] ?? ''));
        $summary     = trim((string) ($_POST['summary'] ?? ''));
        $content     = trim((string) ($_POST['content'] ?? ''));
        $publishedAt = trim((string) ($_POST['published_at'] ?? '')) ?: date('Y-m-d');
        $isFeatured  = !empty($_POST['is_featured']) ? 1 : 0;

        if ($title === '' || !$categoryId || $content === '') {
            $error = 'กรุณากรอกหัวข้อข่าว หมวดหมู่ และเนื้อหาให้ครบถ้วน';
        } else {
            try {
                $newImage = upload_image('image');

                if ($editId) {
                    // Update
                    if ($newImage) {
                        $old = q("SELECT image_path FROM news WHERE id = ?", [$editId])->fetch();
                        if ($old) delete_upload($old['image_path']);
                        q("
                          UPDATE news
                          SET category_id = ?, title = ?, summary = ?, content = ?, image_path = ?, is_featured = ?, published_at = ?
                          WHERE id = ?
                        ", [$categoryId, $title, $summary, $content, $newImage, $isFeatured, $publishedAt, $editId]);
                    } else {
                        q("
                          UPDATE news
                          SET category_id = ?, title = ?, summary = ?, content = ?, is_featured = ?, published_at = ?
                          WHERE id = ?
                        ", [$categoryId, $title, $summary, $content, $isFeatured, $publishedAt, $editId]);
                    }
                    flash('แก้ไขข่าวสารเรียบร้อยแล้ว');
                } else {
                    // Insert
                    q("
                      INSERT INTO news (category_id, title, summary, content, image_path, is_featured, published_at)
                      VALUES (?, ?, ?, ?, ?, ?, ?)
                    ", [$categoryId, $title, $summary, $content, $newImage, $isFeatured, $publishedAt]);
                    flash('เพิ่มข่าวสารใหม่เรียบร้อยแล้ว');
                }
                redirect('admin/news.php');
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch record for editing
$editItem = null;
if ($action === 'edit' && $newsId) {
    $editItem = q("SELECT * FROM news WHERE id = ?", [$newsId])->fetch();
}

// Fetch list
$allNews = q("
  SELECT n.*, c.name as category_name
  FROM news n
  JOIN news_categories c ON n.category_id = c.id
  ORDER BY n.published_at DESC, n.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <p class="eyebrow eyebrow-muted mb-1">MODULE // NEWS & ARTICLES</p>
    <h1 style="font-size: 1.75rem; margin: 0;">จัดการข่าวสารและประกาศ</h1>
  </div>
  <?php if ($action === 'list'): ?>
    <a href="<?= url('admin/news.php?action=new') ?>" class="btn btn-navy">
      <?= icon('plus', 16) ?>
      <span>เพิ่มข่าวสารใหม่</span>
    </a>
  <?php else: ?>
    <a href="<?= url('admin/news.php') ?>" class="btn btn-line">
      <?= icon('arrow-left', 16) ?>
      <span>กลับหน้ารายการข่าว</span>
    </a>
  <?php endif; ?>
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
      <h3><?= $action === 'edit' ? 'แก้ไขข่าวสาร #' . $editItem['id'] : 'สร้างข่าวสารใหม่' ?></h3>
    </div>

    <form method="POST" action="<?= url('admin/news.php') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="sub_action" value="save">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-8">
          <label for="title" class="form-label">หัวข้อข่าว <span class="req">*</span></label>
          <input type="text"
                 name="title"
                 id="title"
                 class="form-control"
                 value="<?= e($_POST['title'] ?? $editItem['title'] ?? '') ?>"
                 required>
        </div>

        <div class="col-12 col-md-4">
          <label for="category_id" class="form-label">หมวดหมู่ <span class="req">*</span></label>
          <select name="category_id" id="category_id" class="form-select" required>
            <option value="">-- เลือกหมวดหมู่ --</option>
            <?php foreach ($categories as $c): ?>
              <?php
              $selected = (int) ($_POST['category_id'] ?? $editItem['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '';
              ?>
              <option value="<?= $c['id'] ?>" <?= $selected ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label for="published_at" class="form-label">วันที่เผยแพร่</label>
          <input type="date"
                 name="published_at"
                 id="published_at"
                 class="form-control mono"
                 value="<?= e($_POST['published_at'] ?? $editItem['published_at'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="col-12 col-md-8 d-flex align-items-center pt-md-4">
          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   name="is_featured"
                   value="1"
                   id="is_featured"
                   <?= !empty($_POST['is_featured'] ?? $editItem['is_featured'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-navy" for="is_featured">
              ปักหมุดเป็นข่าวเด่น (Featured News)
            </label>
          </div>
        </div>

        <div class="col-12">
          <label for="summary" class="form-label">บทสรุปย่อ (Summary Snippet)</label>
          <textarea name="summary"
                    id="summary"
                    rows="2"
                    class="form-control"><?= e($_POST['summary'] ?? $editItem['summary'] ?? '') ?></textarea>
          <div class="form-text">ข้อความเกริ่นนำที่จะแสดงในการ์ดรายการข่าว</div>
        </div>

        <div class="col-12">
          <label for="content" class="form-label">เนื้อหาข่าวฉบับเต็ม (Full HTML/Text) <span class="req">*</span></label>
          <textarea name="content"
                    id="content"
                    rows="8"
                    class="form-control font-monospace"
                    required><?= e($_POST['content'] ?? $editItem['content'] ?? '') ?></textarea>
          <div class="form-text">สามารถใส่แท็ก HTML เช่น &lt;p&gt;, &lt;b&gt;, &lt;ul&gt;, &lt;li&gt; ได้ตามต้องการ</div>
        </div>

        <div class="col-12 col-md-6">
          <label for="image" class="form-label">รูปภาพประกอบข่าว</label>
          <input type="file"
                 name="image"
                 id="image"
                 class="form-control"
                 accept="image/jpeg,image/png,image/webp">
          <?php if (!empty($editItem['image_path'])): ?>
            <div class="mt-2 text-muted" style="font-size: 12px;">
              รูปเดิม: <code><?= e($editItem['image_path']) ?></code>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('admin/news.php') ?>" class="btn btn-line">ยกเลิก</a>
        <button type="submit" class="btn btn-navy">
          <?= icon('check', 16) ?>
          <span>บันทึกข้อมูล</span>
        </button>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- ====================================================================
       NEWS LIST TABLE
       ==================================================================== -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>รายการข่าวสารทั้งหมด (<?= count($allNews) ?>)</h3>
    </div>

    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 50px;">รูป</th>
            <th>หัวข้อข่าว</th>
            <th>หมวดหมู่</th>
            <th>วันที่</th>
            <th class="text-center">เด่น</th>
            <th class="text-end" style="width: 140px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allNews)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีข่าวสารในระบบ</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allNews as $n): ?>
              <tr>
                <td>
                  <?php if (!empty($n['image_path']) && file_exists(ROOT_PATH . '/' . $n['image_path'])): ?>
                    <img src="<?= url($n['image_path']) ?>" class="table-thumb" alt="">
                  <?php else: ?>
                    <div class="table-thumb d-grid place-items-center text-muted mono" style="font-size: 10px;">NO IMG</div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold d-block"><?= e($n['title']) ?></span>
                  <span class="text-muted d-block text-truncate" style="max-width: 380px; font-size: 12px;">
                    <?= e($n['summary']) ?>
                  </span>
                </td>
                <td>
                  <span class="tag"><?= e($n['category_name']) ?></span>
                </td>
                <td class="mono" style="font-size: 12px;">
                  <?= mono_date($n['published_at']) ?>
                </td>
                <td class="text-center">
                  <?php if ($n['is_featured']): ?>
                    <span class="badge-soon" style="font-size: 10px;">FEATURED</span>
                  <?php else: ?>
                    <span class="text-muted mono">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="<?= url('news.php?id=' . $n['id']) ?>" target="_blank" class="btn btn-sm btn-line py-1 px-2" title="ดูหน้าเว็บ">
                      <?= icon('external', 13) ?>
                    </a>
                    <a href="<?= url('admin/news.php?action=edit&id=' . $n['id']) ?>" class="btn btn-sm btn-line py-1 px-2" title="แก้ไข">
                      <?= icon('pencil', 13) ?>
                    </a>
                    <form method="POST" action="<?= url('admin/news.php') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="sub_action" value="delete">
                      <input type="hidden" name="id" value="<?= $n['id'] ?>">
                      <button type="submit"
                              class="btn btn-sm btn-danger-line py-1 px-2"
                              title="ลบ"
                              data-confirm="ยืนยันการลบข่าวสารนี้?">
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
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
