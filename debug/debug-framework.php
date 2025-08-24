<?php

error_log('=== Starting framework debug ===');

try {
	error_log('Loading autoloader...');
	require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
	error_log('Autoloader loaded successfully');

	error_log('Creating Application...');
	$app = new \IslamWiki\Core\Application 2));
	$app->boot();
	error_log('Application booted successfully');

	error_log('Creating SabilRouting...');
	$router = new \IslamWiki\Core\Routing\SabilRouting($app->getContainer());
	error_log('SabilRouting created successfully');

	error_log('Adding test route...');
	$router->get('/debug-framework', function ($request) {
		return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Framework debug works!');
	});
	error_log('Test route added successfully');

	error_log('Loading web routes...');
	require dirname(__DIR__, 2) . '/routes/web.php';
	error_log('Web routes loaded successfully');

	$uri = '/debug-framework';
	$method = 'GET';
	$server = [
		'REQUEST_METHOD' => $method,
		'REQUEST_URI' => $uri,
		'HTTP_HOST' => 'localhost',
	];
	$request = new \GuzzleHttp\Psr7\ServerRequest($method, 'http://localhost' . $uri, [], null, '1.1', $server);

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
	echo "Framework debug failed: " . $e->getMessage();
}
