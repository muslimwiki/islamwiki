<?php
// Simple homepage workaround
echo "<h1>Welcome to IslamWiki</h1>";
echo "<p>This is a temporary homepage. The main application is being fixed.</p>";
echo "<p><a href='/test-simple.php'>Test PHP</a> | ";
echo "<a href='/test-homepage.php'>Test Homepage</a> | ";
echo "<a href='/simple-router-test.php'>Test Router</a></p>";

// Show debug info
echo "<h2>Debug Info:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "</pre>";
