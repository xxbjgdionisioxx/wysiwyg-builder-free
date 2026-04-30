<?php
// api/projects.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = requireAuth();
$userId = $user['id']; // From Supabase JWT

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List projects for user
    try {
        $stmt = $pdo->prepare("SELECT id, name, created_at, updated_at FROM projects WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$userId]);
        $projects = $stmt->fetchAll();
        
        // If a specific ID is requested, return its JSON
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT layout_json FROM projects WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['id'], $userId]);
            $project = $stmt->fetch();
            
            if ($project) {
                echo json_encode(['success' => true, 'data' => $project['layout_json']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Project not found']);
            }
            exit;
        }
        
        echo json_encode(['success' => true, 'data' => $projects]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save project
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? 'Untitled Project';
    $layoutJson = $input['layout_json'] ?? '';
    $id = $input['id'] ?? null;
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE projects SET name = ?, layout_json = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $layoutJson, $id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Project updated', 'id' => $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO projects (user_id, name, layout_json) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $name, $layoutJson]);
            echo json_encode(['success' => true, 'message' => 'Project created', 'id' => $pdo->lastInsertId()]);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
}
