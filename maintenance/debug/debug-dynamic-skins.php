<?php
declare(strict_types=1);

/**
 * Debug Dynamic Skin Discovery
 * 
 * Tests the dynamic skin discovery functionality in the SettingsController.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Simulate the SettingsController's discoverAvailableSkins method
function discoverAvailableSkins(): array
{
    $skinsDir = __DIR__ . '/../skins';
    $availableSkins = [];
    
    if (!is_dir($skinsDir)) {
        echo "❌ Skins directory not found: $skinsDir\n";
        return $availableSkins;
    }
    
    echo "✅ Skins directory found: $skinsDir\n";
    
    $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
    echo "📁 Found " . count($skinDirs) . " skin directories:\n";
    
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $skinConfigFile = $skinDir . '/skin.json';
        
        echo "  - $skinName: ";
        
        if (file_exists($skinConfigFile)) {
            try {
                $config = json_decode(file_get_contents($skinConfigFile), true);
                
                if ($config && isset($config['name'])) {
                    $availableSkins[strtolower($skinName)] = [
                        'name' => $config['name'],
                        'version' => $config['version'] ?? '0.0.1',
                        'author' => $config['author'] ?? 'Unknown',
                        'description' => $config['description'] ?? '',
                        'directory' => $skinName,
                        'features' => $config['features'] ?? [],
                        'config' => $config['config'] ?? [],
                        'dependencies' => $config['dependencies'] ?? []
                    ];
                    echo "✅ Loaded successfully\n";
                } else {
                    echo "❌ Invalid config (missing name)\n";
                }
            } catch (\Exception $e) {
                echo "❌ Error loading config: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Config file not found\n";
        }
    }
    
    return $availableSkins;
}

echo "🔍 Testing Dynamic Skin Discovery\n";
echo "================================\n\n";

$availableSkins = discoverAvailableSkins();

echo "\n📊 Discovery Results:\n";
echo "====================\n";

if (empty($availableSkins)) {
    echo "❌ No skins discovered\n";
} else {
    echo "✅ Discovered " . count($availableSkins) . " skins:\n\n";
    
    foreach ($availableSkins as $key => $skinData) {
        echo "🎨 {$skinData['name']} (v{$skinData['version']})\n";
        echo "   Author: {$skinData['author']}\n";
        echo "   Description: {$skinData['description']}\n";
        echo "   Directory: {$skinData['directory']}\n";
        
        if (!empty($skinData['features'])) {
            echo "   Features: " . implode(', ', $skinData['features']) . "\n";
        }
        
        if (!empty($skinData['dependencies'])) {
            echo "   Dependencies: " . implode(', ', array_keys($skinData['dependencies'])) . "\n";
        }
        
        echo "\n";
    }
}

echo "✅ Dynamic skin discovery test completed!\n"; 