<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing Muslim Skin Styling Fixes ===\n";

// Test 1: Check if Muslim skin CSS has been updated
echo "\n1. Checking Muslim skin CSS for fixes...\n";
$cssPath = __DIR__ . '/../public/skins/Muslim/css/muslim.css';
if (file_exists($cssPath)) {
    echo "✅ Muslim skin CSS exists\n";
    $content = file_get_contents($cssPath);
    
    // Check for ZamZam integration fixes
    if (strpos($content, 'ZAMZAM INTEGRATION FIXES') !== false) {
        echo "✅ ZamZam integration fixes found\n";
    } else {
        echo "❌ ZamZam integration fixes not found\n";
    }
    
    // Check for Safa CSS compatibility
    if (strpos($content, 'SAFA CSS COMPATIBILITY') !== false) {
        echo "✅ Safa CSS compatibility fixes found\n";
    } else {
        echo "❌ Safa CSS compatibility fixes not found\n";
    }
    
    // Check for mobile dropdown improvements
    if (strpos($content, 'Mobile dropdown improvements') !== false) {
        echo "✅ Mobile dropdown improvements found\n";
    } else {
        echo "❌ Mobile dropdown improvements not found\n";
    }
    
    // Check for z-show directive fixes
    if (strpos($content, '[z-show="false"]') !== false) {
        echo "✅ z-show directive fixes found\n";
    } else {
        echo "❌ z-show directive fixes not found\n";
    }
    
} else {
    echo "❌ Muslim skin CSS not found!\n";
}

// Test 2: Check if ZamZam.js has been updated
echo "\n2. Checking ZamZam.js for improvements...\n";
$jsPath = __DIR__ . '/../public/js/zamzam.js';
if (file_exists($jsPath)) {
    echo "✅ ZamZam.js exists\n";
    $content = file_get_contents($jsPath);
    
    // Check for click-away functionality
    if (strpos($content, 'z-click-away') !== false) {
        echo "✅ Click-away functionality found\n";
    } else {
        echo "❌ Click-away functionality not found\n";
    }
    
    // Check for improved event handling
    if (strpos($content, 'e.preventDefault()') !== false) {
        echo "✅ Improved event handling found\n";
    } else {
        echo "❌ Improved event handling not found\n";
    }
    
    // Check for z-html directive
    if (strpos($content, 'z-html') !== false) {
        echo "✅ z-html directive support found\n";
    } else {
        echo "❌ z-html directive support not found\n";
    }
    
    // Check for z-bind directive
    if (strpos($content, 'z-bind') !== false) {
        echo "✅ z-bind directive support found\n";
    } else {
        echo "❌ z-bind directive support not found\n";
    }
    
} else {
    echo "❌ ZamZam.js not found!\n";
}

// Test 3: Test homepage with Muslim skin
echo "\n3. Testing homepage with Muslim skin...\n";
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
            
            // Check for CSS fixes
            if (strpos($response, 'muslim.css') !== false) {
                echo "✅ Muslim skin CSS is being loaded\n";
            } else {
                echo "❌ Muslim skin CSS not being loaded\n";
            }
            
            // Save response for inspection
            file_put_contents(__DIR__ . '/test-muslim-skin-fixed-response.html', $response);
            echo "📄 Response saved to test-muslim-skin-fixed-response.html for inspection\n";
            
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

// Test 4: Test CSS and JS files are accessible
echo "\n4. Testing CSS and JS file accessibility...\n";

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
        
        // Check for specific fixes in the CSS
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
        
        // Check for specific improvements in the JS
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
echo "\n📄 Next steps:\n";
echo "1. Open https://local.islam.wiki/ in your browser\n";
echo "2. Open Developer Tools (F12)\n";
echo "3. Check the Console tab for JavaScript errors\n";
echo "4. Check the Network tab to see if muslim.css and zamzam.js are loading\n";
echo "5. Try clicking on the user dropdown to see if it works properly\n";
echo "6. Test the responsive design on mobile devices\n";
echo "7. Verify that the styling looks correct and professional\n"; 