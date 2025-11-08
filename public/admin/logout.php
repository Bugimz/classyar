<?php
session_start();
$_SESSION = [];
session_destroy();

require_once __DIR__ . '/../../config/helpers.php';

header('Location: ' . mlbb_admin_url('login.php'));
exit;
