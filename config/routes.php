<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\UserController;
use IslamWiki\Http\Controllers\ApiController;
use IslamWiki\Http\Controllers\ConfigurationController;
use IslamWiki\Http\Controllers\SearchController;
use IslamWiki\Http\Controllers\IqraSearchController;
use IslamWiki\Http\Controllers\PrayerTimeController;
use IslamWiki\Http\Controllers\HadithController;
use IslamWiki\Http\Controllers\QuranController;
use IslamWiki\Http\Controllers\IslamicCalendarController;
use IslamWiki\Http\Controllers\IslamicContentController;
use IslamWiki\Http\Controllers\CommunityController;
use IslamWiki\Http\Controllers\SecurityController;
use IslamWiki\Http\Controllers\HomeController;
use IslamWiki\Http\Controllers\DashboardController;
use IslamWiki\Http\Controllers\ProfileController;
use IslamWiki\Http\Controllers\SettingsController;
use IslamWiki\Http\Controllers\QueueController;
use IslamWiki\Http\Middleware\AuthenticationMiddleware;

return function (\IslamWiki\Core\Application $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);

    // Create controller instances
    $pageController = new PageController($db, $container);
    $userController = new UserController($db, $container);
    $apiController = new ApiController($db, $container);
    $configController = new ConfigurationController($container);
    $searchController = new SearchController($db, $container);
    $iqraSearchController = new IqraSearchController($db, $container);
    $prayerController = new PrayerTimeController($db, $container);
    $hadithController = new HadithController($db, $container);
    $quranController = new QuranController($db, $container);
    $calendarController = new IslamicCalendarController($db, $container);
    $contentController = new IslamicContentController($db, $container);
    $communityController = new CommunityController($container);
    $securityController = new SecurityController($db, $container);
    $homeController = new HomeController($db, $container);
    $dashboardController = new DashboardController($db, $container);
    $profileController = new ProfileController($db, $container);
    $settingsController = new SettingsController($db, $container);
    $queueController = new QueueController($db, $container);

    // Create middleware instances
    $authMiddleware = new AuthenticationMiddleware($container->get('session'));

    // Homepage
    $app->get('/', [$homeController, 'index']);

    // Dashboard
    $app->get('/dashboard', [$dashboardController, 'index']);

    // Page routes
    $app->get('/wiki', [$pageController, 'index']);
    $app->get('/wiki/{slug:.+}', [$pageController, 'show']);
    $app->get('/wiki/{slug:.+}/history', [$pageController, 'history']);
    $app->get('/wiki/{slug:.+}/history/{revisionId:\d+}', [$pageController, 'showRevision']);
    $app->get('/wiki/{slug:.+}/revert/{revisionId:\d+}', [$pageController, 'revert']);
    $app->get('/wiki/{slug:.+}/edit', [$pageController, 'edit']);
    $app->post('/wiki/{slug:.+}', [$pageController, 'update']);
    $app->get('/create', [$pageController, 'create']);
    $app->post('/create', [$pageController, 'store']);
    $app->post('/wiki/{slug:.+}/lock', [$pageController, 'lock']);
    $app->post('/wiki/{slug:.+}/unlock', [$pageController, 'unlock']);

    // User authentication routes
    $app->get('/login', [$userController, 'showLoginForm']);
    $app->post('/login', [$userController, 'login']);
    $app->get('/logout', [$userController, 'logout']);
    $app->get('/register', [$userController, 'showRegistrationForm']);
    $app->post('/register', [$userController, 'register']);
    // Protected routes (require authentication)
    $app->get('/profile', [$profileController, 'index'])->middleware($authMiddleware);
    $app->post('/profile', [$profileController, 'update'])->middleware($authMiddleware);
    $app->get('/profile/edit', [$profileController, 'edit'])->middleware($authMiddleware);
    $app->post('/profile/password', [$profileController, 'updatePassword'])->middleware($authMiddleware);

    // Settings routes
    $app->get('/settings', [$settingsController, 'index'])->middleware($authMiddleware);
    $app->post('/settings/skin', [$settingsController, 'updateSkin'])->middleware($authMiddleware);
    $app->get('/settings/skins', [$settingsController, 'getAvailableSkins'])->middleware($authMiddleware);
    $app->get('/settings/skin/{skinName}', [$settingsController, 'getSkinInfo'])->middleware($authMiddleware);

    // Queue management routes
    $app->get('/queue', [$queueController, 'index'])->middleware($authMiddleware);
    $app->get('/queue/stats', [$queueController, 'stats'])->middleware($authMiddleware);
    $app->post('/queue/process', [$queueController, 'process'])->middleware($authMiddleware);
    $app->post('/queue/clear-failed', [$queueController, 'clearFailed'])->middleware($authMiddleware);
    $app->post('/queue/retry-failed', [$queueController, 'retryFailed'])->middleware($authMiddleware);
    $app->get('/queue/failed', [$queueController, 'getFailed'])->middleware($authMiddleware);
    $app->post('/queue/create-test-job', [$queueController, 'createTestJob'])->middleware($authMiddleware);
    $app->get('/queue/driver-info', [$queueController, 'getDriverInfo'])->middleware($authMiddleware);

    // Configuration routes
    $app->get('/configuration', [$configController, 'index']);
    $app->get('/configuration/builder', [$configController, 'builder']);
    $app->get('/configuration/{category}', [$configController, 'show']);
    $app->post('/configuration/update', [$configController, 'update']);
    $app->get('/configuration/export', [$configController, 'export']);
    $app->post('/configuration/import', [$configController, 'import']);
    $app->post('/configuration/validate', [$configController, 'validate']);
    $app->post('/configuration/backup', [$configController, 'createBackup']);
    $app->post('/configuration/restore', [$configController, 'restoreBackup']);
    $app->get('/configuration/audit', [$configController, 'auditLog']);
    $app->get('/configuration/backups', [$configController, 'backups']);

    // Search routes
    $app->get('/search', [$searchController, 'index']);
    $app->post('/search', [$searchController, 'search']);
    $app->get('/search/suggestions', [$searchController, 'suggestions']);
    $app->get('/search/analytics', [$searchController, 'analytics']);

    // Iqra Search routes
    $app->get('/iqra-search', [$iqraSearchController, 'index']);
    $app->get('/iqra-search/api/search', [$iqraSearchController, 'apiSearch']);
    $app->get('/iqra-search/api/suggestions', [$iqraSearchController, 'apiSuggestions']);
    $app->get('/iqra-search/api/analytics', [$iqraSearchController, 'apiAnalytics']);

    // Prayer routes
    $app->get('/prayer', [$prayerController, 'index']);
    $app->get('/prayer/times', [$prayerController, 'getTimes']);
    $app->get('/prayer/search', [$prayerController, 'search']);
    $app->get('/prayer/widget', [$prayerController, 'widget']);
    $app->post('/prayer/calculate', [$prayerController, 'calculate']);

    // Hadith routes
    $app->get('/hadith', [$hadithController, 'index']);
    $app->get('/hadith/collection/{collection}', [$hadithController, 'collection']);
    $app->get('/hadith/{id}', [$hadithController, 'show']);
    $app->get('/hadith/search', [$hadithController, 'search']);
    $app->get('/hadith/widget', [$hadithController, 'widget']);

    // Quran routes
    $app->get('/quran', [$quranController, 'index']);
    $app->get('/quran/verse/{surah}:{ayah}', [$quranController, 'verse']);
    $app->get('/quran/search', [$quranController, 'search']);
    $app->get('/quran/widget', [$quranController, 'widget']);

    // Islamic Calendar routes
    $app->get('/calendar', [$calendarController, 'index']);
    $app->get('/calendar/month/{year}/{month}', [$calendarController, 'month']);
    $app->get('/calendar/event/{id}', [$calendarController, 'event']);
    $app->get('/calendar/search', [$calendarController, 'search']);
    $app->get('/calendar/widget', [$calendarController, 'widget']);

    // Islamic Content routes
    $app->get('/content', [$contentController, 'index']);
    $app->get('/content/category/{category}', [$contentController, 'category']);
    $app->get('/content/{id}', [$contentController, 'show']);
    $app->get('/content/search', [$contentController, 'search']);
    $app->get('/content/recommendations', [$contentController, 'recommendations']);

    // Community routes (some require authentication)
    $app->get('/community', [$communityController, 'index']);
    $app->get('/community/users', [$communityController, 'users']);
    $app->get('/community/activity', [$communityController, 'activity']);
    $app->get('/community/discussions', [$communityController, 'discussions']);
    $app->post('/community/discussions', [$communityController, 'createDiscussion'])->middleware($authMiddleware);
    $app->get('/community/discussions/{id}', [$communityController, 'showDiscussion']);
    $app->post('/community/discussions/{id}/replies', [$communityController, 'addReply'])->middleware($authMiddleware);

    // Security routes
    $app->get('/security', [$securityController, 'index']);
    $app->get('/security/audit', [$securityController, 'audit']);
    $app->get('/security/logs', [$securityController, 'logs']);
    $app->post('/security/scan', [$securityController, 'scan']);
    $app->get('/security/reports', [$securityController, 'reports']);

    // API routes
    $app->group('/api', function () use (
        $apiController,
        $configController,
        $searchController,
        $prayerController,
        $hadithController,
        $quranController,
        $calendarController,
        $contentController,
        $communityController,
        $securityController,
        $profileController
    ) {
        // Pages
        $this->get('/pages', [$apiController, 'listPages']);
        $this->post('/pages', [$apiController, 'createPage']);
        $this->get('/pages/{slug:.+}', [$apiController, 'getPage']);
        $this->put('/pages/{slug:.+}', [$apiController, 'updatePage']);
        $this->delete('/pages/{slug:.+}', [$apiController, 'deletePage']);
        $this->get('/pages/{slug:.+}/history', [$apiController, 'getPageHistory']);

        // Search
        $this->get('/search', [$apiController, 'search']);

        // Users
        $this->get('/users/current', [$apiController, 'getCurrentUser']);

        // Configuration API
        $this->get('/configuration', [$configController, 'apiIndex']);
        $this->get('/configuration/{category}', [$configController, 'apiShow']);
        $this->put('/configuration/{key}', [$configController, 'apiUpdate']);
        $this->get('/configuration/templates', [$configController, 'apiTemplates']);
        $this->post('/configuration/templates', [$configController, 'apiCreateTemplate']);
        $this->post('/configuration/templates/apply', [$configController, 'apiApplyTemplate']);
        $this->post('/configuration/bulk', [$configController, 'apiBulkUpdate']);
        $this->get('/configuration/analytics', [$configController, 'apiAnalytics']);
        $this->post('/configuration/validate/advanced', [$configController, 'apiAdvancedValidate']);
        $this->get('/configuration/dependencies/{key}', [$configController, 'apiDependencies']);
        $this->post('/configuration/suggestions', [$configController, 'apiSuggestions']);
        $this->get('/configuration/performance', [$configController, 'apiPerformance']);

        // Search API
        $this->get('/search', [$searchController, 'apiSearch']);
        $this->post('/search', [$searchController, 'apiSearch']);
        $this->get('/search/suggestions', [$searchController, 'apiSuggestions']);
        $this->get('/search/analytics', [$searchController, 'apiAnalytics']);

        // Prayer API
        $this->get('/prayer/times', [$prayerController, 'apiGetTimes']);
        $this->post('/prayer/calculate', [$prayerController, 'apiCalculate']);
        $this->get('/prayer/search', [$prayerController, 'apiSearch']);

        // Hadith API
        $this->get('/hadith', [$hadithController, 'apiIndex']);
        $this->get('/hadith/{id}', [$hadithController, 'apiShow']);
        $this->get('/hadith/collection/{collection}', [$hadithController, 'apiCollection']);
        $this->get('/hadith/search', [$hadithController, 'apiSearch']);

        // Quran API
        $this->get('/quran', [$quranController, 'apiIndex']);
        $this->get('/quran/verse/{surah}:{ayah}', [$quranController, 'apiVerse']);
        $this->get('/quran/search', [$quranController, 'apiSearch']);

        // Calendar API
        $this->get('/calendar', [$calendarController, 'apiIndex']);
        $this->get('/calendar/month/{year}/{month}', [$calendarController, 'apiMonth']);
        $this->get('/calendar/event/{id}', [$calendarController, 'apiEvent']);
        $this->get('/calendar/search', [$calendarController, 'apiSearch']);

        // Content API
        $this->get('/content', [$contentController, 'apiIndex']);
        $this->get('/content/{id}', [$contentController, 'apiShow']);
        $this->get('/content/category/{category}', [$contentController, 'apiCategory']);
        $this->get('/content/search', [$contentController, 'apiSearch']);
        $this->get('/content/recommendations', [$contentController, 'apiRecommendations']);

        // Community API
        $this->get('/community', [$communityController, 'apiIndex']);
        $this->get('/community/users', [$communityController, 'apiUsers']);
        $this->get('/community/activity', [$communityController, 'apiActivity']);
        $this->get('/community/discussions', [$communityController, 'apiDiscussions']);
        $this->post('/community/discussions', [$communityController, 'apiCreateDiscussion']);
        $this->get('/community/discussions/{id}', [$communityController, 'apiShowDiscussion']);
        $this->post('/community/discussions/{id}/replies', [$communityController, 'apiAddReply']);

        // Security API
        $this->get('/security', [$securityController, 'apiIndex']);
        $this->get('/security/audit', [$securityController, 'apiAudit']);
        $this->get('/security/logs', [$securityController, 'apiLogs']);
        $this->post('/security/scan', [$securityController, 'apiScan']);
        $this->get('/security/reports', [$securityController, 'apiReports']);

        // Profile API
        $this->get('/profile', [$profileController, 'apiIndex']);
        $this->put('/profile', [$profileController, 'apiUpdate']);
        $this->put('/profile/password', [$profileController, 'apiUpdatePassword']);
    });

    // Asset serving routes (serve files from resources through application)
    $app->get('/assets/css/{filename}', 'IslamWiki\Http\Controllers\AssetController@serveCss');
    $app->get('/assets/js/{filename}', 'IslamWiki\Http\Controllers\AssetController@serveJs');

    // Skin asset serving routes (serve files from skins directory)
    $app->get('/skins/{skin}/{type}/{filename}', 'IslamWiki\Http\Controllers\AssetController@serveSkinAsset');

    // Error handling
    $app->addErrorMiddleware(true, true, true);

    // 404 Not Found handler
    $app->setErrorHandler(function (Request $request, Throwable $exception) use ($app) {
        $code = $exception->getCode();
        $message = $exception->getMessage();

        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } elseif ($exception instanceof PDOException) {
            $code = 500;
            $message = 'Database error';
        } else {
            $code = $code >= 400 && $code < 600 ? $code : 500;
        }

        $accept = $request->getHeaderLine('Accept');
        $wantsJson = strpos($accept, 'application/json') !== false;

        if ($wantsJson) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ]
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($code);
        }

        // Render error page
        return $app->getContainer()->get('view')->render(
            new Response($code),
            'error.twig',
            [
                'code' => $code,
                'message' => $message,
                'exception' => $exception,
            ]
        )->withStatus($code);
    });
};
