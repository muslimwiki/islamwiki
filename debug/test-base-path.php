<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Base Path Test ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "📄 App base path: " . $app->basePath() . "\n";
    echo "📄 Current working directory: " . getcwd() . "\n";
    echo "📄 __DIR__: " . __DIR__ . "\n";
    
    // Check if the template path exists
    $templatePath = $app->basePath() . '/resources/views';
    echo "📄 Template path: " . $templatePath . "\n";
    echo "📄 Template path exists: " . (is_dir($templatePath) ? 'Yes' : 'No') . "\n";
    
    // Check if the home template exists
    $homeTemplate = $templatePath . '/pages/home.twig';
    echo "📄 Home template path: " . $homeTemplate . "\n";
    echo "📄 Home template exists: " . (file_exists($homeTemplate) ? 'Yes' : 'No') . "\n";
    
    // List some files in the template directory
    if (is_dir($templatePath)) {
        echo "📄 Files in template directory:\n";
        $files = glob($templatePath . '/*.twig');
        foreach (array_slice($files, 0, 5) as $file) {
            echo "  - " . basename($file) . "\n";
        }
        
        // Check pages directory
        $pagesDir = $templatePath . '/pages';
        if (is_dir($pagesDir)) {
            echo "📄 Files in pages directory:\n";
            $pageFiles = glob($pagesDir . '/*.twig');
            foreach ($pageFiles as $file) {
                echo "  - " . basename($file) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 