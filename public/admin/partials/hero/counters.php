<?php
declare(strict_types=1);

$counters = $activeHeroBundle['counters'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">کانترها</h3>
        <p class="section-description">فقط تصویر و توضیحات لازم را برای هر کانتر مدیریت کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($counters as $counter): ?>
            <form method="post" enctype="multipart/form-data" class="management-card">
                <input type="hidden" name="action" value="update_counter">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="counter_id" value="<?= (int) $counter['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="counters">
                <div class="form-group">
                    <label>نام فارسی کانتر</label>
                    <input type="text" name="counter_hero_fa" class="form-control" value="<?= htmlspecialchars($counter['counter_hero_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نام انگلیسی کانتر</label>
                    <input type="text" name="counter_hero" class="form-control" value="<?= htmlspecialchars($counter['counter_hero'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نوع کانتر</label>
                    <input type="text" name="counter_type" class="form-control" value="<?= htmlspecialchars($counter['counter_type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>دلیل</label>
                    <textarea name="reason" rows="2" class="form-control"><?= htmlspecialchars($counter['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>دلیل انگلیسی</label>
                    <textarea name="reason_en" rows="2" class="form-control"><?= htmlspecialchars($counter['reason_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>تصویر کانتر</label>
                    <input type="file" name="counter_image" accept="image/*">
                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($counter['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/heroes/counter.png">
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">ذخیره کانتر</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_counter">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="counters">
        <h4 class="card-title">افزودن کانتر جدید</h4>
        <div class="form-group grid grid-2">
            <div>
                <label>نام فارسی</label>
                <input type="text" name="counter_hero_fa" class="form-control">
            </div>
            <div>
                <label>نام انگلیسی</label>
                <input type="text" name="counter_hero" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>نوع کانتر</label>
            <input type="text" name="counter_type" class="form-control" placeholder="Hard Counter / Soft Counter">
        </div>
        <div class="form-group">
            <label>دلیل فارسی</label>
            <textarea name="reason" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>دلیل انگلیسی</label>
            <textarea name="reason_en" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>تصویر کانتر</label>
            <input type="file" name="counter_image" accept="image/*">
            <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/counter.png">
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت کانتر</button>
        </div>
    </form>
</section>
