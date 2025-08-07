<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing Muslim Skin Dropdown Functionality ===\n";

// Test 1: Check if Muslim skin layout has ZamZam directives
echo "\n1. Checking Muslim skin layout for ZamZam directives...\n";
$layoutPath = __DIR__ . '/../skins/Muslim/templates/layout.twig';
if (file_exists($layoutPath)) {
    echo "✅ Muslim skin layout exists\n";
    $content = file_get_contents($layoutPath);

    if (strpos($content, 'z-data') !== false) {
        echo "✅ ZamZam z-data directive found\n";
    } else {
        echo "❌ ZamZam z-data directive not found\n";
    }

    if (strpos($content, 'z-click') !== false) {
        echo "✅ ZamZam z-click directive found\n";
    } else {
        echo "❌ ZamZam z-click directive not found\n";
    }

    if (strpos($content, 'z-show') !== false) {
        echo "✅ ZamZam z-show directive found\n";
    } else {
        echo "❌ ZamZam z-show directive not found\n";
    }

    if (strpos($content, 'user-dropdown') !== false) {
        echo "✅ User dropdown HTML found\n";
    } else {
        echo "❌ User dropdown HTML not found\n";
    }

    if (strpos($content, 'zamzam.js') !== false) {
        echo "✅ ZamZam.js script tag found\n";
    } else {
        echo "❌ ZamZam.js script tag not found\n";
    }
} else {
    echo "❌ Muslim skin layout not found!\n";
}

// Test 2: Test homepage with Muslim skin (logged in user)
echo "\n2. Testing homepage with Muslim skin (logged in user)...\n";
try {
    // First, let's simulate a logged-in user by setting a session
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['is_admin'] = true;

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
            file_put_contents(__DIR__ . '/test-muslim-skin-response.html', $response);
            echo "📄 Response saved to test-muslim-skin-response.html for inspection\n";
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

// Test 3: Check if ZamZam.js is working
echo "\n3. Testing ZamZam.js functionality...\n";
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
            echo "✅ ZamZam.js is accessible\n";

            // Check for key ZamZam functions
            if (strpos($response, 'ZamZam') !== false) {
                echo "✅ ZamZam class found in JavaScript\n";
            } else {
                echo "❌ ZamZam class not found in JavaScript\n";
            }

            if (strpos($response, 'init') !== false) {
                echo "✅ ZamZam init function found\n";
            } else {
                echo "❌ ZamZam init function not found\n";
            }

            if (strpos($response, 'click') !== false) {
                echo "✅ ZamZam click handler found\n";
            } else {
                echo "❌ ZamZam click handler not found\n";
            }
        } else {
            echo "❌ ZamZam.js returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing ZamZam.js: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\n📄 Next steps:\n";
echo "1. Open https://local.islam.wiki/ in your browser\n";
echo "2. Open Developer Tools (F12)\n";
echo "3. Check the Console tab for JavaScript errors\n";
echo "4. Check the Network tab to see if zamzam.js is loading\n";
echo "5. Try clicking on the user dropdown to see if it works\n";
echo "6. If the dropdown doesn't work, check if ZamZam.js is initializing properly\n";
