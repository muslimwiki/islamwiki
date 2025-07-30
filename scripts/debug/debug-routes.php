<?php
declare(strict_types=1);

// Start output buffering
ob_start();

echo "=== Route Debug Test ===<br>";

try {
    echo "Step 1: Loading autoloader...<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Step 2: Autoloader loaded successfully<br>";
    
    echo "Step 3: Creating Application...<br>";
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "Step 4: Application created successfully<br>";
    
    echo "Step 5: Creating IslamRouter...<br>";
    $router = new \IslamWiki\Core\Routing\IslamRouter($app->getContainer());
    echo "Step 6: IslamRouter created successfully<br>";
    
    echo "Step 7: Adding test routes...<br>";
    $router->get('/debug-routes', function($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Debug routes test works!');
    });
    $router->get('/test-simple', function($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Simple test works!');
    });
    echo "Step 8: Test routes added successfully<br>";
    
    echo "Step 9: Loading web routes...<br>";
    $webRoutesPath = __DIR__ . '/../routes/web.php';
    if (file_exists($webRoutesPath)) {
        echo "Step 10: Web routes file exists<br>";
        // Store router in variable for routes file
        $routerForRoutes = $router;
        require $webRoutesPath;
        echo "Step 11: Web routes loaded successfully<br>";
    } else {
        echo "Step 10: ERROR - Web routes file not found at: $webRoutesPath<br>";
    }
    
    echo "Step 12: Creating PSR-7 request...<br>";
    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    echo "Step 13: PSR-7 request created successfully<br>";
    
    echo "Step 14: Request URI is: " . $request->getUri()->getPath() . "<br>";
    
    echo "Step 15: Testing router->handle()...<br>";
    $response = $router->handle($request);
    echo "Step 16: Router->handle() completed successfully<br>";
    
    echo "Step 17: Clearing output buffer...<br>";
    ob_end_clean();
    
    echo "Step 18: Sending response...<br>";
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $response->getBody();
    echo "<br>Step 19: Response sent successfully<br>";
    
} catch (\Throwable $e) {
    ob_end_clean();
    echo "<br><strong>ERROR: " . $e->getMessage() . "</strong><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 