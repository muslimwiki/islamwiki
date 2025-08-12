<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Create application
$app = new NizamApplication(__DIR__ . '/..');
$app->bootstrap();

// Create request
$request = Request::capture();

// Test simple route
$router = $app->getContainer()->get('router');
$router->get('/test-simple-route', function ($request) {
    return new Response(200, ['Content-Type' => 'text/html'], '<h1>Simple route test works!</h1>');
});

// Handle request
$response = $router->handle($request);
$response->send();
