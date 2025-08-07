<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Test Homepage with Muslim Skin ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager and force Muslim skin
    $skinManager = $container->get('skin.manager');
    $skinManager->setActiveSkin('Muslim');

    // Get view renderer
    $viewRenderer = $container->get('view');

    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        'https://local.islam.wiki/',
        [],
        '',
        '1.1'
    );

    // Create HomeController
    $db = $container->get('db');
    $homeController = new \IslamWiki\Http\Controllers\HomeController($db, $container);

    // Call the index method
    $response = $homeController->index($request);

    // Get the response content
    $content = $response->getBody()->getContents();

    // Check if the content contains Muslim skin elements
    echo "📄 Response status: " . $response->getStatusCode() . "\n";
    echo "📄 Content length: " . strlen($content) . "\n";

    if (strpos($content, 'citizen-header') !== false) {
        echo "✅ Content contains citizen-header (Muslim skin)\n";
    } else {
        echo "❌ Content does not contain citizen-header\n";
    }

    if (strpos($content, 'Bismillah Skin') !== false) {
        echo "❌ Content still contains Bismillah skin\n";
    } else {
        echo "✅ Content does not contain Bismillah skin\n";
    }

    if (strpos($content, 'z-data') !== false) {
        echo "✅ Content contains ZamZam directives\n";
    } else {
        echo "❌ Content does not contain ZamZam directives\n";
    }

    // Show a snippet of the content
    echo "\n📄 Content snippet (first 500 chars):\n";
    echo substr($content, 0, 500) . "...\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
