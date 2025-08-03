<?php
declare(strict_types=1);

/**
 * Debug Settings Direct Test
 * 
 * Tests the SettingsController directly with the correct template name.
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

echo "🔍 Testing SettingsController Directly\n";
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

// Test 1: Test template rendering directly
echo "\n📊 Test 1: Direct Template Rendering\n";
echo "=====================================\n";

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
    
    $result = $view->render('settings/index.twig', $templateData);
    
    echo "✅ Settings template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";
    
    if (strpos($result, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
    
    if (strpos($result, 'Bismillah') !== false) {
        echo "✅ Response contains Bismillah skin\n";
    } else {
        echo "❌ Response does not contain Bismillah skin\n";
    }
    
    if (strpos($result, 'Muslim') !== false) {
        echo "✅ Response contains Muslim skin\n";
    } else {
        echo "❌ Response does not contain Muslim skin\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Direct template error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 2: Test SettingsController with reflection
echo "\n📊 Test 2: SettingsController with Reflection\n";
echo "=============================================\n";

try {
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private index method
    $reflection = new ReflectionClass($settingsController);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    echo "✅ SettingsController created\n";
    echo "✅ Method made accessible\n";
    
    echo "🔄 Invoking index method...\n";
    $response = $method->invoke($settingsController);
    
    echo "✅ SettingsController executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    // Get the response body - handle Stream object properly
    $body = $response->getBody();
    if (is_object($body) && method_exists($body, 'getContents')) {
        $bodyContent = $body->getContents();
    } else {
        $bodyContent = (string) $body;
    }
    
    echo "Response length: " . strlen($bodyContent) . " characters\n";
    
    // Check for specific content
    if (strpos($bodyContent, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
    
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
    
    // Show a snippet of the response
    echo "\n📋 Response Preview (first 500 chars):\n";
    echo "=====================================\n";
    echo substr($bodyContent, 0, 500) . "...\n";
    
} catch (\Exception $e) {
    echo "❌ SettingsController error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Settings direct test completed!\n"; 