<?php
declare(strict_types=1);
?>
<section id="create" class="panel-card">
    <div class="section-heading">
        <div>
            <h2 class="section-title">افزودن هیرو جدید</h2>
            <p class="section-subtitle">تمام اطلاعات پایه، تصویر و توضیحات را در این بخش ثبت کنید.</p>
        </div>
    </div>
    <form method="post" enctype="multipart/form-data" class="form-grid form-grid-2">
        <input type="hidden" name="action" value="add_hero">
        <div>
            <div class="form-group">
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control" required>
            </div>
            <div class="form-group">
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control" required>
            </div>
            <div class="form-group">
                <label>نقش</label>
                <select name="role_id" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name_fa']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>تخصص</label>
                <input type="text" name="specialty" class="form-control">
            </div>
            <div class="form-group">
                <label>درجه سختی</label>
                <select name="difficulty" class="form-control">
                    <?php foreach ($difficulties as $difficultyValue => $label): ?>
                        <option value="<?= htmlspecialchars($difficultyValue, ENT_QUOTES, 'UTF-8') ?>" <?= $difficultyValue === 'Medium' ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>تصویر (بارگذاری یا لینک)</label>
                <input type="file" name="hero_image" accept="image/*">
                <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/name.png">
                <p class="form-note">در صورت آپلود، مسیر ذخیره به‌صورت خودکار ثبت می‌شود.</p>
            </div>
        </div>
        <div>
            <div class="form-group">
                <label>توضیحات</label>
                <textarea name="description" rows="6" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>نکات کلیدی</label>
                <textarea name="tips" rows="4" class="form-control"></textarea>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>HP</label>
                    <input type="number" name="hp" class="form-control" min="0">
                </div>
                <div>
                    <label>HP Regen</label>
                    <input type="number" step="0.1" name="hp_regen" class="form-control" min="0">
                </div>
                <div>
                    <label>Mana</label>
                    <input type="number" name="mana" class="form-control" min="0">
                </div>
                <div>
                    <label>Mana Regen</label>
                    <input type="number" step="0.1" name="mana_regen" class="form-control" min="0">
                </div>
                <div>
                    <label>Physical Attack</label>
                    <input type="number" name="physical_attack" class="form-control" min="0">
                </div>
                <div>
                    <label>Magic Power</label>
                    <input type="number" name="magic_power" class="form-control" min="0">
                </div>
                <div>
                    <label>Physical Defense</label>
                    <input type="number" name="physical_defense" class="form-control" min="0">
                </div>
                <div>
                    <label>Magic Defense</label>
                    <input type="number" name="magic_defense" class="form-control" min="0">
                </div>
                <div>
                    <label>Attack Speed</label>
                    <input type="number" step="0.01" name="attack_speed" class="form-control" min="0">
                </div>
                <div>
                    <label>Movement Speed</label>
                    <input type="number" name="movement_speed" class="form-control" min="0">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="primary">ذخیره هیرو</button>
            </div>
        </div>
    </form>
</section>
