<?php
/**
 * Test Skin Switching with Safa CSS Framework
 * 
 * This script tests the skin switching functionality to ensure it works
 * correctly with the Safa CSS framework.
 * 
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

// Define the application's base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Autoload file not found. Please run `composer install` to install the project dependencies.');
}

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;
use IslamWiki\Http\Middleware\SkinMiddleware;

// Initialize the application
$app = new Application(BASE_PATH);

echo "🧪 Testing Skin Switching with Safa CSS Framework\n";
echo "================================================\n\n";

try {
    // Get the skin manager
    $skinManager = $app->getContainer()->get('skin.manager');
    
    echo "📋 Available Skins:\n";
    $availableSkins = $skinManager->getSkins();
    foreach ($availableSkins as $name => $skin) {
        echo "   - {$name}: {$skin->getName()} v{$skin->getVersion()}\n";
    }
    echo "\n";
    
    // Test each skin
    foreach ($availableSkins as $skinName => $skin) {
        echo "🎨 Testing Skin: {$skinName}\n";
        echo "   - Name: {$skin->getName()}\n";
        echo "   - Version: {$skin->getVersion()}\n";
        echo "   - Author: {$skin->getAuthor()}\n";
        echo "   - Description: {$skin->getDescription()}\n";
        
        // Test CSS content
        $cssContent = $skin->getCssContent();
        echo "   - CSS Length: " . strlen($cssContent) . " characters\n";
        
        // Check if CSS contains Safa-compatible variables
        if (strpos($cssContent, ':root') !== false && strpos($cssContent, '--primary-color') !== false) {
            echo "   ✅ CSS contains Safa-compatible CSS variables\n";
        } else {
            echo "   ❌ CSS may not be Safa-compatible\n";
        }
        
        // Test JavaScript content
        $jsContent = $skin->getJsContent();
        echo "   - JS Length: " . strlen($jsContent) . " characters\n";
        
        // Check if skin has custom layout
        if ($skin->hasCustomLayout()) {
            echo "   ✅ Has custom layout template\n";
        } else {
            echo "   ℹ️  Uses default layout\n";
        }
        
        echo "\n";
    }
    
    // Test skin switching
    echo "🔄 Testing Skin Switching:\n";
    
    // Test setting active skin
    foreach (array_keys($availableSkins) as $skinName) {
        $result = $skinManager->setActiveSkin($skinName);
        $activeSkin = $skinManager->getActiveSkin();
        
        if ($result && $activeSkin) {
            echo "   ✅ Successfully switched to {$skinName}\n";
            echo "      Active skin: {$activeSkin->getName()}\n";
        } else {
            echo "   ❌ Failed to switch to {$skinName}\n";
        }
    }
    
    // Test with user settings (simulate logged-in user)
    echo "\n👤 Testing User-Specific Skin Settings:\n";
    
    // Simulate user ID 1
    $userId = 1;
    $userActiveSkin = $skinManager->getActiveSkinForUser($userId);
    $userActiveSkinName = $skinManager->getActiveSkinNameForUser($userId);
    
    echo "   - User ID: {$userId}\n";
    echo "   - Active Skin: " . ($userActiveSkin ? $userActiveSkin->getName() : 'None') . "\n";
    echo "   - Active Skin Name: {$userActiveSkinName}\n";
    
    // Test skin middleware
    echo "\n🔧 Testing Skin Middleware:\n";
    
    try {
        $skinMiddleware = new SkinMiddleware($app);
        echo "   ✅ SkinMiddleware created successfully\n";
        
        // Create a mock request
        $request = new \IslamWiki\Core\Http\Request('GET', '/test');
        
        // Test middleware handle method
        $response = $skinMiddleware->handle($request, function($req) {
            return new \IslamWiki\Core\Http\Response();
        });
        
        echo "   ✅ SkinMiddleware executed successfully\n";
        
        // Check if skin data was added to view
        $viewRenderer = $app->getContainer()->get('view');
        $globals = $viewRenderer->getTwig()->getGlobals();
        
        echo "   - Skin CSS available: " . (isset($globals['skin_css']) ? 'Yes' : 'No') . "\n";
        echo "   - Skin JS available: " . (isset($globals['skin_js']) ? 'Yes' : 'No') . "\n";
        echo "   - Active skin name: " . ($globals['active_skin'] ?? 'None') . "\n";
        
    } catch (\Throwable $e) {
        echo "   ❌ SkinMiddleware error: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Skin switching test completed successfully!\n";
    
} catch (\Throwable $e) {
    echo "❌ Error during skin switching test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 