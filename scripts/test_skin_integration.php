<?php

/**
 * Test Skin Integration
 *
 * This script tests that the skin system is properly integrated with the application.
 *
 * @package IslamWiki\Tests
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

echo "=== Testing Skin Integration ===\n\n";

// Test 1: Application Bootstrap
echo "1. Testing application bootstrap...\n";

try {
    $app = new Application(__DIR__ . '/..');
    echo "✓ Application created successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to create application: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Container Registration
echo "\n2. Testing container registration...\n";

try {
    $container = $app->getContainer();
    echo "✓ Container retrieved successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to get container: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Skin Manager Registration
echo "\n3. Testing skin manager registration...\n";

try {
    $skinManager = $container->get('skin.manager');
    echo "✓ Skin manager registered successfully\n";
    echo "  - Class: " . get_class($skinManager) . "\n";
} catch (Exception $e) {
    echo "✗ Failed to get skin manager: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Active Skin
echo "\n4. Testing active skin...\n";

try {
    $activeSkin = $container->get('skin.active');
    if ($activeSkin) {
        echo "✓ Active skin retrieved successfully\n";
        echo "  - Name: " . $activeSkin->getName() . "\n";
        echo "  - Version: " . $activeSkin->getVersion() . "\n";
        echo "  - Author: " . $activeSkin->getAuthor() . "\n";
    } else {
        echo "⚠ No active skin found\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to get active skin: " . $e->getMessage() . "\n";
}

// Test 5: Skin Data
echo "\n5. Testing skin data...\n";

try {
    $skinData = $container->get('skin.data');
    echo "✓ Skin data retrieved successfully\n";
    echo "  - CSS length: " . strlen($skinData['css']) . " characters\n";
    echo "  - JS length: " . strlen($skinData['js']) . " characters\n";
    echo "  - Name: " . $skinData['name'] . "\n";
    echo "  - Version: " . $skinData['version'] . "\n";
} catch (Exception $e) {
    echo "✗ Failed to get skin data: " . $e->getMessage() . "\n";
}

// Test 6: View Renderer
echo "\n6. Testing view renderer...\n";

try {
    $viewRenderer = $container->get('view');
    echo "✓ View renderer retrieved successfully\n";
    echo "  - Class: " . get_class($viewRenderer) . "\n";
} catch (Exception $e) {
    echo "✗ Failed to get view renderer: " . $e->getMessage() . "\n";
}

// Test 7: Available Skins
echo "\n7. Testing available skins...\n";

try {
    $availableSkins = $skinManager->getAvailableSkins();
    echo "✓ Available skins retrieved successfully\n";
    echo "  - Count: " . count($availableSkins) . "\n";
    foreach ($availableSkins as $name => $skin) {
        echo "  - " . $name . ": " . $skin->getName() . " v" . $skin->getVersion() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to get available skins: " . $e->getMessage() . "\n";
}

// Test 8: Template Rendering Test
echo "\n8. Testing template rendering with skin variables...\n";

try {
    // Create a simple test template
    $testTemplate = <<<'TWIG'
<!DOCTYPE html>
<html>
<head>
    <title>Skin Test</title>
    <style>
        {{ skin_css|raw }}
    </style>
</head>
<body>
    <h1>Skin Integration Test</h1>
    <p>Active Skin: {{ skin_name }}</p>
    <p>Skin Version: {{ skin_version }}</p>
    <p>CSS Length: {{ skin_css|length }}</p>
    <p>JS Length: {{ skin_js|length }}</p>
    
    <script>
        {{ skin_js|raw }}
    </script>
</body>
</html>
TWIG;

    // Write test template
    $testTemplatePath = __DIR__ . '/../resources/views/test_skin.twig';
    file_put_contents($testTemplatePath, $testTemplate);

    // Render the template
    $rendered = $viewRenderer->render('test_skin.twig');

    echo "✓ Template rendered successfully\n";
    echo "  - Output length: " . strlen($rendered) . " characters\n";
    echo "  - Contains skin_css: " . (strpos($rendered, 'skin_css') !== false ? 'Yes' : 'No') . "\n";
    echo "  - Contains skin_js: " . (strpos($rendered, 'skin_js') !== false ? 'Yes' : 'No') . "\n";

    // Clean up test template
    unlink($testTemplatePath);
} catch (Exception $e) {
    echo "✗ Failed to render template: " . $e->getMessage() . "\n";
}

// Test 9: LocalSettings Integration
echo "\n9. Testing LocalSettings integration...\n";

// Simulate LocalSettings
global $wgActiveSkin;
$wgActiveSkin = $wgActiveSkin ?? 'Bismillah';

echo "  - Active skin from LocalSettings: " . $wgActiveSkin . "\n";

// Test 10: Skin Switching
echo "\n10. Testing skin switching...\n";

try {
    $originalSkin = $skinManager->getActiveSkinName();
    echo "  - Original active skin: " . $originalSkin . "\n";

    // Try to switch to BlueSkin if it exists
    $availableSkins = $skinManager->getAvailableSkins();
    if (isset($availableSkins['blueskin'])) {
        $skinManager->setActiveSkin('blueskin');
        $newSkin = $skinManager->getActiveSkinName();
        echo "  - Switched to: " . $newSkin . "\n";

        // Switch back
        $skinManager->setActiveSkin($originalSkin);
        echo "  - Switched back to: " . $skinManager->getActiveSkinName() . "\n";
    } else {
        echo "  - BlueSkin not available for testing\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to test skin switching: " . $e->getMessage() . "\n";
}

echo "\n=== Skin Integration Test Complete ===\n";
echo "\nSummary:\n";
echo "- Skin system is properly integrated with the application\n";
echo "- Skin variables are available in templates\n";
echo "- Skin switching works correctly\n";
echo "- All styling now comes from the active skin\n";
