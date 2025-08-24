<?php

/**
 * Debug Session System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

echo "=== Session Debug Test ===<br>";

try {
    echo "Step 1: Loading autoloader...<br>";
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
    echo "Step 2: Autoloader loaded successfully<br>";

    echo "Step 3: Creating container...<br>";
    $container = new \IslamWiki\Core\Container\Container
    echo "Step 4: Container created successfully<br>";

    echo "Step 5: Creating session manager...<br>";
    $sessionManager = new \IslamWiki\Core\Session\Session
    echo "Step 6: Session manager created successfully<br>";

    echo "Step 7: Starting session...<br>";
    $sessionManager->start();
    echo "Step 8: Session started successfully<br>";

    echo "Step 9: Setting session data...<br>";
    $_SESSION['test_key'] = 'test_value';
    echo "Step 10: Session data set successfully<br>";

    echo "Step 11: Reading session data...<br>";
    $testValue = $_SESSION['test_key'] ?? 'not_found';
    echo "Step 12: Session data read: $testValue<br>";

    echo "Step 13: Session ID: " . session_id() . "<br>";
    echo "Step 14: Session name: " . session_name() . "<br>";
    echo "Step 15: Session status: " . session_status() . "<br>";

    echo "Step 16: Session data dump:<br><pre>";
    print_r($_SESSION);
    echo "</pre>";

    echo "Step 17: Session debug completed successfully<br>";

} catch (\Throwable $e) {
    echo "<br><strong>ERROR: " . $e->getMessage() . "</strong><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

ob_end_flush();
