<?php
declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple response
echo "<h1>Hello, IslamWiki!</h1>";
echo "<p>If you can see this, PHP is working correctly.</p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>This is a test file to verify PHP execution</li>";
echo "<li>If you see this, the web server is correctly processing PHP files</li>";
echo "<li>Now let's check if the router is working: <a href='/test-router'>Test Router</a></li>";
echo "</ol>";
