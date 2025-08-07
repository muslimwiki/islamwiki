<?php

// Simple router test
require __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Http\Response;

// Create a simple response
$response = new Response(
    status: 200,
    headers: ['Content-Type' => 'text/html'],
    body: '<h1>Simple Router Test</h1><p>If you can see this, the router is working!</p>'
);

// Send the response
$response->send();
