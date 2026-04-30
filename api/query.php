<?php
// api/query.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$table = $input['table'] ?? '';
$columns = $input['columns'] ?? ['*']; // e.g. ['name', 'email'] or ['*']

// Basic validation
if (empty($table) || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table name']);
    exit;
}

// Sanitize columns
$safeColumns = [];
foreach ($columns as $col) {
    if ($col === '*') {
        $safeColumns[] = '*';
    } else {
        $safeCol = preg_replace('/[^a-zA-Z0-9_]/', '', $col);
        if (!empty($safeCol)) {
            $safeColumns[] = "`$safeCol`";
        }
    }
}

if (empty($safeColumns)) {
    $safeColumns = ['*'];
}

$colsSql = implode(', ', $safeColumns);
$sql = "SELECT $colsSql FROM `$table` LIMIT 100"; // Limit for safety in builder

try {
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
