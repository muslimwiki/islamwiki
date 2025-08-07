<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== HomeController Simple Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get view renderer
    $viewRenderer = $container->get('view');

    // Test direct template rendering
    echo "📄 Testing direct template rendering...\n";
    try {
        $content = $viewRenderer->render('pages/home.twig', ['test' => 'Hello']);
        echo "✅ Direct template rendering successful\n";
        echo "📄 Content length: " . strlen($content) . "\n";
        echo "📄 Content snippet (first 200 chars):\n";
        echo substr($content, 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "❌ Direct template rendering failed: " . $e->getMessage() . "\n";
    }

    // Test with skin rendering
    echo "\n📄 Testing with skin rendering...\n";
    try {
        $content = $viewRenderer->renderWithSkin('pages/home.twig', ['test' => 'Hello']);
        echo "✅ Skin template rendering successful\n";
        echo "📄 Content length: " . strlen($content) . "\n";
        echo "📄 Content snippet (first 200 chars):\n";
        echo substr($content, 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "❌ Skin template rendering failed: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
