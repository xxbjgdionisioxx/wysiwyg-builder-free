<?php
// api/create-table.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$tableName = $input['tableName'] ?? '';
$fields = $input['fields'] ?? [];

if (empty($tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table name']);
    exit;
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'error' => 'No fields provided']);
    exit;
}

// Build query securely
$columns = ["`id` INT AUTO_INCREMENT PRIMARY KEY"];

foreach ($fields as $field) {
    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $field['name']);
    if (empty($name)) continue;
    
    // Default to VARCHAR(255) for simplicity unless specified
    $type = "VARCHAR(255)";
    if (isset($field['type'])) {
        if ($field['type'] === 'text' || $field['type'] === 'textarea') $type = "TEXT";
        if ($field['type'] === 'number') $type = "INT";
        if ($field['type'] === 'date') $type = "DATE";
    }
    
    $columns[] = "`$name` $type";
}

$columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";

$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(', ', $columns) . ")";

try {
    $pdo->exec($sql);
    echo json_encode(['success' => true, 'message' => "Table $tableName created"]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
