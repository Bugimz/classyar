<?php
declare(strict_types=1);

function admin_check_schema(PDO $db): array
{
    $requiredTables = [
        'roles',
        'heroes',
        'hero_skills',
        'hero_combos',
        'hero_tips',
        'hero_counters',
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
    $stmt = $db->query('SELECT id, name_fa FROM roles ORDER BY sort_order ASC, id ASC');

    return $stmt ? $stmt->fetchAll() : [];
}

function admin_fetch_heroes(PDO $db): array
{
    $sql = 'SELECT h.id, h.name_fa, h.name_en, h.image_url, r.name_fa AS role_name
            FROM heroes h
            LEFT JOIN roles r ON r.id = h.role_id
            ORDER BY h.name_fa ASC, h.id ASC';

    $stmt = $db->query($sql);

    return $stmt ? $stmt->fetchAll() : [];
}

function admin_fetch_hero_bundle(PDO $db, int $heroId): ?array
{
    $heroStmt = $db->prepare('SELECT h.*, r.name_fa AS role_name
        FROM heroes h
        LEFT JOIN roles r ON r.id = h.role_id
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
    $combos = $comboStmt->fetchAll();

    $tipStmt = $db->prepare('SELECT * FROM hero_tips WHERE hero_id = :id ORDER BY priority DESC, id DESC');
    $tipStmt->execute([':id' => $heroId]);
    $tips = $tipStmt->fetchAll();

    $counterStmt = $db->prepare('SELECT * FROM hero_counters WHERE hero_id = :id ORDER BY id ASC');
    $counterStmt->execute([':id' => $heroId]);
    $counters = $counterStmt->fetchAll();

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
