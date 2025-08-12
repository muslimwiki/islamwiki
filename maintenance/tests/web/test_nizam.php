<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing NizamApplication-like initialization...\n";

// Step 1: Create container and logger
echo "1. Creating container and logger...\n";
$container = new \IslamWiki\Core\Container\AsasContainer();
$logger = new \IslamWiki\Core\Logging\ShahidLogger(__DIR__ . '/logs');
echo "✓ Container and logger created\n";

// Step 2: Register service providers
echo "2. Registering service providers...\n";
$providers = [
    \IslamWiki\Providers\DatabaseServiceProvider::class,
    \IslamWiki\Providers\LoggingServiceProvider::class,
    \IslamWiki\Providers\SessionServiceProvider::class,
    \IslamWiki\Providers\AuthServiceProvider::class,
    \IslamWiki\Providers\ViewServiceProvider::class,
    \IslamWiki\Providers\ConfigurationServiceProvider::class,
    \IslamWiki\Providers\RihlahServiceProvider::class,
    \IslamWiki\Providers\SabrServiceProvider::class,
    \IslamWiki\Providers\UsulServiceProvider::class,
    \IslamWiki\Providers\SirajServiceProvider::class,
    \IslamWiki\Providers\BayanServiceProvider::class,
    \IslamWiki\Providers\ExtensionServiceProvider::class,
];

foreach ($providers as $provider) {
    if (class_exists($provider)) {
        $providerInstance = new $provider();
        $providerInstance->register($container);
        echo "✓ Registered " . basename(str_replace('\\', '/', $provider)) . "\n";
    }
}

// Step 3: Boot providers
echo "3. Booting providers...\n";
foreach ($providers as $provider) {
    if (class_exists($provider)) {
        $providerInstance = new $provider();
        if (method_exists($providerInstance, 'boot')) {
            $providerInstance->boot($container);
            echo "✓ Booted " . basename(str_replace('\\', '/', $provider)) . "\n";
        }
    }
}

echo "✓ All providers registered and booted successfully\n";

// Step 4: Test system initialization
echo "4. Testing system initialization...\n";
try {
    $connection = new \IslamWiki\Core\Database\Connection([]);
    echo "✓ Connection created\n";

    $session = new \IslamWiki\Core\Session\WisalSession([]);
    echo "✓ Session created\n";

    $auth = new \IslamWiki\Core\Auth\AmanSecurity($session, $connection);
    echo "✓ Auth created\n";

    $cache = new \IslamWiki\Core\Caching\RihlahCaching($container, $logger, $connection);
    echo "✓ Cache created\n";

    $queue = new \IslamWiki\Core\Queue\SabrQueue($container, $logger, $connection);
    echo "✓ Queue created\n";

    $knowledge = new \IslamWiki\Core\Knowledge\UsulKnowledge($container, $logger, $connection);
    echo "✓ Knowledge created\n";

    $search = new \IslamWiki\Core\Search\IqraSearch($connection);
    echo "✓ Search created\n";

    $formatter = new \IslamWiki\Core\Formatter\BayanFormatter($connection, $logger);
    echo "✓ Formatter created\n";

    $api = new \IslamWiki\Core\API\SirajAPI($container, $logger, $session);
    echo "✓ API created\n";

    $config = new \IslamWiki\Core\Configuration\TadbirConfiguration($logger);
    echo "✓ Config created\n";

    echo "✓ All systems initialized successfully\n";
} catch (Exception $e) {
    echo "✗ System initialization failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "NizamApplication-like test completed.\n";
