<?php
/**
 * Settings Page - Skin Management
 * 
 * Demonstrates the standardized skin management approach
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Application.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new Application(__DIR__ . '/..');

// Get container and skin manager
$container = $app->getContainer();
$skinManager = $container->get('skin.manager');

// Handle skin switching
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'switch_skin' && isset($_POST['skin'])) {
        $skinName = $_POST['skin'];
        
        if ($skinManager->hasSkin($skinName)) {
            $success = SkinManager::setActiveSkinStatic($app, $skinName);
            if ($success) {
                $message = "Successfully switched to $skinName skin!";
            } else {
                $error = "Failed to switch to $skinName skin.";
            }
        } else {
            $error = "Skin '$skinName' is not available.";
        }
    }
}

// Get current active skin
$activeSkin = SkinManager::getActiveSkinNameStatic($app);

// Get available skins
$availableSkins = $skinManager->getAvailableSkinNames();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skin Management Settings - IslamWiki</title>
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
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .skin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .skin-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .skin-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
        }
        .skin-card.active {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        .skin-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .skin-description {
            color: #666;
            margin-bottom: 15px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn.active {
            background-color: #28a745;
        }
        .btn.active:hover {
            background-color: #1e7e34;
        }
        .info-section {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #0056b3;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Skin Management Settings</h1>
            <p>Manage your IslamWiki skin preferences using the standardized approach</p>
        </div>

        <?php if ($message): ?>
            <div class="success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-section">
            <h3>📊 Current Status</h3>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo htmlspecialchars($activeSkin); ?></div>
                    <div class="stat-label">Active Skin</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($availableSkins); ?></div>
                    <div class="stat-label">Available Skins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $skinManager->hasSkin($activeSkin) ? 'Yes' : 'No'; ?></div>
                    <div class="stat-label">Skin Valid</div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>🔧 Standardized Approach Benefits</h3>
            <ul>
                <li><strong>Consistency:</strong> Single source of truth for active skin</li>
                <li><strong>Runtime Flexibility:</strong> Change skins without modifying files</li>
                <li><strong>Better Error Handling:</strong> Validation and proper fallbacks</li>
                <li><strong>Performance:</strong> Caching and optimization</li>
                <li><strong>User Preferences:</strong> Support for user-specific skins</li>
            </ul>
        </div>

        <h2>🎨 Available Skins</h2>
        <div class="skin-grid">
            <?php foreach ($availableSkins as $skinName): ?>
                <?php 
                $isActive = strtolower($skinName) === strtolower($activeSkin);
                $skin = $skinManager->getSkin($skinName);
                $description = $skin ? $skin->getDescription() : 'No description available';
                ?>
                <div class="skin-card <?php echo $isActive ? 'active' : ''; ?>">
                    <div class="skin-name"><?php echo htmlspecialchars($skinName); ?></div>
                    <div class="skin-description"><?php echo htmlspecialchars($description); ?></div>
                    
                    <?php if ($isActive): ?>
                        <button class="btn active" disabled>Currently Active</button>
                    <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="switch_skin">
                            <input type="hidden" name="skin" value="<?php echo htmlspecialchars($skinName); ?>">
                            <button type="submit" class="btn">Switch to <?php echo htmlspecialchars($skinName); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="/" class="btn">← Back to Home</a>
            <a href="/test-muslim-skin.php" class="btn">Test Muslim Skin</a>
        </div>

        <div class="info-section" style="margin-top: 30px;">
            <h3>🔍 Technical Details</h3>
            <p><strong>Method Used:</strong> <code>SkinManager::setActiveSkinStatic($app, 'SkinName')</code></p>
            <p><strong>Active Skin:</strong> <code><?php echo htmlspecialchars($activeSkin); ?></code></p>
            <p><strong>Available Skins:</strong> <?php echo implode(', ', array_map('htmlspecialchars', $availableSkins)); ?></p>
            <p><strong>Skin Manager Class:</strong> <code><?php echo get_class($skinManager); ?></code></p>
        </div>
    </div>
</body>
</html> 