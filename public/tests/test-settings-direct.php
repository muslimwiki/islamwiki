<?php
// Direct test of SettingsController without authentication
require_once __DIR__ . '/../src/Core/Application.php';
require_once __DIR__ . '/../src/Http/Controllers/SettingsController.php';

echo "<h1>Direct SettingsController Test</h1>";

try {
    // Create application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Create SettingsController
    $db = $container->get('db');
    $controller = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    echo "✅ SettingsController created successfully<br>";
    
    // Mock session data
    $_SESSION['user_id'] = 1;
    $_SESSION['logged_in'] = true;
    
    echo "✅ Session data mocked<br>";
    
    // Call the index method
    $response = $controller->index();
    
    echo "✅ SettingsController index() called successfully<br>";
    echo "Response status: " . $response->getStatusCode() . "<br>";
    
    // Get the response body
    $body = $response->getBody();
    $body->rewind();
    $content = $body->getContents();
    
    echo "<h2>Response Content:</h2>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 