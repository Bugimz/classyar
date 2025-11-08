<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت | آکادمی MLBB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/admin.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="panel-title">پنل مدیریت آکادمی</h1>
                    <p class="panel-subtitle">مدیریت کامل هیروها و آموزش‌های مربوط به Mobile Legends</p>
                </div>
                <nav class="admin-nav">
                    <a class="nav-link" href="<?= htmlspecialchars(mlbb_url(), ENT_QUOTES, 'UTF-8') ?>">مشاهده سایت</a>
                    <a class="nav-link" href="<?= htmlspecialchars(mlbb_admin_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>">خروج</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="admin-main container">
