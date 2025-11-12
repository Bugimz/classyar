<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';

session_start();

if (empty($_SESSION['admin_authenticated'])) {
    header('Location: ' . mlbb_admin_url('login.php'));
    exit;
}

$database = new Database();
$db = $database->getConnection();

$flash = $_SESSION['admin_flash'] ?? null;
if ($flash !== null) {
    unset($_SESSION['admin_flash']);
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function admin_redirect(string $path, array $query = [], ?string $anchor = null): void
{
    $url = mlbb_admin_url($path);
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    if ($anchor !== null && $anchor !== '') {
        $url .= '#' . $anchor;
    }

    header('Location: ' . $url);
    exit;
}

function admin_sanitize(?string $value): string
{
    return trim((string) ($value ?? ''));
}

function admin_filter_anchor(?string $value): string
{
    if ($value === null) {
        return '';
    }

    $clean = preg_replace('/[^a-z0-9_-]/i', '', $value);

    return $clean ?? '';
}

function admin_handle_image_upload(string $field, string $folder): ?string
{
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }

    $file = $_FILES[$field];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || ($file['name'] ?? '') === '') {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('آپلود فایل با خطا مواجه شد. لطفاً مجدد تلاش کنید.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('فرمت تصویر مجاز نیست. فقط jpg، jpeg، png، gif یا webp قابل قبول است.');
    }

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        throw new RuntimeException('مسیر public پیدا نشد.');
    }

    $targetDir = $publicRoot . '/assets/uploads/' . $folder;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        throw new RuntimeException('امکان ایجاد پوشه برای آپلود وجود ندارد.');
    }

    $fileName = $folder . '_' . str_replace('.', '', uniqid('', true)) . '.' . $extension;
    $destination = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('انتقال فایل آپلود شده انجام نشد.');
    }

    return '/assets/uploads/' . $folder . '/' . $fileName;
}

function admin_resolve_image_src(?string $path): string
{
    if (empty($path)) {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $trimmed = ltrim($path, '/');

    return mlbb_url($trimmed);
}

function admin_skill_type_label(string $type): string
{
    $map = [
        'Passive' => 'اسکیل پسیو',
        'Skill1' => 'اسکیل ۱',
        'Skill2' => 'اسکیل ۲',
        'Skill3' => 'اسکیل ۳',
        'Ultimate' => 'آلتیمیت',
    ];

    return $map[$type] ?? $type;
}
