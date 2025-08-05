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

echo "=== Testing Pages Functionality ===\n";

// Test 1: Check if pages table exists and has data
echo "\n1. Checking pages table...\n";
try {
    $result = $db->query("SHOW TABLES LIKE 'pages'");
    $tables = $result->fetchAll();
    if (empty($tables)) {
        echo "❌ Pages table does not exist!\n";
    } else {
        echo "✅ Pages table exists\n";
        
        // Check if there are any pages
        $result = $db->query("SELECT COUNT(*) as count FROM pages");
        $count = $result->fetch();
        echo "📊 Total pages in database: " . $count->count . "\n";
        
        if ($count->count > 0) {
            // Show some sample pages
            $result = $db->query("SELECT id, title, slug, namespace FROM pages LIMIT 5");
            $pages = $result->fetchAll();
            echo "📄 Sample pages:\n";
            foreach ($pages as $page) {
                echo "  - ID: {$page->id}, Title: {$page->title}, Slug: {$page->slug}, Namespace: {$page->namespace}\n";
            }
        } else {
            echo "⚠️  No pages found in database\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking pages table: " . $e->getMessage() . "\n";
}

// Test 2: Test the PageController index method
echo "\n2. Testing PageController index method...\n";
try {
    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        new \IslamWiki\Core\Http\Uri('https://local.islam.wiki/pages'),
        [],
        [],
        '1.1'
    );
    
    // Create container
    $container = new \IslamWiki\Core\Container\AsasContainer();
    $container->singleton(\IslamWiki\Core\Database\Connection::class, function() use ($db) {
        return $db;
    });
    
    // Create PageController
    $pageController = new \IslamWiki\Http\Controllers\PageController($db, $container);
    
    // Call index method
    $response = $pageController->index($request);
    
    echo "✅ PageController index method executed successfully\n";
    echo "📄 Response status: " . $response->getStatusCode() . "\n";
    echo "📄 Response headers: " . json_encode($response->getHeaders()) . "\n";
    
    // Check if response body contains expected content
    $body = $response->getBody();
    if (strpos($body, 'pages') !== false || strpos($body, 'Browse') !== false) {
        echo "✅ Response contains expected content\n";
    } else {
        echo "⚠️  Response may not contain expected content\n";
        echo "📄 Response body preview: " . substr($body, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing PageController: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Test the actual web route
echo "\n3. Testing web route /pages...\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages');
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
            echo "✅ Pages route is working\n";
            if (strpos($response, 'pages') !== false || strpos($response, 'Browse') !== false) {
                echo "✅ Response contains expected content\n";
            } else {
                echo "⚠️  Response may not contain expected content\n";
                echo "📄 Response preview: " . substr($response, 0, 200) . "...\n";
            }
        } else {
            echo "❌ Pages route returned status code: " . $httpCode . "\n";
            echo "📄 Response: " . substr($response, 0, 500) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing web route: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n"; 