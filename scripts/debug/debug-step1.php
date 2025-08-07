<?php

// Start output buffering to prevent headers already sent error
ob_start();

echo "Step 1: Basic PHP works<br>";

try {
    echo "Step 2: Loading autoloader...<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Step 3: Autoloader loaded successfully<br>";

    echo "Step 4: Testing Application class...<br>";
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "Step 5: Application created successfully<br>";

    echo "Step 6: Testing Container...<br>";
    $container = $app->getContainer();
    echo "Step 7: Container retrieved successfully<br>";

    echo "Step 8: Testing IslamRouter creation...<br>";
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    echo "Step 9: IslamRouter created successfully<br>";

    echo "Step 10: Testing route addition...<br>";
    // Add route for the actual request URI
    $router->get('/debug-step1.php', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Step-by-step test works!');
    });
    echo "Step 11: Route added successfully<br>";

    echo "Step 12: Testing PSR-7 request creation...<br>";
    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    echo "Step 13: PSR-7 request created successfully<br>";

    // Debug the request URI
    echo "Step 13.5: Request URI is: " . $request->getUri()->getPath() . "<br>";

    echo "Step 14: Testing router->handle()...<br>";
    $response = $router->handle($request);
    echo "Step 15: Router->handle() completed successfully<br>";

    echo "Step 16: Testing response conversion...<br>";
    $body = $response->getBody();
    $bodyContents = $body->getContents();
    echo "Step 17: Response body retrieved successfully<br>";

    echo "Step 18: Clearing output buffer...<br>";
    ob_end_clean();

    echo "Step 19: Sending response...<br>";
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $bodyContents;
    echo "<br>Step 20: Response sent successfully<br>";
} catch (\Throwable $e) {
    ob_end_clean();
    echo "<br><strong>ERROR at step: " . $e->getMessage() . "</strong><br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
