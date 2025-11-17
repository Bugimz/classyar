<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

$config = require __DIR__ . '/../../config/config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $validUsername = hash_equals($config['admin_username'], $username);
    $validPassword = password_verify($password, $config['admin_password_hash']);

    if ($validUsername && $validPassword) {
        $_SESSION['admin_authenticated'] = true;
        header('Location: ' . mlbb_admin_url('index.php'));
        exit;
    }

    $error = 'نام کاربری یا رمز عبور اشتباه است';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود مدیر | آکادمی MLBB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(mlbb_url('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <div class="login-card">
        <h1>ورود به پنل مدیریت</h1>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">نام کاربری</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="نام کاربری" required>
            </div>
            <div class="form-group">
                <label for="password">رمز عبور</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="رمز عبور" required>
            </div>
            <button type="submit" class="primary" style="width:100%;">ورود</button>
        </form>
    </div>
</body>
</html>
