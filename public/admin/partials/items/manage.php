<?php
declare(strict_types=1);
?>
<section class="panel-card">
    <div class="section-heading">
        <h2 class="section-title">مدیریت دسته‌بندی آیتم‌ها</h2>
        <p class="section-subtitle">نام فارسی و انگلیسی هر دسته را مشخص کنید و ترتیب نمایش را مدیریت نمایید.</p>
    </div>
    <div class="grid grid-2" id="categories">
        <form method="post" class="management-card management-card--inline">
            <input type="hidden" name="action" value="add_item_category">
            <input type="hidden" name="return_to" value="items.php">
            <input type="hidden" name="redirect_anchor" value="categories">
            <h4 class="card-title">افزودن دسته‌بندی جدید</h4>
            <div class="form-group">
                <label>اسلاگ (انگلیسی)</label>
                <input type="text" name="slug" class="form-control" placeholder="مثال: attack">
            </div>
            <div class="form-group">
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control" placeholder="نام فارسی دسته">
            </div>
            <div class="form-group">
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control" placeholder="English name">
            </div>
            <div class="form-group">
                <label>ترتیب نمایش</label>
                <input type="number" name="sort_order" class="form-control" value="0">
            </div>
            <div class="form-actions">
                <button type="submit" class="primary">ثبت دسته</button>
            </div>
        </form>

        <div class="category-list">
            <?php if (empty($itemCategories)): ?>
                <p class="empty-state">هیچ دسته‌بندی ثبت نشده است.</p>
            <?php else: ?>
                <?php foreach ($itemCategories as $category): ?>
                    <form method="post" class="management-card management-card--inline">
                        <input type="hidden" name="action" value="update_item_category">
                        <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                        <input type="hidden" name="return_to" value="items.php">
                        <input type="hidden" name="redirect_anchor" value="categories">
                        <h4 class="card-title">ویرایش <?= htmlspecialchars($category['name_fa'] ?? $category['name_en'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <div class="form-group">
                            <label>اسلاگ</label>
                            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>نام فارسی</label>
                            <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($category['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>نام انگلیسی</label>
                            <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($category['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>ترتیب</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int) ($category['sort_order'] ?? 0) ?>">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="secondary-button">ذخیره</button>
                        </div>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('این دسته حذف شود؟');">
                        <input type="hidden" name="action" value="delete_item_category">
                        <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                        <input type="hidden" name="return_to" value="items.php">
                        <input type="hidden" name="redirect_anchor" value="categories">
                        <button type="submit" class="danger-button">حذف دسته</button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="panel-card" id="items">
    <div class="section-heading">
        <h2 class="section-title">مدیریت آیتم‌ها</h2>
        <p class="section-subtitle">آیتم‌ها را به دسته مناسب متصل کنید و مشخصات کامل آنها را ذخیره نمایید.</p>
    </div>

    <form method="post" enctype="multipart/form-data" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="return_to" value="items.php">
        <input type="hidden" name="redirect_anchor" value="items">
        <h4 class="card-title">افزودن آیتم جدید</h4>
        <div class="form-group">
            <label>دسته‌بندی</label>
            <select name="category_id" class="form-control">
                <?php foreach ($itemCategories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name_fa'] ?? $category['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>اسلاگ (اختیاری)</label>
            <input type="text" name="slug" class="form-control" placeholder="مانند sea-halberd">
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control" required>
            </div>
            <div>
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control" required>
            </div>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>رده</label>
                <input type="text" name="tier" class="form-control" placeholder="Tier 3 / Advanced / Roam">
            </div>
            <div>
                <label>قیمت طلا</label>
                <input type="number" name="gold_cost" class="form-control" placeholder="مثال: 2100">
            </div>
        </div>
        <div class="form-group">
            <label>ویژگی‌ها (فارسی)</label>
            <textarea name="stats_fa" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>ویژگی‌ها (انگلیسی)</label>
            <textarea name="stats_en" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>پسیو یکتا (فارسی)</label>
                <textarea name="passive_fa" rows="2" class="form-control"></textarea>
            </div>
            <div>
                <label>Unique Passive</label>
                <textarea name="passive_en" rows="2" class="form-control"></textarea>
            </div>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>فعال یکتا (فارسی)</label>
                <textarea name="active_fa" rows="2" class="form-control"></textarea>
            </div>
            <div>
                <label>Unique Active</label>
                <textarea name="active_en" rows="2" class="form-control"></textarea>
            </div>
        </div>
        <div class="form-group grid grid-2">
            <div>
                <label>توضیحات فارسی</label>
                <textarea name="description_fa" rows="2" class="form-control"></textarea>
            </div>
            <div>
                <label>Description</label>
                <textarea name="description_en" rows="2" class="form-control"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label>تصویر آیتم</label>
            <input type="file" name="item_image" accept="image/*">
            <input type="text" name="image_url" class="form-control" placeholder="یا مسیر موجود /assets/images/items/...">
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت آیتم</button>
        </div>
    </form>

    <?php if (empty($items)): ?>
        <p class="empty-state">آیتمی ثبت نشده است.</p>
    <?php else: ?>
        <div class="management-section">
            <?php foreach ($items as $item): ?>
                <form method="post" enctype="multipart/form-data" class="management-card">
                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                    <input type="hidden" name="return_to" value="items.php">
                    <input type="hidden" name="redirect_anchor" value="items">
                    <h4 class="card-title">ویرایش <?= htmlspecialchars($item['name_fa'] ?? $item['name_en'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="form-group">
                        <label>دسته‌بندی</label>
                        <select name="category_id" class="form-control">
                            <?php foreach ($itemCategories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $category['id'] === (int) $item['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name_fa'] ?? $category['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>اسلاگ</label>
                            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($item['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>رده</label>
                            <input type="text" name="tier" class="form-control" value="<?= htmlspecialchars($item['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>نام فارسی</label>
                            <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($item['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>نام انگلیسی</label>
                            <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($item['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>قیمت طلا</label>
                            <input type="number" name="gold_cost" class="form-control" value="<?= (int) ($item['gold_cost'] ?? 0) ?>">
                        </div>
                        <div>
                            <label>تصویر موجود</label>
                            <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($item['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/images/items/...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>بارگذاری تصویر جدید</label>
                        <input type="file" name="item_image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>ویژگی‌ها (فارسی)</label>
                        <textarea name="stats_fa" rows="2" class="form-control"><?= htmlspecialchars($item['stats_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>ویژگی‌ها (انگلیسی)</label>
                        <textarea name="stats_en" rows="2" class="form-control"><?= htmlspecialchars($item['stats_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>پسیو یکتا (فارسی)</label>
                            <textarea name="passive_fa" rows="2" class="form-control"><?= htmlspecialchars($item['passive_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div>
                            <label>Unique Passive</label>
                            <textarea name="passive_en" rows="2" class="form-control"><?= htmlspecialchars($item['passive_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>فعال یکتا (فارسی)</label>
                            <textarea name="active_fa" rows="2" class="form-control"><?= htmlspecialchars($item['active_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div>
                            <label>Unique Active</label>
                            <textarea name="active_en" rows="2" class="form-control"><?= htmlspecialchars($item['active_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                    <div class="form-group grid grid-2">
                        <div>
                            <label>توضیح فارسی</label>
                            <textarea name="description_fa" rows="2" class="form-control"><?= htmlspecialchars($item['description_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        <div>
                            <label>Description</label>
                            <textarea name="description_en" rows="2" class="form-control"><?= htmlspecialchars($item['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="action" value="update_item" class="secondary-button">ذخیره تغییرات</button>
                        <button type="submit" name="action" value="delete_item" class="danger-button" formnovalidate onclick="return confirm('این آیتم حذف شود؟');">حذف آیتم</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
