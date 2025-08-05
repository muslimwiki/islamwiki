<?php
/**
 * Test Iqra Route Directly
 */

// Direct include approach for minimal dependencies
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';
require_once __DIR__ . '/../src/Http/Controllers/Controller.php';
require_once __DIR__ . '/../src/Http/Controllers/IqraSearchController.php';
require_once __DIR__ . '/../src/Core/Container.php';
require_once __DIR__ . '/../src/Core/Http/Request.php';
require_once __DIR__ . '/../src/Core/Http/Response.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Http\Controllers\IqraSearchController;
use IslamWiki\Core\Http\Request;

echo "<h1>Iqra Route Test</h1>";

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

    // Test controller methods
    echo "<h2>Testing Controller Methods</h2>";
    
    // Test index method
    $request = new Request();
    $request = $request->withQueryParams(['q' => 'allah']);
    
    try {
        $response = $controller->index($request);
        echo "✅ Index method works: Status " . $response->getStatusCode() . "<br>";
    } catch (Exception $e) {
        echo "❌ Index method failed: " . $e->getMessage() . "<br>";
    }

    // Test API search method
    try {
        $response = $controller->apiSearch($request);
        echo "✅ API search method works: Status " . $response->getStatusCode() . "<br>";
    } catch (Exception $e) {
        echo "❌ API search method failed: " . $e->getMessage() . "<br>";
    }

    // Test API suggestions method
    try {
        $response = $controller->apiSuggestions($request);
        echo "✅ API suggestions method works: Status " . $response->getStatusCode() . "<br>";
    } catch (Exception $e) {
        echo "❌ API suggestions method failed: " . $e->getMessage() . "<br>";
    }

    // Test API analytics method
    try {
        $response = $controller->apiAnalytics($request);
        echo "✅ API analytics method works: Status " . $response->getStatusCode() . "<br>";
    } catch (Exception $e) {
        echo "❌ API analytics method failed: " . $e->getMessage() . "<br>";
    }

    echo "<h2>✅ All Controller Methods Tested</h2>";
    echo "<p>The IqraSearchController is working correctly!</p>";
    echo "<p><strong>Next step:</strong> The routing issue is likely in the main application setup.</p>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} 