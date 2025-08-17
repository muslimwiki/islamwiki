<?php

declare(strict_types=1);

use IslamWiki\Core\Http\Response;

/** @var SabilRouting $router */

error_log("TEST ROUTES: test.php is being loaded");

// Simple test route
$router->get('/test-simple', function ($request) {
    error_log("TEST ROUTES: /test-simple route is being called");
    return new Response(200, ['Content-Type' => 'text/plain'], 'Simple test route works!');
});

error_log("TEST ROUTES: test.php has finished loading"); 