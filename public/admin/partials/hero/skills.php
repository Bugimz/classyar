<?php
declare(strict_types=1);

$skills = $activeHeroBundle['skills'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">مهارت‌ها</h3>
        <p class="section-description">تصاویر و توضیحات هر مهارت را به‌روزرسانی کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($skills as $skill): ?>
            <form method="post" enctype="multipart/form-data" class="management-card">
                <input type="hidden" name="action" value="update_skill">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="skills">
                <input type="hidden" name="return_to" value="hero.php">
                <div class="form-group">
                    <label>نوع مهارت</label>
                    <input type="text" name="skill_type" class="form-control" value="<?= htmlspecialchars($skill['skill_type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نام فارسی</label>
                    <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($skill['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نام انگلیسی</label>
                    <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($skill['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>توضیحات</label>
                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($skill['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group grid grid-2">
                    <div>
                        <label>Cooldown</label>
                        <input type="number" step="0.1" name="cooldown" class="form-control" value="<?= htmlspecialchars((string) ($skill['cooldown'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>هزینه مانا</label>
                        <input type="number" step="0.1" name="mana_cost" class="form-control" value="<?= htmlspecialchars((string) ($skill['mana_cost'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>نوع آسیب</label>
                    <input type="text" name="damage_type" class="form-control" value="<?= htmlspecialchars($skill['damage_type'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نکات</label>
                    <textarea name="tips" rows="2" class="form-control"><?= htmlspecialchars($skill['tips'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>تصویر مهارت</label>
                    <input type="file" name="skill_image" accept="image/*">
                    <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($skill['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/skills/name.png">
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">ذخیره تغییرات</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_skill">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="skills">
        <input type="hidden" name="return_to" value="hero.php">
        <h4 class="card-title">افزودن مهارت جدید</h4>
        <div class="form-group grid grid-2">
            <div>
                <label>نوع</label>
                <input type="text" name="skill_type" class="form-control" placeholder="Passive / Skill1 / Skill2 / Ultimate">
            </div>
            <div>
                <label>نوع آسیب</label>
                <input type="text" name="damage_type" class="form-control" placeholder="Magic / Physical / True">
            </div>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control">
            </div>
            <div>
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>توضیحات</label>
            <textarea name="description" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>Cooldown</label>
                <input type="number" step="0.1" name="cooldown" class="form-control">
            </div>
            <div>
                <label>هزینه مانا</label>
                <input type="number" step="0.1" name="mana_cost" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>نکات</label>
            <textarea name="tips" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>تصویر مهارت</label>
            <input type="file" name="skill_image" accept="image/*">
            <input type="text" name="image_url" class="form-control" placeholder="/assets/images/skills/new.png">
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت مهارت</button>
        </div>
    </form>
</section>
