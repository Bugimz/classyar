<?php
declare(strict_types=1);

$strengths = $activeHeroBundle['strengths'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">نقاط قوت</h3>
        <p class="section-description">مواردی که این هیرو را قدرتمند می‌کند مشخص کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($strengths as $strength): ?>
            <form method="post" class="management-card">
                <input type="hidden" name="action" value="update_strength">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="strength_id" value="<?= (int) $strength['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="strengths">
                <div class="form-group">
                    <label>متن نقطه قوت</label>
                    <textarea name="strength_text" rows="2" class="form-control"><?= htmlspecialchars($strength['strength_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">ذخیره</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_strength">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="strengths">
        <h4 class="card-title">افزودن نقطه قوت</h4>
        <div class="form-group">
            <textarea name="strength_text" rows="2" class="form-control" placeholder="به عنوان مثال: محافظت عالی از هم‌تیمی‌ها"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت نقطه قوت</button>
        </div>
    </form>
</section>
