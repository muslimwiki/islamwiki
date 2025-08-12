<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing NizamApplication step by step...\n";

try {
    echo "1. Creating NizamApplication...\n";
    $app = new \IslamWiki\Core\NizamApplication(__DIR__);
    echo "✓ NizamApplication created\n";

    echo "2. Testing bootstrap...\n";
    $app->bootstrap();
    echo "✓ Bootstrap completed\n";

    echo "3. Testing boot...\n";
    $app->boot();
    echo "✓ Boot completed\n";

    echo "4. Testing run...\n";
    $app->run();
    echo "✓ Run completed\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "NizamApplication test completed.\n";
