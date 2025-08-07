<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Simple Middleware Test ===\n";

// Test 1: Make a web request and check logs
echo "\n1. Making web request and checking logs...\n";

// Clear the log file first
$logFile = __DIR__ . '/../storage/logs/debug.log';
if (file_exists($logFile)) {
    file_put_contents($logFile, ''); // Clear the log
    echo "📄 Cleared log file\n";
}

// Make a web request
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'IslamWiki-Debug/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL error: " . $error . "\n";
    } else {
        echo "📄 HTTP Status Code: " . $httpCode . "\n";
        if ($httpCode === 200) {
            echo "✅ Homepage request successful\n";
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error making web request: " . $e->getMessage() . "\n";
}

// Wait a moment for logs to be written
sleep(1);

// Check the logs
echo "\n2. Checking logs for middleware execution...\n";
try {
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);

        echo "📄 Total log lines: " . count($lines) . "\n";

        $middlewareLogs = array_filter($lines, function ($line) {
            return strpos($line, 'SkinMiddleware') !== false ||
                   strpos($line, 'MiddlewareStack') !== false ||
                   strpos($line, 'IslamRouter') !== false;
        });

        if (!empty($middlewareLogs)) {
            echo "✅ Found middleware-related logs:\n";
            foreach ($middlewareLogs as $log) {
                if (!empty(trim($log))) {
                    echo "📄 " . $log . "\n";
                }
            }
        } else {
            echo "❌ No middleware logs found\n";

            // Show all logs for debugging
            echo "📄 All logs:\n";
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    echo "📄 " . $line . "\n";
                }
            }
        }
    } else {
        echo "❌ Log file not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking logs: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
