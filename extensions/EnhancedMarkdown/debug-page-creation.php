<?php
/**
 * Debug Page Creation
 * 
 * This script tests the actual PageController store method to see:
 * - What data is being received
 * - What validation errors occur
 * - What redirect happens
 * - Why it's going back to /create
 */

require_once 'autoload.php';
require_once __DIR__ . '/../../src/Core/Database/Connection.php';
require_once __DIR__ . '/../../src/Http/Controllers/PageController.php';
require_once __DIR__ . '/../../src/Core/Http/Request.php';
require_once __DIR__ . '/../../src/Core/Http/Response.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Http\Controllers\PageController;

echo "=== Debugging Page Creation Issue ===\n\n";

try {
    // Connect to database
    $connection = new Connection();
    echo "✅ Connected to database\n\n";
    
    // Create a mock request that simulates the form submission
    echo "🧪 Creating mock request...\n";
    
    $mockRequest = new class {
        public function getParsedBody() {
            return [
                'title' => 'Test Debug Page',
                'content' => 'This is a test page for debugging the creation issue.',
                'comment' => 'Debug test',
                'content_format' => 'markdown',
                'namespace' => ''
            ];
        }
        
        public function getUri() {
            return new class {
                public function getPath() {
                    return '/wiki/create';
                }
            };
        }
        
        public function getServerParams() {
            return ['REMOTE_ADDR' => '127.0.0.1'];
        }
        
        public function getHeaderLine($header) {
            return 'test';
        }
        
        public function getMethod() {
            return 'POST';
        }
    };
    
    echo "✅ Mock request created\n";
    echo "   - Title: Test Debug Page\n";
    echo "   - Content: This is a test page for debugging...\n";
    echo "   - Content Format: markdown\n";
    echo "   - Namespace: (empty)\n\n";
    
    // Create a mock container
    echo "🔧 Creating mock container...\n";
    
    $mockContainer = new class {
        public function get($service) {
            if ($service === 'Psr\Log\LoggerInterface') {
                return new class {
                    public function info($message, $context = []) {
                        echo "   📝 LOG INFO: {$message}\n";
                    }
                    public function error($message, $context = []) {
                        echo "   ❌ LOG ERROR: {$message}\n";
                    }
                };
            }
            return null;
        }
    };
    
    echo "✅ Mock container created\n\n";
    
    // Create PageController instance
    echo "🚀 Creating PageController instance...\n";
    
    $pageController = new PageController($connection, $mockContainer);
    echo "✅ PageController created\n\n";
    
    // Test the store method
    echo "🧪 Testing PageController::store method...\n";
    
    try {
        $response = $pageController->store($mockRequest, 'en');
        
        echo "✅ Store method completed successfully\n";
        echo "   - Response type: " . get_class($response) . "\n";
        
        // Check if it's a redirect response
        if (method_exists($response, 'getStatusCode')) {
            echo "   - Status code: " . $response->getStatusCode() . "\n";
        }
        
        if (method_exists($response, 'getHeader')) {
            $location = $response->getHeader('Location');
            if ($location) {
                echo "   - Redirect location: " . implode(', ', $location) . "\n";
            }
        }
        
        // Check if it's a view response (validation errors)
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if (strpos($content, 'pages/edit') !== false) {
                echo "   - Response: View with validation errors (redirecting to edit form)\n";
            } else {
                echo "   - Response: " . substr($content, 0, 100) . "...\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Store method failed with exception:\n";
        echo "   - Error: " . $e->getMessage() . "\n";
        echo "   - File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "   - Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n🔍 Checking what happened...\n";
    
    // Check if the page was actually created
    $slug = 'test-debug-page';
    $stmt = $connection->getPdo()->prepare("SELECT id, title, content FROM wiki_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $createdPage = $stmt->fetch();
    
    if ($createdPage) {
        echo "✅ Page was created in database:\n";
        echo "   - ID: {$createdPage['id']}\n";
        echo "   - Title: {$createdPage['title']}\n";
        echo "   - Content: " . substr($createdPage['title'], 0, 50) . "...\n";
        
        // Clean up
        $deleteStmt = $connection->getPdo()->prepare("DELETE FROM wiki_pages WHERE id = ?");
        $deleteStmt->execute([$createdPage['id']]);
        echo "   🧹 Test page cleaned up\n";
    } else {
        echo "❌ Page was NOT created in database\n";
        echo "   - This explains why it redirects back to /create\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during debug: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n"; 