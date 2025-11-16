<?php
if (!isset($schemaIssues) || !is_array($schemaIssues)) {
    $schemaIssues = [];
}
?>
<section class="card mb-4">
    <div class="card-body">
        <h1 class="h4 mb-3">🚧 دیتابیس هنوز آماده نیست</h1>
        <p class="mb-3">
            برای استفاده از پنل مدیریت، باید جداول موردنیاز در دیتابیس ساخته شوند. لطفاً فایل‌های SQL پروژه را به ترتیب زیر روی دیتابیس خود اعمال کنید:
        </p>
        <ol class="mb-3 ps-3">
            <li><code>database/schema.sql</code> — ایجاد تمام جداول و ساختار اولیه</li>
            <li><code>database/seed_roles.sql</code> — مقداردهی اولیه جدول نقش‌ها (Tank، Mage و ...)</li>
        </ol>
        <?php if (!empty($schemaIssues)): ?>
            <div class="alert alert-warning" role="alert">
                <strong>جداول ناموجود:</strong>
                <span><?= htmlspecialchars(implode(', ', $schemaIssues), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>
        <p class="mb-0">
            پس از اجرای اسکریپت‌ها، صفحه را مجدداً بارگذاری کنید. اگر هنوز خطا داشتید، مطمئن شوید نام دیتابیس در فایل <code>config/config.php</code> درست تنظیم شده باشد.
        </p>
    </div>
</section>
