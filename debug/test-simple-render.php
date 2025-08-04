<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Simple Render Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get view renderer
    $viewRenderer = $container->get('view');
    
    // Test simple rendering with correct template name
    $data = ['test' => 'Hello World'];
    $content = $viewRenderer->render('pages/home', $data);
    
    echo "📄 Content length: " . strlen($content) . "\n";
    echo "📄 Content snippet (first 200 chars):\n";
    echo substr($content, 0, 200) . "...\n";
    
    // Test with skin rendering
    $contentWithSkin = $viewRenderer->renderWithSkin('pages/home', $data);
    
    echo "📄 Content with skin length: " . strlen($contentWithSkin) . "\n";
    echo "📄 Content with skin snippet (first 200 chars):\n";
    echo substr($contentWithSkin, 0, 200) . "...\n";
    
    // Check if content contains skin elements
    if (strpos($contentWithSkin, 'citizen-header') !== false) {
        echo "✅ Content with skin contains citizen-header\n";
    } else {
        echo "❌ Content with skin does not contain citizen-header\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 