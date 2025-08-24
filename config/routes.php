<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

return function (\IslamWiki\Core\Application $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);
    $router = $app->getRouter();

    // Register auth service manually since extension is not auto-loaded
    if (!$container->has('auth')) {
        try {
            $session = $container->get('session');
            $auth = new \IslamWiki\Core\Auth\Security($session, $db, $container->get('logger'), []);
            $container->set(\IslamWiki\Core\Auth\Security::class, $auth);
            $container->alias('auth', \IslamWiki\Core\Auth\Security::class);
            $container->alias('security', \IslamWiki\Core\Auth\Security::class);
        } catch (\Exception $e) {
            error_log('Failed to register auth service: ' . $e->getMessage());
        }
    }

    // Create controllers
    $pageController = new \IslamWiki\Http\Controllers\PageController($db, $container);
    $homeController = new \IslamWiki\Http\Controllers\HomeController($db, $container);
    $wikiController = new \IslamWiki\Http\Controllers\WikiController($db, $container);
    $authController = new \IslamWiki\Http\Controllers\Auth\AuthController($db, $container);
    $dashboardController = new \IslamWiki\Http\Controllers\DashboardController($db, $container);
    $searchController = new \IslamWiki\Http\Controllers\SearchController($db, $container);

    // Test route to verify routing is working
    $router->get('/test-routing', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], 'Routing test works!');
    });

    // Essential wiki routes
    $router->get('/wiki/Home', [$wikiController, 'showMainPage']);
    $router->get('/wiki', [$wikiController, 'dashboard']);
    $router->get('/wiki/index', [$wikiController, 'index']);
    $router->get('/wiki/create', [$wikiController, 'showCreatePage']);

    // Authentication routes
    $router->post('/auth/login', [$authController, 'login']);
    $router->post('/auth/register', [$authController, 'register']);
    $router->get('/auth/logout', [$authController, 'logout']);
    $router->get('/login', [$authController, 'showLogin']);
    $router->get('/register', [$authController, 'showRegister']);

    // Dashboard route
    $router->get('/dashboard', [$dashboardController, 'index']);

    // Search routes
    $router->get('/search', [$wikiController, 'enhancedSearch']);
    $router->get('/api/search/suggestions', [$searchController, 'getSuggestions']);

    // Skin management routes (using core skin services)
    $router->get('/admin/skins', function ($request) use ($container) {
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
        
        $html .= '</ul><a href="/wiki/Home">← Back to Home Page</a></body></html>';
        
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    });

    $router->post('/admin/skins/{skinName}/activate', function ($request, $params) use ($container) {
        $skinName = $params['skinName'];
        $skinManager = $container->get('skin.manager');
        
        if ($skinManager->setActiveSkin($skinName)) {
            $html = '<!DOCTYPE html><html><head><title>Skin Activated - IslamWiki</title>';
            $html .= '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
            $html .= '</head><body><h1>Skin Activated Successfully</h1>';
            $html .= '<p>The skin <strong>' . htmlspecialchars($skinName) . '</strong> has been activated.</p>';
            $html .= '<a href="/admin/skins">← Back to Skin Management</a></body></html>';
            
            return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
        } else {
            $html = '<!DOCTYPE html><html><head><title>Skin Activation Failed - IslamWiki</title>';
            $html .= '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
            $html .= '</head><body><h1>Skin Activation Failed</h1>';
            $html .= '<p>The skin <strong>' . htmlspecialchars($skinName) . '</strong> could not be activated.</p>';
            $html .= '<a href="/admin/skins">← Back to Skin Management</a></body></html>';
            
            return new \IslamWiki\Core\Http\Response(400, ['Content-Type' => 'text/html'], $html);
        }
    });

    // Page routes
    $router->get('/wiki/{slug}', [$pageController, 'show']);
    $router->get('/wiki/{slug}/edit', [$pageController, 'edit']);
    $router->post('/wiki/{slug}', [$pageController, 'update']);
    $router->delete('/wiki/{slug}', [$pageController, 'destroy']);

    // Settings route
    $router->get('/settings', function ($request) {
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
            <a href="/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });

    // Profile route
    $router->get('/profile', function ($request) {
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
            <a href="/wiki/Home">← Back to Home Page</a>
        </body>
        </html>
        ');
    });
};
