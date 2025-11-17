<?php
declare(strict_types=1);

$counters = $activeHeroBundle['counters'];
$referenceLists = $activeHeroBundle['counter_reference'] ?? ['heroes' => [], 'items' => []];
$heroOptions = $referenceLists['heroes'] ?? [];
$itemOptions = $referenceLists['items'] ?? [];

$groupTypeOptions = [
    'hero' => 'هیروهای کانتر',
    'item' => 'آیتم‌های پیشنهادی',
];
?>
<section class="panel-card panel-card--sub">
    <div class="section-heading">
        <h3 class="section-subtitle">کانترها</h3>
        <p class="section-description">
            گروه‌هایی از هیروها یا آیتم‌ها ایجاد کنید تا نقاط ضعف و قوت هیرو را نمایش دهید. با اتصال هر مورد به دیتابیس اصلی،
            تصویر و اطلاعات آن به صورت خودکار استفاده می‌شود و نیاز به بارگذاری تکراری نیست.
        </p>
    </div>
    <div class="counter-groups">
        <?php if (empty($counters)): ?>
            <p class="empty-state">هنوز هیچ گروه کانتری برای این هیرو ثبت نشده است. از فرم زیر یک گروه جدید ایجاد کنید.</p>
        <?php endif; ?>
        <?php foreach ($counters as $group): ?>
            <?php
            $groupId = (int) $group['id'];
            $groupFormId = 'group-form-' . $groupId;
            $isHeroGroup = ($group['group_type'] ?? 'hero') === 'hero';
            ?>
            <div class="counter-group-card">
                <div class="group-header">
                    <h4 class="card-title"><?= htmlspecialchars($group['title_fa'] ?? $group['title_en'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="group-actions">
                        <button type="submit" form="<?= $groupFormId ?>" class="secondary-button">ذخیره گروه</button>
                        <form method="post" class="inline-form" onsubmit="return confirm('آیا از حذف این گروه اطمینان دارید؟');">
                            <input type="hidden" name="action" value="delete_counter_group">
                            <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                            <input type="hidden" name="group_id" value="<?= $groupId ?>">
                            <input type="hidden" name="redirect_anchor" value="counters">
                            <input type="hidden" name="return_to" value="hero.php">
                            <button type="submit" class="danger-button">حذف گروه</button>
                        </form>
                    </div>
                </div>

                <form method="post" id="<?= $groupFormId ?>" class="group-form">
                    <input type="hidden" name="action" value="update_counter_group">
                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
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
                        <?php
                        $entityId = (int) $entity['id'];
                        $entityFormId = 'entity-form-' . $entityId;
                        $entityHeroId = (int) ($entity['target_hero_id'] ?? 0);
                        $entityItemId = (int) ($entity['target_item_id'] ?? 0);
                        $displayImage = $entity['image_url'] ?? '';
                        if ($displayImage === '' && !empty($entity['target_hero']['image_url'])) {
                            $displayImage = $entity['target_hero']['image_url'];
                        }
                        if ($displayImage === '' && !empty($entity['target_item']['image_url'])) {
                            $displayImage = $entity['target_item']['image_url'];
                        }
                        ?>
                        <div class="counter-entity-card">
                            <div class="entity-header">
                                <div class="entity-thumb">
                                    <?php if (!empty($displayImage)): ?>
                                        <img src="<?= htmlspecialchars($displayImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($entity['name_fa'] ?? $entity['name_en'] ?? 'entity', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <div class="entity-placeholder">بدون تصویر</div>
                                    <?php endif; ?>
                                </div>
                                <div class="entity-names">
                                    <input type="text" form="<?= $entityFormId ?>" name="name_fa" class="form-control" value="<?= htmlspecialchars($entity['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="نام فارسی (اختیاری)">
                                    <input type="text" form="<?= $entityFormId ?>" name="name_en" class="form-control" value="<?= htmlspecialchars($entity['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="نام انگلیسی (اختیاری)">
                                </div>
                            </div>
                            <div class="entity-body">
                                <form method="post" enctype="multipart/form-data" id="<?= $entityFormId ?>" class="entity-form">
                                    <input type="hidden" name="action" value="update_counter_entity">
                                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                    <input type="hidden" name="entity_id" value="<?= $entityId ?>">
                                    <input type="hidden" name="redirect_anchor" value="counters">
                                    <input type="hidden" name="return_to" value="hero.php">
                                    <div class="form-group entity-details">
                                        <label>ترتیب</label>
                                        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($entity['sort_order'] ?? 0) ?>">
                                    </div>
                                    <div class="form-group entity-selector">
                                        <?php if ($isHeroGroup): ?>
                                            <label>انتخاب هیرو</label>
                                            <select name="target_hero_id" class="form-control">
                                                <option value="">-- انتخاب کنید --</option>
                                                <?php foreach ($heroOptions as $option): ?>
                                                    <option value="<?= (int) $option['id'] ?>" <?= $entityHeroId === (int) $option['id'] ? 'selected' : '' ?>><?= htmlspecialchars($option['name_fa'] ?? $option['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <label>انتخاب آیتم</label>
                                            <select name="target_item_id" class="form-control">
                                                <option value="">-- انتخاب کنید --</option>
                                                <?php foreach ($itemOptions as $option): ?>
                                                    <option value="<?= (int) $option['id'] ?>" <?= $entityItemId === (int) $option['id'] ? 'selected' : '' ?>><?= htmlspecialchars($option['name_fa'] ?? $option['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group entity-upload">
                                        <label>تصویر سفارشی (اختیاری)</label>
                                        <input type="file" name="entity_image" accept="image/*">
                                        <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($entity['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/uploads/counters/...">
                                    </div>
                                </form>
                            </div>
                            <div class="form-actions entity-actions">
                                <button type="submit" form="<?= $entityFormId ?>" class="secondary-button">ذخیره آیتم</button>
                                <form method="post" class="inline-form" onsubmit="return confirm('این مورد حذف شود؟');">
                                    <input type="hidden" name="action" value="delete_counter_entity">
                                    <input type="hidden" name="hero_id" value="<?= (int) $hero['id'] ?>">
                                    <input type="hidden" name="group_id" value="<?= $groupId ?>">
                                    <input type="hidden" name="entity_id" value="<?= $entityId ?>">
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
                        <input type="hidden" name="group_id" value="<?= $groupId ?>">
                        <input type="hidden" name="redirect_anchor" value="counters">
                        <input type="hidden" name="return_to" value="hero.php">
                        <h5 class="card-title">افزودن مورد جدید</h5>
                        <div class="form-group">
                            <?php if ($isHeroGroup): ?>
                                <label>انتخاب هیرو</label>
                                <select name="target_hero_id" class="form-control">
                                    <option value="">-- انتخاب کنید --</option>
                                    <?php foreach ($heroOptions as $option): ?>
                                        <option value="<?= (int) $option['id'] ?>"><?= htmlspecialchars($option['name_fa'] ?? $option['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <label>انتخاب آیتم</label>
                                <select name="target_item_id" class="form-control">
                                    <option value="">-- انتخاب کنید --</option>
                                    <?php foreach ($itemOptions as $option): ?>
                                        <option value="<?= (int) $option['id'] ?>"><?= htmlspecialchars($option['name_fa'] ?? $option['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>نام فارسی (اختیاری)</label>
                            <input type="text" name="name_fa" class="form-control" placeholder="در صورت نیاز به نام متفاوت">
                        </div>
                        <div class="form-group">
                            <label>نام انگلیسی (اختیاری)</label>
                            <input type="text" name="name_en" class="form-control" placeholder="در صورت نیاز به نام متفاوت">
                        </div>
                        <div class="form-group">
                            <label>تصویر سفارشی (اختیاری)</label>
                            <input type="file" name="entity_image" accept="image/*">
                            <input type="text" name="image_url" class="form-control" placeholder="/assets/uploads/counters/...">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="primary">ثبت</button>
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
