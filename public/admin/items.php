<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/actions.php';

$missingTables = admin_check_schema($db);
if (!empty($missingTables)) {
    require __DIR__ . '/partials/layout_start.php';
    $schemaIssues = $missingTables;
    require __DIR__ . '/partials/schema_warning.php';
    require __DIR__ . '/partials/layout_end.php';

    return;
}

$itemCategories = admin_fetch_item_categories($db);
$items = admin_fetch_items($db);

$currentPage = 'items';

require __DIR__ . '/partials/layout_start.php';
require __DIR__ . '/partials/navigation.php';
require __DIR__ . '/partials/flash.php';
require __DIR__ . '/partials/items/manage.php';
require __DIR__ . '/partials/layout_end.php';
