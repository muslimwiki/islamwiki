<?php
declare(strict_types=1);

/**
 * Configuration Web Test for Version 0.0.28
 * 
 * Tests the configuration web interface and API endpoints.
 * 
 * @package IslamWiki
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

// Define ROOT_PATH constant
define('ROOT_PATH', __DIR__ . '/../../');

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

echo "==========================================\n";
echo "Configuration Web Test\n";
echo "Version: 0.0.28\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

try {
    // Initialize application
    echo "Test 1: Initializing Application...\n";
    $app = new Application(__DIR__ . '/../../');
    echo "✅ Application initialized successfully\n\n";

    // Test 2: Test configuration index route
    echo "Test 2: Testing configuration index route...\n";
    $request = new Request('GET', '/configuration');
    $response = $app->handle($request);
    echo "✅ Configuration index response status: " . $response->getStatusCode() . "\n";
    echo "✅ Response content length: " . strlen($response->getBody()) . " bytes\n\n";

    // Test 3: Test configuration API route
    echo "Test 3: Testing configuration API route...\n";
    $request = new Request('GET', '/api/configuration');
    $response = $app->handle($request);
    echo "✅ Configuration API response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getBody();
        $data = json_decode($content, true);
        if ($data) {
            echo "✅ API returned " . count($data) . " configuration categories\n";
        } else {
            echo "⚠️  API response is not valid JSON\n";
        }
    } else {
        echo "⚠️  API returned error status\n";
    }
    echo "\n";

    // Test 4: Test configuration category route
    echo "Test 4: Testing configuration category route...\n";
    $request = new Request('GET', '/api/configuration/core');
    $response = $app->handle($request);
    echo "✅ Core configuration API response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getBody();
        $data = json_decode($content, true);
        if ($data) {
            echo "✅ Core API returned " . count($data) . " settings\n";
        } else {
            echo "⚠️  Core API response is not valid JSON\n";
        }
    } else {
        echo "⚠️  Core API returned error status\n";
    }
    echo "\n";

    // Test 5: Test configuration validation API
    echo "Test 5: Testing configuration validation API...\n";
    $request = new Request('POST', '/api/configuration/validate/advanced', [
        'Content-Type' => 'application/json'
    ], json_encode(['mode' => 'comprehensive']));
    $response = $app->handle($request);
    echo "✅ Validation API response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getBody();
        $data = json_decode($content, true);
        if ($data) {
            echo "✅ Validation API returned valid response\n";
            if (isset($data['valid'])) {
                echo "✅ Configuration valid: " . ($data['valid'] ? 'Yes' : 'No') . "\n";
            }
        } else {
            echo "⚠️  Validation API response is not valid JSON\n";
        }
    } else {
        echo "⚠️  Validation API returned error status\n";
    }
    echo "\n";

    // Test 6: Test configuration analytics API
    echo "Test 6: Testing configuration analytics API...\n";
    $request = new Request('GET', '/api/configuration/analytics');
    $response = $app->handle($request);
    echo "✅ Analytics API response status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getBody();
        $data = json_decode($content, true);
        if ($data) {
            echo "✅ Analytics API returned valid response\n";
        } else {
            echo "⚠️  Analytics API response is not valid JSON\n";
        }
    } else {
        echo "⚠️  Analytics API returned error status\n";
    }
    echo "\n";

    echo "==========================================\n";
    echo "✅ Configuration Web Tests Complete!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 