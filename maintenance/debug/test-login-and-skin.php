<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing Login and Skin Functionality ===\n";

// Test 1: Login the user
echo "\n1. Logging in user...\n";
try {
    // Start session
    session_start();
    
    // Create session manager
    $session = new \IslamWiki\Core\Session\Wisal();
    
    // Login the user
    $session->login(1, 'admin', true);
    
    echo "✅ User logged in successfully\n";
    echo "📄 User ID: " . $session->getUserId() . "\n";
    echo "📄 Username: " . $session->getUsername() . "\n";
    echo "📄 Is Admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error logging in user: " . $e->getMessage() . "\n";
}

// Test 2: Test homepage with logged-in user
echo "\n2. Testing homepage with logged-in user...\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'IslamWiki-Debug/1.0');
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL error: " . $error . "\n";
    } else {
        echo "📄 HTTP Status Code: " . $httpCode . "\n";
        if ($httpCode === 200) {
            echo "✅ Homepage is accessible\n";
            
            // Check for Muslim skin specific content
            if (strpos($response, 'citizen-header') !== false) {
                echo "✅ Muslim skin layout is being used (citizen-header found)\n";
            } else {
                echo "❌ Muslim skin layout not being used (citizen-header not found)\n";
            }
            
            // Check for ZamZam directives in the response
            if (strpos($response, 'z-data') !== false) {
                echo "✅ ZamZam directives found in response\n";
            } else {
                echo "❌ ZamZam directives not found in response\n";
            }
            
            // Check for user dropdown
            if (strpos($response, 'user-dropdown') !== false) {
                echo "✅ User dropdown found in response\n";
            } else {
                echo "❌ User dropdown not found in response\n";
            }
            
            // Check for ZamZam.js script
            if (strpos($response, 'zamzam.js') !== false) {
                echo "✅ ZamZam.js script tag found in response\n";
            } else {
                echo "❌ ZamZam.js script tag not found in response\n";
            }
            
            // Check for user menu
            if (strpos($response, 'citizen-user-menu') !== false) {
                echo "✅ Citizen user menu found in response\n";
            } else {
                echo "❌ Citizen user menu not found in response\n";
            }
            
            // Save response for inspection
            file_put_contents(__DIR__ . '/test-login-skin-response.html', $response);
            echo "📄 Response saved to test-login-skin-response.html for inspection\n";
            
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\n📄 Next steps:\n";
echo "1. Check the saved response file to see if Muslim skin is being used\n";
echo "2. If Muslim skin is working, test the dropdown functionality\n";
echo "3. If not, check the session and authentication flow\n"; 