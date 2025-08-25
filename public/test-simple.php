<?php
// Simple test to see if basic PHP routing works
error_log('TEST SIMPLE: File loaded');

echo "Simple test works!";
echo "<br>Current time: " . date('Y-m-d H:i:s');
echo "<br>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown');
echo "<br>Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown');
?> 