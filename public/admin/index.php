<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/actions.php';

$roles = admin_fetch_roles($db);
$heroes = admin_fetch_heroes($db);

$difficulties = [
    'Easy' => 'آسان',
    'Medium' => 'متوسط',
    'Hard' => 'سخت',
    'Expert' => 'حرفه‌ای',
];

$requestedHeroId = isset($_GET['hero']) ? (int) $_GET['hero'] : null;
$activeHeroId = $requestedHeroId ?: (isset($heroes[0]) ? (int) $heroes[0]['id'] : 0);
$activeHeroId = $activeHeroId > 0 ? $activeHeroId : 0;

$activeHeroBundle = $activeHeroId > 0 ? admin_fetch_hero_bundle($db, $activeHeroId) : null;

require __DIR__ . '/partials/layout_start.php';
require __DIR__ . '/partials/flash.php';
require __DIR__ . '/partials/hero/create_form.php';
require __DIR__ . '/partials/hero/manage.php';
require __DIR__ . '/partials/layout_end.php';
