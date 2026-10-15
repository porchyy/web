<?php
/**
 * Public News Autocomplete Search API
 * GET /api/news-search.php?q=...&cat=...
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/services/NewsService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = trim((string) ($_GET['q'] ?? ''));
$categorySlug = trim((string) ($_GET['cat'] ?? ''));
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;

if ($query === '') {
    echo json_encode(['success' => true, 'query' => '', 'count' => 0, 'items' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $items = NewsService::searchSuggestions($query, $categorySlug, $limit);
    echo json_encode([
        'success' => true,
        'query'   => $query,
        'count'   => count($items),
        'items'   => $items
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error occurred'], JSON_UNESCAPED_UNICODE);
}
