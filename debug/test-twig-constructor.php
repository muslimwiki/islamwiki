<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Twig Constructor Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get the base path
    $basePath = $app->basePath();
    $basePath = realpath($basePath);
    $templatePath = $basePath . '/resources/views';
    $skinsPath = dirname($templatePath, 2) . '/skins';
    
    echo "📄 Base path: " . $basePath . "\n";
    echo "📄 Template path: " . $templatePath . "\n";
    echo "📄 Skins path: " . $skinsPath . "\n";
    echo "📄 Template path exists: " . (is_dir($templatePath) ? 'Yes' : 'No') . "\n";
    echo "📄 Skins path exists: " . (is_dir($skinsPath) ? 'Yes' : 'No') . "\n";
    
    // Check if the home template exists
    $homeTemplate = $templatePath . '/pages/home.twig';
    echo "📄 Home template path: " . $homeTemplate . "\n";
    echo "📄 Home template exists: " . (file_exists($homeTemplate) ? 'Yes' : 'No') . "\n";
    
    // Create a TwigRenderer manually to test
    $twigRenderer = new \IslamWiki\Core\View\TwigRenderer($templatePath, false, true);
    
    // Get the Twig environment
    $twig = $twigRenderer->getTwig();
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
    
    // Try to render a template
    try {
        $content = $twigRenderer->render('pages/home', ['test' => 'Hello']);
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