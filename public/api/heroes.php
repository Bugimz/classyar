<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
    $db = api_db();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = (int) ($_GET['per_page'] ?? 20);
    $perPage = max(5, min(100, $perPage));
    $offset = ($page - 1) * $perPage;

    $search = trim((string) ($_GET['search'] ?? ''));
    $roleId = isset($_GET['role_id']) ? (int) $_GET['role_id'] : 0;
    $sort = (string) ($_GET['sort'] ?? 'name');
    $sort = in_array($sort, ['recent', 'oldest', 'name'], true) ? $sort : 'name';

    $includeTokens = array_values(array_filter(array_map('trim', explode(',', (string) ($_GET['include'] ?? '')))));
    $includeStats = in_array('stats', $includeTokens, true);

    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(h.name_fa LIKE :search OR h.name_en LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    if ($roleId > 0) {
        $conditions[] = 'h.role_id = :role_id';
        $params[':role_id'] = $roleId;
    }

    $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);

    $countStmt = $db->prepare('SELECT COUNT(*) FROM heroes h' . $whereSql);
    foreach ($params as $key => $value) {
        $paramType = $key === ':role_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
        $countStmt->bindValue($key, $value, $paramType);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $orderClause = 'ORDER BY h.name_fa ASC, h.id ASC';
    if ($sort === 'recent') {
        $orderClause = 'ORDER BY h.updated_at DESC, h.id DESC';
    } elseif ($sort === 'oldest') {
        $orderClause = 'ORDER BY h.created_at ASC, h.id ASC';
    }

    $sql = 'SELECT h.id, h.name_en, h.name_fa, h.image_url, h.specialty, h.difficulty, h.role_id, r.name_en AS role_name_en, r.name_fa AS role_name_fa
            FROM heroes h
            LEFT JOIN hero_roles r ON r.id = h.role_id'
        . $whereSql . ' ' . $orderClause . ' LIMIT :limit OFFSET :offset';

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $paramType = $key === ':role_id' ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $paramType);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $heroes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $heroes[] = api_transform_hero_summary($row);
    }

    if ($includeStats && !empty($heroes)) {
        $heroIds = array_map(static fn(array $row): int => $row['id'], $heroes);
        $statsByHero = api_fetch_hero_stats($db, $heroIds);

        foreach ($heroes as &$heroRow) {
            $heroRow['metrics'] = $statsByHero[$heroRow['id']] ?? [
                'skills' => 0,
                'combos' => 0,
                'tips' => 0,
                'counter_groups' => 0,
            ];
        }
        unset($heroRow);
    }

    $meta = api_paginate_meta($total, $page, $perPage);
    $meta['filters'] = [
        'search' => $search,
        'role_id' => $roleId,
        'sort' => $sort,
        'include' => $includeTokens,
    ];

    api_success($heroes, $meta);
} catch (Throwable $e) {
    api_error('خطای غیرمنتظره در پردازش درخواست رخ داد.', 500);
}
