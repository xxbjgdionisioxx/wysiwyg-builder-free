<?php
// api/export.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$user = requireAuth();
$userId = $user['id'];

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID required']);
    exit;
}

$projectId = $_GET['id'];
$exportType = $_GET['type'] ?? 'html'; // 'html' or 'php'

// Fetch project
$stmt = $pdo->prepare("SELECT name, layout_json FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$projectId, $userId]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    echo json_encode(['error' => 'Project not found']);
    exit;
}

$layoutHtml = $project['layout_json'];

// Clean HTML (Basic server-side cleanup, though JS export logic does some of this too)
// In a real app, use a DOM parser like DOMDocument. For MVP, we'll use regex/string replace.
$cleanHtml = preg_replace('/data-builder-id="[^"]*"/', '', $layoutHtml);
$cleanHtml = preg_replace('/draggable="true"/', '', $cleanHtml);
$cleanHtml = preg_replace('/\bbuilder-(element|container|grid|divider|image|button|form|input|datatable)-?(hover|selected)?\b/', '', $cleanHtml);
$cleanHtml = preg_replace('/\b(?:sortable-(?:ghost|drag|chosen))\b/', '', $cleanHtml);
$cleanHtml = preg_replace('/class="\s*"/', '', $cleanHtml); // Remove empty classes

// Generate base CSS
$cssContent = "
/* Exported Styles for " . $project['name'] . " */
body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
.builder-container { padding: 1rem; }
.builder-grid { display: grid; gap: 1rem; grid-template-columns: repeat(12, 1fr); }
.builder-grid > * { grid-column: span 12; }
@media(min-width: 768px) { .builder-grid > * { grid-column: span 6; } }
";

$zip = new ZipArchive();
$zipFileName = tempnam(sys_get_temp_dir(), 'export') . '.zip';

if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    echo json_encode(['error' => 'Cannot create zip file']);
    exit;
}

if ($exportType === 'html') {
    // Static HTML Export
    $htmlContent = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"UTF-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n<title>" . htmlspecialchars($project['name']) . "</title>\n<link rel=\"stylesheet\" href=\"style.css\">\n</head>\n<body>\n";
    $htmlContent .= $cleanHtml;
    $htmlContent .= "\n</body>\n</html>";
    
    $zip->addFromString('index.html', $htmlContent);
    $zip->addFromString('style.css', $cssContent);

} else if ($exportType === 'php') {
    // PHP/MySQL Dynamic Export
    
    // Convert data-bind attributes to PHP code using DOMDocument
    $doc = new DOMDocument();
    @$doc->loadHTML(mb_convert_encoding($cleanHtml, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//*[@data-bind]');
    
    $phpLogic = "<?php\nrequire_once 'db.php';\n";
    $queries = [];
    
    foreach ($nodes as $node) {
        $bind = $node->getAttribute('data-bind'); // e.g. "users.name"
        $parts = explode('.', $bind);
        if (count($parts) === 2) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[0]);
            $col = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[1]);
            
            if (!isset($queries[$table])) {
                $queries[$table] = [];
            }
            $queries[$table][] = $col;
            
            // For a datatable, we loop through rows
            if ($node->nodeName === 'table') {
                $tbody = $node->getElementsByTagName('tbody')->item(0);
                if ($tbody) {
                    $trHtml = "<?php foreach ($" . $table . "_data as \$row): ?>\n<tr>";
                    $thNodes = $node->getElementsByTagName('th');
                    foreach ($thNodes as $th) {
                        $trHtml .= "<td><?= htmlspecialchars(\$row['" . trim($th->nodeValue) . "'] ?? '') ?></td>";
                    }
                    $trHtml .= "</tr>\n<?php endforeach; ?>";
                    
                    // Clear existing tbody contents and insert PHP
                    while ($tbody->hasChildNodes()) {
                        $tbody->removeChild($tbody->firstChild);
                    }
                    // Since DOMDocument escapes PHP tags, we insert a placeholder
                    $tbody->appendChild($doc->createTextNode('@@PHP_TABLE_ROWS_' . $table . '@@'));
                }
            } else {
                // For simple text binding (e.g. heading, paragraph)
                $node->nodeValue = '@@PHP_VAR_' . $table . '_' . $col . '@@';
            }
        }
        $node->removeAttribute('data-bind');
    }
    
    // Add queries to PHP logic
    foreach ($queries as $table => $cols) {
        $phpLogic .= "\$stmt = \$pdo->query('SELECT * FROM `$table` LIMIT 100');\n";
        $phpLogic .= "\$" . $table . "_data = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
        $phpLogic .= "\$" . $table . "_first = \$" . $table . "_data[0] ?? [];\n";
    }
    $phpLogic .= "?>\n";
    
    $finalHtml = $doc->saveHTML();
    
    // Replace placeholders with real PHP code
    foreach ($queries as $table => $cols) {
        foreach ($cols as $col) {
            $finalHtml = str_replace('@@PHP_VAR_' . $table . '_' . $col . '@@', "<?= htmlspecialchars(\$" . $table . "_first['$col'] ?? '') ?>", $finalHtml);
        }
        $finalHtml = str_replace('@@PHP_TABLE_ROWS_' . $table . '@@', "<?php foreach ($" . $table . "_data as \$row): ?>\n<tr>\n<?php foreach (\$row as \$val): ?><td><?= htmlspecialchars(\$val) ?></td><?php endforeach; ?>\n</tr>\n<?php endforeach; ?>", $finalHtml);
    }
    
    $indexPhp = $phpLogic . "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"UTF-8\">\n<title>" . htmlspecialchars($project['name']) . "</title>\n<link rel=\"stylesheet\" href=\"style.css\">\n</head>\n<body>\n" . $finalHtml . "\n</body>\n</html>";
    
    $dbPhp = "<?php\n// Configure your database connection here\n\$host = '127.0.0.1';\n\$db   = 'builder_db';\n\$user = 'root';\n\$pass = '';\n\$charset = 'utf8mb4';\n\n\$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";\n\$options = [\n    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n    PDO::ATTR_EMULATE_PREPARES   => false,\n];\ntry {\n    \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);\n} catch (\\PDOException \$e) {\n    throw new \\PDOException(\$e->getMessage(), (int)\$e->getCode());\n}\n";
    
    $zip->addFromString('index.php', $indexPhp);
    $zip->addFromString('db.php', $dbPhp);
    $zip->addFromString('style.css', $cssContent);
}

$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=project_' . $projectId . '_' . $exportType . '.zip');
header('Content-Length: ' . filesize($zipFileName));
readfile($zipFileName);
unlink($zipFileName);
