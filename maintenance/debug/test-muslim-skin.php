<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "=== Test Muslim Skin Layout ===\n\n";

try {
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✅ Application loaded\n";
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    echo "✅ Skin manager loaded\n";
    
    // Test setting Muslim skin
    echo "Setting active skin to Muslim...\n";
    $result = $skinManager->setActiveSkin('Muslim');
    echo "Set active skin result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    // Get active skin
    $activeSkin = $skinManager->getActiveSkin();
    echo "Active skin: " . ($activeSkin ? $activeSkin->getName() : 'null') . "\n";
    
    // Test layout path
    if ($activeSkin) {
        $layoutPath = $activeSkin->getLayoutPath();
        echo "Layout path: " . $layoutPath . "\n";
        echo "Layout directory exists: " . (is_dir(dirname($layoutPath)) ? 'Yes' : 'No') . "\n";
        echo "Layout file exists: " . (file_exists($layoutPath) ? 'Yes' : 'No') . "\n";
        
        // Test CSS content
        $cssContent = $activeSkin->getCssContent();
        echo "CSS content length: " . strlen($cssContent) . " characters\n";
        
        // Test view renderer
        $viewRenderer = $container->get('view');
        echo "✅ View renderer loaded\n";
        
        // Set the skin layout path
        $viewRenderer->setActiveSkinLayoutPath(dirname($layoutPath));
        echo "✅ Set skin layout path: " . dirname($layoutPath) . "\n";
        
        // Test rendering a simple template
        $testData = [
            'title' => 'Test Page',
            'content' => '<h1>Test Content</h1><p>This is a test of the Muslim skin layout.</p>',
            'user' => null,
            'skin_css' => $cssContent,
            'skin_js' => $activeSkin->getJsContent(),
            'skin_name' => $activeSkin->getName(),
            'skin_version' => $activeSkin->getVersion(),
            'skin_config' => $activeSkin->getConfig() ?? [],
            'active_skin' => $activeSkin->getName(),
        ];
        
        // Add globals
        $viewRenderer->addGlobals($testData);
        
        // Test rendering
        echo "Testing template rendering...\n";
        $rendered = $viewRenderer->render('layouts/app.twig', $testData);
        echo "✅ Template rendered successfully\n";
        echo "Rendered content length: " . strlen($rendered) . " characters\n";
        
        // Check if the skin layout was used
        if (strpos($rendered, 'citizen-header') !== false) {
            echo "✅ Muslim skin layout detected in rendered content\n";
        } else {
            echo "❌ Muslim skin layout not detected in rendered content\n";
        }
        
        // Check if CSS is included
        if (strpos($rendered, '--primary-color: #2c5aa0') !== false) {
            echo "✅ Muslim skin CSS detected in rendered content\n";
        } else {
            echo "❌ Muslim skin CSS not detected in rendered content\n";
        }
        
    } else {
        echo "❌ No active skin found\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 