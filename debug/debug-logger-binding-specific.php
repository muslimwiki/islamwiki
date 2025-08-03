<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Container\Asas;
use IslamWiki\Providers\LoggingServiceProvider;
use Psr\Log\LoggerInterface;

echo "Testing LoggerInterface binding specifically...\n";

try {
    // Create container
    $container = new Asas();
    
    // Bind settings
    $container->singleton('settings', function () {
        return [
            'logging' => [
                'log_path' => __DIR__ . '/../storage/logs',
                'level' => \Psr\Log\LogLevel::DEBUG,
                'max_file_size' => 10,
                'max_files' => 5,
            ],
        ];
    });
    
    // Register logging service provider
    $loggingProvider = new LoggingServiceProvider();
    $loggingProvider->register($container);
    
    // Check what's bound
    echo "Has LoggerInterface: " . ($container->has(LoggerInterface::class) ? 'yes' : 'no') . "\n";
    
    // Get the concrete binding
    $reflection = new ReflectionClass($container);
    $getConcreteMethod = $reflection->getMethod('getConcrete');
    $getConcreteMethod->setAccessible(true);
    
    $concrete = $getConcreteMethod->invoke($container, LoggerInterface::class);
    echo "Concrete type: " . gettype($concrete) . "\n";
    if ($concrete instanceof Closure) {
        echo "Concrete is a closure\n";
    } else {
        echo "Concrete value: " . var_export($concrete, true) . "\n";
    }
    
    // Try to get logger
    echo "Getting logger from container...\n";
    $logger = $container->get(LoggerInterface::class);
    
    echo "Logger type: " . gettype($logger) . "\n";
    if (is_object($logger)) {
        echo "Logger class: " . get_class($logger) . "\n";
        echo "Implements LoggerInterface: " . (($logger instanceof LoggerInterface) ? 'yes' : 'no') . "\n";
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 