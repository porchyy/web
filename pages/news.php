<?php
/**
 * ข่าวสารและประกาศ - แผนกเทคโนโลยีธุรกิจดิจิทัล
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
            <?= clean_html($article['content']) ?>
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

include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>ข่าวสารและประกาศ</span>
    </p>
    <h1>ข่าวสารและประกาศ</h1>
    <p class="lead-text">
      ติดตามข่าวสารทางวิชาการ กิจกรรมพัฒนาทักษะวิชาชีพ ความเคลื่อนไหวภายในแผนก และผลงานความสำเร็จของนักศึกษา
    </p>
  </div>
</div>

<!-- ========================================================================
     CATEGORY FILTER CHIPS
     ======================================================================== -->
<section class="section" style="padding-top: var(--s-5); padding-bottom: var(--s-5); border-bottom: 1px solid var(--c-line);">
  <div class="container-xl">
    <div class="row align-items-center gy-3">
      <div class="col-12 col-md-7">
        <div class="chip-scroll" role="tablist" aria-label="กรองหมวดหมู่ข่าว">
          <a href="<?= url('news' . ($searchQuery ? '?q=' . urlencode($searchQuery) : '')) ?>" class="chip" <?= empty($catSlug) ? 'aria-current="page"' : '' ?>>
            <span>ทั้งหมด</span>
            <span class="count"><?= $totalCount ?></span>
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= url('news?cat=' . urlencode($cat['slug']) . ($searchQuery ? '&q=' . urlencode($searchQuery) : '')) ?>" class="chip" <?= $catSlug === $cat['slug'] ? 'aria-current="page"' : '' ?>>
              <span><?= e($cat['name']) ?></span>
              <span class="count"><?= (int) $cat['news_count'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-12 col-md-5">
        <form method="get" action="<?= url('news') ?>" class="d-flex align-items-center gap-2 justify-content-md-end m-0">
          <?php if ($catSlug): ?>
            <input type="hidden" name="cat" value="<?= e($catSlug) ?>">
          <?php endif; ?>
          <input type="search"
                 name="q"
                 class="form-control form-control-sm"
                 style="max-width: 260px;"
                 placeholder="ค้นหาข่าวสาร..."
                 value="<?= e($searchQuery) ?>">
          <button type="submit" class="btn btn-sm btn-navy" style="white-space: nowrap;">
            <span>ค้นหา</span>
          </button>
          <?php if ($searchQuery !== ''): ?>
            <a href="<?= url('news' . ($catSlug ? '?cat=' . urlencode($catSlug) : '')) ?>" class="btn btn-sm btn-line text-muted">ล้าง</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     NEWS CONTENT LIST
     ======================================================================== -->
<section class="section">
  <div class="container-xl">

    <?php if (empty($newsList)): ?>
      <div class="text-center py-5 text-muted">
        <?= icon('news', 40) ?>
        <p class="mt-3">ไม่พบข่าวสารในเงื่อนไขที่ค้นหา</p>
      </div>
    <?php else: ?>
      <ul class="news-list">
        <?php foreach ($newsList as $item): ?>
          <li>
            <a href="<?= url('news?id=' . $item['id']) ?>" class="news-row">
              <div>
                <div class="meta-line mb-1">
                  <span class="tag"><?= e($item['category_name']) ?></span>
                  <?php if ($item['is_featured']): ?>
                    <span class="badge-active">ข่าวเด่น</span>
                  <?php endif; ?>
                  <span class="dot"></span>
                  <span class="text-muted" style="font-size: 13px;"><?= thai_date($item['published_at']) ?></span>
                </div>
                <h3><?= e($item['title']) ?></h3>
                <p class="text-muted mb-0" style="font-size: .875rem;">
                  <?= e($item['summary']) ?>
                </p>
              </div>
              <div class="thumb">
                <?php if (!empty($item['image_path']) && file_exists(ROOT_PATH . '/' . $item['image_path'])): ?>
                  <img src="<?= url($item['image_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="monogram"><?= e(SITE_SHORT) ?></div>
                <?php endif; ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

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

<?php include __DIR__ . '/../includes/footer.php'; ?>
