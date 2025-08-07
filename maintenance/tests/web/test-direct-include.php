<?php

/**
 * Test Direct Include
 */

echo "<h1>Direct Include Test</h1>";

// Directly include the files
$connectionFile = __DIR__ . '/../src/Core/Database/Connection.php';
$searchEngineFile = __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';

echo "Connection file: $connectionFile<br>";
echo "Connection file exists: " . (file_exists($connectionFile) ? 'YES' : 'NO') . "<br>";

echo "Search engine file: $searchEngineFile<br>";
echo "Search engine file exists: " . (file_exists($searchEngineFile) ? 'YES' : 'NO') . "<br>";

try {
    require_once $connectionFile;
    echo "✅ Connection class loaded<br>";

    require_once $searchEngineFile;
    echo "✅ IqraSearchEngine class loaded<br>";

    // Test instantiation
    $connection = new IslamWiki\Core\Database\Connection();
    echo "✅ Connection instantiated<br>";

    $searchEngine = new IslamWiki\Core\Search\IqraSearchEngine($connection);
    echo "✅ IqraSearchEngine instantiated<br>";

    echo "<h2>✅ All tests passed!</h2>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
