<?php
declare(strict_types=1);
?>
<section id="manage" class="panel-card">
    <div class="section-heading">
        <div>
            <h2 class="section-title">مدیریت هیروهای موجود</h2>
            <p class="section-subtitle">یک هیرو را انتخاب کنید تا تمام اطلاعات، مهارت‌ها و نکات آن را ویرایش کنید.</p>
        </div>
        <form method="get" class="hero-selector">
            <div class="form-group" style="margin-bottom:0;">
                <label for="hero-select">انتخاب هیرو</label>
                <select id="hero-select" name="hero" class="form-control" style="min-width:220px;">
                    <option value="">---</option>
                    <?php foreach ($heroes as $heroRow): ?>
                        <option value="<?= (int) $heroRow['id'] ?>" <?= $activeHeroId === (int) $heroRow['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="secondary-button">نمایش</button>
        </form>
    </div>

    <?php if ($activeHeroBundle): ?>
        <?php $hero = $activeHeroBundle['hero']; ?>
        <div class="hero-overview">
            <?php require __DIR__ . '/overview.php'; ?>
            <?php require __DIR__ . '/edit_form.php'; ?>
        </div>

        <div class="management-section" id="skills">
            <?php require __DIR__ . '/skills.php'; ?>
        </div>

        <div class="management-section" id="combos">
            <?php require __DIR__ . '/combos.php'; ?>
        </div>

        <div class="management-section" id="tips">
            <?php require __DIR__ . '/tips.php'; ?>
        </div>

        <div class="management-section" id="counters">
            <?php require __DIR__ . '/counters.php'; ?>
        </div>

        <div class="management-section" id="strengths">
            <?php require __DIR__ . '/strengths.php'; ?>
        </div>

        <div class="management-section" id="weaknesses">
            <?php require __DIR__ . '/weaknesses.php'; ?>
        </div>
    <?php else: ?>
        <p class="empty-state">برای ویرایش ابتدا یک هیرو را از لیست انتخاب کنید.</p>
    <?php endif; ?>
</section>
