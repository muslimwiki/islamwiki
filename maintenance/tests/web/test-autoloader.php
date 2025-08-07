<?php

/**
 * Test Autoloader
 */

// Simple autoloader for testing
spl_autoload_register(function ($class) {
    $file = realpath(__DIR__ . '/../src/') . '/' . str_replace('IslamWiki\\', '', $class) . '.php';
    echo "Trying to load: $class<br>";
    echo "File path: $file<br>";
    echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "<br>";
    if (file_exists($file)) {
        require_once $file;
        echo "✅ Loaded: $class<br><br>";
    } else {
        echo "❌ Not found: $file<br><br>";
    }
});

echo "<h1>Autoloader Test</h1>";

// Test loading the Connection class
echo "<h2>Testing Connection class:</h2>";
try {
    $connectionClass = 'IslamWiki\Core\Database\Connection';
    $connection = new $connectionClass();
    echo "✅ Connection class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test loading the IqraSearchEngine class
echo "<h2>Testing IqraSearchEngine class:</h2>";
try {
    $searchEngineClass = 'IslamWiki\Core\Search\IqraSearchEngine';
    $searchEngine = new $searchEngineClass(new IslamWiki\Core\Database\Connection());
    echo "✅ IqraSearchEngine class instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
