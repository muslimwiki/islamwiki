<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing service providers one by one...\n";

$container = new \IslamWiki\Core\Container\AsasContainer();
$logger = new \IslamWiki\Core\Logging\ShahidLogger(__DIR__ . '/logs');

$providers = [
    'DatabaseServiceProvider' => \IslamWiki\Providers\DatabaseServiceProvider::class,
    'LoggingServiceProvider' => \IslamWiki\Providers\LoggingServiceProvider::class,
    'SessionServiceProvider' => \IslamWiki\Providers\SessionServiceProvider::class,
    'AuthServiceProvider' => \IslamWiki\Providers\AuthServiceProvider::class,
    'ViewServiceProvider' => \IslamWiki\Providers\ViewServiceProvider::class,
    'ConfigurationServiceProvider' => \IslamWiki\Providers\ConfigurationServiceProvider::class,
    'RihlahServiceProvider' => \IslamWiki\Providers\RihlahServiceProvider::class,
    'SabrServiceProvider' => \IslamWiki\Providers\SabrServiceProvider::class,
    'UsulServiceProvider' => \IslamWiki\Providers\UsulServiceProvider::class,
    'SirajServiceProvider' => \IslamWiki\Providers\SirajServiceProvider::class,
    'BayanServiceProvider' => \IslamWiki\Providers\BayanServiceProvider::class,
    'ExtensionServiceProvider' => \IslamWiki\Providers\ExtensionServiceProvider::class,
];

foreach ($providers as $name => $providerClass) {
    echo "Testing $name registration...\n";
    try {
        $providerInstance = new $providerClass();
        $providerInstance->register($container);
        echo "✓ $name registered successfully\n";

        echo "Testing $name boot...\n";
        if (method_exists($providerInstance, 'boot')) {
            $providerInstance->boot($container);
            echo "✓ $name booted successfully\n";
        }
    } catch (Exception $e) {
        echo "✗ $name failed: " . $e->getMessage() . "\n";
        break;
    }
}

echo "Provider tests completed.\n";
