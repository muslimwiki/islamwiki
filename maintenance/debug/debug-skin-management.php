<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "🔍 Debug Skin Management System\n";
echo "===============================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    
    echo "✅ Application and SkinManager loaded successfully\n";
    echo "✅ SkinManager class: " . get_class($skinManager) . "\n\n";
    
    // Test skin discovery
    echo "📁 Skin Discovery:\n";
    $availableSkins = $skinManager->getSkins();
    echo "- Total skins loaded: " . count($availableSkins) . "\n";
    
    foreach ($availableSkins as $name => $skin) {
        echo "- Skin: {$name}\n";
        echo "  - Name: " . $skin->getName() . "\n";
        echo "  - Version: " . $skin->getVersion() . "\n";
        echo "  - Author: " . $skin->getAuthor() . "\n";
        echo "  - Description: " . $skin->getDescription() . "\n";
        echo "  - Has CSS: " . ($skin->getCssContent() ? 'Yes' : 'No') . "\n";
        echo "  - Has JS: " . ($skin->getJsContent() ? 'Yes' : 'No') . "\n";
        echo "  - Has Layout: " . ($skin->hasCustomLayout() ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n🎯 Active Skin:\n";
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "- Active Skin: " . $activeSkin->getName() . "\n";
        echo "- Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
    } else {
        echo "- No active skin found\n";
    }
    
    // Test skin switching
    echo "\n🔄 Skin Switching Test:\n";
    $availableSkinNames = $skinManager->getAvailableSkinNames();
    foreach ($availableSkinNames as $skinName) {
        echo "- Testing switch to: {$skinName}\n";
        $result = $skinManager->setActiveSkin($skinName);
        echo "  - Result: " . ($result ? 'Success' : 'Failed') . "\n";
        
        if ($result) {
            $newActiveSkin = $skinManager->getActiveSkin();
            echo "  - New active skin: " . ($newActiveSkin ? $newActiveSkin->getName() : 'None') . "\n";
        }
    }
    
    // Test user-specific skin settings
    echo "\n👤 User-Specific Skin Settings:\n";
    $testUserId = 1; // Test with user ID 1
    
    $userActiveSkin = $skinManager->getActiveSkinNameForUser($testUserId);
    echo "- User {$testUserId} active skin: {$userActiveSkin}\n";
    
    $userSkin = $skinManager->getActiveSkinForUser($testUserId);
    if ($userSkin) {
        echo "- User {$testUserId} skin object: " . $userSkin->getName() . "\n";
    } else {
        echo "- User {$testUserId} has no specific skin, using default\n";
    }
    
    // Test skin validation
    echo "\n✅ Skin Validation:\n";
    foreach ($availableSkinNames as $skinName) {
        $hasSkin = $skinManager->hasSkin($skinName);
        echo "- Skin '{$skinName}': " . ($hasSkin ? 'Valid' : 'Invalid') . "\n";
    }
    
    // Test skin metadata
    echo "\n📋 Skin Metadata:\n";
    $allMetadata = $skinManager->getAllSkinMetadata();
    foreach ($allMetadata as $name => $metadata) {
        echo "- {$name}:\n";
        echo "  - Version: " . ($metadata['version'] ?? 'Unknown') . "\n";
        echo "  - Author: " . ($metadata['author'] ?? 'Unknown') . "\n";
        echo "  - Description: " . ($metadata['description'] ?? 'No description') . "\n";
    }
    
    // Test debug information
    echo "\n🐛 Debug Information:\n";
    $debugInfo = $skinManager->debugSkins();
    echo "- Loaded skins: " . implode(', ', $debugInfo['loaded_skins']) . "\n";
    echo "- Valid skins from LocalSettings: " . implode(', ', array_keys($debugInfo['valid_skins_from_localsettings'])) . "\n";
    echo "- Active skin: " . $debugInfo['active_skin'] . "\n";
    
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
        echo "- Skin data in container:\n";
        echo "  - Name: " . ($skinData['name'] ?? 'Unknown') . "\n";
        echo "  - Version: " . ($skinData['version'] ?? 'Unknown') . "\n";
        echo "  - Has CSS: " . (!empty($skinData['css']) ? 'Yes' : 'No') . "\n";
        echo "  - Has JS: " . (!empty($skinData['js']) ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n✅ Skin management debugging completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 