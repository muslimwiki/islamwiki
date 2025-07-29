<?php
declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple test to check PHP is working
$test = "Test successful!";
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
</head>
<body>
    <h1>PHP Test Page</h1>
    <p>If you can see this, PHP is working correctly.</p>
    <p>Test variable: <?php echo phpversion(); ?></p>
    <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Not available'; ?></p>
</body>
</html>
