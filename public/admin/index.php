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

$message = null;
$error = null;

function sanitize(string $value): string
{
    return trim($value);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add_hero':
                $stmt = $db->prepare('INSERT INTO heroes (name_en, name_fa, role_id, specialty, hp, hp_regen, mana, mana_regen, physical_attack, magic_power, physical_defense, magic_defense, attack_speed, movement_speed, difficulty, description, tips, image_url)
                    VALUES (:name_en, :name_fa, :role_id, :specialty, :hp, :hp_regen, :mana, :mana_regen, :physical_attack, :magic_power, :physical_defense, :magic_defense, :attack_speed, :movement_speed, :difficulty, :description, :tips, :image_url)');
                $stmt->execute([
                    ':name_en' => sanitize($_POST['name_en']),
                    ':name_fa' => sanitize($_POST['name_fa']),
                    ':role_id' => (int) $_POST['role_id'],
                    ':specialty' => sanitize($_POST['specialty']),
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
                    ':difficulty' => sanitize($_POST['difficulty']),
                    ':description' => $_POST['description'],
                    ':tips' => $_POST['tips'],
                    ':image_url' => sanitize($_POST['image_url']),
                ]);
                $message = 'هیرو جدید با موفقیت اضافه شد.';
                break;
            case 'add_skill':
                $stmt = $db->prepare('INSERT INTO hero_skills (hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url)
                    VALUES (:hero_id, :skill_type, :name_en, :name_fa, :description, :cooldown, :mana_cost, :damage_type, :tips, :image_url)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':skill_type' => sanitize($_POST['skill_type']),
                    ':name_en' => sanitize($_POST['name_en']),
                    ':name_fa' => sanitize($_POST['name_fa']),
                    ':description' => $_POST['description'],
                    ':cooldown' => $_POST['cooldown'] !== '' ? (float) $_POST['cooldown'] : null,
                    ':mana_cost' => $_POST['mana_cost'] !== '' ? (float) $_POST['mana_cost'] : null,
                    ':damage_type' => sanitize($_POST['damage_type']),
                    ':tips' => $_POST['tips'],
                    ':image_url' => sanitize($_POST['image_url']),
                ]);
                $message = 'مهارت جدید اضافه شد.';
                break;
            case 'add_combo':
                $stmt = $db->prepare('INSERT INTO hero_combos (hero_id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty)
                    VALUES (:hero_id, :combo_name, :combo_name_fa, :combo_steps, :combo_description, :combo_description_fa, :difficulty)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':combo_name' => sanitize($_POST['combo_name']),
                    ':combo_name_fa' => sanitize($_POST['combo_name_fa']),
                    ':combo_steps' => $_POST['combo_steps'],
                    ':combo_description' => $_POST['combo_description'],
                    ':combo_description_fa' => $_POST['combo_description_fa'],
                    ':difficulty' => sanitize($_POST['difficulty']),
                ]);
                $message = 'کامبو جدید ثبت شد.';
                break;
            case 'add_tip':
                $stmt = $db->prepare('INSERT INTO hero_tips (hero_id, tip_type, tip_text, tip_text_en, priority)
                    VALUES (:hero_id, :tip_type, :tip_text, :tip_text_en, :priority)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':tip_type' => sanitize($_POST['tip_type']),
                    ':tip_text' => $_POST['tip_text'],
                    ':tip_text_en' => $_POST['tip_text_en'] ?: $_POST['tip_text'],
                    ':priority' => (int) ($_POST['priority'] ?? 0),
                ]);
                $message = 'نکته اضافه شد.';
                break;
            case 'add_counter':
                $stmt = $db->prepare('INSERT INTO hero_counters (hero_id, counter_hero, counter_hero_fa, counter_type, reason, reason_en, image_url)
                    VALUES (:hero_id, :counter_hero, :counter_hero_fa, :counter_type, :reason, :reason_en, :image_url)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':counter_hero' => sanitize($_POST['counter_hero']),
                    ':counter_hero_fa' => sanitize($_POST['counter_hero_fa']),
                    ':counter_type' => sanitize($_POST['counter_type']),
                    ':reason' => $_POST['reason'],
                    ':reason_en' => $_POST['reason_en'] ?: $_POST['reason'],
                    ':image_url' => sanitize($_POST['image_url']),
                ]);
                $message = 'کانتر ذخیره شد.';
                break;
            case 'add_strength':
                $stmt = $db->prepare('INSERT INTO hero_strengths (hero_id, strength_text) VALUES (:hero_id, :strength_text)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':strength_text' => $_POST['strength_text'],
                ]);
                $message = 'نقطه قوت ثبت شد.';
                break;
            case 'add_weakness':
                $stmt = $db->prepare('INSERT INTO hero_weaknesses (hero_id, weakness_text) VALUES (:hero_id, :weakness_text)');
                $stmt->execute([
                    ':hero_id' => (int) $_POST['hero_id'],
                    ':weakness_text' => $_POST['weakness_text'],
                ]);
                $message = 'نقطه ضعف ثبت شد.';
                break;
        }
    }
} catch (PDOException $exception) {
    $error = 'خطا: ' . $exception->getMessage();
}

$heroes = $db->query('SELECT h.id, h.name_fa, r.name_fa AS role_name, h.difficulty FROM heroes h JOIN hero_roles r ON h.role_id = r.id ORDER BY h.id DESC')->fetchAll();
$stats = [
    'heroes' => (int) $db->query('SELECT COUNT(*) FROM heroes')->fetchColumn(),
    'skills' => (int) $db->query('SELECT COUNT(*) FROM hero_skills')->fetchColumn(),
    'combos' => (int) $db->query('SELECT COUNT(*) FROM hero_combos')->fetchColumn(),
    'tips' => (int) $db->query('SELECT COUNT(*) FROM hero_tips')->fetchColumn(),
];
$roles = $db->query('SELECT id, name_fa FROM hero_roles ORDER BY id')->fetchAll();
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
        .dashboard-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .stat-card {
            background: linear-gradient(145deg, rgba(37, 99, 235, 0.3), rgba(76, 29, 149, 0.45));
            border-radius: 22px;
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            box-shadow: 0 20px 40px rgba(14, 30, 78, 0.45);
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: rgba(226, 232, 240, 0.9);
        }
        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
        }
        .cards-grid {
            display: grid;
            gap: 1.2rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="brand">داشبورد مدیریت MLBB</div>
        <div>
            <a href="<?= htmlspecialchars(mlbb_url(), ENT_QUOTES, 'UTF-8') ?>" style="margin-left:1rem;">مشاهده سایت</a>
            <a href="<?= htmlspecialchars(mlbb_admin_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>">خروج</a>
        </div>
    </nav>

    <div class="container">
        <div class="admin-header">
            <div>
                <h1>سلام، مدیر!</h1>
                <p>از اینجا تمام اطلاعات هیروها را مدیریت کن.</p>
            </div>
            <div class="admin-tabs">
                <div class="admin-tab active">مدیریت هیرو</div>
                <div class="admin-tab">مهارت‌ها</div>
                <div class="admin-tab">کامبوها</div>
                <div class="admin-tab">نکات</div>
                <div class="admin-tab">کانترها</div>
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

        <?php if ($message): ?>
            <div class="alert" style="margin-top:2rem;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error" style="margin-top:2rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="hero-card">
            <div class="section-title">افزودن هیرو جدید</div>
            <form method="post" class="grid grid-2" style="gap:2rem;">
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
                        <label>لینک تصویر</label>
                        <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/name.png">
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
                    <button type="submit" class="primary">ذخیره هیرو</button>
                </div>
            </form>
        </div>

        <div class="hero-card">
            <div class="section-title">افزودن داده تکمیلی</div>
            <div class="cards-grid">
                <form method="post" class="card">
                    <h3>مهارت جدید</h3>
                    <input type="hidden" name="action" value="add_skill">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>نوع مهارت</label>
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
                        <label>توضیحات</label>
                        <textarea name="description" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>کول‌دان (ثانیه)</label>
                        <input type="number" step="0.1" name="cooldown" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>هزینه مانا</label>
                        <input type="number" step="0.1" name="mana_cost" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>نوع آسیب</label>
                        <input type="text" name="damage_type" class="form-control" placeholder="Magic / Physical / ...">
                    </div>
                    <div class="form-group">
                        <label>نکته</label>
                        <textarea name="tips" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>لینک تصویر</label>
                        <input type="text" name="image_url" class="form-control">
                    </div>
                    <button type="submit" class="primary">ثبت مهارت</button>
                </form>

                <form method="post" class="card">
                    <h3>کامبو جدید</h3>
                    <input type="hidden" name="action" value="add_combo">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>نام فارسی کامبو</label>
                        <input type="text" name="combo_name_fa" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نام انگلیسی کامبو</label>
                        <input type="text" name="combo_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>مراحل</label>
                        <textarea name="combo_steps" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>توضیحات فارسی</label>
                        <textarea name="combo_description_fa" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>توضیحات انگلیسی</label>
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

                <form method="post" class="card">
                    <h3>نکته جدید</h3>
                    <input type="hidden" name="action" value="add_tip">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                        <textarea name="tip_text_en" rows="3" class="form-control" placeholder="اگر خالی بماند مثل فارسی ذخیره می‌شود"></textarea>
                    </div>
                    <div class="form-group">
                        <label>اولویت</label>
                        <input type="number" name="priority" class="form-control" min="0" value="0">
                    </div>
                    <button type="submit" class="primary">ذخیره نکته</button>
                </form>

                <form method="post" class="card">
                    <h3>کانتر جدید</h3>
                    <input type="hidden" name="action" value="add_counter">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>نام فارسی کانتر</label>
                        <input type="text" name="counter_hero_fa" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نام انگلیسی کانتر</label>
                        <input type="text" name="counter_hero" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>نوع کانتر</label>
                        <select name="counter_type" class="form-control">
                            <option value="Hard Counter">Hard Counter</option>
                            <option value="Soft Counter">Soft Counter</option>
                            <option value="Situational">Situational</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>دلیل فارسی</label>
                        <textarea name="reason" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>دلیل انگلیسی</label>
                        <textarea name="reason_en" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>لینک تصویر</label>
                        <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/diggie.png">
                    </div>
                    <button type="submit" class="primary">ذخیره کانتر</button>
                </form>

                <form method="post" class="card">
                    <h3>نقطه قوت</h3>
                    <input type="hidden" name="action" value="add_strength">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>متن</label>
                        <textarea name="strength_text" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="primary">ثبت نقطه قوت</button>
                </form>

                <form method="post" class="card">
                    <h3>نقطه ضعف</h3>
                    <input type="hidden" name="action" value="add_weakness">
                    <div class="form-group">
                        <label>هیرو</label>
                        <select name="hero_id" class="form-control">
                            <?php foreach ($heroes as $hero): ?>
                                <option value="<?= (int) $hero['id'] ?>"><?= htmlspecialchars($hero['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>متن</label>
                        <textarea name="weakness_text" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="primary">ثبت نقطه ضعف</button>
                </form>
            </div>
        </div>

        <div class="hero-card">
            <div class="section-title">لیست هیروها</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نقش</th>
                        <th>درجه سختی</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($heroes as $hero): ?>
                        <tr>
                            <td><?= (int) $hero['id'] ?></td>
                            <td><?= htmlspecialchars($hero['name_fa']) ?></td>
                            <td><?= htmlspecialchars($hero['role_name']) ?></td>
                            <td><?= htmlspecialchars($hero['difficulty']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
