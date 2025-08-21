<?php

/**
 * Test Path Resolution
 */

echo "<h1>Path Resolution Test</h1>";

$basePath = __DIR__ . '/../src/';
$classPath = 'IslamWiki\Core\Database\Connection';
$filePath = $basePath . str_replace('IslamWiki\\', '', $classPath) . '.php';

echo "Base path: $basePath<br>";
echo "Class path: $classPath<br>";
echo "File path: $filePath<br>";
echo "File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "<br>";

// Test with realpath
$realPath = realpath($filePath);
echo "Real path: $realPath<br>";

// Test with absolute path
$absolutePath = '/var/www/html/local.islam.wiki/src/Core/Database/Connection.php';
echo "Absolute path: $absolutePath<br>";
echo "Absolute path exists: " . (file_exists($absolutePath) ? 'YES' : 'NO') . "<br>";
