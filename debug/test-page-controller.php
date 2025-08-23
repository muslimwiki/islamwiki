<?php
/**
 * Test script to debug PageController
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Core\Http\Request;

echo "=== Testing PageController ===\n";

try {
    // Test 1: Check if classes can be loaded
    echo "1. Checking class loading...\n";
    
    if (!class_exists(Connection::class)) {
        echo "   ❌ Connection class not found\n";
        exit(1);
    }
    echo "   ✅ Connection class loaded\n";
    
    if (!class_exists(AsasContainer::class)) {
        echo "   ❌ AsasContainer class not found\n";
        exit(1);
    }
    echo "   ✅ AsasContainer class loaded\n";
    
    if (!class_exists(PageController::class)) {
        echo "   ❌ PageController class not found\n";
        exit(1);
    }
    echo "   ✅ PageController class loaded\n";
    
    // Test 2: Try to create a mock request
    echo "\n2. Creating mock request...\n";
    
    $mockRequest = new class {
        public function getMethod(): string { return 'POST'; }
        public function getUri() { 
            return new class {
                public function getPath(): string { return '/wiki/create'; }
            };
        }
        public function getParsedBody(): array { 
            return [
                'title' => 'TestPage',
                'content' => 'Test content',
                'comment' => 'Created page',
                'content_format' => 'markdown'
            ]; 
        }
        public function getQueryParams(): array { return []; }
        public function getServerParams(): array { return ['REMOTE_ADDR' => '127.0.0.1']; }
        public function getHeaderLine(string $name): string { return ''; }
    };
    
    echo "   ✅ Mock request created\n";
    
    // Test 3: Try to create container and database connection
    echo "\n3. Testing container and database...\n";
    
    try {
        $container = new AsasContainer();
        echo "   ✅ Container created\n";
        
        // Try to get database connection
        $db = $container->get(Connection::class);
        echo "   ✅ Database connection obtained\n";
        
        // Test 4: Try to create PageController
        echo "\n4. Creating PageController...\n";
        
        $controller = new PageController($db, $container);
        echo "   ✅ PageController created successfully\n";
        
        // Test 5: Try to call store method
        echo "\n5. Testing store method...\n";
        
        try {
            $response = $controller->store($mockRequest, 'en');
            echo "   ✅ Store method executed successfully\n";
            echo "   Response type: " . get_class($response) . "\n";
        } catch (Exception $e) {
            echo "   ❌ Store method failed: " . $e->getMessage() . "\n";
            echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Container/Database error: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ General error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n"; 