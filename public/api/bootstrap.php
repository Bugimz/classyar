<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

function api_db(): PDO
{
    static $db = null;

    if ($db instanceof PDO) {
        return $db;
    }

    $database = new Database();
    $db = $database->getConnection();

    return $db;
}

function api_success(array $payload, array $meta = [], int $status = 200): void
{
    http_response_code($status);

    $body = [
        'status' => 'success',
        'data' => $payload,
    ];

    if (!empty($meta)) {
        $body['meta'] = $meta;
    }

    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_error(string $message, int $status = 400, array $meta = []): void
{
    http_response_code($status);

    $body = [
        'status' => 'error',
        'message' => $message,
    ];

    if (!empty($meta)) {
        $body['meta'] = $meta;
    }

    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_normalize_image(?string $path): ?string
{
    $normalized = mlbb_asset_url($path ?? '');

    return $normalized === '' ? null : $normalized;
}

function api_paginate_meta(int $total, int $page, int $perPage): array
{
    $totalPages = (int) ceil($total / max($perPage, 1));

    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
    ];
}

function api_transform_hero_summary(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name_fa' => $row['name_fa'],
        'name_en' => $row['name_en'],
        'specialty' => $row['specialty'] ?? null,
        'difficulty' => $row['difficulty'] ?? null,
        'image_url' => api_normalize_image($row['image_url'] ?? null),
        'role' => [
            'id' => isset($row['role_id']) ? (int) $row['role_id'] : null,
            'name_fa' => $row['role_name_fa'] ?? null,
            'name_en' => $row['role_name_en'] ?? null,
        ],
        'links' => [
            'web' => mlbb_url('index.php?id=' . (int) $row['id']),
            'api' => mlbb_url('api/hero_detail.php?id=' . (int) $row['id']),
        ],
    ];
}

function api_fetch_hero_stats(PDO $db, array $heroIds): array
{
    if (empty($heroIds)) {
        return [];
    }

    $stats = [];
    $placeholders = implode(',', array_fill(0, count($heroIds), '?'));
    $groups = [
        'skills' => 'hero_skills',
        'combos' => 'hero_combos',
        'tips' => 'hero_tips',
        'counter_groups' => 'hero_counter_groups',
    ];

    foreach ($groups as $key => $table) {
        $stmt = $db->prepare("SELECT hero_id, COUNT(*) AS total FROM {$table} WHERE hero_id IN ({$placeholders}) GROUP BY hero_id");
        $stmt->execute($heroIds);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $heroId = (int) $row['hero_id'];
            $stats[$heroId][$key] = (int) $row['total'];
        }
    }

    foreach ($heroIds as $heroId) {
        $stats[$heroId] = array_merge([
            'skills' => 0,
            'combos' => 0,
            'tips' => 0,
            'counter_groups' => 0,
        ], $stats[$heroId] ?? []);
    }

    return $stats;
}

function api_transform_skill(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'skill_type' => $row['skill_type'],
        'name_fa' => $row['name_fa'],
        'name_en' => $row['name_en'],
        'description' => $row['description'],
        'cooldown' => $row['cooldown'],
        'mana_cost' => $row['mana_cost'],
        'damage_type' => $row['damage_type'],
        'tips' => $row['tips'],
        'image_url' => api_normalize_image($row['image_url'] ?? null),
    ];
}

function api_transform_combo_step(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'skill_id' => isset($row['skill_id']) ? (int) $row['skill_id'] : null,
        'label_fa' => $row['skill_name_fa'] ?? null,
        'label_en' => $row['skill_name_en'] ?? null,
        'skill_type' => $row['skill_type'] ?? null,
        'image_url' => api_normalize_image($row['skill_image_url'] ?? null),
        'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
    ];
}

function api_transform_combo(array $row): array
{
    $steps = $row['steps'] ?? [];

    return [
        'id' => (int) $row['id'],
        'name_fa' => $row['combo_name_fa'],
        'name_en' => $row['combo_name'],
        'description_fa' => $row['combo_description_fa'],
        'description_en' => $row['combo_description'],
        'difficulty' => $row['difficulty'],
        'steps_text' => $row['combo_steps'],
        'steps' => array_map('api_transform_combo_step', $steps),
    ];
}

function api_transform_counter_entity(array $entity): array
{
    $targetType = 'custom';
    $targetUrl = null;

    if (!empty($entity['target_hero_id'])) {
        $targetType = 'hero';
        $targetUrl = mlbb_url('index.php?id=' . (int) $entity['target_hero_id']);
    } elseif (!empty($entity['target_item_id'])) {
        $targetType = 'item';
        $targetUrl = mlbb_url('item.php?id=' . (int) $entity['target_item_id']);
    }

    return [
        'id' => (int) $entity['id'],
        'type' => $targetType,
        'name_fa' => $entity['display_name_fa'] ?? null,
        'name_en' => $entity['display_name_en'] ?? null,
        'notes' => $entity['description'] ?? null,
        'image_url' => api_normalize_image($entity['display_image'] ?? null),
        'target_url' => $targetUrl,
        'target_hero_id' => isset($entity['target_hero_id']) ? (int) $entity['target_hero_id'] : null,
        'target_item_id' => isset($entity['target_item_id']) ? (int) $entity['target_item_id'] : null,
        'category_fa' => $entity['item_category_name_fa'] ?? null,
        'category_en' => $entity['item_category_name_en'] ?? null,
    ];
}

function api_transform_counter_group(array $group): array
{
    $entities = array_map('api_transform_counter_entity', $group['entities'] ?? []);

    return [
        'id' => (int) $group['id'],
        'hero_id' => (int) $group['hero_id'],
        'title_fa' => $group['title_fa'],
        'title_en' => $group['title_en'],
        'description_fa' => $group['description_fa'],
        'description_en' => $group['description_en'],
        'group_type' => $group['group_type'],
        'entities' => $entities,
    ];
}

function api_transform_item_summary(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'name_fa' => $row['name_fa'],
        'name_en' => $row['name_en'],
        'tier' => $row['tier'],
        'gold_cost' => isset($row['gold_cost']) ? (int) $row['gold_cost'] : null,
        'image_url' => api_normalize_image($row['image_url'] ?? null),
        'category' => [
            'id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
            'name_fa' => $row['category_name_fa'] ?? null,
            'name_en' => $row['category_name_en'] ?? null,
        ],
        'links' => [
            'web' => mlbb_url('item.php?id=' . (int) $row['id']),
        ],
    ];
}

function api_transform_item_detail(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'name_fa' => $row['name_fa'],
        'name_en' => $row['name_en'],
        'tier' => $row['tier'],
        'gold_cost' => isset($row['gold_cost']) ? (int) $row['gold_cost'] : null,
        'stats_fa' => $row['stats_fa'],
        'stats_en' => $row['stats_en'],
        'passive_fa' => $row['passive_fa'],
        'passive_en' => $row['passive_en'],
        'active_fa' => $row['active_fa'],
        'active_en' => $row['active_en'],
        'description_fa' => $row['description_fa'],
        'description_en' => $row['description_en'],
        'image_url' => api_normalize_image($row['image_url'] ?? null),
        'category' => [
            'id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
            'name_fa' => $row['category_name_fa'] ?? null,
            'name_en' => $row['category_name_en'] ?? null,
        ],
        'links' => [
            'web' => mlbb_url('item.php?id=' . (int) $row['id']),
            'api' => mlbb_url('api/item.php?id=' . (int) $row['id']),
        ],
    ];
}
?>
