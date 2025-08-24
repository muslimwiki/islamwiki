<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Container;\Container
use IslamWiki\Providers\LoggingServiceProvider;
use Psr\Log\LoggerInterface;

echo "Testing container logger binding...\n";

try {
    // Create container
    $container = new ContainerContainer();

    // Bind settings (needed by LoggingServiceProvider)
    $container->singleton('settings', function () {
        return [
            'logging' => [
                'log_path' => __DIR__ . '/../storage/logs',
                'level' => \Psr\Log\LogLevel::DEBUG,
                'max_file_size' => 10,
                'max_files' => 5,
            ],
            'app_name' => 'IslamWiki',
            'app_debug' => true,
            'default_skin' => 'Bismillah',
        ];
    });

    // Register logging service provider
    $loggingProvider = new LoggingServiceProvider();
    $loggingProvider->register($container);

    echo "Container bindings:\n";
    var_dump($container->has(LoggerInterface::class));

    // Try to get logger
    echo "Getting logger from container...\n";
    $logger = $container->get(LoggerInterface::class);

    echo "Logger type: " . gettype($logger) . "\n";
    if (is_object($logger)) {
        echo "Logger class: " . get_class($logger) . "\n";
        $temp_6727c5f8 = (($logger instanceof LoggerInterface) ? 'yes' : 'no') . "\n";
        echo "Implements LoggerInterface: " . $temp_6727c5f8;
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
