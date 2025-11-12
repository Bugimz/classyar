<?php
declare(strict_types=1);

function admin_check_schema(PDO $db): array
{
    $requiredTables = [
        'hero_roles',
        'heroes',
        'hero_skills',
        'hero_combos',
        'hero_combo_steps',
        'hero_tips',
        'hero_counter_groups',
        'hero_counter_entities',
        'hero_strengths',
        'hero_weaknesses',
    ];

    $missing = [];

    foreach ($requiredTables as $table) {
        $quoted = $db->quote($table);
        $stmt = $db->query("SHOW TABLES LIKE {$quoted}");

        if (!$stmt || $stmt->fetchColumn() === false) {
            $missing[] = $table;
        }
    }

    return $missing;
}

function admin_fetch_roles(PDO $db): array
{
    $stmt = $db->query('SELECT id, name_fa FROM hero_roles ORDER BY id ASC');

    return $stmt ? $stmt->fetchAll() : [];
}

function admin_fetch_dashboard_stats(PDO $db): array
{
    $stats = [
        'roles' => 0,
        'heroes' => 0,
        'skills' => 0,
        'combos' => 0,
        'combo_steps' => 0,
        'tips' => 0,
        'counter_groups' => 0,
        'counter_entities' => 0,
        'strengths' => 0,
        'weaknesses' => 0,
    ];

    $queries = [
        'roles' => 'SELECT COUNT(*) FROM hero_roles',
        'heroes' => 'SELECT COUNT(*) FROM heroes',
        'skills' => 'SELECT COUNT(*) FROM hero_skills',
        'combos' => 'SELECT COUNT(*) FROM hero_combos',
        'combo_steps' => 'SELECT COUNT(*) FROM hero_combo_steps',
        'tips' => 'SELECT COUNT(*) FROM hero_tips',
        'counter_groups' => 'SELECT COUNT(*) FROM hero_counter_groups',
        'counter_entities' => 'SELECT COUNT(*) FROM hero_counter_entities',
        'strengths' => 'SELECT COUNT(*) FROM hero_strengths',
        'weaknesses' => 'SELECT COUNT(*) FROM hero_weaknesses',
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $db->query($sql);
        $stats[$key] = $stmt ? (int) $stmt->fetchColumn() : 0;
    }

    return $stats;
}

function admin_fetch_heroes(PDO $db): array
{
    $sql = 'SELECT h.id, h.name_fa, h.name_en, h.image_url, r.name_fa AS role_name
            FROM heroes h
            LEFT JOIN hero_roles r ON r.id = h.role_id
            ORDER BY h.name_fa ASC, h.id ASC';

    $stmt = $db->query($sql);
    $heroes = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    if (empty($heroes)) {
        return [];
    }

    $heroIds = array_map(static fn(array $row): int => (int) $row['id'], $heroes);
    $placeholders = implode(',', array_fill(0, count($heroIds), '?'));

    $skillSql = "SELECT hero_id, skill_type, name_fa, name_en, image_url
                 FROM hero_skills
                 WHERE hero_id IN ({$placeholders})
                 ORDER BY hero_id ASC, FIELD(skill_type, 'Passive','Skill1','Skill2','Skill3','Ultimate'), id ASC";

    $skillStmt = $db->prepare($skillSql);
    $skillStmt->execute($heroIds);

    $skillsByHero = [];
    while ($skillRow = $skillStmt->fetch(PDO::FETCH_ASSOC)) {
        $heroId = (int) ($skillRow['hero_id'] ?? 0);
        if ($heroId === 0) {
            continue;
        }

        $nameFa = isset($skillRow['name_fa']) ? (string) $skillRow['name_fa'] : '';
        $nameEn = isset($skillRow['name_en']) ? (string) $skillRow['name_en'] : '';
        $skillName = $nameFa !== '' ? $nameFa : $nameEn;

        $skillsByHero[$heroId][] = [
            'skill_type' => (string) ($skillRow['skill_type'] ?? ''),
            'name' => $skillName,
            'image_url' => (string) ($skillRow['image_url'] ?? ''),
        ];
    }

    foreach ($heroes as &$heroRow) {
        $heroId = (int) $heroRow['id'];
        $heroRow['skills'] = $skillsByHero[$heroId] ?? [];
    }
    unset($heroRow);

    return $heroes;
}

function admin_fetch_hero_bundle(PDO $db, int $heroId): ?array
{
    $heroStmt = $db->prepare('SELECT h.*, r.name_fa AS role_name
        FROM heroes h
        LEFT JOIN hero_roles r ON r.id = h.role_id
        WHERE h.id = :id');
    $heroStmt->execute([':id' => $heroId]);

    $hero = $heroStmt->fetch();
    if (!$hero) {
        return null;
    }

    $skillStmt = $db->prepare("SELECT * FROM hero_skills WHERE hero_id = :id ORDER BY FIELD(skill_type, 'Passive','Skill1','Skill2','Skill3','Ultimate'), id");
    $skillStmt->execute([':id' => $heroId]);
    $skills = $skillStmt->fetchAll();

    $comboStmt = $db->prepare('SELECT * FROM hero_combos WHERE hero_id = :id ORDER BY id ASC');
    $comboStmt->execute([':id' => $heroId]);
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
            $comboId = (int) ($stepRow['combo_id'] ?? 0);
            if ($comboId === 0) {
                continue;
            }

            $stepsByCombo[$comboId][] = [
                'id' => (int) ($stepRow['id'] ?? 0),
                'combo_id' => $comboId,
                'skill_id' => (int) ($stepRow['skill_id'] ?? 0),
                'custom_label' => $stepRow['custom_label'] ?? null,
                'sort_order' => (int) ($stepRow['sort_order'] ?? 0),
                'skill_type' => $stepRow['skill_type'] ?? null,
                'skill_name_en' => $stepRow['skill_name_en'] ?? null,
                'skill_name_fa' => $stepRow['skill_name_fa'] ?? null,
                'skill_image_url' => $stepRow['skill_image_url'] ?? null,
            ];
        }

        foreach ($combos as &$comboRow) {
            $comboRow['steps'] = $stepsByCombo[(int) $comboRow['id']] ?? [];
        }
        unset($comboRow);
    }

    $tipStmt = $db->prepare('SELECT * FROM hero_tips WHERE hero_id = :id ORDER BY priority DESC, id DESC');
    $tipStmt->execute([':id' => $heroId]);
    $tips = $tipStmt->fetchAll();

    $counterGroupStmt = $db->prepare('SELECT * FROM hero_counter_groups WHERE hero_id = :id ORDER BY sort_order ASC, id ASC');
    $counterGroupStmt->execute([':id' => $heroId]);
    $counterGroups = $counterGroupStmt->fetchAll(PDO::FETCH_ASSOC);

    $counters = [];
    if ($counterGroups) {
        $groupIds = array_map(static fn(array $row): int => (int) $row['id'], $counterGroups);
        $placeholder = implode(',', array_fill(0, count($groupIds), '?'));
        $entityStmt = $db->prepare("SELECT * FROM hero_counter_entities WHERE group_id IN ({$placeholder}) ORDER BY sort_order ASC, id ASC");
        $entityStmt->execute($groupIds);

        $entitiesByGroup = [];
        while ($entity = $entityStmt->fetch(PDO::FETCH_ASSOC)) {
            $entitiesByGroup[(int) $entity['group_id']][] = $entity;
        }

        foreach ($counterGroups as $group) {
            $groupId = (int) $group['id'];
            $group['entities'] = $entitiesByGroup[$groupId] ?? [];
            $counters[] = $group;
        }
    }

    $strengthStmt = $db->prepare('SELECT * FROM hero_strengths WHERE hero_id = :id ORDER BY id ASC');
    $strengthStmt->execute([':id' => $heroId]);
    $strengths = $strengthStmt->fetchAll();

    $weaknessStmt = $db->prepare('SELECT * FROM hero_weaknesses WHERE hero_id = :id ORDER BY id ASC');
    $weaknessStmt->execute([':id' => $heroId]);
    $weaknesses = $weaknessStmt->fetchAll();

    return [
        'hero' => $hero,
        'skills' => $skills,
        'combos' => $combos,
        'tips' => $tips,
        'counters' => $counters,
        'strengths' => $strengths,
        'weaknesses' => $weaknesses,
    ];
}
