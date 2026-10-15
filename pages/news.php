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
      ติดตามข่าวสารทางวิชาการ กิจกรรมพัฒนาทักษะวิชาชีพ ความเคลื่อนไหวภายในแผนก และผลงานความสำเร็จของนักศึกษา
    </p>
  </div>
</div>

<!-- ========================================================================
     NEWS TOOLBAR & FILTER SECTION (Gestalt Common Region)
     ======================================================================== -->
<section class="news-toolbar-section">
  <div class="container-xl">
    <div class="news-toolbar" role="region" aria-label="เครื่องมือกรองและค้นหาข่าวสาร">
      <!-- Category Filter Chips (Single-Row on Desktop, Scroll on Mobile) -->
      <nav class="news-categories-nav" aria-label="หมวดหมู่ข่าวสาร">
        <div class="news-chip-scroll" role="tablist">
          <a href="<?= url('news' . ($searchQuery ? '?q=' . urlencode($searchQuery) : '')) ?>"
             class="chip"
             <?= empty($catSlug) ? 'aria-current="page"' : '' ?>
             role="tab"
             aria-selected="<?= empty($catSlug) ? 'true' : 'false' ?>"
             aria-label="ข่าวสารทั้งหมด จำนวน <?= $totalCount ?> รายการ">
            <span>ทั้งหมด</span>
            <span class="count"><?= $totalCount ?></span>
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= url('news?cat=' . urlencode($cat['slug']) . ($searchQuery ? '&q=' . urlencode($searchQuery) : '')) ?>"
               class="chip"
               <?= $catSlug === $cat['slug'] ? 'aria-current="page"' : '' ?>
               role="tab"
               aria-selected="<?= $catSlug === $cat['slug'] ? 'true' : 'false' ?>"
               aria-label="หมวด <?= e($cat['name']) ?> จำนวน <?= (int) $cat['news_count'] ?> รายการ">
              <span><?= e($cat['name']) ?></span>
              <span class="count"><?= (int) $cat['news_count'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </nav>

      <!-- Integrated Search Form -->
      <form method="get" action="<?= url('news') ?>" class="news-search-form" role="search" id="news-search-form">
        <?php if ($catSlug): ?>
          <input type="hidden" name="cat" value="<?= e($catSlug) ?>" id="news-search-cat">
        <?php endif; ?>
        <label for="news-search-input" class="visually-hidden">ค้นหาข่าวสารหรือคำสำคัญ</label>
        <div class="search-input-group">
          <span class="search-icon-prefix" aria-hidden="true">
            <?= icon('search', 16) ?>
          </span>
          <input type="search"
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

      <?php if ($searchQuery !== '' || $catSlug !== ''): ?>
        <a href="<?= url('news') ?>" class="btn-reset-filters" aria-label="ล้างตัวกรองและแสดงข่าวสารทั้งหมด">
          <?= icon('refresh', 13) ?>
          <span>แสดงทั้งหมด</span>
        </a>
      <?php endif; ?>
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

<script>
(function() {
  const form = document.getElementById('news-search-form');
  const input = document.getElementById('news-search-input');
  const list = document.getElementById('news-autocomplete-list');
  const catInput = document.getElementById('news-search-cat');
  if (!form || !input || !list) return;

  const endpoint = <?= json_encode(url('api/news-search.php')) ?>;
  let activeIndex = -1;
  let debounceTimer = null;
  let abortCtrl = null;
  let currentItems = [];

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function highlightMatches(text, query) {
    if (!query) return escapeHtml(text);
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const parts = text.split(new RegExp(`(${escaped})`, 'gi'));
    return parts.map(p => {
      if (p.toLowerCase() === query.toLowerCase()) {
        return `<mark class="sugg-match">${escapeHtml(p)}</mark>`;
      }
      return escapeHtml(p);
    }).join('');
  }

  function closeDropdown() {
    list.hidden = true;
    list.innerHTML = '';
    activeIndex = -1;
    currentItems = [];
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
  }

  function openDropdown() {
    if (currentItems.length > 0) {
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }
  }

  function updateActiveOption() {
    const options = list.querySelectorAll('.news-suggest-item');
    options.forEach((opt, idx) => {
      const isActive = idx === activeIndex;
      opt.classList.toggle('is-active', isActive);
      opt.setAttribute('aria-selected', isActive ? 'true' : 'false');
      if (isActive) {
        input.setAttribute('aria-activedescendant', opt.id);
        opt.scrollIntoView({ block: 'nearest' });
      }
    });
    if (activeIndex === -1) {
      input.removeAttribute('aria-activedescendant');
    }
  }

  function selectItem(title) {
    input.value = title;
    closeDropdown();
    form.submit();
  }

  function renderSuggestions(items, query) {
    currentItems = items;
    activeIndex = -1;
    list.innerHTML = '';

    if (!items || items.length === 0) {
      const empty = document.createElement('li');
      empty.className = 'news-suggest-empty';
      empty.textContent = 'ไม่พบข่าวที่ตรงกับคำค้นหา';
      list.appendChild(empty);
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      return;
    }

    items.forEach((item, idx) => {
      const li = document.createElement('li');
      li.id = 'sugg-opt-' + idx;
      li.className = 'news-suggest-item';
      li.setAttribute('role', 'option');
      li.setAttribute('aria-selected', 'false');
      li.dataset.title = item.title;

      const titleSpan = document.createElement('span');
      titleSpan.className = 'news-suggest-title';
      titleSpan.innerHTML = highlightMatches(item.title, query);

      const catSpan = document.createElement('span');
      catSpan.className = 'news-suggest-cat';
      catSpan.textContent = item.category_name;

      li.appendChild(titleSpan);
      li.appendChild(catSpan);

      li.addEventListener('mousedown', function(e) {
        e.preventDefault();
      });

      li.addEventListener('click', function() {
        selectItem(item.title);
      });

      list.appendChild(li);
    });

    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  }

  function fetchSuggestions(query) {
    if (abortCtrl) {
      abortCtrl.abort();
    }
    abortCtrl = new AbortController();

    const cat = catInput ? catInput.value : '';
    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('q', query);
    if (cat) {
      url.searchParams.set('cat', cat);
    }

    fetch(url.toString(), {
      signal: abortCtrl.signal,
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(data => {
        if (data && data.success) {
          renderSuggestions(data.items, query);
        } else {
          closeDropdown();
        }
      })
      .catch(err => {
        if (err.name !== 'AbortError') {
          closeDropdown();
        }
      });
  }

  input.addEventListener('input', function() {
    const val = this.value.trim();
    clearTimeout(debounceTimer);

    if (val.length < 1) {
      closeDropdown();
      return;
    }

    debounceTimer = setTimeout(() => {
      fetchSuggestions(val);
    }, 200);
  });

  input.addEventListener('keydown', function(e) {
    const options = list.querySelectorAll('.news-suggest-item');
    if (!options.length || list.hidden) {
      if (e.key === 'ArrowDown' && input.value.trim().length >= 1) {
        fetchSuggestions(input.value.trim());
      }
      return;
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = (activeIndex + 1) % options.length;
      updateActiveOption();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = activeIndex <= 0 ? options.length - 1 : activeIndex - 1;
      updateActiveOption();
    } else if (e.key === 'Enter') {
      if (activeIndex >= 0 && options[activeIndex]) {
        e.preventDefault();
        selectItem(options[activeIndex].dataset.title);
      }
    } else if (e.key === 'Escape') {
      closeDropdown();
    }
  });

  input.addEventListener('focus', function() {
    if (currentItems.length > 0) {
      openDropdown();
    }
  });

  document.addEventListener('click', function(e) {
    if (!form.contains(e.target)) {
      closeDropdown();
    }
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
