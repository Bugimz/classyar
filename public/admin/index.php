<?php
session_start();

require_once __DIR__ . '/../../config/helpers.php';

if (empty($_SESSION['admin_authenticated'])) {
    header('Location: ' . mlbb_admin_url('login.php'));
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$flash = $_SESSION['admin_flash'] ?? null;
if ($flash !== null) {
    unset($_SESSION['admin_flash']);
}

$error = null;

function sanitize(string $value): string
{
    return trim($value);
}

function filter_anchor(?string $value): string
{
    if ($value === null) {
        return '';
    }

    $clean = preg_replace('/[^a-z0-9_-]/i', '', $value);

    return $clean ?? '';
}

function handle_image_upload(string $field, string $folder): ?string
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }

    $file = $_FILES[$field];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || ($file['name'] ?? '') === '') {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('آپلود فایل با خطا مواجه شد. لطفاً مجدد تلاش کنید.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('فرمت تصویر مجاز نیست. فقط jpg, jpeg, png, gif یا webp قابل قبول است.');
    }

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        throw new RuntimeException('مسیر public پیدا نشد.');
    }

    $targetDir = $publicRoot . '/assets/uploads/' . $folder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        throw new RuntimeException('امکان ایجاد پوشه برای آپلود وجود ندارد.');
    }

    $fileName = $folder . '_' . str_replace('.', '', uniqid('', true)) . '.' . $extension;
    $destination = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('انتقال فایل آپلود شده انجام نشد.');
    }

    return '/assets/uploads/' . $folder . '/' . $fileName;
}

function resolve_image_src(?string $path): string
{
    if (empty($path)) {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $trimmed = ltrim($path, '/');

    return mlbb_url($trimmed);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $anchor = filter_anchor($_POST['redirect_anchor'] ?? '');

        switch ($action) {
            case 'add_hero':
                $imagePath = handle_image_upload('hero_image', 'heroes');
                $manualImage = sanitize($_POST['image_url'] ?? '');
                $imageUrl = $imagePath ?? $manualImage;

                $stmt = $db->prepare('INSERT INTO heroes (name_en, name_fa, role_id, specialty, hp, hp_regen, mana, mana_regen, physical_attack, magic_power, physical_defense, magic_defense, attack_speed, movement_speed, difficulty, description, tips, image_url)
                    VALUES (:name_en, :name_fa, :role_id, :specialty, :hp, :hp_regen, :mana, :mana_regen, :physical_attack, :magic_power, :physical_defense, :magic_defense, :attack_speed, :movement_speed, :difficulty, :description, :tips, :image_url)');
                $stmt->execute([
                    ':name_en' => sanitize($_POST['name_en'] ?? ''),
                    ':name_fa' => sanitize($_POST['name_fa'] ?? ''),
                    ':role_id' => (int) ($_POST['role_id'] ?? 0),
                    ':specialty' => sanitize($_POST['specialty'] ?? ''),
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
                    ':difficulty' => sanitize($_POST['difficulty'] ?? 'Medium'),
                    ':description' => $_POST['description'] ?? '',
                    ':tips' => $_POST['tips'] ?? '',
                    ':image_url' => $imageUrl,
                ]);

                $heroId = (int) $db->lastInsertId();
                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'هیرو جدید با موفقیت ذخیره شد.'];

                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                header('Location: ' . $target);
                exit;

            case 'update_hero':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $imagePath = handle_image_upload('hero_image', 'heroes');
                $manualImage = sanitize($_POST['image_url'] ?? '');
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
                    ':name_en' => sanitize($_POST['name_en'] ?? ''),
                    ':name_fa' => sanitize($_POST['name_fa'] ?? ''),
                    ':role_id' => (int) ($_POST['role_id'] ?? 0),
                    ':specialty' => sanitize($_POST['specialty'] ?? ''),
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
                    ':difficulty' => sanitize($_POST['difficulty'] ?? 'Medium'),
                    ':description' => $_POST['description'] ?? '',
                    ':tips' => $_POST['tips'] ?? '',
                    ':image_url' => $imageUrl,
                    ':id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'تغییرات هیرو ذخیره شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_skill':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $imagePath = handle_image_upload('skill_image', 'skills');
                $manualImage = sanitize($_POST['image_url'] ?? '');
                $imageUrl = $imagePath ?? $manualImage;

                $stmt = $db->prepare('INSERT INTO hero_skills (hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url)
                    VALUES (:hero_id, :skill_type, :name_en, :name_fa, :description, :cooldown, :mana_cost, :damage_type, :tips, :image_url)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':skill_type' => sanitize($_POST['skill_type'] ?? ''),
                    ':name_en' => sanitize($_POST['name_en'] ?? ''),
                    ':name_fa' => sanitize($_POST['name_fa'] ?? ''),
                    ':description' => $_POST['description'] ?? '',
                    ':cooldown' => $_POST['cooldown'] !== '' ? (float) $_POST['cooldown'] : null,
                    ':mana_cost' => $_POST['mana_cost'] !== '' ? (float) $_POST['mana_cost'] : null,
                    ':damage_type' => sanitize($_POST['damage_type'] ?? ''),
                    ':tips' => $_POST['tips'] ?? '',
                    ':image_url' => $imageUrl,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'مهارت جدید ذخیره شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'update_skill':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $skillId = (int) ($_POST['skill_id'] ?? 0);
                $imagePath = handle_image_upload('skill_image', 'skills');
                $manualImage = sanitize($_POST['image_url'] ?? '');
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
                    ':skill_type' => sanitize($_POST['skill_type'] ?? ''),
                    ':name_en' => sanitize($_POST['name_en'] ?? ''),
                    ':name_fa' => sanitize($_POST['name_fa'] ?? ''),
                    ':description' => $_POST['description'] ?? '',
                    ':cooldown' => $_POST['cooldown'] !== '' ? (float) $_POST['cooldown'] : null,
                    ':mana_cost' => $_POST['mana_cost'] !== '' ? (float) $_POST['mana_cost'] : null,
                    ':damage_type' => sanitize($_POST['damage_type'] ?? ''),
                    ':tips' => $_POST['tips'] ?? '',
                    ':image_url' => $imageUrl,
                    ':id' => $skillId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'تغییرات مهارت اعمال شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_combo':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $stmt = $db->prepare('INSERT INTO hero_combos (hero_id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty)
                    VALUES (:hero_id, :combo_name, :combo_name_fa, :combo_steps, :combo_description, :combo_description_fa, :difficulty)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':combo_name' => sanitize($_POST['combo_name'] ?? ''),
                    ':combo_name_fa' => sanitize($_POST['combo_name_fa'] ?? ''),
                    ':combo_steps' => $_POST['combo_steps'] ?? '',
                    ':combo_description' => $_POST['combo_description'] ?? '',
                    ':combo_description_fa' => $_POST['combo_description_fa'] ?? '',
                    ':difficulty' => sanitize($_POST['difficulty'] ?? 'Medium'),
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'کامبو جدید اضافه شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

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
                    ':combo_name' => sanitize($_POST['combo_name'] ?? ''),
                    ':combo_name_fa' => sanitize($_POST['combo_name_fa'] ?? ''),
                    ':combo_steps' => $_POST['combo_steps'] ?? '',
                    ':combo_description' => $_POST['combo_description'] ?? '',
                    ':combo_description_fa' => $_POST['combo_description_fa'] ?? '',
                    ':difficulty' => sanitize($_POST['difficulty'] ?? 'Medium'),
                    ':id' => $comboId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'کامبو به‌روزرسانی شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_tip':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $stmt = $db->prepare('INSERT INTO hero_tips (hero_id, tip_type, tip_text, tip_text_en, priority)
                    VALUES (:hero_id, :tip_type, :tip_text, :tip_text_en, :priority)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':tip_type' => sanitize($_POST['tip_type'] ?? ''),
                    ':tip_text' => $_POST['tip_text'] ?? '',
                    ':tip_text_en' => $_POST['tip_text_en'] !== '' ? $_POST['tip_text_en'] : ($_POST['tip_text'] ?? ''),
                    ':priority' => (int) ($_POST['priority'] ?? 0),
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نکته ذخیره شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

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
                    ':tip_type' => sanitize($_POST['tip_type'] ?? ''),
                    ':tip_text' => $_POST['tip_text'] ?? '',
                    ':tip_text_en' => $_POST['tip_text_en'] !== '' ? $_POST['tip_text_en'] : ($_POST['tip_text'] ?? ''),
                    ':priority' => (int) ($_POST['priority'] ?? 0),
                    ':id' => $tipId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نکته به‌روزرسانی شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_counter':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $imagePath = handle_image_upload('counter_image', 'counters');
                $manualImage = sanitize($_POST['image_url'] ?? '');
                $imageUrl = $imagePath ?? $manualImage;

                $stmt = $db->prepare('INSERT INTO hero_counters (hero_id, counter_hero, counter_hero_fa, counter_type, reason, reason_en, image_url)
                    VALUES (:hero_id, :counter_hero, :counter_hero_fa, :counter_type, :reason, :reason_en, :image_url)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':counter_hero' => sanitize($_POST['counter_hero'] ?? ''),
                    ':counter_hero_fa' => sanitize($_POST['counter_hero_fa'] ?? ''),
                    ':counter_type' => sanitize($_POST['counter_type'] ?? ''),
                    ':reason' => $_POST['reason'] ?? '',
                    ':reason_en' => $_POST['reason_en'] !== '' ? $_POST['reason_en'] : ($_POST['reason'] ?? ''),
                    ':image_url' => $imageUrl,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'کانتر ثبت شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'update_counter':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $counterId = (int) ($_POST['counter_id'] ?? 0);
                $imagePath = handle_image_upload('counter_image', 'counters');
                $manualImage = sanitize($_POST['image_url'] ?? '');
                $imageUrl = $imagePath ?? $manualImage;

                $stmt = $db->prepare('UPDATE hero_counters
                    SET counter_hero = :counter_hero,
                        counter_hero_fa = :counter_hero_fa,
                        counter_type = :counter_type,
                        reason = :reason,
                        reason_en = :reason_en,
                        image_url = :image_url
                    WHERE id = :id AND hero_id = :hero_id');
                $stmt->execute([
                    ':counter_hero' => sanitize($_POST['counter_hero'] ?? ''),
                    ':counter_hero_fa' => sanitize($_POST['counter_hero_fa'] ?? ''),
                    ':counter_type' => sanitize($_POST['counter_type'] ?? ''),
                    ':reason' => $_POST['reason'] ?? '',
                    ':reason_en' => $_POST['reason_en'] !== '' ? $_POST['reason_en'] : ($_POST['reason'] ?? ''),
                    ':image_url' => $imageUrl,
                    ':id' => $counterId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'کانتر به‌روزرسانی شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_strength':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $stmt = $db->prepare('INSERT INTO hero_strengths (hero_id, strength_text) VALUES (:hero_id, :strength_text)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':strength_text' => $_POST['strength_text'] ?? '',
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نقطه قوت ذخیره شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'update_strength':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $strengthId = (int) ($_POST['strength_id'] ?? 0);

                $stmt = $db->prepare('UPDATE hero_strengths SET strength_text = :strength_text WHERE id = :id AND hero_id = :hero_id');
                $stmt->execute([
                    ':strength_text' => $_POST['strength_text'] ?? '',
                    ':id' => $strengthId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نقطه قوت ویرایش شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'add_weakness':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $stmt = $db->prepare('INSERT INTO hero_weaknesses (hero_id, weakness_text) VALUES (:hero_id, :weakness_text)');
                $stmt->execute([
                    ':hero_id' => $heroId,
                    ':weakness_text' => $_POST['weakness_text'] ?? '',
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نقطه ضعف ذخیره شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;

            case 'update_weakness':
                $heroId = (int) ($_POST['hero_id'] ?? 0);
                $weaknessId = (int) ($_POST['weakness_id'] ?? 0);

                $stmt = $db->prepare('UPDATE hero_weaknesses SET weakness_text = :weakness_text WHERE id = :id AND hero_id = :hero_id');
                $stmt->execute([
                    ':weakness_text' => $_POST['weakness_text'] ?? '',
                    ':id' => $weaknessId,
                    ':hero_id' => $heroId,
                ]);

                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => 'نقطه ضعف ویرایش شد.'];
                $target = mlbb_admin_url('index.php') . '?hero=' . $heroId;
                if ($anchor !== '') {
                    $target .= '#' . $anchor;
                }
                header('Location: ' . $target);
                exit;
        }
    }
} catch (RuntimeException $exception) {
    $error = 'خطا: ' . $exception->getMessage();
} catch (PDOException $exception) {
    $error = 'خطا: ' . $exception->getMessage();
}

$heroes = $db->query('SELECT h.id, h.name_fa, h.name_en, r.name_fa AS role_name, h.difficulty, h.image_url FROM heroes h JOIN hero_roles r ON h.role_id = r.id ORDER BY h.name_fa')->fetchAll();
$stats = [
    'heroes' => (int) $db->query('SELECT COUNT(*) FROM heroes')->fetchColumn(),
    'skills' => (int) $db->query('SELECT COUNT(*) FROM hero_skills')->fetchColumn(),
    'combos' => (int) $db->query('SELECT COUNT(*) FROM hero_combos')->fetchColumn(),
    'tips' => (int) $db->query('SELECT COUNT(*) FROM hero_tips')->fetchColumn(),
];
$roles = $db->query('SELECT id, name_fa FROM hero_roles ORDER BY id')->fetchAll();

$activeHeroId = null;
if (isset($_GET['hero'])) {
    $activeHeroId = (int) $_GET['hero'];
}

if ($activeHeroId === null && !empty($heroes)) {
    $activeHeroId = (int) $heroes[0]['id'];
}

$activeHero = null;
$activeHeroRole = '';
$heroSkills = [];
$heroCombos = [];
$heroTips = [];
$heroCounters = [];
$heroStrengths = [];
$heroWeaknesses = [];

if ($activeHeroId) {
    $heroStmt = $db->prepare('SELECT * FROM heroes WHERE id = :id');
    $heroStmt->execute([':id' => $activeHeroId]);
    $activeHero = $heroStmt->fetch();

    if ($activeHero) {
        foreach ($heroes as $heroRow) {
            if ((int) ($heroRow['id'] ?? 0) === (int) $activeHero['id']) {
                $activeHeroRole = $heroRow['role_name'] ?? '';
                break;
            }
        }

        $skillStmt = $db->prepare("SELECT * FROM hero_skills WHERE hero_id = :id ORDER BY FIELD(skill_type, 'Passive','Skill1','Skill2','Skill3','Ultimate'), id");
        $skillStmt->execute([':id' => $activeHeroId]);
        $heroSkills = $skillStmt->fetchAll();

        $comboStmt = $db->prepare('SELECT * FROM hero_combos WHERE hero_id = :id ORDER BY id');
        $comboStmt->execute([':id' => $activeHeroId]);
        $heroCombos = $comboStmt->fetchAll();

        $tipStmt = $db->prepare('SELECT * FROM hero_tips WHERE hero_id = :id ORDER BY priority DESC, id DESC');
        $tipStmt->execute([':id' => $activeHeroId]);
        $heroTips = $tipStmt->fetchAll();

        $counterStmt = $db->prepare('SELECT * FROM hero_counters WHERE hero_id = :id ORDER BY id');
        $counterStmt->execute([':id' => $activeHeroId]);
        $heroCounters = $counterStmt->fetchAll();

        $strengthStmt = $db->prepare('SELECT * FROM hero_strengths WHERE hero_id = :id ORDER BY id');
        $strengthStmt->execute([':id' => $activeHeroId]);
        $heroStrengths = $strengthStmt->fetchAll();

        $weaknessStmt = $db->prepare('SELECT * FROM hero_weaknesses WHERE hero_id = :id ORDER BY id');
        $weaknessStmt->execute([':id' => $activeHeroId]);
        $heroWeaknesses = $weaknessStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | آکادمی MLBB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
    <style>
        .admin-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }
        .admin-links a {
            background: rgba(148, 163, 184, 0.18);
            padding: 0.55rem 1.2rem;
            border-radius: 999px;
            font-size: 0.9rem;
        }
        .admin-links a.primary-link {
            background: linear-gradient(135deg, #4c1d95, #2563eb);
            color: #fff;
        }
        .admin-sections {
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }
        .panel-card {
            background: var(--card-bg);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 24px;
            padding: 2.2rem;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.4);
            position: relative;
            overflow: hidden;
        }
        .panel-card::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.2), transparent 55%);
        }
        .panel-card > * {
            position: relative;
            z-index: 1;
        }
        .section-heading {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 1.5rem;
        }
        .hero-selector {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .hero-selector label {
            font-weight: 600;
        }
        .secondary-button {
            background: rgba(148, 163, 184, 0.2);
            color: var(--text-color);
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.4rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .secondary-button:hover {
            background: rgba(148, 163, 184, 0.35);
        }
        .form-grid {
            display: grid;
            gap: 2rem;
        }
        .form-grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }
        .hero-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        .hero-summary {
            background: rgba(15, 23, 42, 0.55);
            border-radius: 22px;
            padding: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            width: min(320px, 100%);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .hero-summary img {
            width: 100%;
            border-radius: 18px;
            object-fit: cover;
            aspect-ratio: 1;
        }
        .hero-summary .placeholder {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 18px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px dashed rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted-color);
        }
        .hero-summary ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
            font-size: 0.85rem;
        }
        .hero-summary li span {
            display: block;
            color: var(--muted-color);
            font-size: 0.75rem;
        }
        .management-section {
            margin-top: 3rem;
        }
        .section-subtitle {
            font-size: 1.2rem;
            margin-bottom: 1.2rem;
            border-bottom: 2px solid rgba(148, 163, 184, 0.35);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        .management-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .management-card {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .management-card h3 {
            margin: 0;
            font-size: 1.05rem;
        }
        .thumbnail {
            width: 100%;
            border-radius: 16px;
            aspect-ratio: 1.4;
            object-fit: cover;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        input[type="file"] {
            border-radius: 14px;
            border: 1px dashed rgba(148, 163, 184, 0.4);
            padding: 0.8rem;
            color: var(--muted-color);
            background: rgba(15, 23, 42, 0.7);
        }
        .table-wrapper {
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        .table td:last-child,
        .table th:last-child {
            text-align: center;
        }
        .table-link {
            color: var(--accent-color);
            font-weight: 600;
        }
        .empty-state {
            margin-top: 1rem;
            color: var(--muted-color);
        }
        .pill-muted {
            background: rgba(148, 163, 184, 0.18);
            color: var(--text-color);
            border-radius: 999px;
            padding: 0.25rem 0.8rem;
            font-size: 0.8rem;
        }
        .form-note {
            color: var(--muted-color);
            font-size: 0.8rem;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="brand">داشبورد مدیریت MLBB</div>
        <div class="admin-links">
            <a class="primary-link" href="#add-hero">افزودن هیرو</a>
            <a href="#manage">مدیریت داده‌ها</a>
            <a href="<?= htmlspecialchars(mlbb_url(), ENT_QUOTES, 'UTF-8') ?>">نمایش سایت</a>
            <a href="<?= htmlspecialchars(mlbb_admin_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>">خروج</a>
        </div>
    </nav>

    <div class="container admin-sections">
        <div class="admin-header">
            <div>
                <h1>سلام، مدیر!</h1>
                <p>از اینجا می‌توانی همه چیز را دقیق و سریع مدیریت کنی.</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <span class="label">تعداد هیروها</span>
                <span class="value"><?= $stats['heroes'] ?></span>
            </div>
            <div class="stat-card">
                <span class="label">تعداد مهارت‌ها</span>
                <span class="value"><?= $stats['skills'] ?></span>
            </div>
            <div class="stat-card">
                <span class="label">تعداد کامبوها</span>
                <span class="value"><?= $stats['combos'] ?></span>
            </div>
            <div class="stat-card">
                <span class="label">تعداد نکات</span>
                <span class="value"><?= $stats['tips'] ?></span>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert" style="margin-top:1rem;">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error" style="margin-top:1rem;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section id="add-hero" class="panel-card">
            <h2 class="section-title">افزودن هیرو جدید</h2>
            <form method="post" enctype="multipart/form-data" class="form-grid form-grid-2">
                <input type="hidden" name="action" value="add_hero">
                <div>
                    <div class="form-group">
                        <label>نام فارسی</label>
                        <input type="text" name="name_fa" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نام انگلیسی</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نقش</label>
                        <select name="role_id" class="form-control" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>تخصص</label>
                        <input type="text" name="specialty" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>درجه سختی</label>
                        <select name="difficulty" class="form-control">
                            <option value="Easy">آسان</option>
                            <option value="Medium" selected>متوسط</option>
                            <option value="Hard">سخت</option>
                            <option value="Expert">حرفه‌ای</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>تصویر (بارگذاری یا لینک)</label>
                        <input type="file" name="hero_image" accept="image/*">
                        <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/name.png">
                        <p class="form-note">در صورت آپلود، مسیر ذخیره به‌صورت خودکار ثبت می‌شود.</p>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label>توضیحات</label>
                        <textarea name="description" rows="6" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>نکات کلیدی</label>
                        <textarea name="tips" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>HP</label>
                            <input type="number" name="hp" class="form-control" min="0">
                        </div>
                        <div>
                            <label>HP Regen</label>
                            <input type="number" step="0.1" name="hp_regen" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Mana</label>
                            <input type="number" name="mana" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Mana Regen</label>
                            <input type="number" step="0.1" name="mana_regen" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Physical Attack</label>
                            <input type="number" name="physical_attack" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Magic Power</label>
                            <input type="number" name="magic_power" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Physical Defense</label>
                            <input type="number" name="physical_defense" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Magic Defense</label>
                            <input type="number" name="magic_defense" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Attack Speed</label>
                            <input type="number" step="0.01" name="attack_speed" class="form-control" min="0">
                        </div>
                        <div>
                            <label>Movement Speed</label>
                            <input type="number" name="movement_speed" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="primary">ذخیره هیرو</button>
                    </div>
                </div>
            </form>
        </section>

        <section id="manage" class="panel-card">
            <div class="section-heading">
                <h2 class="section-title">مدیریت هیروهای موجود</h2>
                <form method="get" class="hero-selector">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="hero-select">انتخاب هیرو</label>
                        <select id="hero-select" name="hero" class="form-control" style="min-width:220px;">
                            <option value="">---</option>
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>" <?= $activeHeroId === (int) $hero['id'] ? 'selected' : '' ?>><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="secondary-button">نمایش</button>
                </form>
            </div>

            <?php if ($activeHero): ?>
                <div class="hero-overview">
                    <div class="hero-summary">
                        <?php $heroImage = resolve_image_src($activeHero['image_url'] ?? ''); ?>
                        <?php if ($heroImage): ?>
                            <img src="<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($activeHero['name_fa'] ?? $activeHero['name_en']) ?>">
                        <?php else: ?>
                            <div class="placeholder">بدون تصویر</div>
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($activeHero['name_fa']) ?></strong>
                            <div class="pill-muted" style="margin-top:0.5rem;">نقش: <?= htmlspecialchars($activeHeroRole) ?></div>
                        </div>
                        <ul>
                            <li><span>HP</span><?= (int) $activeHero['hp'] ?></li>
                            <li><span>HP Regen</span><?= $activeHero['hp_regen'] !== null ? htmlspecialchars($activeHero['hp_regen']) : '-' ?></li>
                            <li><span>Mana</span><?= (int) $activeHero['mana'] ?></li>
                            <li><span>Mana Regen</span><?= $activeHero['mana_regen'] !== null ? htmlspecialchars($activeHero['mana_regen']) : '-' ?></li>
                            <li><span>Physical Attack</span><?= (int) $activeHero['physical_attack'] ?></li>
                            <li><span>Magic Power</span><?= (int) $activeHero['magic_power'] ?></li>
                            <li><span>Physical Defense</span><?= (int) $activeHero['physical_defense'] ?></li>
                            <li><span>Magic Defense</span><?= (int) $activeHero['magic_defense'] ?></li>
                            <li><span>Attack Speed</span><?= $activeHero['attack_speed'] !== null ? htmlspecialchars($activeHero['attack_speed']) : '-' ?></li>
                            <li><span>Movement Speed</span><?= (int) $activeHero['movement_speed'] ?></li>
                        </ul>
                    </div>
                    <form method="post" enctype="multipart/form-data" class="form-grid form-grid-2" style="flex:1;">
                        <input type="hidden" name="action" value="update_hero">
                        <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                        <input type="hidden" name="redirect_anchor" value="manage">
                        <div>
                            <div class="form-group">
                                <label>نام فارسی</label>
                                <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($activeHero['name_fa']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>نام انگلیسی</label>
                                <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($activeHero['name_en']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>نقش</label>
                                <select name="role_id" class="form-control" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= (int) $role['id'] ?>" <?= $activeHero['role_id'] == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name_fa']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>تخصص</label>
                                <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($activeHero['specialty'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>درجه سختی</label>
                                <select name="difficulty" class="form-control">
                                    <?php foreach (['Easy' => 'آسان', 'Medium' => 'متوسط', 'Hard' => 'سخت', 'Expert' => 'حرفه‌ای'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($activeHero['difficulty'] ?? 'Medium') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>تصویر (بارگذاری یا لینک)</label>
                                <input type="file" name="hero_image" accept="image/*">
                                <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($activeHero['image_url'] ?? '') ?>">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>توضیحات</label>
                                <textarea name="description" rows="6" class="form-control"><?= htmlspecialchars($activeHero['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>نکات کلیدی</label>
                                <textarea name="tips" rows="4" class="form-control"><?= htmlspecialchars($activeHero['tips'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group grid grid-2">
                                <div>
                                    <label>HP</label>
                                    <input type="number" name="hp" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['hp'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>HP Regen</label>
                                    <input type="number" step="0.1" name="hp_regen" class="form-control" value="<?= htmlspecialchars($activeHero['hp_regen'] !== null ? (string) $activeHero['hp_regen'] : '') ?>">
                                </div>
                                <div>
                                    <label>Mana</label>
                                    <input type="number" name="mana" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['mana'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>Mana Regen</label>
                                    <input type="number" step="0.1" name="mana_regen" class="form-control" value="<?= htmlspecialchars($activeHero['mana_regen'] !== null ? (string) $activeHero['mana_regen'] : '') ?>">
                                </div>
                                <div>
                                    <label>Physical Attack</label>
                                    <input type="number" name="physical_attack" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['physical_attack'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>Magic Power</label>
                                    <input type="number" name="magic_power" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['magic_power'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>Physical Defense</label>
                                    <input type="number" name="physical_defense" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['physical_defense'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>Magic Defense</label>
                                    <input type="number" name="magic_defense" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['magic_defense'] ?? 0)) ?>">
                                </div>
                                <div>
                                    <label>Attack Speed</label>
                                    <input type="number" step="0.01" name="attack_speed" class="form-control" value="<?= htmlspecialchars($activeHero['attack_speed'] !== null ? (string) $activeHero['attack_speed'] : '') ?>">
                                </div>
                                <div>
                                    <label>Movement Speed</label>
                                    <input type="number" name="movement_speed" class="form-control" value="<?= htmlspecialchars((string) ($activeHero['movement_speed'] ?? 0)) ?>">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="primary">ذخیره تغییرات</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="management-section" id="skills">
                    <div class="section-subtitle">مهارت‌ها</div>
                    <div class="management-grid">
                        <?php foreach ($heroSkills as $skill): ?>
                            <?php $skillImage = resolve_image_src($skill['image_url'] ?? ''); ?>
                            <form method="post" enctype="multipart/form-data" class="management-card">
                                <h3><?= htmlspecialchars($skill['name_fa']) ?> <span class="pill-muted"><?= htmlspecialchars($skill['skill_type']) ?></span></h3>
                                <?php if ($skillImage): ?>
                                    <img src="<?= htmlspecialchars($skillImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($skill['name_fa']) ?>" class="thumbnail">
                                <?php endif; ?>
                                <input type="hidden" name="action" value="update_skill">
                                <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="skills">
                                <div class="form-group">
                                    <label>نوع</label>
                                    <select name="skill_type" class="form-control">
                                        <?php foreach (['Passive','Skill1','Skill2','Skill3','Ultimate'] as $type): ?>
                                            <option value="<?= $type ?>" <?= $skill['skill_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>نام فارسی</label>
                                    <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($skill['name_fa']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>نام انگلیسی</label>
                                    <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($skill['name_en']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>تصویر</label>
                                    <input type="file" name="skill_image" accept="image/*">
                                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($skill['image_url'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>توضیحات</label>
                                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($skill['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group grid grid-2">
                                    <div>
                                        <label>کول‌دان</label>
                                        <input type="number" step="0.1" name="cooldown" class="form-control" value="<?= htmlspecialchars($skill['cooldown'] !== null ? (string) $skill['cooldown'] : '') ?>">
                                    </div>
                                    <div>
                                        <label>هزینه مانا</label>
                                        <input type="number" step="0.1" name="mana_cost" class="form-control" value="<?= htmlspecialchars($skill['mana_cost'] !== null ? (string) $skill['mana_cost'] : '') ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>نوع آسیب</label>
                                    <input type="text" name="damage_type" class="form-control" value="<?= htmlspecialchars($skill['damage_type'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>نکته</label>
                                    <textarea name="tips" rows="2" class="form-control"><?= htmlspecialchars($skill['tips'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="primary">ذخیره مهارت</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" enctype="multipart/form-data" class="management-card">
                            <h3>افزودن مهارت</h3>
                            <input type="hidden" name="action" value="add_skill">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="skills">
                            <div class="form-group">
                                <label>نوع</label>
                                <select name="skill_type" class="form-control">
                                    <option value="Passive">Passive</option>
                                    <option value="Skill1">Skill 1</option>
                                    <option value="Skill2">Skill 2</option>
                                    <option value="Skill3">Skill 3</option>
                                    <option value="Ultimate">Ultimate</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>نام فارسی</label>
                                <input type="text" name="name_fa" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>نام انگلیسی</label>
                                <input type="text" name="name_en" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>تصویر</label>
                                <input type="file" name="skill_image" accept="image/*">
                                <input type="text" name="image_url" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>توضیحات</label>
                                <textarea name="description" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group grid grid-2">
                                <div>
                                    <label>کول‌دان</label>
                                    <input type="number" step="0.1" name="cooldown" class="form-control">
                                </div>
                                <div>
                                    <label>هزینه مانا</label>
                                    <input type="number" step="0.1" name="mana_cost" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>نوع آسیب</label>
                                <input type="text" name="damage_type" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>نکته</label>
                                <textarea name="tips" rows="2" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="primary">ثبت مهارت</button>
                        </form>
                    </div>
                </div>

                <div class="management-section" id="combos">
                    <div class="section-subtitle">کامبوها</div>
                    <div class="management-grid">
                        <?php foreach ($heroCombos as $combo): ?>
                            <form method="post" class="management-card">
                                <h3><?= htmlspecialchars($combo['combo_name_fa']) ?> <span class="pill-muted"><?= htmlspecialchars($combo['difficulty']) ?></span></h3>
                                <input type="hidden" name="action" value="update_combo">
                                <input type="hidden" name="combo_id" value="<?= (int) $combo['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="combos">
                                <div class="form-group">
                                    <label>نام فارسی</label>
                                    <input type="text" name="combo_name_fa" class="form-control" value="<?= htmlspecialchars($combo['combo_name_fa']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>نام انگلیسی</label>
                                    <input type="text" name="combo_name" class="form-control" value="<?= htmlspecialchars($combo['combo_name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>مراحل</label>
                                    <textarea name="combo_steps" rows="3" class="form-control"><?= htmlspecialchars($combo['combo_steps'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>توضیح فارسی</label>
                                    <textarea name="combo_description_fa" rows="3" class="form-control"><?= htmlspecialchars($combo['combo_description_fa'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>توضیح انگلیسی</label>
                                    <textarea name="combo_description" rows="3" class="form-control"><?= htmlspecialchars($combo['combo_description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>درجه سختی</label>
                                    <select name="difficulty" class="form-control">
                                        <?php foreach (['Easy','Medium','Hard','Expert'] as $difficult): ?>
                                            <option value="<?= $difficult ?>" <?= $combo['difficulty'] === $difficult ? 'selected' : '' ?>><?= $difficult ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="primary">ذخیره کامبو</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" class="management-card">
                            <h3>افزودن کامبو</h3>
                            <input type="hidden" name="action" value="add_combo">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="combos">
                            <div class="form-group">
                                <label>نام فارسی</label>
                                <input type="text" name="combo_name_fa" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>نام انگلیسی</label>
                                <input type="text" name="combo_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>مراحل</label>
                                <textarea name="combo_steps" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>توضیح فارسی</label>
                                <textarea name="combo_description_fa" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>توضیح انگلیسی</label>
                                <textarea name="combo_description" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>درجه سختی</label>
                                <select name="difficulty" class="form-control">
                                    <option value="Easy">Easy</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Hard">Hard</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <button type="submit" class="primary">ثبت کامبو</button>
                        </form>
                    </div>
                </div>

                <div class="management-section" id="tips">
                    <div class="section-subtitle">نکات</div>
                    <div class="management-grid">
                        <?php foreach ($heroTips as $tip): ?>
                            <form method="post" class="management-card">
                                <h3><?= htmlspecialchars($tip['tip_type']) ?> <span class="pill-muted">اولویت <?= (int) $tip['priority'] ?></span></h3>
                                <input type="hidden" name="action" value="update_tip">
                                <input type="hidden" name="tip_id" value="<?= (int) $tip['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="tips">
                                <div class="form-group">
                                    <label>نوع</label>
                                    <select name="tip_type" class="form-control">
                                        <?php foreach (['General','Combat','Strategy','Advanced'] as $tipType): ?>
                                            <option value="<?= $tipType ?>" <?= $tip['tip_type'] === $tipType ? 'selected' : '' ?>><?= $tipType ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>متن فارسی</label>
                                    <textarea name="tip_text" rows="3" class="form-control"><?= htmlspecialchars($tip['tip_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>متن انگلیسی</label>
                                    <textarea name="tip_text_en" rows="3" class="form-control"><?= htmlspecialchars($tip['tip_text_en'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>اولویت</label>
                                    <input type="number" name="priority" class="form-control" value="<?= (int) $tip['priority'] ?>">
                                </div>
                                <button type="submit" class="primary">ذخیره نکته</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" class="management-card">
                            <h3>افزودن نکته</h3>
                            <input type="hidden" name="action" value="add_tip">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="tips">
                            <div class="form-group">
                                <label>نوع</label>
                                <select name="tip_type" class="form-control">
                                    <option value="General">General</option>
                                    <option value="Combat">Combat</option>
                                    <option value="Strategy">Strategy</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>متن فارسی</label>
                                <textarea name="tip_text" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>متن انگلیسی</label>
                                <textarea name="tip_text_en" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>اولویت</label>
                                <input type="number" name="priority" class="form-control" value="0">
                            </div>
                            <button type="submit" class="primary">ثبت نکته</button>
                        </form>
                    </div>
                </div>

                <div class="management-section" id="counters">
                    <div class="section-subtitle">کانترها</div>
                    <div class="management-grid">
                        <?php foreach ($heroCounters as $counter): ?>
                            <?php $counterImage = resolve_image_src($counter['image_url'] ?? ''); ?>
                            <form method="post" enctype="multipart/form-data" class="management-card">
                                <h3><?= htmlspecialchars($counter['counter_hero_fa']) ?> <span class="pill-muted"><?= htmlspecialchars($counter['counter_type']) ?></span></h3>
                                <?php if ($counterImage): ?>
                                    <img src="<?= htmlspecialchars($counterImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($counter['counter_hero_fa']) ?>" class="thumbnail">
                                <?php endif; ?>
                                <input type="hidden" name="action" value="update_counter">
                                <input type="hidden" name="counter_id" value="<?= (int) $counter['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="counters">
                                <div class="form-group">
                                    <label>نام فارسی</label>
                                    <input type="text" name="counter_hero_fa" class="form-control" value="<?= htmlspecialchars($counter['counter_hero_fa']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>نام انگلیسی</label>
                                    <input type="text" name="counter_hero" class="form-control" value="<?= htmlspecialchars($counter['counter_hero']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>نوع</label>
                                    <select name="counter_type" class="form-control">
                                        <?php foreach (['Hard Counter','Soft Counter','Situational'] as $type): ?>
                                            <option value="<?= $type ?>" <?= $counter['counter_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>تصویر</label>
                                    <input type="file" name="counter_image" accept="image/*">
                                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($counter['image_url'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>دلیل فارسی</label>
                                    <textarea name="reason" rows="3" class="form-control"><?= htmlspecialchars($counter['reason'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>دلیل انگلیسی</label>
                                    <textarea name="reason_en" rows="3" class="form-control"><?= htmlspecialchars($counter['reason_en'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="primary">ذخیره کانتر</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" enctype="multipart/form-data" class="management-card">
                            <h3>افزودن کانتر</h3>
                            <input type="hidden" name="action" value="add_counter">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="counters">
                            <div class="form-group">
                                <label>نام فارسی</label>
                                <input type="text" name="counter_hero_fa" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>نام انگلیسی</label>
                                <input type="text" name="counter_hero" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>نوع</label>
                                <select name="counter_type" class="form-control">
                                    <option value="Hard Counter">Hard Counter</option>
                                    <option value="Soft Counter">Soft Counter</option>
                                    <option value="Situational">Situational</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>تصویر</label>
                                <input type="file" name="counter_image" accept="image/*">
                                <input type="text" name="image_url" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>دلیل فارسی</label>
                                <textarea name="reason" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>دلیل انگلیسی</label>
                                <textarea name="reason_en" rows="3" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="primary">ثبت کانتر</button>
                        </form>
                    </div>
                </div>

                <div class="management-section" id="strengths">
                    <div class="section-subtitle">نقاط قوت</div>
                    <div class="management-grid">
                        <?php foreach ($heroStrengths as $strength): ?>
                            <form method="post" class="management-card">
                                <h3>ویرایش نقطه قوت</h3>
                                <input type="hidden" name="action" value="update_strength">
                                <input type="hidden" name="strength_id" value="<?= (int) $strength['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="strengths">
                                <div class="form-group">
                                    <label>متن</label>
                                    <textarea name="strength_text" rows="3" class="form-control"><?= htmlspecialchars($strength['strength_text'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="primary">ذخیره متن</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" class="management-card">
                            <h3>افزودن نقطه قوت</h3>
                            <input type="hidden" name="action" value="add_strength">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="strengths">
                            <div class="form-group">
                                <label>متن</label>
                                <textarea name="strength_text" rows="3" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="primary">ثبت</button>
                        </form>
                    </div>
                </div>

                <div class="management-section" id="weaknesses">
                    <div class="section-subtitle">نقاط ضعف</div>
                    <div class="management-grid">
                        <?php foreach ($heroWeaknesses as $weakness): ?>
                            <form method="post" class="management-card">
                                <h3>ویرایش نقطه ضعف</h3>
                                <input type="hidden" name="action" value="update_weakness">
                                <input type="hidden" name="weakness_id" value="<?= (int) $weakness['id'] ?>">
                                <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                                <input type="hidden" name="redirect_anchor" value="weaknesses">
                                <div class="form-group">
                                    <label>متن</label>
                                    <textarea name="weakness_text" rows="3" class="form-control"><?= htmlspecialchars($weakness['weakness_text'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="primary">ذخیره متن</button>
                            </form>
                        <?php endforeach; ?>

                        <form method="post" class="management-card">
                            <h3>افزودن نقطه ضعف</h3>
                            <input type="hidden" name="action" value="add_weakness">
                            <input type="hidden" name="hero_id" value="<?= (int) $activeHero['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="weaknesses">
                            <div class="form-group">
                                <label>متن</label>
                                <textarea name="weakness_text" rows="3" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="primary">ثبت</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <p class="empty-state">برای ویرایش ابتدا یک هیرو انتخاب کن.</p>
            <?php endif; ?>
        </section>

        <section class="panel-card">
            <h2 class="section-title">لیست تمام هیروها</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام فارسی</th>
                            <th>نام انگلیسی</th>
                            <th>نقش</th>
                            <th>درجه سختی</th>
                            <th>ویرایش</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($heroes as $hero): ?>
                            <tr>
                                <td><?= (int) $hero['id'] ?></td>
                                <td><?= htmlspecialchars($hero['name_fa']) ?></td>
                                <td><?= htmlspecialchars($hero['name_en']) ?></td>
                                <td><?= htmlspecialchars($hero['role_name']) ?></td>
                                <td><?= htmlspecialchars($hero['difficulty']) ?></td>
                                <td><a class="table-link" href="<?= htmlspecialchars(mlbb_admin_url('index.php') . '?hero=' . (int) $hero['id'] . '#manage', ENT_QUOTES, 'UTF-8') ?>">ویرایش</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
