<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing minimal NizamApplication creation...\n";

try {
    echo "1. Creating NizamApplication...\n";
    $app = new \IslamWiki\Core\NizamApplication(__DIR__);
    echo "✓ NizamApplication created successfully\n";

    echo "2. Testing basic properties...\n";
    echo "   - Container: " . (null !== $app->getContainer() ? 'OK' : 'FAIL') . "\n";
    echo "   - Logger: " . (null !== $app->getLogger() ? 'OK' : 'FAIL') . "\n";
    echo "   - Router: " . (null !== $app->getSabilRouter() ? 'OK' : 'FAIL') . "\n";

    echo "✓ Basic properties checked\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Minimal NizamApplication test completed.\n";
