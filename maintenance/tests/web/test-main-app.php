<?php
declare(strict_types=1);

// Start output buffering
ob_start();

echo "=== Main App Test ===<br>";

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
    
    echo "Step 7: Loading web routes...<br>";
    $webRoutesPath = __DIR__ . '/../routes/web.php';
    if (file_exists($webRoutesPath)) {
        echo "Step 8: Web routes file exists<br>";
        // Store router in variable for routes file
        $routerForRoutes = $router;
        require $webRoutesPath;
        echo "Step 9: Web routes loaded successfully<br>";
    } else {
        echo "Step 8: ERROR - Web routes file not found at: $webRoutesPath<br>";
    }
    
    echo "Step 10: Creating PSR-7 request...<br>";
    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    echo "Step 11: PSR-7 request created successfully<br>";
    
    echo "Step 12: Request URI is: " . $request->getUri()->getPath() . "<br>";
    
    echo "Step 13: Testing router->handle()...<br>";
    $psrResponse = $router->handle($request);
    echo "Step 14: Router->handle() completed successfully<br>";
    
    echo "Step 15: Testing response conversion...<br>";
    $body = $psrResponse->getBody();
    $bodyContents = $body->getContents();
    echo "Step 16: Response body retrieved successfully<br>";
    
    echo "Step 17: Creating our Response object...<br>";
    $response = new \IslamWiki\Core\Http\Response(
        $psrResponse->getStatusCode(),
        $psrResponse->getHeaders(),
        $bodyContents
    );
    echo "Step 18: Our Response object created successfully<br>";
    
    echo "Step 19: Clearing output buffer...<br>";
    ob_end_clean();
    
    echo "Step 20: Sending response...<br>";
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $response->getBody();
    echo "<br>Step 21: Response sent successfully<br>";
    
} catch (\Throwable $e) {
    ob_end_clean();
    echo "<br><strong>ERROR: " . $e->getMessage() . "</strong><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 