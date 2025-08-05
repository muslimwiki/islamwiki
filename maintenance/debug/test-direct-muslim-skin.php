<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Direct Muslim Skin Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    echo "✅ SkinManager found\n";
    
    // Force reload all skins
    echo "📄 Reloading all skins...\n";
    $skinManager->reloadAllSkins();
    
    // Force set Muslim as active skin
    echo "📄 Setting Muslim as active skin...\n";
    $result = $skinManager->setActiveSkin('Muslim');
    echo "📄 Set active skin result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    // Get active skin name
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "📄 Active skin name: " . $activeSkinName . "\n";
    
    // Get active skin
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "📄 Active skin object: " . get_class($activeSkin) . "\n";
        echo "📄 Active skin name: " . $activeSkin->getName() . "\n";
        echo "📄 Active skin version: " . $activeSkin->getVersion() . "\n";
        
        // Get CSS content
        $cssContent = $activeSkin->getCssContent();
        echo "📄 CSS content length: " . strlen($cssContent) . "\n";
        
        // Check if CSS contains Muslim skin content
        if (strpos($cssContent, 'citizen-header') !== false) {
            echo "✅ CSS contains citizen-header (Muslim skin)\n";
        } else {
            echo "❌ CSS does not contain citizen-header\n";
        }
        
        if (strpos($cssContent, 'ZAMZAM INTEGRATION FIXES') !== false) {
            echo "✅ CSS contains ZamZam integration fixes\n";
        } else {
            echo "❌ CSS does not contain ZamZam integration fixes\n";
        }
        
    } else {
        echo "❌ No active skin found\n";
    }
    
    // Get all available skins
    $skins = $skinManager->getAvailableSkins();
    echo "📄 Available skins: " . implode(', ', $skins) . "\n";
    
    // Check if Muslim skin exists
    if ($skinManager->hasSkin('Muslim')) {
        echo "✅ Muslim skin is available\n";
    } else {
        echo "❌ Muslim skin not available\n";
    }
    
    // Debug skins
    $debugInfo = $skinManager->debugSkins();
    echo "📄 Debug info:\n";
    echo "  - Loaded skins: " . implode(', ', $debugInfo['loaded_skins']) . "\n";
    echo "  - Valid skins from LocalSettings: " . implode(', ', $debugInfo['valid_skins_from_localsettings']) . "\n";
    echo "  - Active skin: " . $debugInfo['active_skin'] . "\n";
    
    // Test homepage with forced Muslim skin
    echo "\n📄 Testing homepage with forced Muslim skin...\n";
    
    // Create a mock request with Muslim skin
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        'https://local.islam.wiki/',
        [],
        '',
        '1.1'
    );
    
    // Create SkinMiddleware and force Muslim skin
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    
    // Test the middleware
    $response = $skinMiddleware->handle($request, function($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    
    echo "✅ SkinMiddleware executed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 