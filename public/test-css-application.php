<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Skins\SkinManager;

$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Simulate a logged-in user
$session = $container->get('session');
$session->setUserId(1);
$session->setUsername('testuser');

// Get skin manager
$skinManager = $container->get('skin.manager');
$activeSkin = $skinManager->getActiveSkinForUser(1);
$activeSkinName = $skinManager->getActiveSkinNameForUser(1);

echo "Active Skin: " . $activeSkinName . "\n";
echo "Skin CSS Length: " . strlen($activeSkin ? $activeSkin->getCssContent() : '') . "\n";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Application Test</title>
    
    <!-- Skin CSS (must come before Tailwind to take precedence) -->
    <style>
        <?php echo $activeSkin ? $activeSkin->getCssContent() : ''; ?>
    </style>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="header" style="background: linear-gradient(135deg, #2E7D32, #4CAF50) !important; color: white !important; padding: 1rem 0 !important;">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo" style="color: white !important; font-size: 1.5rem !important; font-weight: 700 !important;">
                    📚 IslamWiki (GreenSkin Test)
                </a>
                
                <nav class="nav-menu">
                    <a href="/" class="nav-link active" style="color: white !important;">Home</a>
                    <a href="/pages" class="nav-link" style="color: white !important;">Browse</a>
                    <a href="/about" class="nav-link" style="color: white !important;">About</a>
                </nav>
                
                <div class="user-menu">
                    <a href="/login" class="nav-link" style="color: white !important;">Sign In</a>
                    <a href="/register" class="btn btn-small" style="background-color: #4CAF50 !important; color: white !important;">Get Started</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" style="margin-top: 2rem;">
        <div class="card" style="background: white !important; border: 2px solid #4CAF50 !important; border-radius: 10px !important; padding: 2rem !important;">
            <h1 style="color: #2E7D32 !important;">GreenSkin Test Page</h1>
            <p style="color: #1B5E20 !important;">This page should show green colors if the skin is working correctly.</p>
            
            <div style="margin-top: 2rem;">
                <button class="btn" style="background-color: #4CAF50 !important; color: white !important; padding: 0.5rem 1rem !important; border-radius: 0.5rem !important; border: none !important;">
                    Green Button
                </button>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background-color: #F1F8E9 !important; border-radius: 0.5rem !important;">
                <h3 style="color: #2E7D32 !important;">Color Test</h3>
                <ul style="color: #1B5E20 !important;">
                    <li>Primary Color: #2E7D32 (Dark Green)</li>
                    <li>Secondary Color: #4CAF50 (Green)</li>
                    <li>Accent Color: #81C784 (Light Green)</li>
                    <li>Background: #F1F8E9 (Very Light Green)</li>
                    <li>Text Color: #1B5E20 (Dark Green)</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        console.log('GreenSkin test page loaded');
        console.log('Active skin: <?php echo $activeSkinName; ?>');
    </script>
</body>
</html> 