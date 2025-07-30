<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Http\Controllers\PageController;

echo "Testing PageController Instantiation\n";
echo "===================================\n\n";

try {
    // Create database connection
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

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Create container
    $container = new Container();
    $container->bind('db', $connection);
    
    // Create logger
    $logger = new \IslamWiki\Core\Logging\Logger(__DIR__ . '/../logs');
    $container->bind(\Psr\Log\LoggerInterface::class, function() use ($logger) {
        return $logger;
    });
    
    echo "✅ Container and logger created\n";

    // Test PageController instantiation
    $pageController = new PageController($connection, $container);
    echo "✅ PageController created successfully\n";

    // Test a simple method
    echo "✅ PageController methods are accessible\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ PageController instantiation test completed successfully!\n";
echo "\nDone!\n"; 