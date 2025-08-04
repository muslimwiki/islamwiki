<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Twig Paths Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get view renderer
    $viewRenderer = $container->get('view');
    
    // Get the Twig environment
    $twig = $viewRenderer->getTwig();
    $loader = $twig->getLoader();
    
    echo "📄 Twig loader class: " . get_class($loader) . "\n";
    
    // Get the paths from the loader
    if (method_exists($loader, 'getPaths')) {
        $paths = $loader->getPaths();
        echo "📄 Twig loader paths:\n";
        foreach ($paths as $path) {
            echo "  - " . $path . "\n";
        }
    }
    
    // Try to render a simple template
    try {
        $content = $viewRenderer->render('pages/home', ['test' => 'Hello']);
        echo "✅ Template rendered successfully\n";
        echo "📄 Content length: " . strlen($content) . "\n";
    } catch (Exception $e) {
        echo "❌ Template rendering failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 