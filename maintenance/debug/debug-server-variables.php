<?php
/**
 * Debug Server Variables
 * 
 * This script shows what server variables are available when the request is made.
 */

echo "<h1>🔍 Debug Server Variables</h1>";

echo "<h2>Key Server Variables</h2>";
$keyVars = [
    'HTTP_HOST',
    'SERVER_NAME', 
    'SERVER_ADDR',
    'REQUEST_URI',
    'REQUEST_METHOD',
    'HTTPS',
    'SERVER_PORT',
    'HTTP_X_FORWARDED_HOST',
    'HTTP_X_FORWARDED_PROTO',
    'HTTP_X_FORWARDED_PORT'
];

foreach ($keyVars as $var) {
    $value = $_SERVER[$var] ?? 'NOT SET';
    echo "<strong>$var:</strong> $value<br>";
}

echo "<h2>All Server Variables</h2>";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    echo "$key: $value\n";
}
echo "</pre>";
?> 