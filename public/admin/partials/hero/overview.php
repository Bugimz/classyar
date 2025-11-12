<?php
declare(strict_types=1);

$heroImage = admin_resolve_image_src($hero['image_url'] ?? '');
?>
<div class="hero-summary">
    <?php if ($heroImage): ?>
        <img src="<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($hero['name_fa'] ?? $hero['name_en'], ENT_QUOTES, 'UTF-8') ?>">
    <?php else: ?>
        <div class="placeholder">بدون تصویر</div>
    <?php endif; ?>
    <div>
        <strong><?= htmlspecialchars($hero['name_fa'] ?? $hero['name_en'], ENT_QUOTES, 'UTF-8') ?></strong>
        <div class="pill-muted" style="margin-top:0.5rem;">نقش: <?= htmlspecialchars($hero['role_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <ul>
        <li><span>HP</span><?= (int) ($hero['hp'] ?? 0) ?></li>
        <li><span>Mana</span><?= (int) ($hero['mana'] ?? 0) ?></li>
        <li><span>Physical ATK</span><?= (int) ($hero['physical_attack'] ?? 0) ?></li>
        <li><span>Magic DEF</span><?= (int) ($hero['magic_defense'] ?? 0) ?></li>
        <li><span>Attack Speed</span><?= htmlspecialchars((string) ($hero['attack_speed'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
        <li><span>Movement</span><?= (int) ($hero['movement_speed'] ?? 0) ?></li>
    </ul>
</div>
