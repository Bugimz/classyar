<?php
if (!function_exists('mlbb_public_base_path')) {
    function mlbb_public_base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $segments = array_values(array_filter(explode('/', trim($scriptName, '/')), 'strlen'));

        if (!empty($segments)) {
            array_pop($segments);
        }

        while (!empty($segments)) {
            $last = end($segments);
            if (in_array($last, ['admin', 'api'], true)) {
                array_pop($segments);
                continue;
            }

            break;
        }

        if (empty($segments)) {
            return '/';
        }

        return '/' . implode('/', $segments) . '/';
    }

    function mlbb_url(string $path = ''): string
    {
        $base = mlbb_public_base_path();
        $trimBase = rtrim($base, '/');
        $trimPath = ltrim($path, '/');

        if ($trimPath === '') {
            return $trimBase === '' ? '/' : $trimBase . '/';
        }

        $prefix = $trimBase === '' ? '' : $trimBase;

        return $prefix . '/' . $trimPath;
    }

    function mlbb_admin_url(string $path = ''): string
    {
        $base = rtrim(mlbb_public_base_path(), '/');
        $adminBase = $base === '' ? '/admin' : $base . '/admin';
        $trimPath = ltrim($path, '/');

        return $trimPath === '' ? $adminBase . '/' : $adminBase . '/' . $trimPath;
    }

    function mlbb_asset_url(?string $path): string
    {
        if ($path === null) {
            return '';
        }

        $normalized = trim($path);

        if ($normalized === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $normalized)) {
            return $normalized;
        }

        return mlbb_url(ltrim($normalized, '/'));
    }
}
