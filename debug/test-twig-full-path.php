<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Twig Full Path Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get view renderer
    $viewRenderer = $container->get('view');
    
    // Try to render with different template names
    $templates = [
        'pages/home',
        'pages/home.twig',
        'home',
        'home.twig',
        'index',
        'index.twig'
    ];
    
    foreach ($templates as $template) {
        try {
            echo "📄 Trying template: " . $template . "\n";
            $content = $viewRenderer->render($template, ['test' => 'Hello']);
            echo "✅ Template rendered successfully\n";
            echo "📄 Content length: " . strlen($content) . "\n";
            break;
        } catch (Exception $e) {
            echo "❌ Template rendering failed: " . $e->getMessage() . "\n";
        }
    }
    
    // Try to list available templates
    echo "\n📄 Checking available templates:\n";
    $templateDir = '/var/www/html/local.islam.wiki/resources/views';
    $files = glob($templateDir . '/**/*.twig', GLOB_BRACE);
    foreach (array_slice($files, 0, 10) as $file) {
        $relativePath = str_replace($templateDir . '/', '', $file);
        $relativePath = str_replace('.twig', '', $relativePath);
        echo "  - " . $relativePath . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 