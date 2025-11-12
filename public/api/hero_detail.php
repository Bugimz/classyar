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

    $combosStmt = $db->prepare('SELECT combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty FROM hero_combos WHERE hero_id = :hero_id ORDER BY id');
    $combosStmt->execute([':hero_id' => $id]);

    $tipsStmt = $db->prepare('SELECT tip_type, tip_text, tip_text_en, priority FROM hero_tips WHERE hero_id = :hero_id ORDER BY priority DESC, id ASC');
    $tipsStmt->execute([':hero_id' => $id]);

    $counterGroupStmt = $db->prepare('SELECT * FROM hero_counter_groups WHERE hero_id = :hero_id ORDER BY sort_order ASC, id ASC');
    $counterGroupStmt->execute([':hero_id' => $id]);
    $counterGroupsRaw = $counterGroupStmt->fetchAll(PDO::FETCH_ASSOC);

    $counterGroups = [];
    if ($counterGroupsRaw) {
        $groupIds = array_map(static fn(array $row): int => (int) $row['id'], $counterGroupsRaw);
        $placeholder = implode(',', array_fill(0, count($groupIds), '?'));
        $entityStmt = $db->prepare("SELECT * FROM hero_counter_entities WHERE group_id IN ({$placeholder}) ORDER BY sort_order ASC, id ASC");
        $entityStmt->execute($groupIds);

        $entitiesByGroup = [];
        while ($entity = $entityStmt->fetch(PDO::FETCH_ASSOC)) {
            $entitiesByGroup[(int) $entity['group_id']][] = $entity;
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
            'combos' => $combosStmt->fetchAll(),
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
