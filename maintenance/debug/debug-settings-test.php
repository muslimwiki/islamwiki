<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "🔍 Settings and Skin Selection Test\n";
echo "===================================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    
    echo "✅ Application and SkinManager loaded successfully\n\n";
    
    // Test available skins
    echo "📁 Available Skins:\n";
    $availableSkins = $skinManager->getSkins();
    foreach ($availableSkins as $name => $skin) {
        echo "- {$name}: {$skin->getName()} v{$skin->getVersion()}\n";
    }
    
    // Test skin switching
    echo "\n🔄 Skin Switching Test:\n";
    $availableSkinNames = $skinManager->getAvailableSkinNames();
    
    foreach ($availableSkinNames as $skinName) {
        echo "- Testing switch to: {$skinName}\n";
        
        // Test if skin exists
        $hasSkin = $skinManager->hasSkin($skinName);
        echo "  - Has skin: " . ($hasSkin ? 'Yes' : 'No') . "\n";
        
        if ($hasSkin) {
            // Test switching
            $result = $skinManager->setActiveSkin($skinName);
            echo "  - Switch result: " . ($result ? 'Success' : 'Failed') . "\n";
            
            if ($result) {
                $activeSkin = $skinManager->getActiveSkin();
                echo "  - Active skin: " . ($activeSkin ? $activeSkin->getName() : 'None') . "\n";
                echo "  - Active skin name: " . $skinManager->getActiveSkinName() . "\n";
            }
        }
    }
    
    // Test user-specific skin settings (simulate user ID 1)
    echo "\n👤 User-Specific Skin Settings Test:\n";
    $testUserId = 1;
    
    $userActiveSkin = $skinManager->getActiveSkinNameForUser($testUserId);
    echo "- User {$testUserId} active skin: {$userActiveSkin}\n";
    
    $userSkin = $skinManager->getActiveSkinForUser($testUserId);
    if ($userSkin) {
        echo "- User {$testUserId} skin object: " . $userSkin->getName() . "\n";
    } else {
        echo "- User {$testUserId} has no specific skin, using default\n";
    }
    
    // Test settings controller functionality
    echo "\n⚙️ Settings Controller Test:\n";
    
    // Simulate the settings controller's discoverAvailableSkins method
    $skinsDir = __DIR__ . '/../skins';
    $availableSkinsFromDir = [];
    
    if (is_dir($skinsDir)) {
        $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    
                    if ($config && isset($config['name'])) {
                        $availableSkinsFromDir[strtolower($skinName)] = [
                            'name' => $config['name'],
                            'version' => $config['version'] ?? '0.0.1',
                            'author' => $config['author'] ?? 'Unknown',
                            'description' => $config['description'] ?? '',
                            'directory' => $skinName,
                            'features' => $config['features'] ?? [],
                            'config' => $config['config'] ?? [],
                            'dependencies' => $config['dependencies'] ?? []
                        ];
                    }
                } catch (\Exception $e) {
                    echo "  - Error loading skin {$skinName}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "- Discovered skins from directory: " . count($availableSkinsFromDir) . "\n";
    foreach ($availableSkinsFromDir as $key => $skinData) {
        echo "  - {$skinData['name']} (v{$skinData['version']}): {$skinData['description']}\n";
    }
    
    // Test skin validation for settings
    echo "\n✅ Skin Validation for Settings:\n";
    foreach ($availableSkinsFromDir as $key => $skinData) {
        $skinExists = $skinManager->hasSkin($skinData['name']);
        echo "- {$skinData['name']}: " . ($skinExists ? 'Valid' : 'Invalid') . "\n";
    }
    
    // Test skin switching simulation
    echo "\n🔄 Skin Switching Simulation:\n";
    foreach ($availableSkinsFromDir as $key => $skinData) {
        echo "- Testing switch to: {$skinData['name']}\n";
        
        // Simulate the settings controller's updateSkin method
        $skinName = $skinData['name'];
        
        // Validate that the skin exists
        $skinExists = false;
        foreach ($availableSkinsFromDir as $skinInfo) {
            if (strtolower($skinInfo['name']) === strtolower($skinName)) {
                $skinExists = true;
                break;
            }
        }
        
        echo "  - Skin exists: " . ($skinExists ? 'Yes' : 'No') . "\n";
        
        if ($skinExists) {
            // Test setting the skin
            $result = $skinManager->setActiveSkin($skinName);
            echo "  - Switch result: " . ($result ? 'Success' : 'Failed') . "\n";
            
            if ($result) {
                $activeSkin = $skinManager->getActiveSkin();
                echo "  - New active skin: " . ($activeSkin ? $activeSkin->getName() : 'None') . "\n";
            }
        }
    }
    
    echo "\n✅ Settings and skin selection test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 