<?php

/**
 * Database Connection Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Database Connection Test</h1>";

// Include necessary files
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Search\IqraSearchEngine;

try {
    // Test database connection
    echo "<h2>Testing Database Connection</h2>";

    $dbConfig = [
        'host' => 'localhost',
        'database' => 'islamwiki',
        'username' => 'islamwiki',
        'password' => 'islamwiki123'
    ];

    $db = new Connection($dbConfig);
    echo "✅ Database connection successful<br>";

    // Test basic query
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM pages");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Pages count: " . $result['count'] . "<br>";

    // Test search engine
    echo "<h2>Testing Iqra Search Engine</h2>";

    $searchEngine = new IqraSearchEngine($db);
    echo "✅ Search engine created successfully<br>";

    // Test search
    $query = "test";
    echo "Testing search for: '{$query}'<br>";

    $results = $searchEngine->search($query, [
        'type' => 'pages',
        'limit' => 5,
        'page' => 1
    ]);

    echo "✅ Search completed successfully<br>";
    echo "Total results: " . $results['total'] . "<br>";
    echo "Results returned: " . count($results['results']) . "<br>";

    if (!empty($results['results'])) {
        echo "<h3>Search Results:</h3>";
        foreach ($results['results'] as $result) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<strong>Title:</strong> " . htmlspecialchars($result['title']) . "<br>";
            echo "<strong>Type:</strong> " . htmlspecialchars($result['type']) . "<br>";
            echo "<strong>Relevance:</strong> " . ($result['relevance'] ?? 'N/A') . "<br>";
            if (!empty($result['excerpt'])) {
                echo "<strong>Excerpt:</strong> " . htmlspecialchars($result['excerpt']) . "<br>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No results found for '{$query}'</p>";
    }

    // Test analytics
    echo "<h2>Testing Search Analytics</h2>";

    $analytics = $searchEngine->getSearchAnalytics($query);
    echo "✅ Analytics generated successfully<br>";
    echo "<pre>" . json_encode($analytics, JSON_PRETTY_PRINT) . "</pre>";

    echo "<h2>✅ All Tests Passed!</h2>";
    echo "<p>The database connection and Iqra search engine are working correctly.</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
