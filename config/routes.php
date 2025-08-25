<?php

declare(strict_types=1);

use IslamWiki\Http\Controllers\Simple\SimpleController;
use IslamWiki\Http\Middleware\AuthMiddleware;
use IslamWiki\Http\Middleware\AdminMiddleware;
use IslamWiki\Http\Middleware\GuestMiddleware;

return function (\IslamWiki\Core\Application $app) {
    // Routes file - using SimpleController with middleware
    
    $router = $app->getRouter();
    $container = $app->getContainer();
    
    // Create controller and middleware instances
    $controller = new SimpleController();
    $authMiddleware = new AuthMiddleware($container);
    $adminMiddleware = new AdminMiddleware($container);
    $guestMiddleware = new GuestMiddleware($container);
    
    // Test routes (keep these for now)
    $router->get("/test-simple", function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ["Content-Type" => "text/html"], "Simple test route works!");
    });
    
    $router->get("/test-container", function ($request) use ($app) {
        try {
            $container = $app->getContainer();
            $router = $app->getRouter();
            return new \IslamWiki\Core\Http\Response(200, ["Content-Type" => "text/html"], "Container and Router accessible!");
        } catch (Exception $e) {
            return new \IslamWiki\Core\Http\Response(200, ["Content-Type" => "text/html"], "Error: " . $e->getMessage());
        }
    });
    
    // Root route
    $router->get("/", [$controller, "welcome"]);
    
    // English routes
    $router->get("/en", [$controller, "englishMessage"]);
    
    // Arabic routes  
    $router->get("/ar", [$controller, "arabicMessage"]);
    
    // Guest routes (login/register) - only for non-authenticated users
    $router->get("/en/login", [$controller, "welcome"]);
    $router->get("/ar/login", [$controller, "welcome"]);
    $router->post("/en/login", [$controller, "login"]);
    $router->post("/ar/login", [$controller, "login"]);
    $router->get("/en/register", [$controller, "welcome"]);
    $router->get("/ar/register", [$controller, "welcome"]);
    $router->post("/en/register", [$controller, "register"]);
    $router->post("/ar/register", [$controller, "register"]);
    
    // Protected routes - require authentication
    $router->get("/en/dashboard", [$controller, "dashboard"]);
    $router->get("/ar/dashboard", [$controller, "dashboard"]);
    $router->get("/en/settings", [$controller, "settings"]);
    $router->get("/ar/settings", [$controller, "settings"]);
    $router->get("/en/profile", [$controller, "profile"]);
    $router->get("/ar/profile", [$controller, "profile"]);
    $router->get("/en/bookmarks", [$controller, "bookmarks"]);
    $router->get("/ar/bookmarks", [$controller, "bookmarks"]);
    $router->get("/en/notifications", [$controller, "notifications"]);
    $router->get("/ar/notifications", [$controller, "notifications"]);
    $router->get("/en/messages", [$controller, "messages"]);
    $router->get("/ar/messages", [$controller, "messages"]);
    $router->get("/en/messages/{id}", [$controller, "message"]);
    $router->get("/ar/messages/{id}", [$controller, "message"]);
    
    // Admin routes - require admin privileges
    $router->get("/en/admin/dashboard", [$controller, "adminDashboard"]);
    $router->get("/ar/admin/dashboard", [$controller, "adminDashboard"]);
    $router->get("/en/admin/skins", [$controller, "adminSkins"]);
    $router->get("/ar/admin/skins", [$controller, "adminSkins"]);
    $router->get("/en/admin/users", [$controller, "adminUsers"]);
    $router->get("/ar/admin/users", [$controller, "adminUsers"]);
    
    // Public routes - no authentication required
    $router->get("/en/wiki/Home", [$controller, "wikiPage"]);
    $router->get("/ar/wiki/Home", [$controller, "wikiPage"]);
    $router->get("/en/search", [$controller, "search"]);
    $router->get("/ar/search", [$controller, "search"]);
    $router->post("/en/search", [$controller, "searchSubmit"]);
    $router->post("/ar/search", [$controller, "searchSubmit"]);
    $router->get("/en/forums", [$controller, "forums"]);
    $router->get("/ar/forums", [$controller, "forums"]);
    $router->get("/en/forums/{category}", [$controller, "forumCategory"]);
    $router->get("/ar/forums/{category}", [$controller, "forumCategory"]);
    $router->get("/en/calendar", [$controller, "calendar"]);
    $router->get("/ar/calendar", [$controller, "calendar"]);
    $router->get("/en/calendar/{year}", [$controller, "calendarYear"]);
    $router->get("/ar/calendar/{year}", [$controller, "calendarYear"]);
    $router->get("/en/salah", [$controller, "salah"]);
    $router->get("/ar/salah", [$controller, "salah"]);
    $router->get("/en/salah/{city}", [$controller, "salahCity"]);
    $router->get("/ar/salah/{city}", [$controller, "salahCity"]);
    $router->get("/en/docs", [$controller, "docs"]);
    $router->get("/ar/docs", [$controller, "docs"]);
    $router->get("/en/docs/{section}", [$controller, "docsSection"]);
    $router->get("/ar/docs/{section}", [$controller, "docsSection"]);
    $router->get("/en/community", [$controller, "community"]);
    $router->get("/ar/community", [$controller, "community"]);
    $router->get("/en/help", [$controller, "help"]);
    $router->get("/ar/help", [$controller, "help"]);
    $router->get("/en/about", [$controller, "about"]);
    $router->get("/ar/about", [$controller, "about"]);
    $router->get("/en/contact", [$controller, "contact"]);
    $router->get("/ar/contact", [$controller, "contact"]);
    $router->get("/en/privacy", [$controller, "privacy"]);
    $router->get("/ar/privacy", [$controller, "privacy"]);
    $router->get("/en/terms", [$controller, "terms"]);
    $router->get("/ar/terms", [$controller, "terms"]);
    $router->get("/en/fatwas", [$controller, "fatwas"]);
    $router->get("/ar/fatwas", [$controller, "fatwas"]);
    $router->get("/en/fatwas/{category}", [$controller, "fatwaCategory"]);
    $router->get("/ar/fatwas/{category}", [$controller, "fatwaCategory"]);
    $router->get("/en/scholars", [$controller, "scholars"]);
    $router->get("/ar/scholars", [$controller, "scholars"]);
    $router->get("/en/scholars/{name}", [$controller, "scholarProfile"]);
    $router->get("/ar/scholars/{name}", [$controller, "scholarProfile"]);
    $router->get("/en/learn", [$controller, "learn"]);
    $router->get("/ar/learn", [$controller, "learn"]);
    $router->get("/en/learn/{topic}", [$controller, "learningTopic"]);
    $router->get("/ar/learn/{topic}", [$controller, "learningTopic"]);
    $router->get("/en/events", [$controller, "events"]);
    $router->get("/ar/events", [$controller, "events"]);
    $router->get("/en/events/{id}", [$controller, "eventDetails"]);
    $router->get("/ar/events/{id}", [$controller, "eventDetails"]);
    $router->get("/en/news", [$controller, "news"]);
    $router->get("/ar/news", [$controller, "news"]);
    $router->get("/en/news/{id}", [$controller, "newsArticle"]);
    $router->get("/ar/news/{id}", [$controller, "newsArticle"]);
    $router->get("/en/translate", [$controller, "translate"]);
    $router->get("/ar/translate", [$controller, "translate"]);
    $router->post("/en/translate", [$controller, "translateSubmit"]);
    $router->post("/ar/translate", [$controller, "translateSubmit"]);
    $router->get("/en/media", [$controller, "media"]);
    $router->get("/ar/media", [$controller, "media"]);
    $router->get("/en/media/{type}", [$controller, "mediaType"]);
    $router->get("/ar/media/{type}", [$controller, "mediaType"]);
    $router->get("/en/feedback", [$controller, "feedback"]);
    $router->get("/ar/feedback", [$controller, "feedback"]);
    $router->post("/en/feedback", [$controller, "feedbackSubmit"]);
    $router->post("/ar/feedback", [$controller, "feedbackSubmit"]);
    $router->get("/en/stats", [$controller, "stats"]);
    $router->get("/ar/stats", [$controller, "stats"]);
    $router->get("/en/stats/{period}", [$controller, "statsPeriod"]);
    $router->get("/ar/stats/{period}", [$controller, "statsPeriod"]);
    $router->get("/en/export", [$controller, "export"]);
    $router->get("/ar/export", [$controller, "export"]);
    $router->post("/en/export", [$controller, "exportSubmit"]);
    $router->post("/ar/export", [$controller, "exportSubmit"]);
    
    // Extension routes
    $router->get("/en/quran", [$controller, "quran"]);
    $router->get("/ar/quran", [$controller, "quran"]);
    $router->get("/en/hadith", [$controller, "hadith"]);
    $router->get("/ar/hadith", [$controller, "hadith"]);
    
    // Advanced routes - Dynamic wiki pages
    $router->get("/en/wiki/{page}", [$controller, "wikiPage"]);
    $router->get("/ar/wiki/{page}", [$controller, "wikiPage"]);
    
    // Advanced routes - User profiles
    $router->get("/en/user/{username}", [$controller, "userProfile"]);
    $router->get("/ar/user/{username}", [$controller, "userProfile"]);
    
    // Advanced routes - API endpoints
    $router->get("/en/api/users", [$controller, "apiUsers"]);
    $router->get("/ar/api/users", [$controller, "apiUsers"]);
    
    // Advanced routes - PUT and DELETE methods
    $router->put("/en/api/users/{id}", [$controller, "updateUser"]);
    $router->delete("/en/api/users/{id}", [$controller, "deleteUser"]);
    
    // POST routes for protected features
    $router->post("/en/bookmarks", [$controller, "bookmarkSubmit"]);
    $router->post("/ar/bookmarks", [$controller, "bookmarkSubmit"]);
    $router->post("/en/notifications/mark-read", [$controller, "markNotificationRead"]);
    $router->post("/ar/notifications/mark-read", [$controller, "markNotificationRead"]);
    
    // Note: In a real application, middleware would be applied to routes like this:
    // $router->get("/en/dashboard", [$controller, "dashboard"])->middleware($authMiddleware);
    // $router->get("/en/admin/dashboard", [$controller, "adminDashboard"])->middleware($adminMiddleware);
    // $router->get("/en/login", [$controller, "welcome"])->middleware($guestMiddleware);
    
    // For now, we're just organizing routes by protection level
    // The actual middleware application would be handled by the router system
};
