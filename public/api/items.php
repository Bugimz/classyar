<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
    $db = api_db();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = (int) ($_GET['per_page'] ?? 24);
    $perPage = max(6, min(100, $perPage));
    $offset = ($page - 1) * $perPage;

    $search = trim((string) ($_GET['search'] ?? ''));
    $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
    $sort = (string) ($_GET['sort'] ?? 'name');
    $sort = in_array($sort, ['name', 'price_desc', 'price_asc'], true) ? $sort : 'name';

    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(i.name_fa LIKE :search OR i.name_en LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    if ($categoryId > 0) {
        $conditions[] = 'i.category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);

    $countStmt = $db->prepare('SELECT COUNT(*) FROM items i' . $whereSql);
    foreach ($params as $key => $value) {
        $paramType = $key === ':category_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
        $countStmt->bindValue($key, $value, $paramType);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $orderClause = 'ORDER BY i.name_fa ASC, i.id ASC';
    if ($sort === 'price_desc') {
        $orderClause = 'ORDER BY i.gold_cost DESC, i.name_fa ASC';
    } elseif ($sort === 'price_asc') {
        $orderClause = 'ORDER BY i.gold_cost ASC, i.name_fa ASC';
    }

    $sql = 'SELECT i.*, c.name_fa AS category_name_fa, c.name_en AS category_name_en
            FROM items i
            LEFT JOIN item_categories c ON c.id = i.category_id'
        . $whereSql . ' ' . $orderClause . ' LIMIT :limit OFFSET :offset';

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $paramType = $key === ':category_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $paramType);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $items = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $items[] = api_transform_item_summary($row);
    }

    $meta = api_paginate_meta($total, $page, $perPage);
    $meta['filters'] = [
        'search' => $search,
        'category_id' => $categoryId,
        'sort' => $sort,
    ];

    api_success($items, $meta);
} catch (Throwable $e) {
    api_error('خطا در واکشی لیست آیتم‌ها رخ داد.', 500);
}
