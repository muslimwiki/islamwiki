<?php

/**
 * Test Class Loading
 */

// Simple autoloader for testing
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    echo "Trying to load: $file<br>";
    if (file_exists($file)) {
        require_once $file;
        echo "✅ Loaded: $class<br>";
    } else {
        echo "❌ Not found: $file<br>";
    }
});

echo "<h1>Class Loading Test</h1>";

// Test loading the Connection class
echo "<h2>Testing Connection class:</h2>";
try {
    $connectionClass = 'IslamWiki\Core\Database\Connection';
    if (class_exists($connectionClass)) {
        echo "✅ Connection class loaded successfully<br>";
    } else {
        echo "❌ Connection class not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading Connection class: " . $e->getMessage() . "<br>";
}

// Test loading the IqraSearchEngine class
echo "<h2>Testing IqraSearchEngine class:</h2>";
try {
    $searchEngineClass = 'IslamWiki\Core\Search\IqraSearchEngine';
    if (class_exists($searchEngineClass)) {
        echo "✅ IqraSearchEngine class loaded successfully<br>";
    } else {
        echo "❌ IqraSearchEngine class not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading IqraSearchEngine class: " . $e->getMessage() . "<br>";
}

// List all files in the src directory
echo "<h2>Files in src directory:</h2>";
function listFiles($dir, $indent = '')
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                echo "$indent📁 $file/<br>";
                listFiles($path, $indent . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                echo "$indent📄 $file<br>";
            }
        }
    }
}

listFiles(__DIR__ . '/../src');
