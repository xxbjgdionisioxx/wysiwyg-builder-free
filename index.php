<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WYSIWYG Builder</title>
    <link rel="stylesheet" href="assets/css/builder.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div id="app" class="builder">
        <!-- Topbar -->
        <?php include 'components/topbar.php'; ?>
        
        <div class="builder__main">
            <!-- Left Sidebar: Components -->
            <?php include 'components/sidebar.php'; ?>
            
            <!-- Center: Canvas Area -->
            <div class="builder__workspace">
                <div class="builder__canvas-wrapper">
                    <!-- The actual canvas where elements are dropped -->
                    <div id="canvas" class="canvas-area"></div>
                    
                    <!-- Code view toggle -->
                    <div id="code-view" class="code-area" style="display: none;">
                        <textarea id="code-editor"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar: Properties & Database -->
            <?php include 'components/properties.php'; ?>
        </div>
    </div>

    <!-- External Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <!-- Modules -->
    <script type="module" src="assets/js/auth.js"></script>
    <script type="module" src="assets/js/history.js"></script>
    <script type="module" src="assets/js/dataBinding.js"></script>
    <script type="module" src="assets/js/properties.js"></script>
    <script type="module" src="assets/js/canvas.js"></script>
    <script type="module" src="assets/js/export.js"></script>
    <script type="module" src="assets/js/builder.js"></script>
</body>
</html>
