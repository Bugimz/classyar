<?php
declare(strict_types=1);

?>
<section class="panel-card" id="hero-list">
    <div class="section-heading">
        <div>
            <h2 class="section-title">لیست هیروهای ثبت‌شده</h2>
            <p class="section-description">برای ویرایش روی دکمه «ویرایش» کلیک کنید یا برای حذف کامل، گزینه «حذف» را بزنید.</p>
        </div>
    </div>

    <?php if (empty($heroes)): ?>
        <p class="empty-state">هنوز هیرویی ثبت نشده است. از فرم «افزودن هیرو جدید» استفاده کنید.</p>
    <?php else: ?>
        <div class="hero-list-grid">
            <?php foreach ($heroes as $heroRow): ?>
                <?php
                    $heroId = (int) $heroRow['id'];
                    $imageSrc = admin_resolve_image_src($heroRow['image_url'] ?? '');
                ?>
                <article class="hero-tile">
                    <div class="hero-tile-image">
                        <?php if ($imageSrc !== ''): ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en']) ?>">
                        <?php else: ?>
                            <div class="hero-tile-placeholder">بدون تصویر</div>
                        <?php endif; ?>
                    </div>
                    <div class="hero-tile-body">
                        <h3 class="hero-tile-title"><?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en']) ?></h3>
                        <?php if (!empty($heroRow['name_en'])): ?>
                            <p class="hero-tile-subtitle">نام انگلیسی: <?= htmlspecialchars($heroRow['name_en']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($heroRow['role_name'])): ?>
                            <span class="hero-tile-role">رول: <?= htmlspecialchars($heroRow['role_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="hero-tile-actions">
                        <a class="secondary-button" href="<?= htmlspecialchars(mlbb_admin_url('index.php') . '?hero=' . $heroId) ?>#manage">ویرایش</a>
                        <form method="post" action="<?= htmlspecialchars(mlbb_admin_url('index.php')) ?>" onsubmit="return confirm('آیا از حذف این هیرو و تمامی اطلاعات وابسته مطمئن هستید؟ این عملیات قابل بازگشت نیست.');">
                            <input type="hidden" name="action" value="delete_hero">
                            <input type="hidden" name="hero_id" value="<?= $heroId ?>">
                            <input type="hidden" name="redirect_anchor" value="hero-list">
                            <button type="submit" class="danger-button">حذف</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
