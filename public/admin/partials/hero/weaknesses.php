<?php
declare(strict_types=1);

$weaknesses = $activeHeroBundle['weaknesses'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">نقاط ضعف</h3>
        <p class="section-description">چالش‌ها و محدودیت‌های هیرو را برای بازیکنان توضیح دهید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($weaknesses as $weakness): ?>
            <form method="post" class="management-card">
                <input type="hidden" name="action" value="update_weakness">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="weakness_id" value="<?= (int) $weakness['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="weaknesses">
                <input type="hidden" name="return_to" value="hero.php">
                <div class="form-group">
                    <label>متن نقطه ضعف</label>
                    <textarea name="weakness_text" rows="2" class="form-control"><?= htmlspecialchars($weakness['weakness_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">ذخیره</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_weakness">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="weaknesses">
        <input type="hidden" name="return_to" value="hero.php">
        <h4 class="card-title">افزودن نقطه ضعف</h4>
        <div class="form-group">
            <textarea name="weakness_text" rows="2" class="form-control" placeholder="به عنوان مثال: آسیب‌پذیر در برابر ضدریجن"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت نقطه ضعف</button>
        </div>
    </form>
</section>
