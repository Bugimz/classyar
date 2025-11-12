<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'شناسه هیرو الزامی است.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $heroStmt = $db->prepare('SELECT h.*, r.name_en AS role_name_en, r.name_fa AS role_name_fa FROM heroes h JOIN hero_roles r ON h.role_id = r.id WHERE h.id = :id');
    $heroStmt->execute([':id' => $id]);
    $hero = $heroStmt->fetch();

    if (!$hero) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'هیرو یافت نشد.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $skillsStmt = $db->prepare('SELECT skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url FROM hero_skills WHERE hero_id = :hero_id ORDER BY FIELD(skill_type, "Passive", "Skill1", "Skill2", "Skill3", "Ultimate")');
    $skillsStmt->execute([':hero_id' => $id]);

    $combosStmt = $db->prepare('SELECT id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty FROM hero_combos WHERE hero_id = :hero_id ORDER BY id');
    $combosStmt->execute([':hero_id' => $id]);
    $combos = $combosStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($combos)) {
        $comboIds = array_map(static fn(array $row): int => (int) $row['id'], $combos);
        $placeholder = implode(',', array_fill(0, count($comboIds), '?'));

        $stepSql = "SELECT cs.*, s.skill_type, s.name_en AS skill_name_en, s.name_fa AS skill_name_fa, s.image_url AS skill_image_url
            FROM hero_combo_steps cs
            LEFT JOIN hero_skills s ON s.id = cs.skill_id
            WHERE cs.combo_id IN ({$placeholder})
            ORDER BY cs.sort_order ASC, cs.id ASC";

        $stepStmt = $db->prepare($stepSql);
        $stepStmt->execute($comboIds);

        $stepsByCombo = [];
        while ($stepRow = $stepStmt->fetch(PDO::FETCH_ASSOC)) {
            $comboId = (int) ($stepRow['combo_id'] ?? 0);
            if ($comboId === 0) {
                continue;
            }

            $stepsByCombo[$comboId][] = $stepRow;
        }

        foreach ($combos as &$comboRow) {
            $comboRow['steps'] = $stepsByCombo[(int) $comboRow['id']] ?? [];
        }
        unset($comboRow);
    }

    $tipsStmt = $db->prepare('SELECT tip_type, tip_text, tip_text_en, priority FROM hero_tips WHERE hero_id = :hero_id ORDER BY priority DESC, id ASC');
    $tipsStmt->execute([':hero_id' => $id]);

    $counterGroupStmt = $db->prepare('SELECT * FROM hero_counter_groups WHERE hero_id = :hero_id ORDER BY sort_order ASC, id ASC');
    $counterGroupStmt->execute([':hero_id' => $id]);
    $counterGroupsRaw = $counterGroupStmt->fetchAll(PDO::FETCH_ASSOC);

    $counterGroups = [];
    if ($counterGroupsRaw) {
        $groupIds = array_map(static fn(array $row): int => (int) $row['id'], $counterGroupsRaw);
        $placeholder = implode(',', array_fill(0, count($groupIds), '?'));
        $entitySql = "SELECT e.*,
                h.name_fa AS hero_name_fa, h.name_en AS hero_name_en, h.image_url AS hero_image_url,
                i.name_fa AS item_name_fa, i.name_en AS item_name_en, i.image_url AS item_image_url, i.slug AS item_slug,
                c.name_fa AS item_category_name_fa, c.name_en AS item_category_name_en
            FROM hero_counter_entities e
            LEFT JOIN heroes h ON h.id = e.target_hero_id
            LEFT JOIN items i ON i.id = e.target_item_id
            LEFT JOIN item_categories c ON c.id = i.category_id
            WHERE e.group_id IN ({$placeholder})
            ORDER BY e.sort_order ASC, e.id ASC";
        $entityStmt = $db->prepare($entitySql);
        $entityStmt->execute($groupIds);

        $entitiesByGroup = [];
        while ($entity = $entityStmt->fetch(PDO::FETCH_ASSOC)) {
            $groupId = (int) ($entity['group_id'] ?? 0);
            if ($groupId === 0) {
                continue;
            }

            $entity['display_name_fa'] = $entity['name_fa'] ?: ($entity['hero_name_fa'] ?? ($entity['item_name_fa'] ?? null));
            $entity['display_name_en'] = $entity['name_en'] ?: ($entity['hero_name_en'] ?? ($entity['item_name_en'] ?? null));
            $entity['display_image'] = $entity['image_url'] ?: ($entity['hero_image_url'] ?? ($entity['item_image_url'] ?? null));

            $entity['target_type'] = 'custom';
            if (!empty($entity['target_item_id'])) {
                $entity['target_type'] = 'item';
            } elseif (!empty($entity['target_hero_id'])) {
                $entity['target_type'] = 'hero';
            }

            $entity['target_hero'] = null;
            if (!empty($entity['target_hero_id'])) {
                $entity['target_hero'] = [
                    'id' => (int) $entity['target_hero_id'],
                    'name_fa' => $entity['hero_name_fa'],
                    'name_en' => $entity['hero_name_en'],
                    'image_url' => $entity['hero_image_url'],
                ];
            }

            $entity['target_item'] = null;
            if (!empty($entity['target_item_id'])) {
                $entity['target_item'] = [
                    'id' => (int) $entity['target_item_id'],
                    'slug' => $entity['item_slug'],
                    'name_fa' => $entity['item_name_fa'],
                    'name_en' => $entity['item_name_en'],
                    'image_url' => $entity['item_image_url'],
                    'category_name_fa' => $entity['item_category_name_fa'],
                    'category_name_en' => $entity['item_category_name_en'],
                ];
            }

            $entitiesByGroup[$groupId][] = $entity;
        }

        foreach ($counterGroupsRaw as $group) {
            $groupId = (int) $group['id'];
            $group['entities'] = $entitiesByGroup[$groupId] ?? [];
            $counterGroups[] = $group;
        }
    }

    $strengthStmt = $db->prepare('SELECT strength_text FROM hero_strengths WHERE hero_id = :hero_id ORDER BY id');
    $strengthStmt->execute([':hero_id' => $id]);

    $weaknessStmt = $db->prepare('SELECT weakness_text FROM hero_weaknesses WHERE hero_id = :hero_id ORDER BY id');
    $weaknessStmt->execute([':hero_id' => $id]);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'hero' => $hero,
            'skills' => $skillsStmt->fetchAll(),
            'combos' => $combos,
            'tips' => $tipsStmt->fetchAll(),
            'counter_groups' => $counterGroups,
            'counters' => $counterGroups,
            'strengths' => $strengthStmt->fetchAll(PDO::FETCH_COLUMN),
            'weaknesses' => $weaknessStmt->fetchAll(PDO::FETCH_COLUMN),
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
