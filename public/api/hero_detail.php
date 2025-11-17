<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$heroId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($heroId <= 0) {
    api_error('شناسه هیرو الزامی است.', 400);
}

try {
    $db = api_db();

    $heroStmt = $db->prepare('SELECT h.*, r.name_en AS role_name_en, r.name_fa AS role_name_fa
        FROM heroes h
        LEFT JOIN hero_roles r ON r.id = h.role_id
        WHERE h.id = :id');
    $heroStmt->execute([':id' => $heroId]);
    $heroRow = $heroStmt->fetch(PDO::FETCH_ASSOC);

    if (!$heroRow) {
        api_error('هیرو یافت نشد.', 404);
    }

    $hero = api_transform_hero_summary($heroRow);
    $hero['attributes'] = [
        'hp' => isset($heroRow['hp']) ? (int) $heroRow['hp'] : null,
        'hp_regen' => $heroRow['hp_regen'],
        'mana' => isset($heroRow['mana']) ? (int) $heroRow['mana'] : null,
        'mana_regen' => $heroRow['mana_regen'],
        'physical_attack' => $heroRow['physical_attack'],
        'physical_defense' => $heroRow['physical_defense'],
        'magic_defense' => $heroRow['magic_defense'],
        'magic_power' => $heroRow['magic_power'],
        'attack_speed' => $heroRow['attack_speed'],
        'movement_speed' => $heroRow['movement_speed'],
    ];
    $hero['description'] = $heroRow['description'];
    $hero['general_tips'] = $heroRow['tips'];

    $skillStmt = $db->prepare("SELECT id, hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url
        FROM hero_skills
        WHERE hero_id = :hero_id
        ORDER BY FIELD(skill_type, 'Passive','Skill1','Skill2','Skill3','Ultimate'), id ASC");
    $skillStmt->execute([':hero_id' => $heroId]);
    $skills = [];
    while ($row = $skillStmt->fetch(PDO::FETCH_ASSOC)) {
        $skills[] = api_transform_skill($row);
    }

    $comboStmt = $db->prepare('SELECT * FROM hero_combos WHERE hero_id = :hero_id ORDER BY id ASC');
    $comboStmt->execute([':hero_id' => $heroId]);
    $combos = $comboStmt->fetchAll(PDO::FETCH_ASSOC);

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
            $comboId = (int) $stepRow['combo_id'];
            $stepsByCombo[$comboId][] = $stepRow;
        }

        foreach ($combos as &$comboRow) {
            $comboId = (int) $comboRow['id'];
            $comboRow['steps'] = $stepsByCombo[$comboId] ?? [];
        }
        unset($comboRow);
    }

    $comboPayload = array_map('api_transform_combo', $combos);

    $tipStmt = $db->prepare('SELECT tip_type, tip_text, tip_text_en, priority FROM hero_tips WHERE hero_id = :hero_id ORDER BY priority DESC, id ASC');
    $tipStmt->execute([':hero_id' => $heroId]);
    $tips = $tipStmt->fetchAll(PDO::FETCH_ASSOC);

    $counterGroupStmt = $db->prepare('SELECT * FROM hero_counter_groups WHERE hero_id = :hero_id ORDER BY sort_order ASC, id ASC');
    $counterGroupStmt->execute([':hero_id' => $heroId]);
    $counterGroupsRaw = $counterGroupStmt->fetchAll(PDO::FETCH_ASSOC);

    $counterGroups = [];
    if (!empty($counterGroupsRaw)) {
        $groupIds = array_map(static fn(array $row): int => (int) $row['id'], $counterGroupsRaw);
        $placeholder = implode(',', array_fill(0, count($groupIds), '?'));
        $entitySql = "SELECT e.*, h.name_fa AS hero_name_fa, h.name_en AS hero_name_en, h.image_url AS hero_image_url,
                i.name_fa AS item_name_fa, i.name_en AS item_name_en, i.image_url AS item_image_url,
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
            $groupId = (int) $entity['group_id'];
            $entity['display_name_fa'] = $entity['name_fa'] ?: ($entity['hero_name_fa'] ?? ($entity['item_name_fa'] ?? null));
            $entity['display_name_en'] = $entity['name_en'] ?: ($entity['hero_name_en'] ?? ($entity['item_name_en'] ?? null));

            if (!empty($entity['target_item_id']) && !empty($entity['item_image_url'])) {
                $entity['display_image'] = $entity['item_image_url'];
            } elseif (!empty($entity['target_hero_id']) && !empty($entity['hero_image_url'])) {
                $entity['display_image'] = $entity['hero_image_url'];
            } else {
                $entity['display_image'] = $entity['image_url'] ?? null;
            }
            $entitiesByGroup[$groupId][] = $entity;
        }

        foreach ($counterGroupsRaw as $groupRow) {
            $groupId = (int) $groupRow['id'];
            $groupRow['entities'] = $entitiesByGroup[$groupId] ?? [];
            $counterGroups[] = api_transform_counter_group($groupRow);
        }
    }

    $strengthStmt = $db->prepare('SELECT strength_text FROM hero_strengths WHERE hero_id = :hero_id ORDER BY id');
    $strengthStmt->execute([':hero_id' => $heroId]);
    $strengths = $strengthStmt->fetchAll(PDO::FETCH_COLUMN);

    $weaknessStmt = $db->prepare('SELECT weakness_text FROM hero_weaknesses WHERE hero_id = :hero_id ORDER BY id');
    $weaknessStmt->execute([':hero_id' => $heroId]);
    $weaknesses = $weaknessStmt->fetchAll(PDO::FETCH_COLUMN);

    api_success([
        'hero' => $hero,
        'skills' => $skills,
        'combos' => $comboPayload,
        'tips' => $tips,
        'counter_groups' => $counterGroups,
        'strengths' => $strengths,
        'weaknesses' => $weaknesses,
    ], ['requested_id' => $heroId, 'last_updated' => $heroRow['updated_at'] ?? null]);
} catch (Throwable $e) {
    api_error('خطای غیرمنتظره در پردازش درخواست رخ داد.', 500);
}
