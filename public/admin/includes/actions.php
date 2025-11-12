<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$action = $_POST['action'] ?? '';
$anchor = admin_filter_anchor($_POST['redirect_anchor'] ?? '');
$allowedReturnTargets = ['index.php', 'hero.php', 'hero_create.php'];
$returnTo = $_POST['return_to'] ?? 'index.php';
if (!in_array($returnTo, $allowedReturnTargets, true)) {
    $returnTo = 'index.php';
}

try {
    switch ($action) {
        case 'add_hero':
            $imagePath = admin_handle_image_upload('hero_image', 'heroes');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('INSERT INTO heroes (name_en, name_fa, role_id, specialty, hp, hp_regen, mana, mana_regen, physical_attack, magic_power, physical_defense, magic_defense, attack_speed, movement_speed, difficulty, description, tips, image_url)
                VALUES (:name_en, :name_fa, :role_id, :specialty, :hp, :hp_regen, :mana, :mana_regen, :physical_attack, :magic_power, :physical_defense, :magic_defense, :attack_speed, :movement_speed, :difficulty, :description, :tips, :image_url)');
            $stmt->execute([
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':role_id' => (int) ($_POST['role_id'] ?? 0),
                ':specialty' => admin_sanitize($_POST['specialty'] ?? ''),
                ':hp' => (int) ($_POST['hp'] ?? 0),
                ':hp_regen' => $_POST['hp_regen'] !== '' ? (float) $_POST['hp_regen'] : null,
                ':mana' => (int) ($_POST['mana'] ?? 0),
                ':mana_regen' => $_POST['mana_regen'] !== '' ? (float) $_POST['mana_regen'] : null,
                ':physical_attack' => (int) ($_POST['physical_attack'] ?? 0),
                ':magic_power' => (int) ($_POST['magic_power'] ?? 0),
                ':physical_defense' => (int) ($_POST['physical_defense'] ?? 0),
                ':magic_defense' => (int) ($_POST['magic_defense'] ?? 0),
                ':attack_speed' => $_POST['attack_speed'] !== '' ? (float) $_POST['attack_speed'] : null,
                ':movement_speed' => (int) ($_POST['movement_speed'] ?? 0),
                ':difficulty' => admin_sanitize($_POST['difficulty'] ?? 'Medium'),
                ':description' => $_POST['description'] ?? '',
                ':tips' => $_POST['tips'] ?? '',
                ':image_url' => $imageUrl,
            ]);

            $heroId = (int) $db->lastInsertId();
            admin_flash('success', 'هیرو جدید با موفقیت ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'manage');
            break;

        case 'update_hero':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $imagePath = admin_handle_image_upload('hero_image', 'heroes');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('UPDATE heroes
                SET name_en = :name_en,
                    name_fa = :name_fa,
                    role_id = :role_id,
                    specialty = :specialty,
                    hp = :hp,
                    hp_regen = :hp_regen,
                    mana = :mana,
                    mana_regen = :mana_regen,
                    physical_attack = :physical_attack,
                    magic_power = :magic_power,
                    physical_defense = :physical_defense,
                    magic_defense = :magic_defense,
                    attack_speed = :attack_speed,
                    movement_speed = :movement_speed,
                    difficulty = :difficulty,
                    description = :description,
                    tips = :tips,
                    image_url = :image_url
                WHERE id = :id');
            $stmt->execute([
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':role_id' => (int) ($_POST['role_id'] ?? 0),
                ':specialty' => admin_sanitize($_POST['specialty'] ?? ''),
                ':hp' => (int) ($_POST['hp'] ?? 0),
                ':hp_regen' => $_POST['hp_regen'] !== '' ? (float) $_POST['hp_regen'] : null,
                ':mana' => (int) ($_POST['mana'] ?? 0),
                ':mana_regen' => $_POST['mana_regen'] !== '' ? (float) $_POST['mana_regen'] : null,
                ':physical_attack' => (int) ($_POST['physical_attack'] ?? 0),
                ':magic_power' => (int) ($_POST['magic_power'] ?? 0),
                ':physical_defense' => (int) ($_POST['physical_defense'] ?? 0),
                ':magic_defense' => (int) ($_POST['magic_defense'] ?? 0),
                ':attack_speed' => $_POST['attack_speed'] !== '' ? (float) $_POST['attack_speed'] : null,
                ':movement_speed' => (int) ($_POST['movement_speed'] ?? 0),
                ':difficulty' => admin_sanitize($_POST['difficulty'] ?? 'Medium'),
                ':description' => $_POST['description'] ?? '',
                ':tips' => $_POST['tips'] ?? '',
                ':image_url' => $imageUrl,
                ':id' => $heroId,
            ]);

            admin_flash('success', 'تغییرات هیرو ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'manage');
            break;

        case 'delete_hero':
            $heroId = (int) ($_POST['hero_id'] ?? 0);

            if ($heroId <= 0) {
                throw new RuntimeException('شناسه هیرو نامعتبر است.');
            }

            $db->beginTransaction();

            $tables = [
                'hero_skills',
                'hero_combos',
                'hero_tips',
                'hero_counter_entities',
                'hero_counter_groups',
                'hero_strengths',
                'hero_weaknesses',
            ];

            foreach ($tables as $table) {
                $stmt = $db->prepare("DELETE FROM {$table} WHERE hero_id = :hero_id");
                $stmt->execute([':hero_id' => $heroId]);
            }

            $heroDelete = $db->prepare('DELETE FROM heroes WHERE id = :id');
            $heroDelete->execute([':id' => $heroId]);

            if ($heroDelete->rowCount() === 0) {
                $db->rollBack();
                throw new RuntimeException('هیرو مورد نظر یافت نشد.');
            }

            $db->commit();

            admin_flash('success', 'هیرو و تمام اطلاعات وابسته با موفقیت حذف شد.');
            admin_redirect($returnTo, [], $anchor ?: 'hero-list');
            break;

        case 'add_skill':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $imagePath = admin_handle_image_upload('skill_image', 'skills');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('INSERT INTO hero_skills (hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url)
                VALUES (:hero_id, :skill_type, :name_en, :name_fa, :description, :cooldown, :mana_cost, :damage_type, :tips, :image_url)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':skill_type' => admin_sanitize($_POST['skill_type'] ?? ''),
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':description' => $_POST['description'] ?? '',
                ':cooldown' => $_POST['cooldown'] !== '' ? (float) $_POST['cooldown'] : null,
                ':mana_cost' => $_POST['mana_cost'] !== '' ? (float) $_POST['mana_cost'] : null,
                ':damage_type' => admin_sanitize($_POST['damage_type'] ?? ''),
                ':tips' => $_POST['tips'] ?? '',
                ':image_url' => $imageUrl,
            ]);

            admin_flash('success', 'مهارت جدید ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'skills');
            break;

        case 'update_skill':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $skillId = (int) ($_POST['skill_id'] ?? 0);
            $imagePath = admin_handle_image_upload('skill_image', 'skills');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('UPDATE hero_skills
                SET skill_type = :skill_type,
                    name_en = :name_en,
                    name_fa = :name_fa,
                    description = :description,
                    cooldown = :cooldown,
                    mana_cost = :mana_cost,
                    damage_type = :damage_type,
                    tips = :tips,
                    image_url = :image_url
                WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':skill_type' => admin_sanitize($_POST['skill_type'] ?? ''),
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':description' => $_POST['description'] ?? '',
                ':cooldown' => $_POST['cooldown'] !== '' ? (float) $_POST['cooldown'] : null,
                ':mana_cost' => $_POST['mana_cost'] !== '' ? (float) $_POST['mana_cost'] : null,
                ':damage_type' => admin_sanitize($_POST['damage_type'] ?? ''),
                ':tips' => $_POST['tips'] ?? '',
                ':image_url' => $imageUrl,
                ':id' => $skillId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'تغییرات مهارت اعمال شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'skills');
            break;

        case 'add_combo':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $stmt = $db->prepare('INSERT INTO hero_combos (hero_id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty)
                VALUES (:hero_id, :combo_name, :combo_name_fa, :combo_steps, :combo_description, :combo_description_fa, :difficulty)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':combo_name' => admin_sanitize($_POST['combo_name'] ?? ''),
                ':combo_name_fa' => admin_sanitize($_POST['combo_name_fa'] ?? ''),
                ':combo_steps' => $_POST['combo_steps'] ?? '',
                ':combo_description' => $_POST['combo_description'] ?? '',
                ':combo_description_fa' => $_POST['combo_description_fa'] ?? '',
                ':difficulty' => admin_sanitize($_POST['difficulty'] ?? 'Medium'),
            ]);

            admin_flash('success', 'کامبو جدید اضافه شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'combos');
            break;

        case 'update_combo':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $comboId = (int) ($_POST['combo_id'] ?? 0);

            $stmt = $db->prepare('UPDATE hero_combos
                SET combo_name = :combo_name,
                    combo_name_fa = :combo_name_fa,
                    combo_steps = :combo_steps,
                    combo_description = :combo_description,
                    combo_description_fa = :combo_description_fa,
                    difficulty = :difficulty
                WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':combo_name' => admin_sanitize($_POST['combo_name'] ?? ''),
                ':combo_name_fa' => admin_sanitize($_POST['combo_name_fa'] ?? ''),
                ':combo_steps' => $_POST['combo_steps'] ?? '',
                ':combo_description' => $_POST['combo_description'] ?? '',
                ':combo_description_fa' => $_POST['combo_description_fa'] ?? '',
                ':difficulty' => admin_sanitize($_POST['difficulty'] ?? 'Medium'),
                ':id' => $comboId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'کامبو به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'combos');
            break;

        case 'add_tip':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $stmt = $db->prepare('INSERT INTO hero_tips (hero_id, tip_type, tip_text, tip_text_en, priority)
                VALUES (:hero_id, :tip_type, :tip_text, :tip_text_en, :priority)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':tip_type' => admin_sanitize($_POST['tip_type'] ?? ''),
                ':tip_text' => $_POST['tip_text'] ?? '',
                ':tip_text_en' => $_POST['tip_text_en'] !== '' ? $_POST['tip_text_en'] : ($_POST['tip_text'] ?? ''),
                ':priority' => (int) ($_POST['priority'] ?? 0),
            ]);

            admin_flash('success', 'نکته ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'tips');
            break;

        case 'update_tip':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $tipId = (int) ($_POST['tip_id'] ?? 0);

            $stmt = $db->prepare('UPDATE hero_tips
                SET tip_type = :tip_type,
                    tip_text = :tip_text,
                    tip_text_en = :tip_text_en,
                    priority = :priority
                WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':tip_type' => admin_sanitize($_POST['tip_type'] ?? ''),
                ':tip_text' => $_POST['tip_text'] ?? '',
                ':tip_text_en' => $_POST['tip_text_en'] !== '' ? $_POST['tip_text_en'] : ($_POST['tip_text'] ?? ''),
                ':priority' => (int) ($_POST['priority'] ?? 0),
                ':id' => $tipId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'نکته به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'tips');
            break;

        case 'add_counter_group':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $stmt = $db->prepare('INSERT INTO hero_counter_groups (hero_id, group_type, title_en, title_fa, description_en, description_fa, sort_order)
                VALUES (:hero_id, :group_type, :title_en, :title_fa, :description_en, :description_fa, :sort_order)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':group_type' => admin_sanitize($_POST['group_type'] ?? 'hero'),
                ':title_en' => admin_sanitize($_POST['title_en'] ?? ''),
                ':title_fa' => admin_sanitize($_POST['title_fa'] ?? ''),
                ':description_en' => ($_POST['description_en'] ?? '') !== '' ? $_POST['description_en'] : null,
                ':description_fa' => ($_POST['description_fa'] ?? '') !== '' ? $_POST['description_fa'] : null,
                ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ]);

            admin_flash('success', 'گروه کانتر جدید ثبت شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'update_counter_group':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $groupId = (int) ($_POST['group_id'] ?? 0);

            $stmt = $db->prepare('UPDATE hero_counter_groups
                SET group_type = :group_type,
                    title_en = :title_en,
                    title_fa = :title_fa,
                    description_en = :description_en,
                    description_fa = :description_fa,
                    sort_order = :sort_order
                WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':group_type' => admin_sanitize($_POST['group_type'] ?? 'hero'),
                ':title_en' => admin_sanitize($_POST['title_en'] ?? ''),
                ':title_fa' => admin_sanitize($_POST['title_fa'] ?? ''),
                ':description_en' => ($_POST['description_en'] ?? '') !== '' ? $_POST['description_en'] : null,
                ':description_fa' => ($_POST['description_fa'] ?? '') !== '' ? $_POST['description_fa'] : null,
                ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
                ':id' => $groupId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'گروه کانتر به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'delete_counter_group':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $groupId = (int) ($_POST['group_id'] ?? 0);

            $stmt = $db->prepare('DELETE FROM hero_counter_groups WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':id' => $groupId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'گروه کانتر حذف شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'add_counter_entity':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $groupId = (int) ($_POST['group_id'] ?? 0);
            $imagePath = admin_handle_image_upload('entity_image', 'counters');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('INSERT INTO hero_counter_entities (group_id, name_en, name_fa, image_url, sort_order)
                VALUES (:group_id, :name_en, :name_fa, :image_url, :sort_order)');
            $stmt->execute([
                ':group_id' => $groupId,
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':image_url' => $imageUrl,
                ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
            ]);

            admin_flash('success', 'آیتم/هیروی کانتر ثبت شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'update_counter_entity':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $groupId = (int) ($_POST['group_id'] ?? 0);
            $entityId = (int) ($_POST['entity_id'] ?? 0);
            $imagePath = admin_handle_image_upload('entity_image', 'counters');
            $manualImage = admin_sanitize($_POST['image_url'] ?? '');
            $imageUrl = $imagePath ?? $manualImage;

            $stmt = $db->prepare('UPDATE hero_counter_entities
                SET name_en = :name_en,
                    name_fa = :name_fa,
                    image_url = :image_url,
                    sort_order = :sort_order
                WHERE id = :id AND group_id = :group_id');
            $stmt->execute([
                ':name_en' => admin_sanitize($_POST['name_en'] ?? ''),
                ':name_fa' => admin_sanitize($_POST['name_fa'] ?? ''),
                ':image_url' => $imageUrl,
                ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
                ':id' => $entityId,
                ':group_id' => $groupId,
            ]);

            admin_flash('success', 'آیتم/هیروی کانتر به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'delete_counter_entity':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $groupId = (int) ($_POST['group_id'] ?? 0);
            $entityId = (int) ($_POST['entity_id'] ?? 0);

            $stmt = $db->prepare('DELETE FROM hero_counter_entities WHERE id = :id AND group_id = :group_id');
            $stmt->execute([
                ':id' => $entityId,
                ':group_id' => $groupId,
            ]);

            admin_flash('success', 'آیتم/هیروی کانتر حذف شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'counters');
            break;

        case 'add_strength':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $stmt = $db->prepare('INSERT INTO hero_strengths (hero_id, strength_text) VALUES (:hero_id, :strength_text)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':strength_text' => $_POST['strength_text'] ?? '',
            ]);

            admin_flash('success', 'نقطه قوت ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'strengths');
            break;

        case 'update_strength':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $strengthId = (int) ($_POST['strength_id'] ?? 0);
            $stmt = $db->prepare('UPDATE hero_strengths SET strength_text = :strength_text WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':strength_text' => $_POST['strength_text'] ?? '',
                ':id' => $strengthId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'نقطه قوت به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'strengths');
            break;

        case 'add_weakness':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $stmt = $db->prepare('INSERT INTO hero_weaknesses (hero_id, weakness_text) VALUES (:hero_id, :weakness_text)');
            $stmt->execute([
                ':hero_id' => $heroId,
                ':weakness_text' => $_POST['weakness_text'] ?? '',
            ]);

            admin_flash('success', 'نقطه ضعف ذخیره شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'weaknesses');
            break;

        case 'update_weakness':
            $heroId = (int) ($_POST['hero_id'] ?? 0);
            $weaknessId = (int) ($_POST['weakness_id'] ?? 0);
            $stmt = $db->prepare('UPDATE hero_weaknesses SET weakness_text = :weakness_text WHERE id = :id AND hero_id = :hero_id');
            $stmt->execute([
                ':weakness_text' => $_POST['weakness_text'] ?? '',
                ':id' => $weaknessId,
                ':hero_id' => $heroId,
            ]);

            admin_flash('success', 'نقطه ضعف به‌روزرسانی شد.');
            admin_redirect($returnTo, ['hero' => $heroId], $anchor ?: 'weaknesses');
            break;

        default:
            admin_flash('error', 'اقدام نامعتبر است.');
            admin_redirect($returnTo, []);
    }
} catch (Throwable $throwable) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Admin action error: ' . $throwable->getMessage());

    $heroId = isset($heroId) ? (int) $heroId : null;
    $query = [];
    if ($heroId) {
        $query['hero'] = $heroId;
    }

    admin_flash('error', 'خطا در ذخیره‌سازی داده. لطفاً دوباره تلاش کنید.');
    admin_redirect($returnTo, $query, $anchor ?: null);
}
