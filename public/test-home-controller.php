<?php
declare(strict_types=1);

// Start output buffering
ob_start();

echo "=== HomeController Test ===<br>";

try {
    echo "Step 1: Loading autoloader...<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Step 2: Autoloader loaded successfully<br>";
    
    echo "Step 3: Creating Application...<br>";
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "Step 4: Application created successfully<br>";
    
    echo "Step 5: Getting container...<br>";
    $container = $app->getContainer();
    echo "Step 6: Container retrieved successfully<br>";
    
    echo "Step 7: Getting database connection...<br>";
    $db = $container->get(\IslamWiki\Core\Database\Connection::class);
    echo "Step 8: Database connection retrieved successfully<br>";
    
    echo "Step 9: Creating HomeController...<br>";
    $controller = new \IslamWiki\Http\Controllers\HomeController($db, $container);
    echo "Step 10: HomeController created successfully<br>";
    
    echo "Step 11: Creating test request...<br>";
    $request = \IslamWiki\Core\Http\Request::capture();
    echo "Step 12: Test request created successfully<br>";
    
    echo "Step 13: Calling HomeController@index...<br>";
    $response = $controller->index($request);
    echo "Step 14: HomeController@index completed successfully<br>";
    
    echo "Step 15: Clearing output buffer...<br>";
    ob_end_clean();
    
    echo "Step 16: Sending response...<br>";
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $response->getBody();
    echo "<br>Step 17: Response sent successfully<br>";
    
} catch (\Throwable $e) {
    ob_end_clean();
    echo "<br><strong>ERROR: " . $e->getMessage() . "</strong><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 