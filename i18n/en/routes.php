<?php

declare(strict_types=1);

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;

return function (\IslamWiki\Core\Application $app) {
    error_log('EN Routes: Loading English language routes');
    
    $container = $app->getContainer();
    $db = $container->get(Connection::class);
    $router = $app->getRouter();
    
    // Create controllers
    $pageController = new \IslamWiki\Http\Controllers\PageController($db, $container);
    $homeController = new \IslamWiki\Http\Controllers\HomeController($db, $container);
    $wikiController = new \IslamWiki\Http\Controllers\WikiController($db, $container);
    $authController = new \IslamWiki\Http\Controllers\Auth\AuthController($db, $container);
    $dashboardController = new \IslamWiki\Http\Controllers\DashboardController($db, $container);
    $searchController = new \IslamWiki\Http\Controllers\SearchController($db, $container);

    // English-specific routes (with /en prefix)
    
    // Home and main pages
    $router->get('/en', [$wikiController, 'dashboard']);
    $router->get('/en/wiki/Home', [$wikiController, 'showMainPage']);
    $router->get('/en/wiki', [$wikiController, 'dashboard']);
    $router->get('/en/wiki/index', [$wikiController, 'index']);
    $router->get('/en/wiki/create', [$wikiController, 'showCreatePage']);
    
    // Authentication routes
    $router->post('/en/auth/login', [$authController, 'login']);
    $router->post('/en/auth/register', [$authController, 'register']);
    $router->get('/en/auth/logout', [$authController, 'logout']);
    $router->get('/en/login', [$authController, 'showLogin']);
    $router->get('/en/register', [$authController, 'showRegister']);
    
    // Dashboard routes
    $router->get('/en/dashboard', [$dashboardController, 'index']);
    $router->get('/en/admin/dashboard', [$dashboardController, 'admin']);
    
    // Search routes
    $router->get('/en/search', [$wikiController, 'enhancedSearch']);
    $router->get('/en/api/search/suggestions', [$searchController, 'getSuggestions']);
    
    // Wiki page routes
    $router->get('/en/wiki/{slug}', [$pageController, 'show']);
    $router->get('/en/wiki/{slug}/edit', [$pageController, 'edit']);
    $router->post('/en/wiki/{slug}', [$pageController, 'update']);
    $router->delete('/en/wiki/{slug}', [$pageController, 'destroy']);
    
    // Settings and profile routes
    $router->get('/en/settings', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
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
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/en/profile', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
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
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    // Extension routes
    $router->get('/en/quran', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Quran - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Quran</h1>
            <p>Quran extension coming soon...</p>
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/en/hadith', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hadith - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Hadith</h1>
            <p>Hadith extension coming soon...</p>
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/en/salah', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Salah Times - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Salah Times</h1>
            <p>Salah times extension coming soon...</p>
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/en/calendar', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hijri Calendar - IslamWiki</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>Hijri Calendar</h1>
            <p>Hijri calendar extension coming soon...</p>
            <a href="/en/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/en/admin/skins', function ($request) use ($container) {
        $skinManager = $container->get('skin.manager');
        $skins = $skinManager->getAvailableSkins();
        
        $html = '<!DOCTYPE html><html><head><title>Skin Management - IslamWiki</title>';
        $html .= '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        $html .= '</head><body><h1>Skin Management</h1>';
        $html .= '<p>Active Skin: <strong>' . htmlspecialchars($skinManager->getActiveSkinName()) . '</strong></p>';
        $html .= '<h2>Available Skins:</h2><ul>';
        
        foreach ($skins as $name => $config) {
            $isActive = $name === $skinManager->getActiveSkinName() ? ' (Active)' : '';
            $html .= '<li><strong>' . htmlspecialchars($name) . '</strong>' . htmlspecialchars($isActive) . '</li>';
            $html .= '<ul><li>Description: ' . htmlspecialchars($config['description'] ?? 'No description') . '</li>';
            $html .= '<li>Version: ' . htmlspecialchars($config['version'] ?? 'Unknown') . '</li>';
            $html .= '<li>Author: ' . htmlspecialchars($config['author'] ?? 'Unknown') . '</li></ul>';
        }
        
        $html .= '</ul><a href="/en/wiki/Home">← Back to Home Page</a></body></html>';
        
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    });
    
    error_log('EN Routes: English language routes loaded successfully');
}; 