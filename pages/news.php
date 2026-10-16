<?php
/**
 * ข่าวสารและประกาศ - สาขาเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/services/NewsService.php';

$activeNav = 'news';

send_cache_headers(60, 120);

// 1. Single Article Detail View
$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($articleId) {
    $article = NewsService::getById($articleId);

    if (!$article) {
        http_response_code(404);
        $pageTitle = 'ไม่พบเนื้อหา';
        include __DIR__ . '/../includes/header.php';
        echo '<div class="container-xl py-5 text-center">';
        echo '<h1>ไม่พบบทความข่าวที่ต้องการ</h1>';
        echo '<p class="text-muted mt-3">เนื้อหานี้อาจถูกลบหรือย้ายที่อยู่แล้ว</p>';
        echo '<a href="' . url('news') . '" class="btn btn-navy mt-4">กลับหน้ารวมข่าว</a>';
        echo '</div>';
        include __DIR__ . '/../includes/footer.php';
        exit;
    }

    $pageTitle = $article['title'];
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="page-head">
      <div class="container-xl">
        <a href="<?= url('news' . ($article['category_slug'] ? '?cat=' . $article['category_slug'] : '')) ?>" class="link-more mb-3 text-muted">
          <?= icon('arrow-left', 16) ?>
          <span>กลับหน้ารวมข่าว</span>
        </a>
        <div class="meta-line mb-2">
          <span class="tag"><?= e($article['category_name']) ?></span>
          <span class="dot"></span>
          <span><?= thai_date($article['published_at'], true) ?></span>
        </div>
        <h1 class="article-title mt-2"><?= e($article['title']) ?></h1>
      </div>
    </div>

    <section class="section">
      <div class="container-xl">
        <article class="article mx-auto">
          <?php if (!empty($article['image_path']) && file_exists(ROOT_PATH . '/' . $article['image_path'])): ?>
            <div class="marks mb-5">
              <figure class="figure w-100">
                <div class="figure-img" style="aspect-ratio: 16/9;">
                  <img src="<?= url($article['image_path']) ?>" alt="<?= e($article['title']) ?>">
                </div>
              </figure>
            </div>
          <?php endif; ?>

          <?php if (!empty($article['summary'])): ?>
            <p class="lead-text fw-bold text-navy mb-4" style="line-height: 1.8;">
              <?= nl2br(e($article['summary'])) ?>
            </p>
          <?php endif; ?>

          <div class="article-body">
            <?= $article['content'] ?>
          </div>

          <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
            <a href="<?= url('news') ?>" class="btn btn-line btn-sm">
              <?= icon('arrow-left', 14) ?> กลับหน้ารวมข่าว
            </a>
            <span class="text-muted" style="font-size: 13px;">เผยแพร่เมื่อ: <?= thai_date($article['published_at']) ?></span>
          </div>
        </article>
      </div>
    </section>

    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// 2. News Listing View with Category, Search and Pagination
$pageTitle   = 'ข่าวสารและประกาศ';
$catSlug     = filter_input(INPUT_GET, 'cat', FILTER_DEFAULT) ?: '';
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

$categories = NewsService::getCategories();
$totalCount = NewsService::countAll();

// Fetch paginated news
$pagination = NewsService::getPaginated($currentPage, 9, $catSlug, $searchQuery);
$newsList   = $pagination['items'];
$filteredTotal = $pagination['total'];

// Resolve active category name for accessibility & results status
$activeCategoryName = '';
if ($catSlug !== '') {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $catSlug) {
            $activeCategoryName = $cat['name'];
            break;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-head news-page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>ข่าวสารและประกาศ</span>
    </p>
    <h1>ข่าวสารและประกาศ</h1>
    <p class="lead-text">
      ติดตามข่าวสารทางวิชาการ กิจกรรมพัฒนาทักษะวิชาชีพ ความเคลื่อนไหวภายในสาขา และผลงานความสำเร็จของนักศึกษา
    </p>
  </div>
</div>

<!-- ========================================================================
     NEWS TOOLBAR & FILTER SECTION (Gestalt Common Region)
     ======================================================================== -->
<section class="news-toolbar-section">
  <div class="container-xl">
    <div class="news-toolbar" role="region" aria-label="เครื่องมือกรองและค้นหาข่าวสาร">
      <form method="get" action="<?= url('news') ?>" class="news-toolbar-form" role="search" id="news-search-form" data-endpoint="<?= url('api/news-search.php') ?>">
        <div class="news-toolbar-filters">
          <div class="news-category-select-wrap">
            <label for="news-search-cat" class="visually-hidden">เลือกหมวดหมู่ข่าวสาร</label>
            <select name="cat"
                    id="news-search-cat"
                    class="form-select news-category-select"
                    onchange="this.form.submit()"
                    aria-label="เลือกหมวดหมู่ข่าวสาร">
              <option value="" <?= empty($catSlug) ? 'selected' : '' ?>>
                ทุกหมวดหมู่ (<?= $totalCount ?>)
              </option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['slug']) ?>" <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?> (<?= (int) $cat['news_count'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="search-input-group">
            <span class="search-icon-prefix" aria-hidden="true">
              <?= icon('search', 16) ?>
            </span>
            <label for="news-search-input" class="visually-hidden">ค้นหาข่าวสารหรือคำสำคัญ</label>
            <input type="text"
                   id="news-search-input"
                   name="q"
                   class="form-control form-control-sm news-search-input"
                   placeholder="ค้นหาชื่อข่าวหรือคำสำคัญ..."
                   value="<?= e($searchQuery) ?>"
                   autocomplete="off"
                   role="combobox"
                   aria-autocomplete="list"
                   aria-expanded="false"
                   aria-controls="news-autocomplete-list"
                   aria-haspopup="listbox">
            <?php if ($searchQuery !== ''): ?>
              <a href="<?= url('news' . ($catSlug ? '?cat=' . urlencode($catSlug) : '')) ?>"
                 class="btn-clear-search"
                 title="ล้างคำค้นหา"
                 aria-label="ล้างคำค้นหา">
                <?= icon('close', 14) ?>
              </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-sm btn-navy btn-search-submit" aria-label="กดเพื่อค้นหา">
              <span>ค้นหา</span>
            </button>
          </div>
        </div>

        <ul id="news-autocomplete-list"
            class="news-autocomplete-dropdown"
            role="listbox"
            aria-label="รายการข่าวที่แนะนำ"
            hidden>
        </ul>
      </form>
    </div>

    <!-- Active Filter & Results Count Bar -->
    <div class="news-status-bar" role="status" aria-live="polite">
      <div class="news-status-text">
        <?php if ($searchQuery !== '' && $activeCategoryName !== ''): ?>
          <span>ผลการค้นหา "<strong><?= e($searchQuery) ?></strong>" ในหมวด <strong><?= e($activeCategoryName) ?></strong> (พบ <strong><?= $filteredTotal ?></strong> รายการ)</span>
        <?php elseif ($searchQuery !== ''): ?>
          <span>ผลการค้นหา "<strong><?= e($searchQuery) ?></strong>" (พบ <strong><?= $filteredTotal ?></strong> รายการ)</span>
        <?php elseif ($activeCategoryName !== ''): ?>
          <span>หมวดหมู่ <strong><?= e($activeCategoryName) ?></strong> (พบ <strong><?= $filteredTotal ?></strong> รายการ)</span>
        <?php else: ?>
          <span>แสดงข่าวสารทั้งหมด <strong><?= $filteredTotal ?></strong> รายการ</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     NEWS CONTENT LIST (Layer-Cake Scannable Cards)
     ======================================================================== -->
<section class="news-content-section">
  <div class="container-xl">

    <?php if (empty($newsList)): ?>
      <div class="news-empty-state">
        <div class="empty-icon" aria-hidden="true">
          <?= icon('news', 48) ?>
        </div>
        <h2 class="empty-title">ไม่พบข่าวสารในเงื่อนไขที่เลือก</h2>
        <p class="empty-desc">
          <?php if ($searchQuery !== ''): ?>
            ลองตรวจสอบคำสะกด หรือค้นหาด้วยคำสำคัญอื่น
          <?php else: ?>
            ยังไม่มีเนื้อหาข่าวสารในหมวดหมู่นี้ในขณะนี้
          <?php endif; ?>
        </p>
        <div class="mt-3">
          <a href="<?= url('news') ?>" class="btn btn-navy btn-sm">
            <?= icon('refresh', 14) ?>
            <span>กลับไปหน้าข่าวสารทั้งหมด</span>
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="news-grid" role="feed" aria-label="รายการข่าวสารและประกาศ">
        <?php foreach ($newsList as $item): ?>
          <article class="news-card">
            <a href="<?= url('news?id=' . $item['id']) ?>" class="news-card-link">
              <div class="news-card-content">
                <div class="news-meta">
                  <span class="news-cat-tag"><?= e($item['category_name']) ?></span>
                  <?php if ($item['is_featured']): ?>
                    <span class="news-badge-featured">
                      <?= icon('flag', 12) ?> ข่าวเด่น
                    </span>
                  <?php endif; ?>
                  <span class="news-meta-dot" aria-hidden="true">•</span>
                  <time class="news-date" datetime="<?= e($item['published_at']) ?>">
                    <?= icon('calendar', 13) ?>
                    <span><?= thai_date($item['published_at']) ?></span>
                  </time>
                </div>
                
                <h2 class="news-title"><?= e($item['title']) ?></h2>
                
                <p class="news-summary">
                  <?= e($item['summary']) ?>
                </p>
              </div>

              <div class="news-card-thumb">
                <?php if (!empty($item['image_path']) && file_exists(ROOT_PATH . '/' . $item['image_path'])): ?>
                  <img src="<?= url($item['image_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy" width="160" height="120">
                <?php else: ?>
                  <div class="news-monogram" aria-hidden="true"><?= e(SITE_SHORT) ?></div>
                <?php endif; ?>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php
      $paginationParams = [];
      if ($catSlug) $paginationParams['cat'] = $catSlug;
      if ($searchQuery) $paginationParams['q'] = $searchQuery;
      echo render_pagination($pagination['current_page'], $pagination['total_pages'], url('news'), $paginationParams);
      ?>
    <?php endif; ?>

  </div>
</section>

<script src="<?= url('assets/js/page-news.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/page-news.js') ? filemtime(ROOT_PATH . '/assets/js/page-news.js') : '1' ?>"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
