<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$slug = trim((string) ($_GET['slug'] ?? ''));

if ($itemId <= 0 && $slug === '') {
    api_error('شناسه یا اسلاگ آیتم الزامی است.', 400);
}

try {
    $db = api_db();

    $conditions = [];
    $params = [];
    if ($itemId > 0) {
        $conditions[] = 'i.id = :id';
        $params[':id'] = $itemId;
    }
    if ($slug !== '') {
        $conditions[] = 'i.slug = :slug';
        $params[':slug'] = $slug;
    }

    $whereSql = ' WHERE ' . implode(' OR ', $conditions);

    $sql = 'SELECT i.*, c.name_fa AS category_name_fa, c.name_en AS category_name_en
        FROM items i
        LEFT JOIN item_categories c ON c.id = i.category_id'
        . $whereSql . ' LIMIT 1';
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, $key === ':id' ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $itemRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$itemRow) {
        api_error('آیتم موردنظر یافت نشد.', 404);
    }

    $item = api_transform_item_detail($itemRow);

    $heroStmt = $db->prepare('SELECT h.id, h.name_en, h.name_fa, h.image_url, h.role_id, h.specialty, h.difficulty,
            r.name_en AS role_name_en, r.name_fa AS role_name_fa,
            cg.title_fa, cg.title_en
        FROM hero_counter_entities e
        INNER JOIN hero_counter_groups cg ON cg.id = e.group_id
        INNER JOIN heroes h ON h.id = cg.hero_id
        LEFT JOIN hero_roles r ON r.id = h.role_id
        WHERE e.target_item_id = :item_id
        ORDER BY cg.sort_order ASC, h.name_fa ASC');
    $heroStmt->execute([':item_id' => $item['id']]);

    $heroes = [];
    while ($row = $heroStmt->fetch(PDO::FETCH_ASSOC)) {
        $heroSummary = api_transform_hero_summary($row);
        $heroSummary['recommendation_title_fa'] = $row['title_fa'];
        $heroSummary['recommendation_title_en'] = $row['title_en'];
        $heroes[] = $heroSummary;
    }

    api_success([
        'item' => $item,
        'recommended_by' => $heroes,
    ], ['requested_id' => $item['id'], 'requested_slug' => $item['slug']]);
} catch (Throwable $e) {
    api_error('خطا در واکشی جزئیات آیتم رخ داد.', 500);
}
