<?php
declare(strict_types=1);

$metricMap = [
    'heroes' => ['label' => 'تعداد هیرو', 'hint' => 'مجموع هیروهای ثبت‌شده در سامانه'],
    'roles' => ['label' => 'نقش‌ها', 'hint' => 'نقش‌های قابل انتخاب برای هر هیرو'],
    'skills' => ['label' => 'مهارت‌ها', 'hint' => 'تمام اسکیل‌های ثبت‌شده'],
    'combos' => ['label' => 'کامبوها', 'hint' => 'راهنمای کامبو و ترتیب استفاده از مهارت‌ها'],
    'combo_steps' => ['label' => 'مراحل کامبو', 'hint' => 'آیکن‌های تصویری برای اجرای کامبوها'],
    'tips' => ['label' => 'نکات و ترفندها', 'hint' => 'آموزش‌های متنی برای هر هیرو'],
    'counter_groups' => ['label' => 'بخش‌های کانتر', 'hint' => 'گروه‌بندی هیروها و آیتم‌های مقابله'],
    'counter_entities' => ['label' => 'آیتم‌ها و هیروهای کانتر', 'hint' => 'موارد ثبت‌شده در هر گروه کانتر'],
    'strengths' => ['label' => 'نقاط قوت', 'hint' => 'مزیت‌های کلیدی هیروها'],
    'weaknesses' => ['label' => 'نقاط ضعف', 'hint' => 'چالش‌های هر هیرو'],
];

?>
<section class="panel-card" id="dashboard-metrics">
    <div class="section-heading">
        <div>
            <h2 class="section-title">آمار کلی پایگاه داده</h2>
            <p class="section-description">وضعیت فعلی محتوا در یک نگاه</p>
        </div>
    </div>

    <div class="metrics-grid">
        <?php foreach ($metricMap as $key => $meta): ?>
            <div class="metric-card">
                <div class="metric-value"><?= number_format($stats[$key] ?? 0) ?></div>
                <div class="metric-label"><?= htmlspecialchars($meta['label']) ?></div>
                <p class="metric-hint"><?= htmlspecialchars($meta['hint']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
