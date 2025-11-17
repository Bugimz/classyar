<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$database = new Database();
$db = $database->getConnection();

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($itemId <= 0) {
    $fallbackStmt = $db->query('SELECT id FROM items ORDER BY name_fa ASC, name_en ASC LIMIT 1');
    $fallbackId = $fallbackStmt ? (int) $fallbackStmt->fetchColumn() : 0;
    $itemId = $fallbackId;
}

$itemStmt = $db->prepare('SELECT i.*, c.name_fa AS category_name_fa, c.name_en AS category_name_en FROM items i LEFT JOIN item_categories c ON c.id = i.category_id WHERE i.id = :id');
$itemStmt->execute([':id' => $itemId]);
$item = $itemStmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    http_response_code(404);
    echo 'آیتمی یافت نشد. لطفاً ابتدا داده‌ها را بارگذاری کنید.';
    exit;
}

$itemImageUrl = mlbb_asset_url($item['image_url'] ?? '');

$categoryItemsStmt = $db->prepare('SELECT id, name_fa, name_en FROM items WHERE category_id = :category_id ORDER BY name_fa ASC, name_en ASC');
$categoryItemsStmt->execute([':category_id' => $item['category_id']]);
$categoryItems = $categoryItemsStmt->fetchAll(PDO::FETCH_ASSOC);

$counterHeroStmt = $db->prepare('SELECT h.id, h.name_fa, h.name_en, h.image_url, cg.title_fa, cg.title_en FROM hero_counter_entities e JOIN hero_counter_groups cg ON cg.id = e.group_id JOIN heroes h ON h.id = cg.hero_id WHERE e.target_item_id = :item_id ORDER BY cg.sort_order ASC, h.name_fa ASC');
$counterHeroStmt->execute([':item_id' => $itemId]);
$referencedHeroes = $counterHeroStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['name_fa']) ?> | اطلاعات آیتم</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="brand">آکادمی Mobile Legends</div>
        <div>
            <a href="<?= htmlspecialchars(mlbb_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">بازگشت به هیروها</a>
            <a href="<?= htmlspecialchars(mlbb_admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">پنل مدیریت</a>
        </div>
    </nav>

    <div class="container">
        <div class="hero-card">
            <div class="grid grid-2">
                <div>
                    <h1><?= htmlspecialchars($item['name_fa']) ?> <span class="badge">آیتم <?= htmlspecialchars($item['category_name_fa'] ?? 'عمومی') ?></span></h1>
                    <p class="item-meta">
                        <strong>نام انگلیسی:</strong> <?= htmlspecialchars($item['name_en']) ?><br>
                        <?php if (!empty($item['tier'])): ?>
                            <strong>رده:</strong> <?= htmlspecialchars($item['tier']) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($item['gold_cost'])): ?>
                            <strong>قیمت:</strong> <?= htmlspecialchars(number_format((int) $item['gold_cost'])) ?> طلا<br>
                        <?php endif; ?>
                        <?php if (!empty($item['stats_fa'])): ?>
                            <strong>ویژگی‌ها:</strong> <?= nl2br(htmlspecialchars($item['stats_fa'])) ?><br>
                        <?php elseif (!empty($item['stats_en'])): ?>
                            <strong>Stats:</strong> <?= nl2br(htmlspecialchars($item['stats_en'])) ?><br>
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($item['passive_fa']) || !empty($item['passive_en'])): ?>
                        <div class="card">
                            <h3>پسیو یکتا</h3>
                            <p><?= nl2br(htmlspecialchars($item['passive_fa'] ?: $item['passive_en'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($item['active_fa']) || !empty($item['active_en'])): ?>
                        <div class="card">
                            <h3>فعال یکتا</h3>
                            <p><?= nl2br(htmlspecialchars($item['active_fa'] ?: $item['active_en'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($item['description_fa']) || !empty($item['description_en'])): ?>
                        <div class="card">
                            <h3>توضیحات</h3>
                            <p><?= nl2br(htmlspecialchars($item['description_fa'] ?: $item['description_en'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($itemImageUrl !== ''): ?>
                        <img src="<?= htmlspecialchars($itemImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name_fa']) ?>" style="width:100%;border-radius:24px;box-shadow:0 20px 40px rgba(15,23,42,0.4);">
                    <?php else: ?>
                        <div class="card" style="height:100%;display:flex;align-items:center;justify-content:center;">برای این آیتم تصویری ثبت نشده است</div>
                    <?php endif; ?>

                    <?php if (!empty($categoryItems)): ?>
                        <div style="margin-top:1.5rem;">
                            <label for="item-switch">سایر آیتم‌های <?= htmlspecialchars($item['category_name_fa'] ?? 'این دسته') ?>:</label>
                            <select id="item-switch" class="form-control" onchange="changeItem(this.value)">
                                <?php foreach ($categoryItems as $candidate): ?>
                                    <option value="<?= (int) $candidate['id'] ?>" <?= (int) $candidate['id'] === $itemId ? 'selected' : '' ?>><?= htmlspecialchars($candidate['name_fa'] ?? $candidate['name_en']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($referencedHeroes)): ?>
            <div class="hero-card">
                <div class="section-title">هیروهایی که این آیتم را توصیه کرده‌اند</div>
                <div class="counter-groups-grid">
                    <?php foreach ($referencedHeroes as $heroRow): ?>
                        <a class="counter-entry__link" href="<?= htmlspecialchars(mlbb_url('index.php?id=' . (int) $heroRow['id']), ENT_QUOTES, 'UTF-8') ?>">
                            <figure class="counter-entry">
                                <?php $heroImage = mlbb_asset_url($heroRow['image_url'] ?? ''); ?>
                                <?php if ($heroImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                                <figcaption>
                                    <span><?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (!empty($heroRow['title_fa']) || !empty($heroRow['title_en'])): ?>
                                        <small><?= htmlspecialchars($heroRow['title_fa'] ?? $heroRow['title_en'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function changeItem(itemId) {
            if (!itemId) {
                return;
            }
            window.location.href = '<?= htmlspecialchars(mlbb_url('item.php'), ENT_QUOTES, 'UTF-8') ?>?id=' + itemId;
        }
    </script>
</body>
</html>
