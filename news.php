<?php
/**
 * 03 / NEWS — ข่าวสารและประกาศ แผนกเทคโนโลยีธุรกิจดิจิทัล
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

$activeNav = 'news';

// 1. Single Article Detail View
$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($articleId) {
    $article = q("
      SELECT n.*, c.name as category_name, c.slug as category_slug
      FROM news n
      JOIN news_categories c ON n.category_id = c.id
      WHERE n.id = ?
    ", [$articleId])->fetch();

    if (!$article) {
        http_response_code(404);
        $pageTitle = 'ไม่พบเนื้อหา';
        include __DIR__ . '/includes/header.php';
        echo '<div class="container-xl py-5 text-center">';
        echo '<p class="eyebrow eyebrow-muted">ERROR 404</p>';
        echo '<h1>ไม่พบบทความข่าวที่ต้องการ</h1>';
        echo '<p class="text-muted mt-3">เนื้อหานี้อาจถูกลบหรือย้ายที่อยู่แล้ว</p>';
        echo '<a href="' . url('news.php') . '" class="btn btn-navy mt-4">กลับหน้ารวมข่าว</a>';
        echo '</div>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }

    $pageTitle = $article['title'];
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="page-head">
      <div class="container-xl">
        <a href="<?= url('news.php' . ($article['category_slug'] ? '?cat=' . $article['category_slug'] : '')) ?>" class="link-more mb-3 text-muted">
          <?= icon('arrow-left', 16) ?>
          <span>กลับหน้ารวมข่าว</span>
        </a>
        <div class="meta-line mb-2">
          <span class="tag"><?= e($article['category_name']) ?></span>
          <span class="dot"></span>
          <span class="mono"><?= thai_date($article['published_at'], true) ?></span>
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
                <figcaption class="figcap">
                  <span>ภาพประกอบข่าวประชาสัมพันธ์ แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล</span>
                  <span class="mono">FIG <?= sprintf('%02d', $article['id']) ?></span>
                </figcaption>
              </figure>
            </div>
          <?php endif; ?>

          <?php if (!empty($article['summary'])): ?>
            <p class="lead-text fw-bold text-navy mb-4" style="line-height: 1.8;">
              <?= nl2br(e($article['summary'])) ?>
            </p>
          <?php endif; ?>

          <div class="article-body">
            <?= $article['content'] /* HTML allowed from admin editor */ ?>
          </div>

          <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
            <a href="<?= url('news.php') ?>" class="btn btn-line btn-sm">
              <?= icon('arrow-left', 14) ?> กลับหน้ารวมข่าว
            </a>
            <span class="mono text-muted" style="font-size: 11px;">DBT NEWS DESK // <?= mono_date($article['published_at']) ?></span>
          </div>
        </article>
      </div>
    </section>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// 2. News Listing View with Category and Search Filter
$pageTitle   = 'ข่าวสารและประกาศ';
$catSlug     = filter_input(INPUT_GET, 'cat', FILTER_DEFAULT) ?: '';
$searchQuery = trim((string) ($_GET['q'] ?? ''));

// Fetch all categories with counts
$categories = q("
  SELECT c.*, COUNT(n.id) as news_count
  FROM news_categories c
  LEFT JOIN news n ON n.category_id = c.id
  GROUP BY c.id
  ORDER BY c.id ASC
")->fetchAll();

$totalCount = (int) q("SELECT COUNT(*) FROM news")->fetchColumn();

// Fetch news articles based on filter
$params = [];
$whereClause = "WHERE 1=1";
if ($catSlug) {
    $whereClause .= " AND c.slug = ?";
    $params[] = $catSlug;
}
if ($searchQuery !== '') {
    $whereClause .= " AND (n.title LIKE ? OR n.summary LIKE ? OR n.content LIKE ?)";
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$newsList = q("
  SELECT n.*, c.name as category_name, c.slug as category_slug
  FROM news n
  JOIN news_categories c ON n.category_id = c.id
  $whereClause
  ORDER BY n.is_featured DESC, n.published_at DESC, n.id DESC
", $params)->fetchAll();

// First article could be featured if viewing all without search
$featuredArticle = null;
$regularList = $newsList;
if (empty($catSlug) && empty($searchQuery) && !empty($newsList)) {
    $featuredArticle = array_shift($regularList);
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="container-xl">
    <p class="eyebrow">
      <span>03</span>
      <span class="sep">/</span>
      <span>NEWS & UPDATES</span>
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
          <a href="<?= url('news.php' . ($searchQuery ? '?q=' . urlencode($searchQuery) : '')) ?>" class="chip" <?= empty($catSlug) ? 'aria-current="page"' : '' ?>>
            <span>ทั้งหมด</span>
            <span class="count"><?= $totalCount ?></span>
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= url('news.php?cat=' . urlencode($cat['slug']) . ($searchQuery ? '&q=' . urlencode($searchQuery) : '')) ?>" class="chip" <?= $catSlug === $cat['slug'] ? 'aria-current="page"' : '' ?>>
              <span><?= e($cat['name']) ?></span>
              <span class="count"><?= (int) $cat['news_count'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-12 col-md-5">
        <form method="get" action="<?= url('news.php') ?>" class="d-flex align-items-center gap-2 justify-content-md-end m-0">
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
            <a href="<?= url('news.php' . ($catSlug ? '?cat=' . urlencode($catSlug) : '')) ?>" class="btn btn-sm btn-line text-muted">ล้าง</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================================
     NEWS CONTENT (Featured + List Rows)
     ======================================================================== -->
<section class="section">
  <div class="container-xl">

    <?php if ($featuredArticle): ?>
      <div class="mb-5 pb-5 border-bottom">
        <a href="<?= url('news.php?id=' . $featuredArticle['id']) ?>" class="news-feature">
          <div class="figure-img">
            <?php if (!empty($featuredArticle['image_path']) && file_exists(ROOT_PATH . '/' . $featuredArticle['image_path'])): ?>
              <img src="<?= url($featuredArticle['image_path']) ?>" alt="<?= e($featuredArticle['title']) ?>">
            <?php else: ?>
              <div class="monogram monogram-lg" style="aspect-ratio: 16/10;">FEATURED</div>
            <?php endif; ?>
          </div>
          <div>
            <div class="meta-line">
              <span class="tag"><?= e($featuredArticle['category_name']) ?></span>
              <span class="dot"></span>
              <span class="mono"><?= thai_date($featuredArticle['published_at']) ?></span>
            </div>
            <h2><?= e($featuredArticle['title']) ?></h2>
            <p><?= e($featuredArticle['summary']) ?></p>
            <span class="link-more mt-3">
              <span>อ่านต่อ</span>
              <?= icon('arrow-right', 16) ?>
            </span>
          </div>
        </a>
      </div>
    <?php endif; ?>

    <?php if (empty($newsList)): ?>
      <div class="text-center py-5 text-muted">
        <?= icon('news', 40) ?>
        <p class="mt-3">ยังไม่มีข่าวสารในหมวดหมู่นี้</p>
      </div>
    <?php else: ?>
      <ul class="news-list">
        <?php foreach ($regularList as $item): ?>
          <li>
            <a href="<?= url('news.php?id=' . $item['id']) ?>" class="news-row">
              <div>
                <div class="meta-line mb-1">
                  <span class="tag"><?= e($item['category_name']) ?></span>
                  <span class="dot"></span>
                  <span class="mono"><?= mono_date($item['published_at']) ?></span>
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
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
