<?php
/**
 * NewsService - Data Access & Business Logic for News & Articles
 * 
 * Includes short-lived caching (60s TTL) and instant cache busting on changes.
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/CacheService.php';

class NewsService
{
    /** List views never render the article body, so skip the MEDIUMTEXT column. */
    private const LIST_COLUMNS = 'n.id, n.category_id, n.title, n.summary, n.image_path, n.is_featured, n.published_at, n.created_at';
    public static function getLatest(int $limit = 3): array
    {
        return CacheService::remember('news_latest_' . $limit, 60, function () use ($limit) {
            return q("
                SELECT " . self::LIST_COLUMNS . ", c.name as category_name, c.slug as category_slug
                FROM news n
                JOIN news_categories c ON n.category_id = c.id
                ORDER BY n.published_at DESC, n.id DESC
                LIMIT ?
            ", [$limit])->fetchAll();
        });
    }

    public static function getById(int $id): ?array
    {
        $res = q("
            SELECT n.*, c.name as category_name, c.slug as category_slug
            FROM news n
            JOIN news_categories c ON n.category_id = c.id
            WHERE n.id = ?
        ", [$id])->fetch();

        return $res ?: null;
    }

    public static function getCategories(): array
    {
        return CacheService::remember('news_categories_all', 120, function () {
            return q("
                SELECT c.*, COUNT(n.id) as news_count
                FROM news_categories c
                LEFT JOIN news n ON n.category_id = c.id
                GROUP BY c.id
                ORDER BY c.id ASC
            ")->fetchAll();
        });
    }

    public static function createCategory(string $name): array
    {
        $name = clean_text($name);
        if ($name === '') {
            throw new InvalidArgumentException('กรุณาระบุชื่อหมวดหมู่');
        }

        $slug = preg_replace('/[^\p{L}\p{M}\p{N}\-]+/u', '-', mb_strtolower($name, 'UTF-8'));
        $slug = trim((string) $slug, '-');
        $slug = mb_substr($slug, 0, 45, 'UTF-8');
        if ($slug === '') {
            $slug = 'cat-' . substr(md5($name), 0, 8);
        }

        $existing = q("SELECT id FROM news_categories WHERE name = ? OR slug = ?", [$name, $slug])->fetch();
        if ($existing) {
            throw new RuntimeException('หมวดหมู่นี้มีอยู่ในระบบแล้ว');
        }

        q("INSERT INTO news_categories (name, slug) VALUES (?, ?)", [$name, $slug]);
        $newId = (int) db()->lastInsertId();

        CacheService::forget('news_categories_all');

        return [
            'id'   => $newId,
            'name' => $name,
            'slug' => $slug,
        ];
    }

    public static function countAll(?string $catSlug = null, ?string $search = null): int
    {
        $where = "WHERE 1=1";
        $params = [];
        if (!empty($catSlug)) {
            $where .= " AND c.slug = ?";
            $params[] = $catSlug;
        }
        if (!empty($search)) {
            $where .= " AND (n.title LIKE ? OR n.summary LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        return (int) q("
            SELECT COUNT(n.id)
            FROM news n
            JOIN news_categories c ON n.category_id = c.id
            {$where}
        ", $params)->fetchColumn();
    }

    public static function getPaginated(int $page = 1, int $perPage = 9, ?string $catSlug = null, ?string $search = null): array
    {
        $page = max(1, $page);
        $total = self::countAll($catSlug, $search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];
        if (!empty($catSlug)) {
            $where .= " AND c.slug = ?";
            $params[] = $catSlug;
        }
        if (!empty($search)) {
            $where .= " AND (n.title LIKE ? OR n.summary LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $params[] = $perPage;
        $params[] = $offset;

        $items = q("
            SELECT " . self::LIST_COLUMNS . ", c.name as category_name, c.slug as category_slug
            FROM news n
            JOIN news_categories c ON n.category_id = c.id
            {$where}
            ORDER BY n.is_featured DESC, n.published_at DESC, n.id DESC
            LIMIT ? OFFSET ?
        ", $params)->fetchAll();

        return [
            'items'        => $items,
            'total'        => $total,
            'current_page' => $page,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage,
        ];
    }

    public static function searchSuggestions(string $query, ?string $catSlug = null, int $limit = 6): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $where = "WHERE n.title LIKE ?";
        $params = ["%{$query}%"];

        if (!empty($catSlug) && $catSlug !== 'all') {
            $where .= " AND c.slug = ?";
            $params[] = $catSlug;
        }

        $params[] = "{$query}%";
        $params[] = $limit;

        return q("
            SELECT n.id, n.title, c.name as category_name, c.slug as category_slug
            FROM news n
            JOIN news_categories c ON n.category_id = c.id
            {$where}
            ORDER BY 
                CASE WHEN n.title LIKE ? THEN 1 ELSE 2 END,
                n.published_at DESC, 
                n.id DESC
            LIMIT ?
        ", $params)->fetchAll();
    }


    public static function getAll(): array
    {
        return q("
            SELECT " . self::LIST_COLUMNS . ", c.name as category_name
            FROM news n
            JOIN news_categories c ON n.category_id = c.id
            ORDER BY n.published_at DESC, n.id DESC
        ")->fetchAll();
    }

    public static function save(array $data, ?string $newImage = null): int
    {
        $id          = !empty($data['id']) ? (int) $data['id'] : null;
        $categoryId  = (int) ($data['category_id'] ?? 1);
        $title       = trim((string) ($data['title'] ?? ''));
        $summary     = trim((string) ($data['summary'] ?? ''));
        $content     = clean_html((string) ($data['content'] ?? ''));
        $publishedAt = trim((string) ($data['published_at'] ?? '')) ?: date('Y-m-d');
        $isFeatured  = !empty($data['is_featured']) ? 1 : 0;

        if ($id) {
            if ($newImage) {
                $old = q("SELECT image_path FROM news WHERE id = ?", [$id])->fetch();
                if ($old) delete_upload($old['image_path']);

                q("
                    UPDATE news
                    SET category_id = ?, title = ?, summary = ?, content = ?, image_path = ?, is_featured = ?, published_at = ?
                    WHERE id = ?
                ", [$categoryId, $title, $summary, $content, $newImage, $isFeatured, $publishedAt, $id]);
            } else {
                q("
                    UPDATE news
                    SET category_id = ?, title = ?, summary = ?, content = ?, is_featured = ?, published_at = ?
                    WHERE id = ?
                ", [$categoryId, $title, $summary, $content, $isFeatured, $publishedAt, $id]);
            }
            $resultId = $id;
        } else {
            q("
                INSERT INTO news (category_id, title, summary, content, image_path, is_featured, published_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$categoryId, $title, $summary, $content, $newImage, $isFeatured, $publishedAt]);
            $resultId = (int) db()->lastInsertId();
        }

        CacheService::forgetPattern('news_');
        CacheService::forget('home_stats');

        return $resultId;
    }

    public static function delete(int $id): bool
    {
        delete_record_with_asset('news', $id);
        CacheService::forgetPattern('news_');
        CacheService::forget('home_stats');
        return true;
    }
}
