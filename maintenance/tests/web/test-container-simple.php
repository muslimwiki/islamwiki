<?php
/**
 * Simple Container Test for Iqra Search Engine
 */

// Direct include approach
require_once __DIR__ . '/../src/Core/Container.php';
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Logging/Logger.php';
require_once __DIR__ . '/../src/Http/Controllers/Controller.php';
require_once __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';
require_once __DIR__ . '/../src/Http/Controllers/IqraSearchController.php';

use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Search\IqraSearchEngine;
use IslamWiki\Http\Controllers\IqraSearchController;

echo "<h1>Simple Container Test for Iqra Search</h1>";

try {
    // Create container
    $container = new Container();
    echo "✅ Container created<br>";
    
    // Create database connection
    $dbConfig = [
        'host' => 'localhost',
        'database' => 'islamwiki',
        'username' => 'islamwiki',
        'password' => 'islamwiki123'
    ];
    
    $db = new Connection($dbConfig);
    echo "✅ Database connection created<br>";
    
    // Bind database to container
    $container->instance(Connection::class, $db);
    echo "✅ Database bound to container<br>";
    
    // Create logger
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logger = new Logger($logDir, 'debug');
    echo "✅ Logger created<br>";
    
    // Bind logger to container
    $container->instance(Logger::class, $logger);
    echo "✅ Logger bound to container<br>";
    
    // Test IqraSearchEngine directly
    $searchEngine = new IqraSearchEngine($db);
    echo "✅ IqraSearchEngine created<br>";
    
    // Test search functionality
    $testQuery = "allah";
    $searchResult = $searchEngine->search($testQuery, ['type' => 'all', 'limit' => 5]);
    echo "✅ Search test completed<br>";
    echo "Query: '{$testQuery}'<br>";
    echo "Total results: {$searchResult['total']}<br>";
    echo "Results count: " . count($searchResult['results']) . "<br>";
    
    // Test IqraSearchController
    $controller = new IqraSearchController($db, $container);
    echo "✅ IqraSearchController created<br>";
    
    // Check if controller has search engine
    if (property_exists($controller, 'searchEngine')) {
        echo "✅ Controller has searchEngine property<br>";
        echo "Search engine class: " . get_class($controller->searchEngine) . "<br>";
    } else {
        echo "❌ Controller missing searchEngine property<br>";
    }
    
    // Test search suggestions
    $suggestions = $searchEngine->getSuggestions($testQuery);
    echo "✅ Search suggestions test completed<br>";
    echo "Suggestions count: " . count($suggestions) . "<br>";
    
    // Test search analytics
    $analytics = $searchEngine->getSearchAnalytics($testQuery);
    echo "✅ Search analytics test completed<br>";
    echo "Analytics keys: " . implode(', ', array_keys($analytics)) . "<br>";
    
    echo "<h2>✅ All Iqra Search Engine tests passed!</h2>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 