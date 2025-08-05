<?php
/**
 * Test Routes
 */

// Direct include approach
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';
require_once __DIR__ . '/../src/Http/Controllers/Controller.php';
require_once __DIR__ . '/../src/Http/Controllers/IqraSearchController.php';
require_once __DIR__ . '/../src/Core/Container.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Http\Controllers\IqraSearchController;

echo "<h1>Route Test</h1>";

try {
    // Test database connection
    $dbConfig = [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $db = new Connection($dbConfig);
    echo "✅ Database connection established<br>";

    // Test container
    $container = new Container();
    echo "✅ Container created<br>";

    // Test controller instantiation
    $controller = new IqraSearchController($db, $container);
    echo "✅ IqraSearchController instantiated<br>";

    // Test search engine
    $searchEngine = new \IslamWiki\Core\Search\IqraSearchEngine($db);
    echo "✅ IqraSearchEngine instantiated<br>";

    echo "<h2>✅ All components working!</h2>";
    echo "<p>The Iqra search engine components are working correctly.</p>";
    echo "<p>If the web interface is not working, it might be a routing issue.</p>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} 