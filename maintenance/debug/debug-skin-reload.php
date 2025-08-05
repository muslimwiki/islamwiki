<?php
declare(strict_types=1);

/**
 * Debug Skin Reload
 * 
 * Tests forcing a reload of the skins to see if that helps load the Muslim skin.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Skin Reload\n";
echo "=====================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();

echo "✅ Application initialized\n";

// Test 1: Check initial skin loading
echo "\n📊 Test 1: Initial Skin Loading\n";
echo "===============================\n";

try {
    $skinManager = $container->get('skin.manager');
    $loadedSkins = $skinManager->getSkins();
    
    echo "✅ SkinManager loaded\n";
    echo "Initial loaded skins: " . count($loadedSkins) . "\n";
    
    foreach ($loadedSkins as $key => $skin) {
        echo "  - $key: {$skin->getName()} (v{$skin->getVersion()})\n";
    }
    
} catch (\Exception $e) {
    echo "❌ SkinManager error: " . $e->getMessage() . "\n";
}

// Test 2: Force reload all skins
echo "\n📊 Test 2: Force Reload All Skins\n";
echo "==================================\n";

try {
    echo "🔄 Calling reloadAllSkins()...\n";
    $skinManager->reloadAllSkins();
    echo "✅ reloadAllSkins() completed\n";
    
    $reloadedSkins = $skinManager->getSkins();
    echo "Reloaded skins: " . count($reloadedSkins) . "\n";
    
    foreach ($reloadedSkins as $key => $skin) {
        echo "  - $key: {$skin->getName()} (v{$skin->getVersion()})\n";
    }
    
    // Check if Muslim skin is now loaded
    if (isset($reloadedSkins['muslim'])) {
        echo "✅ Muslim skin is now loaded!\n";
    } else {
        echo "❌ Muslim skin is still NOT loaded\n";
    }
    
    if (isset($reloadedSkins['Muslim'])) {
        echo "✅ Muslim skin (capitalized) is now loaded!\n";
    } else {
        echo "❌ Muslim skin (capitalized) is still NOT loaded\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Reload error: " . $e->getMessage() . "\n";
}

// Test 3: Check available skin names
echo "\n📊 Test 3: Available Skin Names\n";
echo "===============================\n";

try {
    $availableSkinNames = $skinManager->getAvailableSkinNames();
    echo "Available skin names: " . count($availableSkinNames) . "\n";
    
    foreach ($availableSkinNames as $skinName) {
        $hasSkin = $skinManager->hasSkin($skinName);
        $status = $hasSkin ? "✅" : "❌";
        echo "  $status $skinName\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Available skin names error: " . $e->getMessage() . "\n";
}

// Test 4: Test settings page with reloaded skins
echo "\n📊 Test 4: Settings Page Test\n";
echo "=============================\n";

try {
    $db = $container->get('db');
    $session = $container->get('session');
    
    // Simulate login
    $session->login(1, 'admin', true);
    
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private index method
    $reflection = new ReflectionClass($settingsController);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    $response = $method->invoke($settingsController);
    
    echo "✅ SettingsController executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    // Get the response body
    $body = $response->getBody();
    if (is_object($body) && method_exists($body, 'getContents')) {
        $bodyContent = $body->getContents();
    } else {
        $bodyContent = (string) $body;
    }
    
    echo "Response length: " . strlen($bodyContent) . " characters\n";
    
    // Count skin cards
    $skinCardCount = substr_count($bodyContent, 'skin-card');
    echo "Skin card count: $skinCardCount\n";
    
    if (strpos($bodyContent, 'Bismillah') !== false) {
        echo "✅ Response contains Bismillah skin\n";
    } else {
        echo "❌ Response does not contain Bismillah skin\n";
    }
    
    if (strpos($bodyContent, 'Muslim') !== false) {
        echo "✅ Response contains Muslim skin\n";
    } else {
        echo "❌ Response does not contain Muslim skin\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Settings page test error: " . $e->getMessage() . "\n";
}

echo "\n✅ Skin reload test completed!\n"; 