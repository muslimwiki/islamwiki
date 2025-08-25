<?php

declare(strict_types=1);

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;

return function (\IslamWiki\Core\Application $app) {
    error_log('AR Routes: Loading Arabic language routes');
    
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

    // Arabic-specific routes (with /ar prefix)
    
    // Home and main pages
    $router->get('/ar', [$wikiController, 'dashboard']);
    $router->get('/ar/wiki/Home', [$wikiController, 'showMainPage']);
    $router->get('/ar/wiki', [$wikiController, 'dashboard']);
    $router->get('/ar/wiki/index', [$wikiController, 'index']);
    $router->get('/ar/wiki/create', [$wikiController, 'showCreatePage']);
    
    // Authentication routes
    $router->post('/ar/auth/login', [$authController, 'login']);
    $router->post('/ar/auth/register', [$authController, 'register']);
    $router->get('/ar/auth/logout', [$authController, 'logout']);
    $router->get('/ar/login', [$authController, 'showLogin']);
    $router->get('/ar/register', [$authController, 'showRegister']);
    
    // Dashboard routes
    $router->get('/ar/dashboard', [$dashboardController, 'index']);
    $router->get('/ar/admin/dashboard', [$dashboardController, 'admin']);
    
    // Search routes
    $router->get('/ar/search', [$wikiController, 'enhancedSearch']);
    $router->get('/ar/api/search/suggestions', [$searchController, 'getSuggestions']);
    
    // Wiki page routes
    $router->get('/ar/wiki/{slug}', [$pageController, 'show']);
    $router->get('/ar/wiki/{slug}/edit', [$pageController, 'edit']);
    $router->post('/ar/wiki/{slug}', [$pageController, 'update']);
    $router->delete('/ar/wiki/{slug}', [$pageController, 'destroy']);
    
    // Settings and profile routes
    $router->get('/ar/settings', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <title>الإعدادات - إسلام ويكي</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>الإعدادات</h1>
            <p>صفحة الإعدادات قريباً...</p>
            <a href="/ar/wiki/Home">← العودة إلى الصفحة الرئيسية</a>
        </body>
        </html>
        ');
    });
    
    $router->get('/ar/profile', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <title>الملف الشخصي - إسلام ويكي</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
        </head>
        <body>
            <h1>الملف الشخصي</h1>
            <p>صفحة الملف الشخصي قريباً...</p>
            <a href="/ar/wiki/Home">← العودة إلى الصفحة الرئيسية</a>
        </body>
        </html>
        ');
    });
    
    error_log('AR Routes: Arabic language routes loaded successfully');
}; 