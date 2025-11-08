<?php
declare(strict_types=1);

$combos = $activeHeroBundle['combos'];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">کامبوها</h3>
        <p class="section-description">مراحل اجرای کامبوها و سطح دشواری را تنظیم کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($combos as $combo): ?>
            <form method="post" class="management-card">
                <input type="hidden" name="action" value="update_combo">
                <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                <input type="hidden" name="combo_id" value="<?= (int) $combo['id'] ?>">
                <input type="hidden" name="redirect_anchor" value="combos">
                <div class="form-group">
                    <label>نام فارسی</label>
                    <input type="text" name="combo_name_fa" class="form-control" value="<?= htmlspecialchars($combo['combo_name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>نام انگلیسی</label>
                    <input type="text" name="combo_name" class="form-control" value="<?= htmlspecialchars($combo['combo_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>مراحل کامبو</label>
                    <textarea name="combo_steps" rows="2" class="form-control"><?= htmlspecialchars($combo['combo_steps'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>توضیح</label>
                    <textarea name="combo_description" rows="3" class="form-control"><?= htmlspecialchars($combo['combo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>توضیح فارسی</label>
                    <textarea name="combo_description_fa" rows="3" class="form-control"><?= htmlspecialchars($combo['combo_description_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>درجه سختی</label>
                    <select name="difficulty" class="form-control">
                        <?php foreach ($difficulties as $difficultyValue => $label): ?>
                            <option value="<?= htmlspecialchars($difficultyValue, ENT_QUOTES, 'UTF-8') ?>" <?= ($combo['difficulty'] ?? 'Medium') === $difficultyValue ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="secondary-button">به‌روزرسانی کامبو</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_combo">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="combos">
        <h4 class="card-title">افزودن کامبو جدید</h4>
        <div class="form-group grid grid-2">
            <div>
                <label>نام فارسی</label>
                <input type="text" name="combo_name_fa" class="form-control">
            </div>
            <div>
                <label>نام انگلیسی</label>
                <input type="text" name="combo_name" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>مراحل کامبو</label>
            <textarea name="combo_steps" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>توضیح</label>
            <textarea name="combo_description" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>توضیح فارسی</label>
            <textarea name="combo_description_fa" rows="3" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>درجه سختی</label>
            <select name="difficulty" class="form-control">
                <?php foreach ($difficulties as $difficultyValue => $label): ?>
                    <option value="<?= htmlspecialchars($difficultyValue, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت کامبو</button>
        </div>
    </form>
</section>
