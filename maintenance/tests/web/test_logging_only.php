<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing LoggingServiceProvider only...\n";

try {
    echo "1. Creating container...\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "✓ Container created\n";

    echo "2. Creating LoggingServiceProvider...\n";
    $provider = new \IslamWiki\Providers\LoggingServiceProvider();
    echo "✓ Provider created\n";

    echo "3. Registering LoggingServiceProvider...\n";
    $provider->register($container);
    echo "✓ Provider registered\n";

    echo "4. Testing logger resolution...\n";
    $logger = $container->get('logger');
    echo "✓ Logger resolved: " . get_class($logger) . "\n";

    echo "✓ LoggingServiceProvider test completed successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "LoggingServiceProvider test completed.\n";
