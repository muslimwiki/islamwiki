<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing NizamApplication bootstrap mimic...\n";

try {
    echo "1. Creating container...\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "✓ Container created\n";

    echo "2. Setting up database connection...\n";
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $db = new \IslamWiki\Core\Database\Connection($dbConfig);
    $container->instance('db', $db);
    $container->instance(\IslamWiki\Core\Database\Connection::class, $db);
    echo "✓ Database connection bound\n";

    echo "3. Setting up logger...\n";
    $logDir = __DIR__ . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logger = new \IslamWiki\Core\Logging\ShahidLogger($logDir);
    $container->instance('logger', $logger);
    $container->instance(\IslamWiki\Core\Logging\ShahidLogger::class, $logger);
    echo "✓ Logger bound\n";

    echo "4. Registering service providers...\n";
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

    $providerInstances = [];
    foreach ($providers as $provider) {
        if (class_exists($provider)) {
            try {
                echo "  - Registering " . basename(str_replace('\\', '/', $provider)) . "...\n";
                $providerInstance = new $provider();
                $providerInstance->register($container);
                $providerInstances[] = $providerInstance;
                echo "  ✓ Registered\n";
            } catch (\Exception $e) {
                echo "  ✗ Failed: " . $e->getMessage() . "\n";
                break;
            }
        }
    }

    echo "✓ All providers registered\n";

    echo "5. Booting service providers...\n";
    foreach ($providerInstances as $providerInstance) {
        try {
            echo "  - Booting " . get_class($providerInstance) . "...\n";
            if (method_exists($providerInstance, 'boot')) {
                $providerInstance->boot($container);
            }
            echo "  ✓ Booted\n";
        } catch (\Exception $e) {
            echo "  ✗ Failed: " . $e->getMessage() . "\n";
            break;
        }
    }

    echo "✓ All providers booted\n";
    echo "✓ NizamApplication bootstrap mimic completed successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "NizamApplication bootstrap mimic test completed.\n";
