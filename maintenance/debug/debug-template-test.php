<?php
declare(strict_types=1);

/**
 * Debug Template Test
 * 
 * Tests the template rendering process specifically.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Template Rendering Process\n";
echo "=====================================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";

// Simulate login
$session->login(1, 'admin', true);
echo "✅ Login simulation completed\n";

// Test 1: Check if view is available
echo "\n📊 Test 1: View System Check\n";
echo "=============================\n";
try {
    $view = $container->get('view');
    echo "✅ View system: " . get_class($view) . "\n";
} catch (\Exception $e) {
    echo "❌ View system error: " . $e->getMessage() . "\n";
}

// Test 2: Test simple template rendering
echo "\n📊 Test 2: Simple Template Test\n";
echo "===============================\n";
try {
    $view = $container->get('view');
    
    // Test with a very simple template
    $simpleData = ['title' => 'Test Page'];
    $result = $view->render('settings/index', $simpleData);
    
    echo "✅ Simple template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    if (strlen($result) > 0) {
        echo "✅ Template rendering works\n";
        echo "First 200 chars: " . substr($result, 0, 200) . "...\n";
    } else {
        echo "❌ Template rendered but result is empty\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Template rendering error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 3: Test with actual settings data
echo "\n📊 Test 3: Settings Template with Data\n";
echo "=======================================\n";
try {
    $view = $container->get('view');
    
    // Generate the same data that SettingsController uses
    $availableSkins = [];
    $skinsDir = __DIR__ . '/../skins';
    
    if (is_dir($skinsDir)) {
        $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
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
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
        }
    }
    
    // Simulate skin manager
    $loadedSkins = [
        'bismillah' => new class {
            public function getName(): string { return 'Bismillah'; }
            public function getVersion(): string { return '0.0.28'; }
            public function getAuthor(): string { return 'IslamWiki Team'; }
            public function getDescription(): string { return 'The default skin for IslamWiki with modern Islamic design and beautiful gradients.'; }
        },
        'muslim' => new class {
            public function getName(): string { return 'Muslim'; }
            public function getVersion(): string { return '0.0.1'; }
            public function getAuthor(): string { return 'IslamWiki Team'; }
            public function getDescription(): string { return 'A beautiful, usable, responsive skin inspired by Citizen MediaWiki skin with Islamic design elements.'; }
        }
    ];
    
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
    
    $templateData = [
        'title' => 'Settings - IslamWiki',
        'user' => null,
        'skinOptions' => $skinOptions,
        'activeSkin' => 'bismillah',
        'availableSkins' => $availableSkins,
        'userSettings' => []
    ];
    
    echo "✅ Template data generated:\n";
    echo "  - Skin options: " . count($templateData['skinOptions']) . "\n";
    echo "  - Available skins: " . count($templateData['availableSkins']) . "\n";
    
    $result = $view->render('settings/index', $templateData);
    
    echo "✅ Settings template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    if (strlen($result) > 0) {
        echo "✅ Settings template rendering works\n";
        if (strpos($result, 'skin-card') !== false) {
            echo "✅ Response contains skin cards\n";
        } else {
            echo "❌ Response does not contain skin cards\n";
        }
    } else {
        echo "❌ Settings template rendered but result is empty\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Settings template error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Template test completed!\n"; 