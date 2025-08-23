<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\HomeController;
use IslamWiki\Http\Controllers\WikiController;
use IslamWiki\Http\Controllers\Auth\AuthController;

return function (\IslamWiki\Core\NizamApplication $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);

    // Register auth service manually since extension is not auto-loaded
    if (!$container->has('auth')) {
        try {
            $session = $container->get('session');
            $auth = new \IslamWiki\Core\Auth\AmanSecurity($session, $db);
            $container->set(\IslamWiki\Core\Auth\AmanSecurity::class, $auth);
            $container->alias('auth', \IslamWiki\Core\Auth\AmanSecurity::class);
            $container->alias('aman.security', \IslamWiki\Core\Auth\AmanSecurity::class);
            error_log('Auth service registered successfully');
        } catch (\Exception $e) {
            error_log('Failed to register auth service: ' . $e->getMessage());
        }
    } else {
        error_log('Auth service already exists in container');
    }

    // Create controllers
    $pageController = new PageController($db, $container);
    $homeController = new HomeController($db, $container);
    $wikiController = new WikiController($db, $container);
    $authController = new AuthController($db, $container);

    // Root redirect - handled by .htaccess to /wiki/Main_Page
    // $app->get('/', [$homeController, 'index']);

    // Authentication routes
    $app->post('/auth/login', [$authController, 'login']);
    $app->post('/auth/register', [$authController, 'register']);
    $app->get('/auth/logout', [$authController, 'logout']);
    
    // Auth page routes
    $app->get('/login', [$authController, 'showLogin']);
    $app->get('/register', [$authController, 'showRegister']);

    // Essential wiki routes
    $app->get('/wiki', [$wikiController, 'index']);
    $app->get('/wiki/Main_Page', [$wikiController, 'showMainPage']);
    $app->get('/wiki/Special:Preferences', [$authController, 'showPreferences']);
    $app->post('/wiki/Special:Preferences', [$authController, 'updatePreferences']);
    $app->get('/wiki/{slug}', [$pageController, 'show']);
    $app->get('/wiki/{slug}/history', [$pageController, 'history']);
    $app->get('/wiki/{slug}/edit', [$pageController, 'edit']);
    $app->post('/wiki/{slug}', [$pageController, 'update']);
    $app->delete('/wiki/{slug}', [$pageController, 'destroy']);
    $app->get('/create', [$pageController, 'create']);
    $app->post('/create', [$pageController, 'store']);
    
    // Test route to verify routing is working
    $app->get('/test-routing', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], 'Routing test works!');
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
            <a href="/wiki/Main_Page">← Back to Main Page</a>
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
            <a href="/wiki/Main_Page">← Back to Main Page</a>
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
            <a href="/wiki/Main_Page">← Back to Main Page</a>
        </body>
        </html>
        ');
    });
    
    // Dashboard route
    $app->get('/dashboard', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '
        <h1>Dashboard</h1>
        <p>Dashboard functionality coming soon...</p>
        <a href="/wiki/Main_Page">← Back to Main Page</a>
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
    
    $app->get('/skins/Bismillah/css/pages/auth.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/auth.css';
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
    
    $app->get('/skins/Bismillah/css/pages/preferences.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/preferences.css';
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
