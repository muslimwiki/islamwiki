<?php
/**
 * Test CSS Loading
 * 
 * Test to debug the CSS loading process.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$skinManager = $container->get('skin.manager');

echo "=== Test CSS Loading ===\n\n";

// Test 1: Check GreenSkin CSS
echo "1. Testing GreenSkin CSS:\n";
$greenSkin = $skinManager->getSkin('GreenSkin');
if ($greenSkin) {
    echo "   ✅ GreenSkin found\n";
    echo "   📋 Skin path: " . $greenSkin->getSkinPath() . "\n";
    echo "   📋 CSS path: " . $greenSkin->getCssPath() . "\n";
    echo "   📋 Has custom CSS: " . ($greenSkin->hasCustomCss() ? 'Yes' : 'No') . "\n";
    
    if ($greenSkin->hasCustomCss()) {
        $cssContent = $greenSkin->getCssContent();
        echo "   📋 CSS content length: " . strlen($cssContent) . " characters\n";
        echo "   📋 CSS starts with: " . substr($cssContent, 0, 100) . "...\n";
        
        // Check if CSS contains green colors
        if (strpos($cssContent, 'green') !== false || strpos($cssContent, '#2E7D32') !== false) {
            echo "   ✅ Green colors found in CSS\n";
        } else {
            echo "   ❌ Green colors not found in CSS\n";
        }
    } else {
        echo "   ❌ No custom CSS found\n";
    }
} else {
    echo "   ❌ GreenSkin not found\n";
}

// Test 2: Check BlueSkin CSS
echo "\n2. Testing BlueSkin CSS:\n";
$blueSkin = $skinManager->getSkin('BlueSkin');
if ($blueSkin) {
    echo "   ✅ BlueSkin found\n";
    echo "   📋 Skin path: " . $blueSkin->getSkinPath() . "\n";
    echo "   📋 CSS path: " . $blueSkin->getCssPath() . "\n";
    echo "   📋 Has custom CSS: " . ($blueSkin->hasCustomCss() ? 'Yes' : 'No') . "\n";
    
    if ($blueSkin->hasCustomCss()) {
        $cssContent = $blueSkin->getCssContent();
        echo "   📋 CSS content length: " . strlen($cssContent) . " characters\n";
        echo "   📋 CSS starts with: " . substr($cssContent, 0, 100) . "...\n";
        
        // Check if CSS contains blue colors
        if (strpos($cssContent, 'blue') !== false || strpos($cssContent, '#1976D2') !== false) {
            echo "   ✅ Blue colors found in CSS\n";
        } else {
            echo "   ❌ Blue colors not found in CSS\n";
        }
    } else {
        echo "   ❌ No custom CSS found\n";
    }
} else {
    echo "   ❌ BlueSkin not found\n";
}

// Test 3: Check user-specific skin
echo "\n3. Testing user-specific skin (user ID 1):\n";
$session = $container->get('session');
$session->login(1, 'testuser');

$userSkin = $skinManager->getActiveSkinForUser(1);
if ($userSkin) {
    echo "   ✅ User skin found: " . $userSkin->getName() . "\n";
    echo "   📋 CSS content length: " . strlen($userSkin->getCssContent()) . " characters\n";
    echo "   📋 CSS starts with: " . substr($userSkin->getCssContent(), 0, 100) . "...\n";
} else {
    echo "   ❌ User skin not found\n";
}

// Test 4: Check middleware CSS
echo "\n4. Testing middleware CSS:\n";
try {
    $request = new \IslamWiki\Core\Http\Request('GET', '/test');
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    $response = $skinMiddleware->handle($request, function($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    
    echo "   📋 Skin CSS length: " . strlen($globals['skin_css'] ?? '') . " characters\n";
    echo "   📋 Skin CSS starts with: " . substr($globals['skin_css'] ?? '', 0, 100) . "...\n";
    
    if (strlen($globals['skin_css'] ?? '') > 0) {
        echo "   ✅ CSS content found in globals\n";
    } else {
        echo "   ❌ No CSS content in globals\n";
    }
    
} catch (\Throwable $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n"; 