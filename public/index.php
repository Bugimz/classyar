<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$database = new Database();
$db = $database->getConnection();

$heroId = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($heroId === null) {
    $stmt = $db->prepare('SELECT h.*, r.name_fa AS role_name_fa, r.name_en AS role_name_en FROM heroes h JOIN hero_roles r ON h.role_id = r.id ORDER BY h.name_en ASC LIMIT 1');
    $stmt->execute();
    $hero = $stmt->fetch();
    if ($hero) {
        $heroId = (int) $hero['id'];
    }
} else {
    $stmt = $db->prepare('SELECT h.*, r.name_fa AS role_name_fa, r.name_en AS role_name_en FROM heroes h JOIN hero_roles r ON h.role_id = r.id WHERE h.id = :id');
    $stmt->execute([':id' => $heroId]);
    $hero = $stmt->fetch();
}

if (!$hero) {
    http_response_code(404);
    echo 'هیچ هیرویی یافت نشد. ابتدا داده‌ها را وارد کنید.';
    exit;
}

$skillStmt = $db->prepare('SELECT * FROM hero_skills WHERE hero_id = :hero_id ORDER BY FIELD(skill_type, "Passive", "Skill1", "Skill2", "Skill3", "Ultimate")');
$skillStmt->execute([':hero_id' => $heroId]);
$skills = $skillStmt->fetchAll();

$comboStmt = $db->prepare('SELECT * FROM hero_combos WHERE hero_id = :hero_id ORDER BY id');
$comboStmt->execute([':hero_id' => $heroId]);
$combos = $comboStmt->fetchAll();

$tipStmt = $db->prepare('SELECT * FROM hero_tips WHERE hero_id = :hero_id ORDER BY priority DESC, id ASC');
$tipStmt->execute([':hero_id' => $heroId]);
$heroTips = $tipStmt->fetchAll();

$counterGroupStmt = $db->prepare('SELECT * FROM hero_counter_groups WHERE hero_id = :hero_id ORDER BY sort_order ASC, id ASC');
$counterGroupStmt->execute([':hero_id' => $heroId]);
$counterGroupsRaw = $counterGroupStmt->fetchAll(PDO::FETCH_ASSOC);

$counters = [];
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
        $counters[] = $group;
    }
}

$strengthStmt = $db->prepare('SELECT strength_text FROM hero_strengths WHERE hero_id = :hero_id ORDER BY id');
$strengthStmt->execute([':hero_id' => $heroId]);
$strengths = $strengthStmt->fetchAll(PDO::FETCH_COLUMN);

$weaknessStmt = $db->prepare('SELECT weakness_text FROM hero_weaknesses WHERE hero_id = :hero_id ORDER BY id');
$weaknessStmt->execute([':hero_id' => $heroId]);
$weaknesses = $weaknessStmt->fetchAll(PDO::FETCH_COLUMN);

$allHeroesStmt = $db->query('SELECT id, name_fa FROM heroes ORDER BY name_fa');
$allHeroes = $allHeroesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hero['name_fa']) ?> | آموزش Mobile Legends</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="brand">آکادمی Mobile Legends</div>
        <div>
            <a href="<?= htmlspecialchars(mlbb_admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">پنل مدیریت</a>
        </div>
    </nav>

    <div class="container">
        <div class="hero-card">
            <div class="grid grid-2">
                <div>
                    <h1><?= htmlspecialchars($hero['name_fa']) ?> <span class="badge"><?= htmlspecialchars($hero['role_name_fa']) ?></span></h1>
                    <p><?= nl2br(htmlspecialchars($hero['description'])) ?></p>
                    <div class="section-title">مشخصات کلی</div>
                    <div class="grid grid-2">
                        <div class="card">
                            <strong>اسم انگلیسی:</strong> <?= htmlspecialchars($hero['name_en']) ?><br>
                            <strong>رول:</strong> <?= htmlspecialchars($hero['role_name_fa']) ?> (<?= htmlspecialchars($hero['role_name_en']) ?>)<br>
                            <strong>تخصص:</strong> <?= htmlspecialchars($hero['specialty']) ?><br>
                            <strong>درجه سختی:</strong> <span class="pill"><?= htmlspecialchars($hero['difficulty']) ?></span>
                        </div>
                        <div class="card">
                            <strong>HP:</strong> <?= htmlspecialchars($hero['hp']) ?><br>
                            <strong>HP Regen:</strong> <?= htmlspecialchars($hero['hp_regen']) ?><br>
                            <strong>Mana:</strong> <?= htmlspecialchars($hero['mana']) ?><br>
                            <strong>Mana Regen:</strong> <?= htmlspecialchars($hero['mana_regen']) ?>
                        </div>
                    </div>
                    <div class="grid grid-2" style="margin-top: 1rem;">
                        <div class="card">
                            <strong>Physical Attack:</strong> <?= htmlspecialchars($hero['physical_attack']) ?><br>
                            <strong>Physical Defense:</strong> <?= htmlspecialchars($hero['physical_defense']) ?><br>
                            <strong>Magic Defense:</strong> <?= htmlspecialchars($hero['magic_defense']) ?><br>
                            <strong>Movement Speed:</strong> <?= htmlspecialchars($hero['movement_speed']) ?>
                        </div>
                        <div class="card">
                            <strong>Magic Power:</strong> <?= htmlspecialchars($hero['magic_power']) ?><br>
                            <strong>Attack Speed:</strong> <?= htmlspecialchars($hero['attack_speed']) ?><br>
                            <strong>نکته کلیدی:</strong> <?= nl2br(htmlspecialchars($hero['tips'])) ?>
                        </div>
                    </div>
                </div>
                <div>
                    <?php if (!empty($hero['image_url'])): ?>
                        <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= htmlspecialchars($hero['name_fa']) ?>" style="width:100%;border-radius:24px;box-shadow:0 20px 40px rgba(15,23,42,0.4);">
                    <?php else: ?>
                        <div class="card" style="height:100%;display:flex;align-items:center;justify-content:center;">
                            تصویر برای این هیرو ثبت نشده است
                        </div>
                    <?php endif; ?>
                    <div style="margin-top:1.5rem;">
                        <label for="hero-switch">تغییر هیرو:</label>
                        <select id="hero-switch" class="form-control" onchange="changeHero(this.value)">
                            <?php foreach ($allHeroes as $item): ?>
                                <option value="<?= (int) $item['id'] ?>" <?= $item['id'] == $heroId ? 'selected' : '' ?>><?= htmlspecialchars($item['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-card">
            <div class="section-title">مهارت‌ها</div>
            <div class="grid grid-2">
                <?php foreach ($skills as $skill): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($skill['name_fa']) ?> <span class="badge"><?= htmlspecialchars($skill['skill_type']) ?></span></h3>
                        <p><?= nl2br(htmlspecialchars($skill['description'])) ?></p>
                        <?php if (!empty($skill['cooldown'])): ?>
                            <div><strong>کول‌دان:</strong> <?= htmlspecialchars($skill['cooldown']) ?> ثانیه</div>
                        <?php endif; ?>
                        <?php if (!empty($skill['mana_cost'])): ?>
                            <div><strong>هزینه مانا:</strong> <?= htmlspecialchars($skill['mana_cost']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($skill['tips'])): ?>
                            <div class="badge" style="margin-top:0.8rem;">نکته: <?= htmlspecialchars($skill['tips']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="hero-card">
            <div class="section-title">کامبوهای مهم</div>
            <div class="grid grid-3">
                <?php foreach ($combos as $combo): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($combo['combo_name_fa']) ?></h3>
                        <div class="badge">درجه سختی: <?= htmlspecialchars($combo['difficulty']) ?></div>
                        <p style="margin-top:1rem;">
                            <strong>مراحل:</strong><br>
                            <?= nl2br(htmlspecialchars($combo['combo_steps'])) ?>
                        </p>
                        <p><?= nl2br(htmlspecialchars($combo['combo_description_fa'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="hero-card">
            <div class="section-title">کانترها و آیتم‌های پیشنهادی</div>
            <?php if ($counters): ?>
                <div class="counter-groups-grid">
                    <?php foreach ($counters as $group): ?>
                        <section class="counter-group">
                            <header class="counter-group__header">
                                <h3><?= htmlspecialchars($group['title_fa'] ?? $group['title_en']) ?></h3>
                                <?php if (!empty($group['description_fa'])): ?>
                                    <p><?= nl2br(htmlspecialchars($group['description_fa'])) ?></p>
                                <?php elseif (!empty($group['description_en'])): ?>
                                    <p><?= nl2br(htmlspecialchars($group['description_en'])) ?></p>
                                <?php endif; ?>
                            </header>
                            <div class="counter-entries counter-entries--<?= htmlspecialchars($group['group_type']) ?>">
                                <?php if (!empty($group['entities'])): ?>
                                    <?php foreach ($group['entities'] as $entity): ?>
                                        <?php
                                            $primaryName = $entity['name_fa'] ?? '';
                                            if ($primaryName === '') {
                                                $primaryName = $entity['name_en'] ?? '';
                                            }
                                            $secondaryName = $entity['name_en'] ?? '';
                                            $showSecondary = $secondaryName !== '' && $secondaryName !== $primaryName;
                                        ?>
                                        <figure class="counter-entry">
                                            <?php if (!empty($entity['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($entity['image_url']) ?>" alt="<?= htmlspecialchars($primaryName !== '' ? $primaryName : $secondaryName) ?>">
                                            <?php endif; ?>
                                            <figcaption>
                                                <span><?= htmlspecialchars($primaryName) ?></span>
                                                <?php if ($showSecondary): ?>
                                                    <small><?= htmlspecialchars($secondaryName) ?></small>
                                                <?php endif; ?>
                                            </figcaption>
                                        </figure>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="counter-empty">موردی ثبت نشده است.</p>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>برای این هیرو هنوز کانتری ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        <div class="hero-card">
            <div class="grid grid-2">
                <div>
                    <div class="section-title">نقاط قوت</div>
                    <ul>
                        <?php foreach ($strengths as $strength): ?>
                            <li><?= htmlspecialchars($strength) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <div class="section-title">نقاط ضعف</div>
                    <ul>
                        <?php foreach ($weaknesses as $weakness): ?>
                            <li><?= htmlspecialchars($weakness) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="hero-card">
            <div class="section-title">نکات پیشرفته</div>
            <div class="grid grid-2">
                <?php foreach ($heroTips as $tip): ?>
                    <div class="card">
                        <div class="badge"><?= htmlspecialchars($tip['tip_type']) ?></div>
                        <p><?= nl2br(htmlspecialchars($tip['tip_text'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function changeHero(id) {
            if (!id) return;
            window.location.href = '?id=' + id;
        }
    </script>
</body>
</html>
