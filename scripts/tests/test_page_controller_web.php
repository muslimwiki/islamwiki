<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Uri;

echo "Testing PageController Web Functionality\n";
echo "=======================================\n\n";

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
    
    // Create session manager
    $session = new \IslamWiki\Core\Session\SessionManager();
    $container->bind('session', function() use ($session) {
        return $session;
    });
    
    // Create view renderer (Twig)
    $viewRenderer = new \IslamWiki\Core\View\TwigRenderer(__DIR__ . '/../resources/views');
    $container->bind('view', function() use ($viewRenderer) {
        return $viewRenderer;
    });
    
    echo "✅ Container, logger, session, and view renderer created\n";

    // Test PageController instantiation
    $pageController = new PageController($connection, $container);
    echo "✅ PageController created successfully\n";

    // Create a mock request
    $uri = new Uri('http://local.islam.wiki/test-page');
    $request = new Request('GET', $uri);
    echo "✅ Mock request created\n";

    // Test the show method
    echo "\n🔍 Testing PageController@show method...\n";
    $response = $pageController->show($request, 'test-page');
    echo "✅ PageController@show executed successfully\n";
    echo "  - Status: " . $response->getStatusCode() . "\n";
    echo "  - Content length: " . strlen($response->getBody()) . " characters\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ PageController web functionality test completed successfully!\n";
echo "\nDone!\n"; 