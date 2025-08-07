<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize database connection
$db = new \IslamWiki\Core\Database\Connection([
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
]);

echo "=== Testing ZamZam.js Dropdown Functionality ===\n";

// Test 1: Check if ZamZam.js file exists and is readable
echo "\n1. Checking ZamZam.js file...\n";
$zamzamPath = __DIR__ . '/../public/js/zamzam.js';
if (file_exists($zamzamPath)) {
    echo "✅ ZamZam.js file exists\n";
    $fileSize = filesize($zamzamPath);
    echo "📄 File size: " . number_format($fileSize) . " bytes\n";

    // Check if file contains expected content
    $content = file_get_contents($zamzamPath);
    if (strpos($content, 'ZamZam') !== false) {
        echo "✅ File contains ZamZam references\n";
    } else {
        echo "⚠️  File may not contain expected ZamZam content\n";
    }
} else {
    echo "❌ ZamZam.js file not found!\n";
}

// Test 2: Check if ZamZam.js is accessible via web
echo "\n2. Testing ZamZam.js web accessibility...\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/js/zamzam.js');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL error: " . $error . "\n";
    } else {
        echo "📄 HTTP Status Code: " . $httpCode . "\n";
        if ($httpCode === 200) {
            echo "✅ ZamZam.js is accessible via web\n";
            if (strpos($response, 'ZamZam') !== false) {
                echo "✅ Response contains ZamZam content\n";
            } else {
                echo "⚠️  Response may not contain expected content\n";
            }
        } else {
            echo "❌ ZamZam.js returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing ZamZam.js web access: " . $e->getMessage() . "\n";
}

// Test 3: Test homepage with user dropdown
echo "\n3. Testing homepage with user dropdown...\n";
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
            echo "✅ Homepage is accessible\n";

            // Check for ZamZam.js script tag
            if (strpos($response, 'zamzam.js') !== false) {
                echo "✅ ZamZam.js script tag found in homepage\n";
            } else {
                echo "❌ ZamZam.js script tag not found in homepage\n";
            }

            // Check for dropdown HTML
            if (strpos($response, 'user-dropdown') !== false) {
                echo "✅ User dropdown HTML found\n";
            } else {
                echo "❌ User dropdown HTML not found\n";
            }

            // Check for ZamZam directives
            if (strpos($response, 'z-data') !== false) {
                echo "✅ ZamZam directives found\n";
            } else {
                echo "❌ ZamZam directives not found\n";
            }

            // Check for user menu
            if (strpos($response, 'user-menu') !== false) {
                echo "✅ User menu found\n";
            } else {
                echo "❌ User menu not found\n";
            }
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

// Test 4: Check if there are any JavaScript errors in the browser console
echo "\n4. Testing for JavaScript errors...\n";
echo "📄 To check for JavaScript errors, open browser developer tools and look at the Console tab\n";
echo "📄 Common issues:\n";
echo "  - ZamZam.js not loading (check Network tab)\n";
echo "  - JavaScript syntax errors\n";
echo "  - Missing dependencies\n";
echo "  - CORS issues\n";

echo "\n=== Test Complete ===\n";
echo "\n📄 Next steps:\n";
echo "1. Open https://local.islam.wiki/ in your browser\n";
echo "2. Open Developer Tools (F12)\n";
echo "3. Check the Console tab for JavaScript errors\n";
echo "4. Check the Network tab to see if zamzam.js is loading\n";
echo "5. Try clicking on the user dropdown to see if it works\n";
