<?php
declare(strict_types=1);
?>
<div class="hero-editor" id="manage-form">
    <form method="post" enctype="multipart/form-data" class="form-grid form-grid-2 panel-card--nested">
        <input type="hidden" name="action" value="update_hero">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="manage">
        <input type="hidden" name="return_to" value="hero.php">
        <div>
            <div class="form-group">
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($hero['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($hero['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label>نقش</label>
                <select name="role_id" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>" <?= (int) ($hero['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name_fa'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>تخصص</label>
                <input type="text" name="specialty" class="form-control" value="<?= htmlspecialchars($hero['specialty'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label>درجه سختی</label>
                <select name="difficulty" class="form-control">
                    <?php foreach ($difficulties as $difficultyValue => $label): ?>
                        <option value="<?= htmlspecialchars($difficultyValue, ENT_QUOTES, 'UTF-8') ?>" <?= ($hero['difficulty'] ?? 'Medium') === $difficultyValue ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>تصویر (بارگذاری یا لینک)</label>
                <input type="file" name="hero_image" accept="image/*">
                <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($hero['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/heroes/name.png">
                <p class="form-note">در صورت انتخاب فایل، تصویر جایگزین به‌طور خودکار آپلود می‌شود.</p>
            </div>
        </div>
        <div>
            <div class="form-group">
                <label>توضیحات</label>
                <textarea name="description" rows="6" class="form-control"><?= htmlspecialchars($hero['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group">
                <label>نکات کلیدی</label>
                <textarea name="tips" rows="4" class="form-control"><?= htmlspecialchars($hero['tips'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>HP</label>
                    <input type="number" name="hp" class="form-control" min="0" value="<?= (int) ($hero['hp'] ?? 0) ?>">
                </div>
                <div>
                    <label>HP Regen</label>
                    <input type="number" step="0.1" name="hp_regen" class="form-control" value="<?= htmlspecialchars((string) ($hero['hp_regen'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Mana</label>
                    <input type="number" name="mana" class="form-control" min="0" value="<?= (int) ($hero['mana'] ?? 0) ?>">
                </div>
                <div>
                    <label>Mana Regen</label>
                    <input type="number" step="0.1" name="mana_regen" class="form-control" value="<?= htmlspecialchars((string) ($hero['mana_regen'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Physical Attack</label>
                    <input type="number" name="physical_attack" class="form-control" min="0" value="<?= (int) ($hero['physical_attack'] ?? 0) ?>">
                </div>
                <div>
                    <label>Magic Power</label>
                    <input type="number" name="magic_power" class="form-control" min="0" value="<?= (int) ($hero['magic_power'] ?? 0) ?>">
                </div>
                <div>
                    <label>Physical Defense</label>
                    <input type="number" name="physical_defense" class="form-control" min="0" value="<?= (int) ($hero['physical_defense'] ?? 0) ?>">
                </div>
                <div>
                    <label>Magic Defense</label>
                    <input type="number" name="magic_defense" class="form-control" min="0" value="<?= (int) ($hero['magic_defense'] ?? 0) ?>">
                </div>
                <div>
                    <label>Attack Speed</label>
                    <input type="number" step="0.01" name="attack_speed" class="form-control" value="<?= htmlspecialchars((string) ($hero['attack_speed'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>Movement Speed</label>
                    <input type="number" name="movement_speed" class="form-control" min="0" value="<?= (int) ($hero['movement_speed'] ?? 0) ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="primary">به‌روزرسانی هیرو</button>
            </div>
        </div>
    </form>
</div>
