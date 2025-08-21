<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "🔍 Debug Skin Switching Test\n";
echo "============================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');

    echo "✅ Application and SkinManager loaded successfully\n\n";

    // Test current active skin
    echo "🎯 Current Active Skin:\n";
    $activeSkin = $skinManager->getActiveSkin();
    echo "- Active Skin: " . ($activeSkin ? $activeSkin->getName() : 'None') . "\n";
    echo "- Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";

    // Test switching to Muslim skin
    echo "\n🔄 Testing Switch to Muslim Skin:\n";
    $result = $skinManager->setActiveSkin('Muslim');
    echo "- Switch Result: " . ($result ? 'Success' : 'Failed') . "\n";

    if ($result) {
        $newActiveSkin = $skinManager->getActiveSkin();
        $temp_a1e439a4 = ($newActiveSkin ? $newActiveSkin->getName() : 'None') . "\n";
        echo "- New Active Skin: " . $temp_a1e439a4;
        echo "- New Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";

        // Test skin data
        echo "\n📋 Skin Data:\n";
        $temp_55977e6b = strlen($newActiveSkin->getCssContent()) . " characters\n";
        echo "- CSS Content Length: " . $temp_55977e6b;
        $temp_60687774 = strlen($newActiveSkin->getJsContent()) . " characters\n";
        echo "- JS Content Length: " . $temp_60687774;
        echo "- Has CSS: " . ($newActiveSkin->getCssContent() ? 'Yes' : 'No') . "\n";
        echo "- Has JS: " . ($newActiveSkin->getJsContent() ? 'Yes' : 'No') . "\n";
    }

    // Test switching back to Bismillah
    echo "\n🔄 Testing Switch Back to Bismillah:\n";
    $result = $skinManager->setActiveSkin('Bismillah');
    echo "- Switch Result: " . ($result ? 'Success' : 'Failed') . "\n";

    if ($result) {
        $newActiveSkin = $skinManager->getActiveSkin();
        $temp_a1e439a4 = ($newActiveSkin ? $newActiveSkin->getName() : 'None') . "\n";
        echo "- New Active Skin: " . $temp_a1e439a4;
        echo "- New Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
    }

    // Test container bindings
    echo "\n🔧 Container Bindings:\n";
    $containerBindings = [
        'skin.manager' => $container->has('skin.manager'),
        'skin.active' => $container->has('skin.active'),
        'skin.data' => $container->has('skin.data')
    ];

    foreach ($containerBindings as $binding => $exists) {
        echo "- {$binding}: " . ($exists ? 'Bound' : 'Not bound') . "\n";
    }

    // Test skin data in container
    if ($container->has('skin.data')) {
        $skinData = $container->get('skin.data');
        echo "\n📦 Skin Data in Container:\n";
        echo "- Name: " . ($skinData['name'] ?? 'Unknown') . "\n";
        echo "- Version: " . ($skinData['version'] ?? 'Unknown') . "\n";
        echo "- CSS Length: " . strlen($skinData['css'] ?? '') . " characters\n";
        echo "- JS Length: " . strlen($skinData['js'] ?? '') . " characters\n";
    }

    echo "\n✅ Skin switching test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
