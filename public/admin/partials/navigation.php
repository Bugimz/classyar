<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';

$menuItems = [
    [
        'id' => 'dashboard',
        'label' => 'داشبورد',
        'description' => 'نمای کلی آمار و هیروها',
        'href' => mlbb_admin_url('index.php'),
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="shortcut-icon-svg"><path d="M4 13h6V4H4zm0 7h6v-6H4zm10 0h6V11h-6zm0-16v6h6V4z"/></svg>',
    ],
    [
        'id' => 'hero_create',
        'label' => 'افزودن هیرو',
        'description' => 'ثبت هیرو جدید با تمام جزئیات',
        'href' => mlbb_admin_url('hero_create.php'),
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="shortcut-icon-svg"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>',
    ],
    [
        'id' => 'hero_manage',
        'label' => 'مدیریت هیروها',
        'description' => 'ویرایش اطلاعات کامل و مهارت‌ها',
        'href' => mlbb_admin_url('hero.php'),
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="shortcut-icon-svg"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
    ],
    [
        'id' => 'items',
        'label' => 'مدیریت آیتم‌ها',
        'description' => 'ایجاد و ویرایش آیتم‌ها و دسته‌بندی‌ها',
        'href' => mlbb_admin_url('items.php'),
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="shortcut-icon-svg"><path d="M12 2l-5.5 9h11zM4 13l-2 9h20l-2-9z"/></svg>',
    ],
];
?>
<nav class="admin-shortcuts" aria-label="منوی سریع مدیریت">
    <ul class="shortcut-list">
        <?php foreach ($menuItems as $item): ?>
            <?php $isActive = $currentPage === $item['id']; ?>
            <li class="shortcut-item<?= $isActive ? ' is-active' : '' ?>">
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="shortcut-link">
                    <span class="shortcut-icon"><?= $item['icon'] ?></span>
                    <span class="shortcut-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="shortcut-description"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
