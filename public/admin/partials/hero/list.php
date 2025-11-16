<?php
declare(strict_types=1);

?>
<section class="panel-card hero-catalog" id="hero-list">
    <div class="section-heading">
        <div>
            <h2 class="section-title">لیست هیروهای ثبت‌شده</h2>
            <p class="section-description">به سرعت وضعیت هر هیرو را مشاهده و از همین‌جا ویرایش یا حذف کنید.</p>
        </div>
        <div class="section-actions">
            <a class="primary" href="<?= htmlspecialchars(mlbb_admin_url('hero_create.php'), ENT_QUOTES, 'UTF-8') ?>">+ افزودن هیرو جدید</a>
        </div>
    </div>

    <?php
        $filterValues = $heroFilters ?? ['search' => '', 'role_id' => 0, 'sort' => 'name'];
        $filterRoles = $roleOptions ?? [];
        $activeFilters = trim((string) ($filterValues['search'] ?? '')) !== ''
            || (int) ($filterValues['role_id'] ?? 0) > 0
            || (($filterValues['sort'] ?? 'name') !== 'name');
    ?>

    <form method="get" action="<?= htmlspecialchars(mlbb_admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>" class="hero-filter-toolbar" novalidate>
        <div class="form-group">
            <label for="filter-search">جستجوی نام</label>
            <input type="search" name="search" id="filter-search" class="form-control" value="<?= htmlspecialchars($filterValues['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="نام فارسی یا انگلیسی">
        </div>
        <div class="form-group">
            <label for="filter-role">فیلتر رول</label>
            <select name="role_id" id="filter-role" class="form-control">
                <option value="0">همه نقش‌ها</option>
                <?php foreach ($filterRoles as $role): ?>
                    <option value="<?= (int) $role['id'] ?>" <?= ((int) ($filterValues['role_id'] ?? 0) === (int) $role['id']) ? 'selected' : '' ?>><?= htmlspecialchars($role['name_fa'] ?? $role['name_en'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="filter-sort">مرتب‌سازی</label>
            <select name="sort" id="filter-sort" class="form-control">
                <option value="name" <?= (($filterValues['sort'] ?? 'name') === 'name') ? 'selected' : '' ?>>الفبایی</option>
                <option value="recent" <?= (($filterValues['sort'] ?? 'name') === 'recent') ? 'selected' : '' ?>>آخرین بروزرسانی</option>
                <option value="oldest" <?= (($filterValues['sort'] ?? 'name') === 'oldest') ? 'selected' : '' ?>>قدیمی‌ترین ثبت</option>
            </select>
        </div>
        <div class="form-group hero-filter-actions">
            <button type="submit" class="primary">اعمال فیلتر</button>
            <?php if ($activeFilters): ?>
                <a class="link-muted" href="<?= htmlspecialchars(mlbb_admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">پاکسازی</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($heroes)): ?>
        <p class="empty-state">هنوز هیرویی ثبت نشده است. از منوی بالا گزینه «افزودن هیرو» را انتخاب کنید.</p>
    <?php else: ?>
        <div class="hero-card-grid">
            <?php foreach ($heroes as $heroRow): ?>
                <?php
                    $heroId = (int) $heroRow['id'];
                    $imageSrc = admin_resolve_image_src($heroRow['image_url'] ?? '');
                    $skills = $heroRow['skills'] ?? [];
                ?>
                <article class="hero-card">
                    <header class="hero-card-header">
                        <div class="hero-card-avatar">
                            <?php if ($imageSrc !== ''): ?>
                                <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en']) ?>">
                            <?php else: ?>
                                <div class="hero-card-avatar-placeholder" aria-hidden="true">?</div>
                            <?php endif; ?>
                        </div>
                        <div class="hero-card-info">
                            <h3 class="hero-card-title"><?= htmlspecialchars($heroRow['name_fa'] ?? $heroRow['name_en']) ?></h3>
                            <div class="hero-card-meta">
                                <?php if (!empty($heroRow['role_name'])): ?>
                                    <span class="badge badge-role"><?= htmlspecialchars($heroRow['role_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($heroRow['name_en'])): ?>
                                    <span class="badge badge-ghost"><?= htmlspecialchars($heroRow['name_en']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="hero-card-actions">
                            <a class="icon-button" href="<?= htmlspecialchars(mlbb_admin_url('hero.php') . '?hero=' . $heroId) ?>" title="ویرایش هیرو">
                                <span class="icon edit" aria-hidden="true"></span>
                                <span class="sr-only">ویرایش</span>
                            </a>
                            <form method="post" action="<?= htmlspecialchars(mlbb_admin_url('index.php')) ?>" onsubmit="return confirm('آیا از حذف این هیرو و تمامی اطلاعات وابسته مطمئن هستید؟ این عملیات قابل بازگشت نیست.');">
                                <input type="hidden" name="action" value="delete_hero">
                                <input type="hidden" name="hero_id" value="<?= $heroId ?>">
                                <input type="hidden" name="redirect_anchor" value="hero-list">
                                <input type="hidden" name="return_to" value="index.php">
                                <button type="submit" class="icon-button danger" title="حذف کامل هیرو">
                                    <span class="icon trash" aria-hidden="true"></span>
                                    <span class="sr-only">حذف</span>
                                </button>
                            </form>
                        </div>
                    </header>

                    <?php if (!empty($skills)): ?>
                        <div class="hero-card-skills" aria-label="مهارت‌های هیرو">
                            <?php foreach ($skills as $skill): ?>
                                <?php
                                    $skillLabel = admin_skill_type_label((string) ($skill['skill_type'] ?? ''));
                                    $skillName = trim((string) ($skill['name'] ?? ''));
                                    $skillImage = admin_resolve_image_src($skill['image_url'] ?? '');
                                    $skillInitial = $skillLabel !== '' ? (function_exists('mb_substr') ? mb_substr($skillLabel, 0, 1, 'UTF-8') : substr($skillLabel, 0, 1)) : '?';
                                ?>
                                <div class="skill-chip">
                                    <div class="skill-chip-thumb">
                                        <?php if ($skillImage !== ''): ?>
                                            <img src="<?= htmlspecialchars($skillImage) ?>" alt="<?= htmlspecialchars($skillLabel) ?>">
                                        <?php else: ?>
                                            <span aria-hidden="true"><?= htmlspecialchars($skillInitial ?: '?') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="skill-chip-body">
                                        <span class="skill-chip-type"><?= htmlspecialchars($skillLabel) ?></span>
                                        <?php if ($skillName !== '' && $skillName !== $skillLabel): ?>
                                            <span class="skill-chip-name"><?= htmlspecialchars($skillName) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
