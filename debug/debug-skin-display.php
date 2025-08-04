<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    
    // Get current active skin
    $activeSkin = $skinManager->getActiveSkin();
    $activeSkinName = $skinManager->getActiveSkinName();
    
    // Get all available skins
    $availableSkins = $skinManager->getSkins();
    
    // Get skin data from container
    $skinData = null;
    if ($container->has('skin.data')) {
        $skinData = $container->get('skin.data');
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Skin Debug - IslamWiki</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #eee;
            }
            .section {
                margin-bottom: 30px;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .section h2 {
                margin-top: 0;
                color: #333;
            }
            .skin-info {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin: 10px 0;
            }
            .skin-switch {
                margin: 10px 0;
            }
            .btn {
                padding: 10px 20px;
                margin: 5px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
            }
            .btn-primary {
                background: #007bff;
                color: white;
            }
            .btn-success {
                background: #28a745;
                color: white;
            }
            .btn-warning {
                background: #ffc107;
                color: black;
            }
            .current-skin {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                border-radius: 5px;
                margin: 10px 0;
            }
            .skin-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin: 15px 0;
            }
            .skin-card {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 15px;
                background: white;
            }
            .skin-card.active {
                border-color: #28a745;
                background: #f8fff9;
            }
            .debug-info {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                font-size: 12px;
                overflow-x: auto;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔍 Skin Debug Page</h1>
                <p>Testing dynamic skin management and switching</p>
            </div>
            
            <div class="section">
                <h2>🎯 Current Active Skin</h2>
                <div class="current-skin">
                    <strong>Active Skin:</strong> <?php echo htmlspecialchars($activeSkinName); ?><br>
                    <strong>Skin Object:</strong> <?php echo $activeSkin ? htmlspecialchars($activeSkin->getName()) : 'None'; ?><br>
                    <strong>Version:</strong> <?php echo $activeSkin ? htmlspecialchars($activeSkin->getVersion()) : 'Unknown'; ?><br>
                    <strong>Author:</strong> <?php echo $activeSkin ? htmlspecialchars($activeSkin->getAuthor()) : 'Unknown'; ?>
                </div>
            </div>
            
            <div class="section">
                <h2>🔄 Skin Switching</h2>
                <div class="skin-switch">
                    <p>Click a button to switch skins:</p>
                    <a href="?skin=Bismillah" class="btn btn-primary">Switch to Bismillah</a>
                    <a href="?skin=Muslim" class="btn btn-success">Switch to Muslim</a>
                    <a href="?skin=bismillah" class="btn btn-warning">Switch to bismillah (lowercase)</a>
                    <a href="?skin=muslim" class="btn btn-warning">Switch to muslim (lowercase)</a>
                </div>
                
                <?php
                // Handle skin switching
                if (isset($_GET['skin'])) {
                    $newSkin = $_GET['skin'];
                    $result = $skinManager->setActiveSkin($newSkin);
                    
                    if ($result) {
                        echo '<div class="current-skin">✅ Successfully switched to: ' . htmlspecialchars($newSkin) . '</div>';
                        // Refresh the page to show the new skin
                        echo '<script>setTimeout(function() { window.location.href = window.location.pathname; }, 1000);</script>';
                    } else {
                        echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">❌ Failed to switch to: ' . htmlspecialchars($newSkin) . '</div>';
                    }
                }
                ?>
            </div>
            
            <div class="section">
                <h2>📁 Available Skins</h2>
                <div class="skin-list">
                    <?php foreach ($availableSkins as $name => $skin): ?>
                        <div class="skin-card <?php echo ($name === $activeSkinName) ? 'active' : ''; ?>">
                            <h3><?php echo htmlspecialchars($skin->getName()); ?></h3>
                            <p><strong>Key:</strong> <?php echo htmlspecialchars($name); ?></p>
                            <p><strong>Version:</strong> <?php echo htmlspecialchars($skin->getVersion()); ?></p>
                            <p><strong>Author:</strong> <?php echo htmlspecialchars($skin->getAuthor()); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($skin->getDescription()); ?></p>
                            <p><strong>Has CSS:</strong> <?php echo $skin->getCssContent() ? 'Yes' : 'No'; ?></p>
                            <p><strong>Has JS:</strong> <?php echo $skin->getJsContent() ? 'Yes' : 'No'; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="section">
                <h2>📦 Container Data</h2>
                <div class="debug-info">
                    <strong>Skin Data from Container:</strong><br>
                    <?php if ($skinData): ?>
                        <pre><?php echo htmlspecialchars(json_encode($skinData, JSON_PRETTY_PRINT)); ?></pre>
                    <?php else: ?>
                        <p>No skin data available in container</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="section">
                <h2>🔧 Debug Information</h2>
                <div class="debug-info">
                    <strong>Container Bindings:</strong><br>
                    <?php
                    $bindings = [
                        'skin.manager' => $container->has('skin.manager'),
                        'skin.active' => $container->has('skin.active'),
                        'skin.data' => $container->has('skin.data')
                    ];
                    foreach ($bindings as $binding => $exists) {
                        echo "- {$binding}: " . ($exists ? 'Bound' : 'Not bound') . "<br>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="section">
                <h2>🎨 Current Skin CSS Preview</h2>
                <div class="debug-info">
                    <?php if ($activeSkin && $activeSkin->getCssContent()): ?>
                        <strong>CSS Content (first 500 characters):</strong><br>
                        <pre><?php echo htmlspecialchars(substr($activeSkin->getCssContent(), 0, 500)) . '...'; ?></pre>
                    <?php else: ?>
                        <p>No CSS content available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    echo '<h1>Error</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
} 