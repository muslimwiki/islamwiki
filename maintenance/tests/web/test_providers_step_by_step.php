<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing service providers step by step with detailed logging...\n";

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

echo "Starting registration phase...\n";
foreach ($providers as $name => $providerClass) {
    echo "Registering $name...\n";
    try {
        $providerInstance = new $providerClass();
        $providerInstance->register($container);
        echo "✓ $name registered\n";
    } catch (Exception $e) {
        echo "✗ $name failed: " . $e->getMessage() . "\n";
        break;
    }
}

echo "Starting boot phase...\n";
foreach ($providers as $name => $providerClass) {
    echo "Booting $name...\n";
    try {
        $providerInstance = new $providerClass();
        if (method_exists($providerInstance, 'boot')) {
            $providerInstance->boot($container);
            echo "✓ $name booted\n";
        } else {
            echo "✓ $name (no boot method)\n";
        }
    } catch (Exception $e) {
        echo "✗ $name failed: " . $e->getMessage() . "\n";
        break;
    }
}

echo "Provider tests completed.\n";
