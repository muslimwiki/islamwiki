<?php

// Test index.php functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing index.php functionality...\n";

// Test autoloader
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoloader working\n";
} catch (Exception $e) {
    echo "✗ Autoloader failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test LocalSettings
try {
    require_once __DIR__ . '/../LocalSettings.php';
    echo "✓ LocalSettings loaded\n";
} catch (Exception $e) {
    echo "✗ LocalSettings failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test application creation
try {
    $app = new \IslamWiki\Core\Application . '/..');
    echo "✓ Application created\n";

    $container = $app->getContainer();
    echo "✓ Container created\n";

    $router = new \IslamWiki\Core\Routing\SabilRouting($container);
    echo "✓ Router created\n";

    // Make router global
    global $router;

    // Load routes
    echo "✓ About to load routes...\n";
    require_once __DIR__ . '/../routes/web.php';
    echo "✓ Routes loaded\n";

    echo "✓ Index.php functionality test completed!\n";
} catch (Exception $e) {
    echo "✗ Index.php functionality test failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
