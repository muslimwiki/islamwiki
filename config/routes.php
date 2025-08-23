<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\HomeController;

return function (\IslamWiki\Core\NizamApplication $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);

    // Create controllers
    $pageController = new PageController($db, $container);
    $homeController = new HomeController($db, $container);

    // Homepage - show beautiful main page
    $app->get('/', [$homeController, 'index']);

    // Essential wiki routes
    $app->get('/wiki', [$pageController, 'index']);
    $app->get('/wiki/{slug}', [$pageController, 'show']);
    $app->get('/wiki/{slug}/history', [$pageController, 'history']);
    $app->get('/wiki/{slug}/edit', [$pageController, 'edit']);
    $app->post('/wiki/{slug}', [$pageController, 'update']);
    $app->delete('/wiki/{slug}', [$pageController, 'destroy']);
    $app->get('/create', [$pageController, 'create']);
    $app->post('/create', [$pageController, 'store']);
    
    // Test route to verify routing is working
    $app->get('/test-routing', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Routing test works!');
    });
    
    // Search route
    $app->get('/search', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Search - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Search Page</h1>
            <p>Advanced search functionality coming soon...</p>
            <a href="/">← Back to Home</a>
        </body>
        </html>
        ');
    });
    
    // Settings route
    $app->get('/settings', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Settings - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Settings</h1>
            <p>Settings page coming soon...</p>
            <a href="/">← Back to Home</a>
        </body>
        </html>
        ');
    });
    
    // Profile route
    $app->get('/profile', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Profile - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Profile</h1>
            <p>Profile page coming soon...</p>
            <a href="/">← Back to Home</a>
        </body>
        </html>
        ');
    });
    
    // Dashboard route
    $app->get('/dashboard', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Dashboard - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Dashboard</h1>
            <p>Dashboard page coming soon...</p>
            <a href="/">← Back to Home</a>
        </body>
        </html>
        ');
    });
    
    // Skin asset routes - serve CSS and JS files directly through Sabil routing
    $app->get('/skins/Bismillah/css/bismillah.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/bismillah.css';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return new \IslamWiki\Core\Http\Response(200, [
                'Content-Type' => 'text/css; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ], $content);
        }
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
    });
    
    $app->get('/skins/Bismillah/js/bismillah.js', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/js/bismillah.js';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return new \IslamWiki\Core\Http\Response(200, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ], $content);
        }
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'JS file not found');
    });
    
    // Page-specific CSS routes
    $app->get('/skins/Bismillah/css/pages/main-page.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/main-page.css';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return new \IslamWiki\Core\Http\Response(200, [
                'Content-Type' => 'text/css; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ], $content);
        }
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
    });
    
    $app->get('/skins/Bismillah/css/pages/settings.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/settings.css';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return new \IslamWiki\Core\Http\Response(200, [
                'Content-Type' => 'text/css; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ], $content);
        }
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
    });
    
    $app->get('/skins/Bismillah/css/pages/dashboard.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/dashboard.css';
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return new \IslamWiki\Core\Http\Response(200, [
                'Content-Type' => 'text/css; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'X-Content-Type-Options' => 'nosniff'
            ], $content);
        }
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
    });
};
