<?php
/**
 * Routes for app.php entry point
 * 
 * This file defines routes using SabilRouting and pre-instantiated controllers
 */

// Get the container
$container = $router->getContainer();

// Create controller instances with proper dependencies
$db = $container->get('db');
$authController = new \IslamWiki\Http\Controllers\Auth\AuthController($db, $container);
$homeController = new \IslamWiki\Http\Controllers\HomeController($db, $container);
$dashboardController = new \IslamWiki\Http\Controllers\DashboardController($db, $container);
$profileController = new \IslamWiki\Http\Controllers\ProfileController($db, $container);
$pageController = new \IslamWiki\Http\Controllers\PageController($db, $container);
$quranController = new \IslamWiki\Extensions\QuranExtension\Http\Controllers\QuranController($db, $container);
$hadithController = new \IslamWiki\Http\Controllers\HadithController($db, $container);
$sciencesController = new \IslamWiki\Http\Controllers\SciencesController($db, $container);
$communityController = new \IslamWiki\Http\Controllers\CommunityController($db, $container);
$searchController = new \IslamWiki\Http\Controllers\SearchController($db, $container);
$calendarController = new \IslamWiki\Http\Controllers\IslamicCalendarController($db, $container);
$salahController = new \IslamWiki\Http\Controllers\SalahTimeController($db, $container);
$docsController = new \IslamWiki\Http\Controllers\DocsController($db, $container);
$settingsController = new \IslamWiki\Http\Controllers\SettingsController(
    $db,
    $container,
    $container->get(\IslamWiki\Core\Language\LanguageService::class)
);

// Create middleware instances
$authMiddleware = new \IslamWiki\Http\Middleware\AuthenticationMiddleware($container->get('session'));

// Define routes
$router->get('/', [$homeController, 'index']);
$router->get('/dashboard', [$dashboardController, 'index']);

// Authentication routes
$router->get('/auth/login', [$authController, 'showLogin']);
$router->post('/auth/login', [$authController, 'login']);
$router->get('/auth/register', [$authController, 'showRegister']);
$router->post('/auth/register', [$authController, 'register']);
$router->get('/auth/logout', [$authController, 'logout']);

// Protected routes
$router->get('/profile', [$profileController, 'index']);
$router->get('/settings', [$settingsController, 'index']);
$router->post('/settings/language', [$settingsController, 'updateLanguage']);

// Page routes (Wiki)
$router->get('/wiki', [$pageController, 'index']);
$router->get('/wiki/{slug:.+}', [$pageController, 'show']);
$router->get('/wiki/{slug:.+}/history', [$pageController, 'history']);
$router->get('/wiki/{slug:.+}/history/{revisionId:\d+}', [$pageController, 'showRevision']);
$router->get('/wiki/{slug:.+}/edit', [$pageController, 'edit']);
$router->post('/wiki/{slug:.+}', [$pageController, 'update']);
$router->get('/create', [$pageController, 'create']);
$router->post('/create', [$pageController, 'store']);

// Quran routes
$router->get('/quran', [$quranController, 'indexPage']);
$router->get('/quran/surah/{surah:\d+}', [$quranController, 'surahPage']);
$router->get('/quran/ayah/{surah:\d+}/{ayah:\d+}', [$quranController, 'ayahPage']);
$router->get('/quran/juz/{juz:\d+}', [$quranController, 'juzPage']);
$router->get('/quran/search', [$quranController, 'searchPage']);

// Hadith routes
$router->get('/hadith', [$hadithController, 'index']);
$router->get('/hadith/collection/{collection}', [$hadithController, 'showCollection']);
$router->get('/hadith/{id:\d+}', [$hadithController, 'show']);
$router->get('/hadith/search', [$hadithController, 'search']);

// Islamic Sciences routes
$router->get('/sciences', [$sciencesController, 'index']);
$router->get('/sciences/category/{category}', [$sciencesController, 'showCategory']);

// Community routes
$router->get('/community', [$communityController, 'index']);
$router->get('/community/users', [$communityController, 'users']);

// Search routes
$router->get('/search', [$searchController, 'index']);
$router->get('/iqra-search', [$searchController, 'iqraSearch']);

// Calendar routes
$router->get('/calendar', [$calendarController, 'index']);
$router->get('/calendar/month/{year:\d+}/{month:\d+}', [$calendarController, 'showMonth']);
$router->get('/calendar/event/{id:\d+}', [$calendarController, 'showEvent']);

// Prayer time routes
$router->get('/prayer', [$salahController, 'index']);
$router->get('/prayer/search', [$salahController, 'search']);

// Documentation routes
$router->get('/docs', [$docsController, 'index']);
$router->get('/docs/{path:.*}', [$docsController, 'show']);

// Language-specific routes (for the core language system)
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}', [$homeController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/dashboard', [$dashboardController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/quran', [$quranController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/hadith', [$hadithController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/wiki', [$pageController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/sciences', [$sciencesController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/community', [$communityController, 'index']);
$router->get('/{language:en|ar|tr|ur|id|ms|fa|he}/settings', [$settingsController, 'index']); 