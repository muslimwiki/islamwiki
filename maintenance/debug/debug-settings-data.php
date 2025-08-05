<?php
declare(strict_types=1);

/**
 * Debug Settings Data
 * 
 * Tests what data is being passed to the settings template.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Settings Data\n";
echo "=======================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";

// Simulate login
$session->login(1, 'admin', true);
echo "✅ Login simulation completed\n";

// Test the SettingsController data generation
echo "\n📊 Testing SettingsController Data Generation\n";
echo "============================================\n";

try {
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private discoverAvailableSkins method
    $reflection = new ReflectionClass($settingsController);
    $discoverMethod = $reflection->getMethod('discoverAvailableSkins');
    $discoverMethod->setAccessible(true);
    
    echo "✅ SettingsController created\n";
    
    // Test skin discovery
    echo "\n🔄 Testing skin discovery...\n";
    $availableSkins = $discoverMethod->invoke($settingsController);
    
    echo "✅ Skin discovery completed\n";
    echo "Available skins: " . count($availableSkins) . "\n";
    
    foreach ($availableSkins as $key => $skinData) {
        echo "  - {$skinData['name']} (v{$skinData['version']}) by {$skinData['author']}\n";
        echo "    Directory: {$skinData['directory']}\n";
        echo "    Features: " . implode(', ', $skinData['features']) . "\n";
    }
    
    // Test skin manager
    echo "\n🔄 Testing skin manager...\n";
    $skinManager = $container->get('skin.manager');
    $loadedSkins = $skinManager->getSkins();
    
    echo "✅ Skin manager loaded\n";
    echo "Loaded skins: " . count($loadedSkins) . "\n";
    
    foreach ($loadedSkins as $key => $skin) {
        echo "  - $key: {$skin->getName()} (v{$skin->getVersion()})\n";
    }
    
    // Test skin options generation
    echo "\n🔄 Testing skin options generation...\n";
    
    $skinOptions = [];
    foreach ($availableSkins as $skinKey => $skinData) {
        $lowerSkinName = strtolower($skinData['name']);
        
        if (isset($loadedSkins[$lowerSkinName])) {
            $skin = $loadedSkins[$lowerSkinName];
            
            $isActive = $lowerSkinName === 'bismillah'; // Default to bismillah
            
            $skinOptions[$skinData['name']] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => $isActive,
                'css_key' => $lowerSkinName,
                'directory' => $skinData['directory'],
                'features' => $skinData['features'] ?? [],
                'config' => $skinData['config'] ?? []
            ];
        }
    }
    
    echo "✅ Skin options generated\n";
    echo "Skin options: " . count($skinOptions) . "\n";
    
    foreach ($skinOptions as $skinName => $skinData) {
        echo "  - {$skinData['name']} (v{$skinData['version']}) by {$skinData['author']}\n";
        echo "    Active: " . ($skinData['active'] ? 'Yes' : 'No') . "\n";
        echo "    Features: " . implode(', ', $skinData['features']) . "\n";
    }
    
    // Test template data
    echo "\n🔄 Testing template data...\n";
    
    $templateData = [
        'title' => 'Settings - IslamWiki',
        'user' => null,
        'skinOptions' => $skinOptions,
        'activeSkin' => 'bismillah',
        'availableSkins' => $availableSkins,
        'userSettings' => []
    ];
    
    echo "✅ Template data generated\n";
    echo "Template data keys: " . implode(', ', array_keys($templateData)) . "\n";
    echo "Skin options count: " . count($templateData['skinOptions']) . "\n";
    echo "Available skins count: " . count($templateData['availableSkins']) . "\n";
    
    // Test template rendering with this data
    echo "\n🔄 Testing template rendering...\n";
    
    $view = $container->get('view');
    $result = $view->render('settings/index.twig', $templateData);
    
    echo "✅ Template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    // Check for skin cards in the result
    if (strpos($result, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
        
        // Count skin cards
        $skinCardCount = substr_count($result, 'skin-card');
        echo "Skin card count: $skinCardCount\n";
        
        // Show the skin grid section
        $skinGridStart = strpos($result, '<div class="skin-grid">');
        if ($skinGridStart !== false) {
            $skinGridEnd = strpos($result, '</div>', $skinGridStart);
            if ($skinGridEnd !== false) {
                $skinGridSection = substr($result, $skinGridStart, $skinGridEnd - $skinGridStart + 6);
                echo "\n📋 Skin Grid Section:\n";
                echo "====================\n";
                echo $skinGridSection . "\n";
            }
        }
    } else {
        echo "❌ Response does not contain skin cards\n";
        
        // Show what's around the skin-grid div
        $skinGridPos = strpos($result, 'skin-grid');
        if ($skinGridPos !== false) {
            $context = substr($result, max(0, $skinGridPos - 200), 400);
            echo "\n📋 Context around skin-grid:\n";
            echo "===========================\n";
            echo $context . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Settings data error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Settings data test completed!\n"; 