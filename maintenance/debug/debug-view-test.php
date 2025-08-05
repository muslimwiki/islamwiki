<?php
declare(strict_types=1);

/**
 * Debug View Test
 * 
 * Tests the view rendering system to identify the 500 error.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing View Rendering System\n";
echo "================================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";

// Simulate login
$session->login(1, 'admin', true);
echo "✅ Login simulation completed\n";

// Test 1: Check if view system is available
echo "\n📊 Test 1: View System Check\n";
echo "=============================\n";
try {
    $view = $container->get('view');
    echo "✅ View system: " . get_class($view) . "\n";
} catch (\Exception $e) {
    echo "❌ View system error: " . $e->getMessage() . "\n";
}

// Test 2: Check if template file exists
echo "\n📊 Test 2: Template File Check\n";
echo "===============================\n";
$templatePath = __DIR__ . '/../resources/views/settings/index.twig';
echo "Template path: $templatePath\n";
echo "Template exists: " . (file_exists($templatePath) ? 'Yes' : 'No') . "\n";

if (file_exists($templatePath)) {
    echo "Template size: " . filesize($templatePath) . " bytes\n";
    echo "Template readable: " . (is_readable($templatePath) ? 'Yes' : 'No') . "\n";
}

// Test 3: Test simple view rendering
echo "\n📊 Test 3: Simple View Test\n";
echo "===========================\n";
try {
    $view = $container->get('view');
    
    // Test with a simple template
    $simpleTemplate = '<!DOCTYPE html><html><head><title>{{ title }}</title></head><body><h1>{{ title }}</h1></body></html>';
    $tempFile = tempnam(sys_get_temp_dir(), 'test_template_') . '.twig';
    file_put_contents($tempFile, $simpleTemplate);
    
    echo "✅ Simple template created: $tempFile\n";
    
    // Test rendering
    $result = $view->render('test_template', ['title' => 'Test Page']);
    echo "✅ Simple view rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    // Clean up
    unlink($tempFile);
    
} catch (\Exception $e) {
    echo "❌ Simple view error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 4: Test settings template data
echo "\n📊 Test 4: Settings Template Data\n";
echo "==================================\n";
try {
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
    echo "  - Title: {$templateData['title']}\n";
    echo "  - Skin options: " . count($templateData['skinOptions']) . "\n";
    echo "  - Available skins: " . count($templateData['availableSkins']) . "\n";
    echo "  - Active skin: {$templateData['activeSkin']}\n";
    
    // Test rendering with actual template
    $view = $container->get('view');
    $result = $view->render('settings/index', $templateData);
    
    echo "✅ Settings template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    // Check if it contains expected content
    if (strpos($result, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Settings template error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ View test completed!\n"; 