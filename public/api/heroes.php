<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->query('SELECT h.id, h.name_en, h.name_fa, h.image_url, r.name_en AS role_en, r.name_fa AS role_fa FROM heroes h JOIN hero_roles r ON h.role_id = r.id ORDER BY h.name_fa');
    $heroes = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $heroes,
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
