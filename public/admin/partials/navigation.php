<?php
declare(strict_types=1);

$currentPage = $currentPage ?? '';

$menuItems = [
    [
        'id' => 'dashboard',
        'label' => 'داشبورد',
        'description' => 'نمای کلی آمار و هیروها',
        'href' => mlbb_admin_url('index.php'),
    ],
    [
        'id' => 'hero_create',
        'label' => 'افزودن هیرو',
        'description' => 'ثبت هیرو جدید با تمام جزئیات',
        'href' => mlbb_admin_url('hero_create.php'),
    ],
    [
        'id' => 'hero_manage',
        'label' => 'مدیریت هیروها',
        'description' => 'ویرایش اطلاعات کامل و مهارت‌ها',
        'href' => mlbb_admin_url('hero.php'),
    ],
];
?>
<nav class="admin-shortcuts" aria-label="منوی سریع مدیریت">
    <ul class="shortcut-list">
        <?php foreach ($menuItems as $item): ?>
            <?php $isActive = $currentPage === $item['id']; ?>
            <li class="shortcut-item<?= $isActive ? ' is-active' : '' ?>">
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="shortcut-link">
                    <span class="shortcut-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="shortcut-description"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
