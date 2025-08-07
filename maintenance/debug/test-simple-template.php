<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Simple Template Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get view renderer
    $viewRenderer = $container->get('view');

    // Try to render a simple template that we know exists
    try {
        $content = $viewRenderer->render('search/index', ['test' => 'Hello']);
        echo "✅ Template rendered successfully\n";
        echo "📄 Content length: " . strlen($content) . "\n";
        echo "📄 Content snippet (first 200 chars):\n";
        echo substr($content, 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "❌ Template rendering failed: " . $e->getMessage() . "\n";
    }

    // Try to render the home template
    try {
        $content = $viewRenderer->render('pages/home', ['test' => 'Hello']);
        echo "✅ Home template rendered successfully\n";
        echo "📄 Content length: " . strlen($content) . "\n";
        echo "📄 Content snippet (first 200 chars):\n";
        echo substr($content, 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "❌ Home template rendering failed: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
