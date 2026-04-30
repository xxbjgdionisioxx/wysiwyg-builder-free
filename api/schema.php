<?php
// api/schema.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = requireAuth();

try {
    // Get all tables except system/internal ones
    $tablesQuery = $pdo->query("SHOW TABLES");
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
    
    $schema = [];
    
    foreach ($tables as $table) {
        if (in_array($table, ['users', 'projects'])) continue; // Optionally hide internal tables
        
        $columnsQuery = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);
        
        $schema[$table] = array_map(function($col) {
            return [
                'name' => $col['Field'],
                'type' => $col['Type']
            ];
        }, $columns);
    }
    
    echo json_encode(['success' => true, 'data' => $schema]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
