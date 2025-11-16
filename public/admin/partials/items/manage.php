<?php
declare(strict_types=1);

$editItem = $editItem ?? null;
?>
<section class="panel-card" id="categories">
    <div class="section-heading">
        <div>
            <h2 class="section-title">دسته‌بندی آیتم‌ها</h2>
            <p class="section-subtitle">ساختار حمله، جادو، دفاع، حرکت و سایر شاخه‌ها را مطابق ویکی رسمی تنظیم کنید.</p>
        </div>
    </div>
    <div class="grid grid-2">
        <form method="post" class="management-card management-card--inline">
            <input type="hidden" name="action" value="add_item_category">
            <input type="hidden" name="return_to" value="items.php">
            <input type="hidden" name="redirect_anchor" value="categories">
            <h4 class="card-title">افزودن دسته‌بندی جدید</h4>
            <div class="form-group">
                <label>اسلاگ (انگلیسی)</label>
                <input type="text" name="slug" class="form-control" placeholder="attack / magic / defense ...">
            </div>
            <div class="form-group">
                <label>نام فارسی</label>
                <input type="text" name="name_fa" class="form-control" placeholder="مثال: حمله">
            </div>
            <div class="form-group">
                <label>نام انگلیسی</label>
                <input type="text" name="name_en" class="form-control" placeholder="Attack">
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
                <p class="empty-state">هیچ دسته‌بندی یافت نشد.</p>
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

<section class="panel-card" id="item-create">
    <div class="section-heading">
        <div>
            <h2 class="section-title">افزودن آیتم جدید</h2>
            <p class="section-subtitle">مشخصات کامل را پر کنید تا در صفحه عمومی و کانترها استفاده شود.</p>
        </div>
    </div>
    <form method="post" enctype="multipart/form-data" class="management-card management-card--inline">
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="return_to" value="items.php">
        <input type="hidden" name="redirect_anchor" value="item-create">
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
            <input type="text" name="slug" class="form-control" placeholder="sea-halberd">
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
                <input type="number" name="gold_cost" class="form-control" placeholder="2100">
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
            <input type="text" name="image_url" class="form-control" placeholder="یا مسیر موجود /assets/uploads/items/...">
        </div>
        <div class="form-actions">
            <button type="submit" class="primary">ثبت آیتم</button>
        </div>
    </form>
</section>

<section class="panel-card" id="item-editor">
    <div class="section-heading">
        <div>
            <h2 class="section-title">ویرایش سریع آیتم</h2>
            <p class="section-subtitle">از لیست پایین روی ویرایش بزنید تا اطلاعات همین‌جا لود شود.</p>
        </div>
    </div>
    <?php if ($editItem): ?>
        <form method="post" enctype="multipart/form-data" class="management-card management-card--inline">
            <input type="hidden" name="action" value="update_item">
            <input type="hidden" name="item_id" value="<?= (int) $editItem['id'] ?>">
            <input type="hidden" name="return_to" value="items.php">
            <input type="hidden" name="redirect_anchor" value="item-editor">
            <div class="form-group">
                <label>دسته‌بندی</label>
                <select name="category_id" class="form-control">
                    <?php foreach ($itemCategories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (int) $category['id'] === (int) $editItem['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name_fa'] ?? $category['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>نام فارسی</label>
                    <input type="text" name="name_fa" class="form-control" value="<?= htmlspecialchars($editItem['name_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>نام انگلیسی</label>
                    <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($editItem['name_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>اسلاگ</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editItem['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>رده</label>
                    <input type="text" name="tier" class="form-control" value="<?= htmlspecialchars($editItem['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>قیمت طلا</label>
                    <input type="number" name="gold_cost" class="form-control" value="<?= htmlspecialchars((string) ($editItem['gold_cost'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label>تصویر فعلی</label>
                    <?php $preview = admin_resolve_image_src($editItem['image_url'] ?? ''); ?>
                    <?php if ($preview !== ''): ?>
                        <div class="image-preview image-preview--circle">
                            <img src="<?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($editItem['name_fa'] ?? $editItem['name_en'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    <?php else: ?>
                        <span class="muted">تصویری آپلود نشده است.</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label>آپلود تصویر جدید</label>
                <input type="file" name="item_image" accept="image/*">
                <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($editItem['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/uploads/items/...">
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>ویژگی‌ها (فارسی)</label>
                    <textarea name="stats_fa" rows="2" class="form-control"><?= htmlspecialchars($editItem['stats_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div>
                    <label>ویژگی‌ها (انگلیسی)</label>
                    <textarea name="stats_en" rows="2" class="form-control"><?= htmlspecialchars($editItem['stats_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>پسیو یکتا (فارسی)</label>
                    <textarea name="passive_fa" rows="2" class="form-control"><?= htmlspecialchars($editItem['passive_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div>
                    <label>Unique Passive</label>
                    <textarea name="passive_en" rows="2" class="form-control"><?= htmlspecialchars($editItem['passive_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>فعال یکتا (فارسی)</label>
                    <textarea name="active_fa" rows="2" class="form-control"><?= htmlspecialchars($editItem['active_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div>
                    <label>Unique Active</label>
                    <textarea name="active_en" rows="2" class="form-control"><?= htmlspecialchars($editItem['active_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="form-group grid grid-2">
                <div>
                    <label>توضیحات فارسی</label>
                    <textarea name="description_fa" rows="2" class="form-control"><?= htmlspecialchars($editItem['description_fa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div>
                    <label>Description</label>
                    <textarea name="description_en" rows="2" class="form-control"><?= htmlspecialchars($editItem['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="secondary-button">ذخیره تغییرات</button>
                <a class="ghost-button" href="<?= htmlspecialchars(mlbb_admin_url('items.php') . '#item-list', ENT_QUOTES, 'UTF-8') ?>">انصراف</a>
            </div>
        </form>
    <?php else: ?>
        <p class="empty-state">برای ویرایش روی دکمه «ویرایش» در لیست آیتم‌ها کلیک کنید.</p>
    <?php endif; ?>
</section>

<section class="panel-card item-catalog" id="item-list">
    <div class="section-heading">
        <div>
            <h2 class="section-title">لیست آیتم‌ها</h2>
            <p class="section-description">همه آیتم‌ها با تصویر کوچک نمایش داده می‌شوند تا سریع پیدا شوند.</p>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <p class="empty-state">آیتمی برای نمایش وجود ندارد.</p>
    <?php else: ?>
        <div class="item-card-grid">
            <?php foreach ($items as $item): ?>
                <?php
                    $itemId = (int) $item['id'];
                    $itemImage = admin_resolve_image_src($item['image_url'] ?? '');
                    $categoryLabel = $item['category_name_fa'] ?? $item['category_name_en'] ?? 'بدون دسته';
                ?>
                <article class="item-card">
                    <div class="item-card-media">
                        <?php if ($itemImage !== ''): ?>
                            <img src="<?= htmlspecialchars($itemImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name_fa'] ?? $item['name_en'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <span class="item-card-placeholder" aria-hidden="true">?</span>
                        <?php endif; ?>
                    </div>
                    <div class="item-card-body">
                        <h3 class="item-card-title"><?= htmlspecialchars($item['name_fa'] ?? $item['name_en'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="item-card-meta">
                            <?php if (!empty($item['name_en'])): ?>
                                <span class="badge badge-ghost"><?= htmlspecialchars($item['name_en'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="badge badge-role"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($item['gold_cost'])): ?>
                                <span class="badge badge-gold"><?= number_format((int) $item['gold_cost']) ?> <span>طلا</span></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-card-actions">
                        <a class="icon-button" href="<?= htmlspecialchars(mlbb_admin_url('items.php') . '?item=' . $itemId . '#item-editor', ENT_QUOTES, 'UTF-8') ?>" title="ویرایش آیتم">
                            <span class="icon edit" aria-hidden="true"></span>
                            <span class="sr-only">ویرایش</span>
                        </a>
                        <form method="post" action="<?= htmlspecialchars(mlbb_admin_url('items.php'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('آیتم حذف شود؟ تمام ارجاعات کانتر نیز پاک می‌شوند.');">
                            <input type="hidden" name="action" value="delete_item">
                            <input type="hidden" name="item_id" value="<?= $itemId ?>">
                            <input type="hidden" name="return_to" value="items.php">
                            <input type="hidden" name="redirect_anchor" value="item-list">
                            <button type="submit" class="icon-button danger" title="حذف آیتم">
                                <span class="icon trash" aria-hidden="true"></span>
                                <span class="sr-only">حذف</span>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
