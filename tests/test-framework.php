<?php

error_log('=== Starting framework test ===');

try {
    error_log('Loading autoloader...');
    require_once __DIR__ . '/../vendor/autoload.php';
    error_log('Autoloader loaded successfully');

    error_log('Creating Application...');
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    error_log('Application created successfully');

    error_log('Creating IslamRouter...');
    $router = new \IslamWiki\Core\Routing\IslamRouter($app->getContainer());
    error_log('IslamRouter created successfully');

    error_log('Adding test route...');
    $router->get('/test-framework', function ($request) {
        error_log('Test route handler called');
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Framework test works!');
    });
    error_log('Test route added successfully');

    error_log('Creating PSR-7 request...');
    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    error_log('PSR-7 request created successfully');

    error_log('Calling router->handle()...');
    $response = $router->handle($request);
    error_log('Router->handle() completed successfully');

    error_log('Sending response...');
    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }
    echo $response->getBody();
    error_log('Response sent successfully');
} catch (\Throwable $e) {
    error_log('ERROR: ' . $e->getMessage());
    error_log('File: ' . $e->getFile() . ':' . $e->getLine());
    error_log('Stack trace: ' . $e->getTraceAsString());

    http_response_code(500);
    echo "Framework test failed: " . $e->getMessage();
}
