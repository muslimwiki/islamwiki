<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;

// Create application
$app = new NizamApplication(__DIR__ . '/..');
$app->bootstrap();

// Create request
$request = Request::capture();

// Test homepage route
$router = $app->getContainer()->get('router');
$router->get('/test-homepage', function ($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '<h1>Homepage test works!</h1>');
});

// Handle request
$response = $router->handle($request);
$response->send();
