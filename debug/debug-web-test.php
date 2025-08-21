<?php

/**
 * Debug Web Test
 *
 * Simulates the actual web request to test the settings page.
 *
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Web Request Simulation\n";
echo "=================================\n\n";

// Simulate the web environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/settings';
$_SERVER['HTTP_HOST'] = 'local.islam.wiki';
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";

// Simulate login
$session->login(1, 'admin', true);
echo "✅ Login simulation completed\n";

// Test the SettingsController directly
echo "\n📊 Testing SettingsController\n";
echo "=============================\n";

try {
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);

    // Use reflection to access the private index method
    $reflection = new ReflectionClass($settingsController);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);

    $response = $method->invoke($settingsController);

    echo "✅ SettingsController executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";

    // Get the response body
    $body = $response->getBody();
    echo "Response length: " . strlen($body) . " characters\n";

    // Check for specific content
    if (strpos($body, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }

    if (strpos($body, 'Bismillah') !== false) {
        echo "✅ Response contains Bismillah skin\n";
    } else {
        echo "❌ Response does not contain Bismillah skin\n";
    }

    if (strpos($body, 'Muslim') !== false) {
        echo "✅ Response contains Muslim skin\n";
    } else {
        echo "❌ Response does not contain Muslim skin\n";
    }

    // Show a snippet of the response
    echo "\n📋 Response Preview (first 500 chars):\n";
    echo "=====================================\n";
    echo substr($body, 0, 500) . "...\n";
} catch (\Exception $e) {
    echo "❌ SettingsController error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Web test completed!\n";
