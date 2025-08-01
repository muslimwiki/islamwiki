<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get database connection
$db = $container->get('db');

// Manually check user settings
$userId = 1;
$stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch(\PDO::FETCH_ASSOC);

$userSkin = null;
if ($result && isset($result['settings'])) {
    $settings = json_decode($result['settings'], true);
    $userSkin = $settings['skin'] ?? null;
}

echo "User ID: $userId\n";
echo "User Skin Setting: " . ($userSkin ?? 'none') . "\n";

// Get skin manager
$skinManager = $container->get('skin.manager');

if ($userSkin) {
    $activeSkin = $skinManager->getSkin($userSkin);
    $activeSkinName = $userSkin;
} else {
    $activeSkin = $skinManager->getActiveSkin();
    $activeSkinName = $skinManager->getActiveSkinName();
}

echo "Active Skin: " . $activeSkinName . "\n";
echo "Skin CSS Length: " . strlen($activeSkin ? $activeSkin->getCssContent() : '') . "\n";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Direct Test</title>
    
    <!-- Skin CSS (must come before Tailwind to take precedence) -->
    <style>
        <?php echo $activeSkin ? $activeSkin->getCssContent() : ''; ?>
    </style>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    📚 IslamWiki (CSS Test - <?php echo $activeSkinName; ?>)
                </a>
                
                <nav class="nav-menu">
                    <a href="/" class="nav-link active">Home</a>
                    <a href="/pages" class="nav-link">Browse</a>
                    <a href="/about" class="nav-link">About</a>
                </nav>
                
                <div class="user-menu">
                    <a href="/login" class="nav-link">Sign In</a>
                    <a href="/register" class="btn btn-small">Get Started</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h1>CSS Test Page</h1>
            <p>This page should show the active skin colors.</p>
            <p><strong>Active Skin:</strong> <?php echo $activeSkinName; ?></p>
            <p><strong>User Skin Setting:</strong> <?php echo ($userSkin ?? 'none'); ?></p>
            
            <div style="margin-top: 2rem;">
                <button class="btn">Test Button</button>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background-color: var(--background-color); border-radius: 0.5rem;">
                <h3>Color Test</h3>
                <ul>
                    <li>Primary Color: var(--primary-color)</li>
                    <li>Secondary Color: var(--secondary-color)</li>
                    <li>Background: var(--background-color)</li>
                    <li>Text Color: var(--text-color)</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        console.log('CSS test page loaded');
        console.log('Active skin: <?php echo $activeSkinName; ?>');
    </script>
</body>
</html> 