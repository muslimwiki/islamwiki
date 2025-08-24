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

    // Get the router from the application
    $router = $app->getRouter();

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
    $dashboardController = new \IslamWiki\Http\Controllers\DashboardController($db, $container);
    $searchApiController = new \IslamWiki\Http\Controllers\SearchApiController($db, null);

    // Root redirect - handled by .htaccess to /wiki/Main_Page
    // $router->get('/', [$homeController, 'index']);

    // Authentication routes
    $router->post('/auth/login', [$authController, 'login']);
    $router->post('/auth/register', [$authController, 'register']);
    $router->get('/auth/logout', [$authController, 'logout']);
    
    // Auth page routes
    $router->get('/login', [$authController, 'showLogin']);
    $router->get('/register', [$authController, 'showRegister']);

    // Essential wiki routes
    $router->get('/wiki', [$wikiController, 'dashboard']); // Main wiki dashboard
    $router->get('/wiki/index', [$wikiController, 'index']); // All pages list
    $router->get('/wiki/Main_Page', [$wikiController, 'showMainPage']);
    $router->get('/wiki/Special:Preferences', [$authController, 'showPreferences']);
    $router->post('/wiki/Special:Preferences', [$authController, 'updatePreferences']);
    $router->get('/wiki/templates', [$wikiController, 'templates']); // Template gallery
    $router->get('/wiki/drafts', [$wikiController, 'drafts']); // Draft management
    $router->post('/wiki/drafts/{id}/approve', [$wikiController, 'approveDraft']); // Approve draft
    $router->post('/wiki/drafts/{id}/reject', [$wikiController, 'rejectDraft']); // Reject draft
    $router->get('/wiki/create', [$wikiController, 'showCreatePage']); // Simple page creation form
    
    // Collaborative editing routes
    $router->get('/wiki/{slug}/collaborative', [$wikiController, 'getCollaborativeSession']); // Get collaborative session
    $router->post('/wiki/{slug}/collaborative/join', [$wikiController, 'joinCollaborativeSession']); // Join editing session
    $router->post('/wiki/{slug}/collaborative/leave', [$wikiController, 'leaveCollaborativeSession']); // Leave editing session
    $router->post('/wiki/{slug}/collaborative/save', [$wikiController, 'saveCollaborativeEdit']); // Save collaborative edit
    

    
    // Primary search route - used by all domains
$router->get('/search', [$wikiController, 'enhancedSearch']); // Primary search functionality

// Wiki-specific search route - redirects to main search
$router->get('/wiki/search', [$wikiController, 'redirectToSearch']); // Redirect to main search

// Search API routes for live search and advanced features
$router->get('/api/search/suggestions', [$searchApiController, 'getSuggestions']);
$router->post('/api/search/save', [$searchApiController, 'saveSearch']);
$router->get('/api/search/saved', [$searchApiController, 'getSavedSearches']);
$router->delete('/api/search/saved/{id}', [$searchApiController, 'deleteSavedSearch']);
$router->post('/api/search/history/clear', [$searchApiController, 'clearSearchHistory']);
    
    // User namespace routes - must come before generic slug route
    $router->get('/wiki/User/{username}', [$wikiController, 'showUserProfile']);
    
    // Red link handling routes - must come before generic slug route
    $router->get('/wiki/{slug}/redlink', [$wikiController, 'handleRedLink']); // Handle red links
    $router->post('/wiki/{slug}/create', [$wikiController, 'createFromRedLink']); // Create page from red link
    
    // Test route to trigger enhanced error handler
    $router->get('/test-error', function() {
        throw new \Exception('This is a test error to demonstrate the enhanced error handler');
    });
    
    // Test route to trigger WikiController enhanced error handling
    $router->get('/test-wiki-error', [$wikiController, 'testError']);
    
    // Regular wiki page routes
    $router->get('/wiki/{slug}', [$pageController, 'show']);
    $router->get('/wiki/{slug}/history', [$wikiController, 'history']); // Enhanced history with WikiController
    $router->get('/wiki/{slug}/edit', [$pageController, 'edit']);
    $router->post('/wiki/{slug}', [$pageController, 'update']);
    $router->delete('/wiki/{slug}', [$pageController, 'destroy']);
    
    // Enhanced revision management routes
    $router->get('/wiki/{slug}/compare', [$wikiController, 'compareRevisions']); // Compare two revisions
    $router->post('/wiki/{slug}/revert', [$wikiController, 'revertToRevision']); // Revert to specific revision
    $router->get('/wiki/{slug}/revision/{id}', [$wikiController, 'showRevision']); // View specific revision
    // Redirect /create to wiki dashboard (create functionality handled by redlink system)
    $router->get('/create', function($request) {
        return new \IslamWiki\Core\Http\Response(302, ['Location' => '/wiki'], '');
    });
    
    // Test route to verify routing is working
    $router->get('/test-routing', function ($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], 'Routing test works!');
    });
    

    

    
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
            <a href="/wiki/Main_Page">← Back to Main Page</a>
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
            <a href="/wiki/Main_Page">← Back to Main Page</a>
        </body>
        </html>
        ');
    });
    
    // Dashboard route - use the proper DashboardController
    $router->get('/dashboard', [$dashboardController, 'index']);
    
    // Skin asset routes - serve CSS and JS files directly through Sabil routing
    $router->get('/skins/Bismillah/css/bismillah.css', function($request) {
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
    
    $router->get('/skins/Bismillah/js/bismillah.js', function($request) {
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
    $router->get('/skins/Bismillah/css/pages/main-page.css', function($request) {
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
    
    $router->get('/skins/Bismillah/css/pages/auth.css', function($request) {
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
    
    $router->get('/skins/Bismillah/css/pages/settings.css', function($request) {
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
    
    $router->get('/skins/Bismillah/css/pages/dashboard.css', function($request) {
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
    
    $router->get('/skins/Bismillah/css/pages/all-dashboards.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/all-dashboards.css';
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
    
    $router->get('/skins/Bismillah/css/pages/admin-dashboard.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/admin-dashboard.css';
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
    
    $router->get('/skins/Bismillah/css/pages/user-dashboard.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/user-dashboard.css';
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
    
    $router->get('/skins/Bismillah/css/pages/scholar-dashboard.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/scholar-dashboard.css';
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
    
    $router->get('/skins/Bismillah/css/pages/contributor-dashboard.css', function($request) {
        $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/contributor-dashboard.css';
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
    
    $router->get('/skins/Bismillah/css/pages/preferences.css', function($request) {
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
