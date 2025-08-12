<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing container only...\n";

try {
    echo "1. Creating container...\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "✓ Container created\n";

    echo "2. Testing basic binding...\n";
    $container->bind('test', function () {
        return 'test_value';
    });
    echo "✓ Basic binding works\n";

    echo "3. Testing singleton...\n";
    $container->singleton('singleton_test', function () {
        return new stdClass();
    });
    echo "✓ Singleton works\n";

    echo "4. Testing service provider registration...\n";
    $provider = new \IslamWiki\Providers\LoggingServiceProvider();
    $provider->register($container);
    echo "✓ Service provider registration works\n";

    echo "5. Testing service provider boot...\n";
    $provider->boot($container);
    echo "✓ Service provider boot works\n";

    echo "✓ Container test completed successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Container test completed.\n";
