<?php
echo "<h1>Simple Test</h1>";
echo "<p>This is a simple test to verify Apache is working.</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "</p>";
echo "<p>Host: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "</p>";
echo "<p>URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "</p>";
?> 