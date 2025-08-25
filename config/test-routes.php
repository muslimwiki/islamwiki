<?php

declare(strict_types=1);

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

return function (\IslamWiki\Core\Application $app) {
    error_log('TEST ROUTES: Starting test route registration');
    
    $router = $app->getRouter();
    
    // Simple test route
    $router->get('/test-simple', function ($request) {
        error_log('TEST ROUTES: Test route handler called');
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], 'Simple test route works!');
    });
    
    error_log('TEST ROUTES: Test route registered successfully');
}; 