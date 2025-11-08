<?php
if (!function_exists('mlbb_public_base_path')) {
    function mlbb_public_base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $segments = array_values(array_filter(explode('/', trim($scriptName, '/')), 'strlen'));

        if (!empty($segments)) {
            array_pop($segments);
        }

        if (!empty($segments) && end($segments) === 'admin') {
            array_pop($segments);
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
}
