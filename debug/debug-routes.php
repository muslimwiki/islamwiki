<?php

declare(strict_types=1);

// Start output buffering
ob_start();

echo "=== Route Debug Test ===<br>";

try {
    echo "Step 1: Loading autoloader...<br>";
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
    echo "Step 2: Autoloader loaded successfully<br>";

    echo "Step 3: Creating Application...<br>";
    $app = new \IslamWiki\Core\Application 2));
    $app->boot();
    echo "Step 4: Application booted successfully<br>";

    echo "Step 5: Creating SabilRouting...<br>";
    $router = new \IslamWiki\Core\Routing\SabilRouting($app->getContainer());
    echo "Step 6: SabilRouting created successfully<br>";

    echo "Step 7: Adding test routes...<br>";
    $router->get('/debug-routes', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Debug routes test works!');
    });
    $router->get('/test-simple', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Simple test works!');
    });
    echo "Step 8: Test routes added successfully<br>";

    echo "Step 9: Loading web routes...<br>";
    $webRoutesPath = dirname(__DIR__, 2) . '/routes/web.php';
    if (file_exists($webRoutesPath)) {
        echo "Step 10: Web routes file exists<br>";
        require $webRoutesPath;
        echo "Step 11: Web routes loaded successfully<br>";
    } else {
        echo "Step 10: ERROR - Web routes file not found at: $webRoutesPath<br>";
    }

    echo "Step 12: Creating PSR-7 request...<br>";
    $uri = '/debug-routes';
    $method = 'GET';
    $server = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'HTTP_HOST' => 'localhost',
    ];
    $request = new \GuzzleHttp\Psr7\ServerRequest($method, 'http://localhost' . $uri, [], null, '1.1', $server);
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
