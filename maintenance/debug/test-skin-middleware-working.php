<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing SkinMiddleware Functionality ===\n";

// Test 1: Check if SkinMiddleware is being executed
echo "\n1. Testing SkinMiddleware execution...\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    
    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        'https://local.islam.wiki/',
        [],
        '',
        '1.1'
    );
    
    // Create SkinMiddleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    echo "✅ SkinMiddleware created successfully\n";
    
    // Test the middleware
    $response = $skinMiddleware->handle($request, function($req) {
        echo "✅ Next handler called\n";
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    
    echo "✅ SkinMiddleware executed successfully\n";
    echo "📄 Response status: " . $response->getStatusCode() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing SkinMiddleware: " . $e->getMessage() . "\n";
}

// Test 2: Check if Muslim skin is being loaded
echo "\n2. Testing Muslim skin loading...\n";

try {
    $container = $app->getContainer();
    $skinManager = $container->get('skin.manager');
    
    echo "✅ SkinManager found\n";
    
    // Get all available skins
    $skins = $skinManager->getAvailableSkins();
    echo "📄 Available skins: " . implode(', ', array_keys($skins)) . "\n";
    
    // Check if Muslim skin exists
    if (isset($skins['Muslim'])) {
        echo "✅ Muslim skin found\n";
        
        // Get Muslim skin details
        $muslimSkin = $skins['Muslim'];
        echo "📄 Muslim skin name: " . $muslimSkin->getName() . "\n";
        echo "📄 Muslim skin version: " . $muslimSkin->getVersion() . "\n";
        
        // Check if Muslim skin has CSS
        $cssContent = $muslimSkin->getCssContent();
        if ($cssContent) {
            echo "✅ Muslim skin CSS found (length: " . strlen($cssContent) . ")\n";
        } else {
            echo "❌ Muslim skin CSS not found\n";
        }
        
        // Check if Muslim skin has layout
        if (method_exists($muslimSkin, 'getLayoutPath')) {
            $layoutPath = $muslimSkin->getLayoutPath();
            echo "📄 Muslim skin layout path: " . $layoutPath . "\n";
            
            if (file_exists($layoutPath)) {
                echo "✅ Muslim skin layout file exists\n";
            } else {
                echo "❌ Muslim skin layout file not found\n";
            }
        } else {
            echo "❌ Muslim skin does not have getLayoutPath method\n";
        }
        
    } else {
        echo "❌ Muslim skin not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing Muslim skin: " . $e->getMessage() . "\n";
}

// Test 3: Test homepage with middleware
echo "\n3. Testing homepage with middleware...\n";

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
            
            // Check for skin-related content
            if (strpos($response, 'citizen-header') !== false) {
                echo "✅ Muslim skin layout detected (citizen-header found)\n";
            } else {
                echo "❌ Muslim skin layout not detected (citizen-header not found)\n";
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
            file_put_contents(__DIR__ . '/test-skin-middleware-response.html', $response);
            echo "📄 Response saved to test-skin-middleware-response.html for inspection\n";
            
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing homepage: " . $e->getMessage() . "\n";
}

// Test 4: Check middleware stack
echo "\n4. Checking middleware stack...\n";

try {
    $router = $container->get('router');
    
    if (method_exists($router, 'getMiddlewareStack')) {
        $middlewareStack = $router->getMiddlewareStack();
        echo "✅ Middleware stack found\n";
        
        if ($middlewareStack) {
            echo "📄 Middleware stack has " . count($middlewareStack->getMiddleware()) . " middleware\n";
            
            // Check if SkinMiddleware is in the stack
            $skinMiddlewareFound = false;
            foreach ($middlewareStack->getMiddleware() as $mw) {
                if (get_class($mw) === 'IslamWiki\Http\Middleware\SkinMiddleware') {
                    $skinMiddlewareFound = true;
                    break;
                }
            }
            
            if ($skinMiddlewareFound) {
                echo "✅ SkinMiddleware found in middleware stack\n";
            } else {
                echo "❌ SkinMiddleware not found in middleware stack\n";
            }
        } else {
            echo "❌ Middleware stack is null\n";
        }
    } else {
        echo "❌ Router does not have getMiddlewareStack method\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error checking middleware stack: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\n📄 Next steps:\n";
echo "1. Check the logs for SkinMiddleware execution\n";
echo "2. Verify that the Muslim skin is being loaded correctly\n";
echo "3. Test the user dropdown functionality\n";
echo "4. Check if the CSS and JS are being applied properly\n"; 