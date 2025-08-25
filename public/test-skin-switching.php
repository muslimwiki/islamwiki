<?php
/**
 * Test Skin Switching
 * 
 * This page tests the skin switching functionality
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = new IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$skinManager = $container->get('skin.manager');

// Handle skin switching
$newSkin = $_GET['skin'] ?? null;
if ($newSkin && $skinManager->hasSkin($newSkin)) {
    $skinManager->setActiveSkin($newSkin);
}

$currentSkin = $skinManager->getActiveSkinName();
$availableSkins = $skinManager->getAvailableSkins();
$currentSkinAssets = $skinManager->getSkinAssets();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skin Switching Test - IslamWiki</title>
    
    <!-- Load current skin CSS -->
    <?php if (isset($currentSkinAssets['css'])): ?>
        <?php foreach ($currentSkinAssets['css'] as $cssFile): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <h1>Skin Switching Test</h1>
        
        <div class="current-skin">
            <h2>Current Active Skin: <?= htmlspecialchars($currentSkin) ?></h2>
            
            <h3>Available Skins:</h3>
            <div class="skin-list">
                <?php foreach ($availableSkins as $name => $skin): ?>
                    <div class="skin-item <?= $name === $currentSkin ? 'active' : '' ?>">
                        <h4><?= htmlspecialchars($skin['name']) ?></h4>
                        <p><strong>Version:</strong> <?= htmlspecialchars($skin['version'] ?? 'Unknown') ?></p>
                        <p><strong>Author:</strong> <?= htmlspecialchars($skin['author'] ?? 'Unknown') ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($skin['description'] ?? 'No description') ?></p>
                        
                        <?php if ($name !== $currentSkin): ?>
                            <a href="?skin=<?= urlencode($name) ?>" class="switch-btn">Switch to <?= htmlspecialchars($name) ?></a>
                        <?php else: ?>
                            <span class="current-label">Currently Active</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="skin-assets">
            <h3>Current Skin Assets:</h3>
            <h4>CSS Files:</h4>
            <ul>
                <?php if (isset($currentSkinAssets['css'])): ?>
                    <?php foreach ($currentSkinAssets['css'] as $cssFile): ?>
                        <li><?= htmlspecialchars($cssFile) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No CSS files</li>
                <?php endif; ?>
            </ul>
            
            <h4>JavaScript Files:</h4>
            <ul>
                <?php if (isset($currentSkinAssets['js'])): ?>
                    <?php foreach ($currentSkinAssets['js'] as $jsFile): ?>
                        <li><?= htmlspecialchars($jsFile) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No JavaScript files</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="test-links">
            <h3>Test Pages:</h3>
            <ul>
                <li><a href="/en">Home Page</a></li>
                <li><a href="/en/dashboard">Dashboard</a></li>
                <li><a href="/en/wiki/Home">Wiki Home</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Load current skin JavaScript -->
    <?php if (isset($currentSkinAssets['js'])): ?>
        <?php foreach ($currentSkinAssets['js'] as $jsFile): ?>
            <script src="<?= htmlspecialchars($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .skin-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .skin-item { border: 2px solid #ddd; border-radius: 8px; padding: 20px; }
        .skin-item.active { border-color: #4F46E5; background-color: #f0f4ff; }
        .switch-btn { display: inline-block; padding: 10px 20px; background: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
        .switch-btn:hover { background: #3730a3; }
        .current-label { display: inline-block; padding: 10px 20px; background: #10b981; color: white; border-radius: 5px; margin-top: 10px; }
        .skin-assets { margin: 30px 0; }
        .skin-assets ul { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        .test-links { margin: 30px 0; }
        .test-links ul { list-style: none; padding: 0; }
        .test-links li { margin: 10px 0; }
        .test-links a { color: #4F46E5; text-decoration: none; }
        .test-links a:hover { text-decoration: underline; }
    </style>
</body>
</html> 