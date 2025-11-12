<?php
declare(strict_types=1);

$combos = $activeHeroBundle['combos'];
$heroSkills = $activeHeroBundle['skills'];

$skillOptions = [];
foreach ($heroSkills as $skillRow) {
    $skillId = (int) ($skillRow['id'] ?? 0);
    if ($skillId === 0) {
        continue;
    }

    $nameFa = $skillRow['name_fa'] ?? '';
    $nameEn = $skillRow['name_en'] ?? '';
    $skillLabel = $nameFa !== '' ? $nameFa : ($nameEn !== '' ? $nameEn : ($skillRow['skill_type'] ?? ''));

    $skillOptions[$skillId] = [
        'label' => $skillLabel,
        'type' => $skillRow['skill_type'] ?? '',
        'image_url' => $skillRow['image_url'] ?? '',
    ];
}
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">کامبوها</h3>
        <p class="section-description">مراحل اجرای کامبوها، ترتیب مهارت‌ها و سطح دشواری را کنترل کنید.</p>
    </div>
    <div class="management-grid">
        <?php foreach ($combos as $combo): ?>
            <div class="management-card">
                <form method="post" class="combo-form">
                    <input type="hidden" name="action" value="update_combo">
                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                    <input type="hidden" name="combo_id" value="<?= (int) $combo['id'] ?>">
                    <input type="hidden" name="redirect_anchor" value="combos">
                    <input type="hidden" name="return_to" value="hero.php">
                    <div class="form-group">
                        <label>نام فارسی</label>
                        <input type="text" name="combo_name_fa" class="form-control" value="<?= htmlspecialchars($combo['combo_name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>نام انگلیسی</label>
                        <input type="text" name="combo_name" class="form-control" value="<?= htmlspecialchars($combo['combo_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>توضیح متنی مراحل (اختیاری)</label>
                        <textarea name="combo_steps" rows="2" class="form-control" placeholder="در صورت نیاز یک توضیح کوتاه از ترتیب مهارت‌ها بنویسید."><?= htmlspecialchars($combo['combo_steps'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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

                <div class="combo-steps">
                    <div class="combo-steps__header">
                        <h4>مراحل مهارت‌ها</h4>
                        <p>مهارت‌های این کامبو را به ترتیب دلخواه اضافه کنید.</p>
                    </div>

                    <?php $steps = $combo['steps'] ?? []; ?>
                    <?php if (!empty($steps)): ?>
                        <ul class="combo-step-list">
                            <?php foreach ($steps as $index => $step): ?>
                                <?php
                                $stepNameFa = $step['skill_name_fa'] ?? '';
                                $stepNameEn = $step['skill_name_en'] ?? '';
                                $stepCustom = $step['custom_label'] ?? '';
                                $displayName = $stepNameFa !== '' ? $stepNameFa : ($stepNameEn !== '' ? $stepNameEn : $stepCustom);
                                $displayName = $displayName !== '' ? $displayName : 'مهارت';
                                $typeLabel = $step['skill_type'] ? admin_skill_type_label((string) $step['skill_type']) : '';
                                $imageSrc = admin_resolve_image_src($step['skill_image_url'] ?? '');
                                $initialSource = $displayName !== '' ? $displayName : ($typeLabel !== '' ? $typeLabel : '?');
                                $initialChar = function_exists('mb_substr') ? mb_substr($initialSource, 0, 1, 'UTF-8') : substr($initialSource, 0, 1);
                                ?>
                                <li class="combo-step-item">
                                    <span class="combo-step-order"><?= $index + 1 ?></span>
                                    <div class="combo-step-thumb">
                                        <?php if ($imageSrc !== ''): ?>
                                            <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php else: ?>
                                            <span class="combo-step-thumb__fallback"><?= htmlspecialchars($initialChar ?: '?', ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="combo-step-details">
                                        <span class="combo-step-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($typeLabel !== ''): ?>
                                            <span class="combo-step-type"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <form method="post" class="combo-step-delete">
                                        <input type="hidden" name="action" value="delete_combo_step">
                                        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                                        <input type="hidden" name="combo_id" value="<?= (int) $combo['id'] ?>">
                                        <input type="hidden" name="step_id" value="<?= (int) $step['id'] ?>">
                                        <input type="hidden" name="redirect_anchor" value="combos">
                                        <input type="hidden" name="return_to" value="hero.php">
                                        <button type="submit" class="icon-button danger" title="حذف مرحله">
                                            <span class="sr-only">حذف</span>
                                            ×
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-state">هنوز مرحله‌ای برای این کامبو ثبت نشده است.</p>
                    <?php endif; ?>

                    <?php if (!empty($skillOptions)): ?>
                        <form method="post" class="combo-step-add">
                            <input type="hidden" name="action" value="add_combo_step">
                            <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                            <input type="hidden" name="combo_id" value="<?= (int) $combo['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="combos">
                            <input type="hidden" name="return_to" value="hero.php">
                            <div class="form-group">
                                <label>افزودن مرحله جدید</label>
                                <div class="combo-step-add__fields">
                                    <select name="skill_id" class="form-control" required>
                                        <option value="">انتخاب مهارت</option>
                                        <?php foreach ($skillOptions as $skillId => $skillData): ?>
                                            <?php
                                            $typeLabel = $skillData['type'] !== '' ? admin_skill_type_label((string) $skillData['type']) : '';
                                            $optionLabel = $skillData['label'];
                                            $optionText = $typeLabel !== '' ? ($typeLabel . ' — ' . $optionLabel) : $optionLabel;
                                            ?>
                                            <option value="<?= $skillId ?>"><?= htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="sort_order" class="form-control" placeholder="ترتیب (اختیاری)">
                                    <button type="submit" class="primary">افزودن</button>
                                </div>
                                <small class="form-hint">اگر ترتیب را خالی بگذارید، مرحله در انتهای لیست قرار می‌گیرد.</small>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_combo">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="combos">
        <input type="hidden" name="return_to" value="hero.php">
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
            <label>توضیح متنی مراحل (اختیاری)</label>
            <textarea name="combo_steps" rows="2" class="form-control" placeholder="در صورت نیاز یک توضیح کوتاه از ترتیب مهارت‌ها بنویسید."></textarea>
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
        <p class="form-hint">پس از ثبت، از بخش بالا می‌توانید مهارت‌های مربوط به این کامبو را به ترتیب موردنظر اضافه کنید.</p>
    </form>
</section>
