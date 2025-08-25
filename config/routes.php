<?php

declare(strict_types=1);

use IslamWiki\Http\Controllers\Auth\AuthController;
use IslamWiki\Http\Controllers\HomeController;
use IslamWiki\Http\Controllers\WikiController;
use IslamWiki\Http\Controllers\SearchController;
use IslamWiki\Http\Middleware\AuthMiddleware;
use IslamWiki\Http\Middleware\AdminMiddleware;
use IslamWiki\Http\Middleware\GuestMiddleware;

return function ($app) {
    // Minimal routes file - using only essential working controllers
    
    $router = $app->getRouter();
    $container = $app->getContainer();
    
    // Create only the essential controllers
    try {
        $authController = new AuthController($container->get('db'), $container);
        $homeController = new HomeController($container->get('db'), $container);
        $wikiController = new WikiController($container->get('db'), $container);
        $searchController = new SearchController($container->get('db'), $container);
        
        echo "✓ All essential controllers created successfully\n";
    } catch (Exception $e) {
        echo "✗ Controller creation failed: " . $e->getMessage() . "\n";
        return;
    }
    
    // Create middleware instances
    $authMiddleware = new AuthMiddleware($container);
    $adminMiddleware = new AdminMiddleware($container);
    $guestMiddleware = new GuestMiddleware($container);
    
    // Test routes
    $router->get("/test-simple", function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ["Content-Type" => "text/html"], "Simple test route works!");
    });
    
    // ===== ESSENTIAL ROUTES =====
    // Root and Home
    $router->get("/", [$homeController, "index"]);
    $router->get("/en", [$homeController, "index"]);
    $router->get("/ar", [$homeController, "index"]);
    
    // Authentication (most important for login)
    $router->get("/en/login", [$authController, "showLogin"]);
    $router->get("/ar/login", [$authController, "showLogin"]);
    $router->post("/en/login", [$authController, "login"]);
    $router->post("/ar/login", [$authController, "login"]);
    $router->get("/en/register", [$authController, "showRegister"]);
    $router->get("/ar/register", [$authController, "showRegister"]);
    $router->post("/en/register", [$authController, "register"]);
    $router->post("/ar/register", [$authController, "register"]);
    
    // Wiki (for home page)
    $router->get("/en/wiki", [$wikiController, "index"]);
    $router->get("/ar/wiki", [$wikiController, "index"]);
    $router->get("/en/wiki/Home", [$wikiController, "show"]);
    $router->get("/ar/wiki/Home", [$wikiController, "show"]);
    
    // Search
    $router->get("/en/search", [$searchController, "index"]);
    $router->get("/ar/search", [$searchController, "index"]);
    
    echo "✓ Essential routes registered successfully\n";
};
