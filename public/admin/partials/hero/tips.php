<?php
declare(strict_types=1);

$tips = $activeHeroBundle['tips'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">نکات و ترفندها</h3>
        <p class="section-description">نکات عمومی، مبارزه، استراتژی و حرفه‌ای را برای هیرو ثبت کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($tips as $tip): ?>
            <form method="post" class="management-card">
                <input type="hidden" name="action" value="update_tip">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="tip_id" value="<?= (int) $tip['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="tips">
                <div class="form-group">
                    <label>دسته‌بندی</label>
                    <input type="text" name="tip_type" class="form-control" value="<?= htmlspecialchars($tip['tip_type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>متن نکته</label>
                    <textarea name="tip_text" rows="3" class="form-control"><?= htmlspecialchars($tip['tip_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>نسخه انگلیسی</label>
                    <textarea name="tip_text_en" rows="3" class="form-control"><?= htmlspecialchars($tip['tip_text_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>اولویت</label>
                    <input type="number" name="priority" class="form-control" value="<?= (int) ($tip['priority'] ?? 0) ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">ثبت تغییرات</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_tip">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="tips">
        <h4 class="card-title">افزودن نکته جدید</h4>
        <div class="form-group grid grid-2">
            <div>
                <label>دسته‌بندی</label>
                <input type="text" name="tip_type" class="form-control" placeholder="General / Combat / Strategy / Advanced">
            </div>
            <div>
                <label>اولویت</label>
                <input type="number" name="priority" class="form-control" value="10">
            </div>
        </div>
        <div class="form-group">
            <label>متن نکته</label>
            <textarea name="tip_text" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>نسخه انگلیسی</label>
            <textarea name="tip_text_en" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت نکته</button>
        </div>
    </form>
</section>
