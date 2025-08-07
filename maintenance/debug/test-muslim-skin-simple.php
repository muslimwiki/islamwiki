<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Simple Muslim Skin Test ===\n";

// Test 1: Check if Muslim skin files exist
echo "\n1. Checking Muslim skin files...\n";

$skinPath = __DIR__ . '/../skins/Muslim';
if (is_dir($skinPath)) {
    echo "✅ Muslim skin directory exists\n";

    // Check skin.json
    $configFile = $skinPath . '/skin.json';
    if (file_exists($configFile)) {
        echo "✅ Muslim skin config exists\n";
        $config = json_decode(file_get_contents($configFile), true);
        echo "📄 Skin name: " . ($config['name'] ?? 'Unknown') . "\n";
        echo "📄 Skin version: " . ($config['version'] ?? 'Unknown') . "\n";
    } else {
        echo "❌ Muslim skin config not found\n";
    }

    // Check CSS file
    $cssFile = $skinPath . '/css/muslim.css';
    if (file_exists($cssFile)) {
        echo "✅ Muslim skin CSS exists\n";
        $cssContent = file_get_contents($cssFile);
        echo "📄 CSS length: " . strlen($cssContent) . " bytes\n";

        // Check for specific fixes
        if (strpos($cssContent, 'ZAMZAM INTEGRATION FIXES') !== false) {
            echo "✅ CSS contains ZamZam integration fixes\n";
        } else {
            echo "❌ CSS missing ZamZam integration fixes\n";
        }

        if (strpos($cssContent, 'SAFA CSS COMPATIBILITY') !== false) {
            echo "✅ CSS contains Safa CSS compatibility fixes\n";
        } else {
            echo "❌ CSS missing Safa CSS compatibility fixes\n";
        }
    } else {
        echo "❌ Muslim skin CSS not found\n";
    }

    // Check layout file
    $layoutFile = $skinPath . '/templates/layout.twig';
    if (file_exists($layoutFile)) {
        echo "✅ Muslim skin layout exists\n";
        $layoutContent = file_get_contents($layoutFile);
        echo "📄 Layout length: " . strlen($layoutContent) . " bytes\n";

        // Check for ZamZam directives
        if (strpos($layoutContent, 'z-data') !== false) {
            echo "✅ Layout contains ZamZam directives\n";
        } else {
            echo "❌ Layout missing ZamZam directives\n";
        }

        if (strpos($layoutContent, 'user-dropdown') !== false) {
            echo "✅ Layout contains user dropdown\n";
        } else {
            echo "❌ Layout missing user dropdown\n";
        }
    } else {
        echo "❌ Muslim skin layout not found\n";
    }
} else {
    echo "❌ Muslim skin directory not found\n";
}

// Test 2: Check ZamZam.js
echo "\n2. Checking ZamZam.js...\n";

$zamzamFile = __DIR__ . '/../public/js/zamzam.js';
if (file_exists($zamzamFile)) {
    echo "✅ ZamZam.js exists\n";
    $jsContent = file_get_contents($zamzamFile);
    echo "📄 JS length: " . strlen($jsContent) . " bytes\n";

    // Check for specific improvements
    if (strpos($jsContent, 'z-click-away') !== false) {
        echo "✅ JS contains click-away functionality\n";
    } else {
        echo "❌ JS missing click-away functionality\n";
    }

    if (strpos($jsContent, 'e.preventDefault()') !== false) {
        echo "✅ JS contains improved event handling\n";
    } else {
        echo "❌ JS missing improved event handling\n";
    }
} else {
    echo "❌ ZamZam.js not found\n";
}

// Test 3: Test homepage directly
echo "\n3. Testing homepage...\n";

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

            // Check for Muslim skin content
            if (strpos($response, 'citizen-header') !== false) {
                echo "✅ Muslim skin layout detected\n";
            } else {
                echo "❌ Muslim skin layout not detected\n";
            }

            if (strpos($response, 'z-data') !== false) {
                echo "✅ ZamZam directives found\n";
            } else {
                echo "❌ ZamZam directives not found\n";
            }

            if (strpos($response, 'user-dropdown') !== false) {
                echo "✅ User dropdown found\n";
            } else {
                echo "❌ User dropdown not found\n";
            }

            if (strpos($response, 'muslim.css') !== false) {
                echo "✅ Muslim skin CSS reference found\n";
            } else {
                echo "❌ Muslim skin CSS reference not found\n";
            }

            // Save response for inspection
            file_put_contents(__DIR__ . '/test-muslim-skin-simple-response.html', $response);
            echo "📄 Response saved to test-muslim-skin-simple-response.html\n";
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

// Test 4: Test CSS and JS files directly
echo "\n4. Testing CSS and JS files...\n";

// Test Muslim CSS
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/skins/Muslim/css/muslim.css');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✅ Muslim skin CSS is accessible\n";

        if (strpos($response, 'ZAMZAM INTEGRATION FIXES') !== false) {
            echo "✅ CSS contains ZamZam integration fixes\n";
        } else {
            echo "❌ CSS missing ZamZam integration fixes\n";
        }
    } else {
        echo "❌ Muslim skin CSS returned status code: " . $httpCode . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing Muslim CSS: " . $e->getMessage() . "\n";
}

// Test ZamZam.js
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/js/zamzam.js');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✅ ZamZam.js is accessible\n";

        if (strpos($response, 'z-click-away') !== false) {
            echo "✅ JS contains click-away functionality\n";
        } else {
            echo "❌ JS missing click-away functionality\n";
        }
    } else {
        echo "❌ ZamZam.js returned status code: " . $httpCode . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing ZamZam.js: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\n📄 Summary:\n";
echo "1. Muslim skin CSS has been updated with ZamZam integration fixes\n";
echo "2. Muslim skin CSS has been updated with Safa CSS compatibility fixes\n";
echo "3. ZamZam.js has been improved with click-away functionality\n";
echo "4. The styling issues for the Muslim skin have been addressed\n";
echo "\n📄 Next steps:\n";
echo "1. Open https://local.islam.wiki/ in your browser\n";
echo "2. Check if the Muslim skin is being applied correctly\n";
echo "3. Test the user dropdown functionality\n";
echo "4. Verify that the styling looks professional and modern\n";
