<?php
declare(strict_types=1);

$counters = $activeHeroBundle['counters'];
$groupTypeOptions = [
    'hero' => 'هیروهای کانتر',
    'item' => 'آیتم‌های پیشنهادی',
];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">کانترها</h3>
        <p class="section-description">بخش‌های جداگانه برای هیروهای کانتر و آیتم‌های پیشنهادی ایجاد کنید و تصاویر کوچک برای هر مورد بارگذاری نمایید.</p>
    </div>
    <div class="counter-groups">
        <?php if (empty($counters)): ?>
            <p class="empty-state">هنوز هیچ گروه کانتری برای این هیرو ثبت نشده است. از فرم زیر یک گروه جدید ایجاد کنید.</p>
        <?php endif; ?>
        <?php foreach ($counters as $group): ?>
            <?php $groupFormId = 'group-form-' . (int) $group['id']; ?>
            <div class="counter-group-card">
                <div class="group-header">
                    <h4 class="card-title"><?= htmlspecialchars($group['title_fa'] ?? $group['title_en'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="group-actions">
                        <button type="submit" form="<?= $groupFormId ?>" class="secondary-button">ذخیره گروه</button>
                        <form method="post" class="inline-form" onsubmit="return confirm('آیا از حذف این گروه اطمینان دارید؟');">
                            <input type="hidden" name="action" value="delete_counter_group">
                            <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                            <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                            <input type="hidden" name="redirect_anchor" value="counters">
                            <input type="hidden" name="return_to" value="hero.php">
                            <button type="submit" class="danger-button">حذف گروه</button>
                        </form>
                    </div>
                </div>

                <form method="post" id="<?= $groupFormId ?>" class="group-form">
                    <input type="hidden" name="action" value="update_counter_group">
                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                    <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                    <input type="hidden" name="redirect_anchor" value="counters">
                    <input type="hidden" name="return_to" value="hero.php">
                    <div class="form-group grid grid-3 group-grid">
                        <div>
                            <label>عنوان فارسی</label>
                            <input type="text" name="title_fa" class="form-control" value="<?= htmlspecialchars($group['title_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>عنوان انگلیسی</label>
                            <input type="text" name="title_en" class="form-control" value="<?= htmlspecialchars($group['title_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int) ($group['sort_order'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="form-group grid grid-2 group-grid">
                        <div>
                            <label>نوع گروه</label>
                            <select name="group_type" class="form-control">
                                <?php foreach ($groupTypeOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($group['group_type'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>توضیح کوتاه</label>
                            <textarea name="description_fa" rows="2" class="form-control" placeholder="توضیح فارسی"><?= htmlspecialchars($group['description_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>توضیح انگلیسی</label>
                        <textarea name="description_en" rows="2" class="form-control" placeholder="Short description in English"><?= htmlspecialchars($group['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </form>

                <div class="counter-entities">
                    <?php foreach ($group['entities'] as $entity): ?>
                        <?php $entityFormId = 'entity-form-' . (int) $entity['id']; ?>
                        <div class="counter-entity-card">
                            <div class="entity-header">
                                <div class="entity-thumb">
                                    <?php if (!empty($entity['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($entity['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($entity['name_fa'] ?? $entity['name_en'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <div class="entity-placeholder">بدون تصویر</div>
                                    <?php endif; ?>
                                </div>
                                <div class="entity-names">
                                    <input type="text" form="<?= $entityFormId ?>" name="name_fa" class="form-control" value="<?= htmlspecialchars($entity['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="نام فارسی">
                                    <input type="text" form="<?= $entityFormId ?>" name="name_en" class="form-control" value="<?= htmlspecialchars($entity['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="نام انگلیسی">
                                </div>
                            </div>
                            <div class="entity-body">
                                <form method="post" enctype="multipart/form-data" id="<?= $entityFormId ?>" class="entity-form">
                                    <input type="hidden" name="action" value="update_counter_entity">
                                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                                    <input type="hidden" name="entity_id" value="<?= (int) $entity['id'] ?>">
                                    <input type="hidden" name="redirect_anchor" value="counters">
                                    <input type="hidden" name="return_to" value="hero.php">
                                    <div class="form-group entity-details">
                                        <label>ترتیب</label>
                                        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($entity['sort_order'] ?? 0) ?>">
                                    </div>
                                    <div class="form-group entity-upload">
                                        <label>تصویر</label>
                                        <input type="file" name="entity_image" accept="image/*">
                                        <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($entity['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/heroes/sample.png">
                                    </div>
                                </form>
                            </div>
                            <div class="form-actions entity-actions">
                                <button type="submit" form="<?= $entityFormId ?>" class="secondary-button">ذخیره آیتم</button>
                                <form method="post" class="inline-form" onsubmit="return confirm('این آیتم حذف شود؟');">
                                    <input type="hidden" name="action" value="delete_counter_entity">
                                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                                    <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                                    <input type="hidden" name="entity_id" value="<?= (int) $entity['id'] ?>">
                                    <input type="hidden" name="redirect_anchor" value="counters">
                                    <input type="hidden" name="return_to" value="hero.php">
                                    <button type="submit" class="danger-button">حذف</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <form method="post" enctype="multipart/form-data" class="counter-entity-card counter-entity-card--add">
                        <input type="hidden" name="action" value="add_counter_entity">
                        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                        <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                        <input type="hidden" name="redirect_anchor" value="counters">
                        <input type="hidden" name="return_to" value="hero.php">
                        <h5 class="card-title">افزودن آیتم/هیرو جدید</h5>
                        <div class="form-group">
                            <label>نام فارسی</label>
                            <input type="text" name="name_fa" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>نام انگلیسی</label>
                            <input type="text" name="name_en" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>ترتیب</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>تصویر</label>
                            <input type="file" name="entity_image" accept="image/*">
                            <input type="text" name="image_url" class="form-control" placeholder="/assets/images/heroes/sample.png">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary">ذخیره</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_counter_group">
        <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
        <input type="hidden" name="redirect_anchor" value="counters">
        <input type="hidden" name="return_to" value="hero.php">
        <h4 class="card-title">ایجاد گروه کانتر جدید</h4>
        <div class="form-group grid grid-3">
            <div>
                <label>عنوان فارسی</label>
                <input type="text" name="title_fa" class="form-control">
            </div>
            <div>
                <label>عنوان انگلیسی</label>
                <input type="text" name="title_en" class="form-control">
            </div>
            <div>
                <label>ترتیب</label>
                <input type="number" name="sort_order" class="form-control" value="0">
            </div>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>نوع گروه</label>
                <select name="group_type" class="form-control">
                    <?php foreach ($groupTypeOptions as $value => $label): ?>
                        <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>توضیح فارسی</label>
                <textarea name="description_fa" rows="2" class="form-control"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label>توضیح انگلیسی</label>
            <textarea name="description_en" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت گروه</button>
        </div>
    </form>
</section>
