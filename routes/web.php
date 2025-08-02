<?php
declare(strict_types=1);

use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Http\Controllers\Auth\LoginController;
use IslamWiki\Http\Controllers\Auth\RegisterController;
use IslamWiki\Http\Controllers\Auth\ForgotPasswordController;
use IslamWiki\Http\Controllers\Auth\ResetPasswordController;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\ProfileController;
use IslamWiki\Http\Controllers\DashboardController;
use IslamWiki\Http\Controllers\HomeController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var IslamRouter $router */

// Homepage
$router->get('/', 'IslamWiki\Http\Controllers\HomeController@index');

// About page
$router->get('/about', 'IslamWiki\Http\Controllers\PageController@about');

// Authentication Routes
$router->get('/login', 'IslamWiki\Http\Controllers\Auth\AuthController@showLogin');
$router->post('/login', 'IslamWiki\Http\Controllers\Auth\AuthController@login');
    
$router->get('/register', 'IslamWiki\Http\Controllers\Auth\AuthController@showRegister');
$router->post('/register', 'IslamWiki\Http\Controllers\Auth\AuthController@register');
    
$router->post('/logout', 'IslamWiki\Http\Controllers\Auth\AuthController@logout');

// Password Reset Routes
$router->get('/forgot-password', 'IslamWiki\Http\Controllers\Auth\AuthController@showForgotPassword');
$router->post('/forgot-password', 'IslamWiki\Http\Controllers\Auth\AuthController@forgotPassword');
$router->get('/reset-password', 'IslamWiki\Http\Controllers\Auth\AuthController@showResetPassword');
$router->post('/reset-password', 'IslamWiki\Http\Controllers\Auth\AuthController@resetPassword');

// Auth Profile Routes (Legacy - keeping for backward compatibility)
// $router->get('/profile', 'IslamWiki\Http\Controllers\Auth\AuthController@profile');
// $router->post('/profile', 'IslamWiki\Http\Controllers\Auth\AuthController@updateProfile');
// $router->post('/profile/change-password', 'IslamWiki\Http\Controllers\Auth\AuthController@changePassword');

// Debug session route (must be before variable routes)
$router->get('/debug-session', function($request) use ($router) {
    try {
        $container = $router->getContainer();
        $session = $container->get('session');
        $sessionData = $_SESSION;
        $html = "<h1>🔍 Debug Session (Framework Context)</h1>";
        $html .= "<h2>Session Information</h2>";
        $html .= "<p><strong>Session ID:</strong> " . session_id() . "</p>";
        $html .= "<p><strong>Session Name:</strong> " . session_name() . "</p>";
        $html .= "<p><strong>Session Status:</strong> " . session_status() . "</p>";
        $html .= "<h2>Session Data</h2><pre>" . print_r($sessionData, true) . "</pre>";
        $html .= "<h2>Session Manager Methods</h2>";
        $html .= "<p><strong>isLoggedIn:</strong> " . ($session->isLoggedIn() ? 'true' : 'false') . "</p>";
        $html .= "<p><strong>getUserId:</strong> " . ($session->getUserId() ?? 'null') . "</p>";
        $html .= "<p><strong>getUsername:</strong> " . ($session->getUsername() ?? 'null') . "</p>";
        $html .= "<p><strong>isAdmin:</strong> " . ($session->isAdmin() ? 'true' : 'false') . "</p>";
        $html .= "<h2>Cookies</h2><pre>" . print_r($_COOKIE, true) . "</pre>";
        return new \IslamWiki\Core\Http\Response(200, [], $html);
    } catch (\Throwable $e) {
        $html = "<h1>Exception in /debug-session</h1>";
        $html .= "<pre>" . htmlspecialchars($e) . "</pre>";
        return new \IslamWiki\Core\Http\Response(500, [], $html);
    }
});

// Authenticated Routes
// Middleware can be added as a third parameter, e.g., ['auth']



// Dashboard
$router->get('/dashboard', 'IslamWiki\Http\Controllers\DashboardController@index');

// Profile Routes
$router->get('/profile', 'IslamWiki\Http\Controllers\ProfileController@show'); // Private profile
$router->post('/profile/update', 'IslamWiki\Http\Controllers\ProfileController@update');
$router->get('/user/{username}', 'IslamWiki\Http\Controllers\ProfileController@showPublic'); // Public profile
$router->post('/profile/privacy-settings', 'IslamWiki\Http\Controllers\ProfileController@updatePrivacySettings');
$router->post('/profile/customization-settings', 'IslamWiki\Http\Controllers\ProfileController@updateCustomizationSettings');

// Settings (Temporarily unprotected for debugging)
$router->get('/settings', 'IslamWiki\Http\Controllers\SettingsController@index');
$router->post('/settings/skin', 'IslamWiki\Http\Controllers\SettingsController@updateSkin');
$router->get('/settings/skins', 'IslamWiki\Http\Controllers\SettingsController@getAvailableSkins');
$router->get('/settings/skin/{name}', 'IslamWiki\Http\Controllers\SettingsController@getSkinInfo');

// Test endpoint for debugging
$router->post('/test-skin-update', function($request) {
    $body = $request->getBody()->getContents();
    $contentType = $request->getHeaderLine('Content-Type');
    $parsedBody = $request->getParsedBody();
    
    $response = [
        'body' => $body,
        'contentType' => $contentType,
        'parsedBody' => $parsedBody,
        'post' => $_POST,
        'isJson' => strpos($contentType, 'application/json') !== false
    ];
    
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'application/json'], json_encode($response));
});

// Test routes for debugging
$router->get('/test', 'IslamWiki\Http\Controllers\TestController@test');
$router->get('/test/error', 'IslamWiki\Http\Controllers\TestController@testError');
$router->get('/test/debug', 'IslamWiki\Http\Controllers\TestController@testDebug');

// Test closure route for router debugging
$router->get('/test-closure', function($request) {
    error_log('Test closure route called');
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Test closure route works!');
});

// Pages
$router->get('/pages', 'IslamWiki\Http\Controllers\PageController@index');
$router->get('/pages/create', 'IslamWiki\Http\Controllers\PageController@create');
$router->post('/pages', 'IslamWiki\Http\Controllers\PageController@store');
$router->get('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@show');
$router->get('/pages/{id}/edit', 'IslamWiki\Http\Controllers\PageController@edit');
$router->put('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@update');
$router->delete('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@destroy');

// Search Routes - Phase 6 Search and Discovery Features (Moved before variable routes)
$router->get('/search', 'IslamWiki\Http\Controllers\SearchController@index');
$router->get('/api/search', 'IslamWiki\Http\Controllers\SearchController@apiSearch');
$router->get('/api/search/suggestions', 'IslamWiki\Http\Controllers\SearchController@apiSuggestions');

// Iqra Search Engine Routes - Advanced Islamic Search (Moved before variable routes)
$router->get('/iqra-search', 'IslamWiki\Http\Controllers\IqraSearchController@index');
$router->get('/iqra-search/api/search', 'IslamWiki\Http\Controllers\IqraSearchController@apiSearch');
$router->get('/iqra-search/api/suggestions', 'IslamWiki\Http\Controllers\IqraSearchController@apiSuggestions');
$router->get('/iqra-search/api/analytics', 'IslamWiki\Http\Controllers\IqraSearchController@apiAnalytics');

// Additional page routes (variable routes - must come after specific routes)
$router->get('/{slug}', 'IslamWiki\Http\Controllers\PageController@show');
$router->get('/{slug}/history', 'IslamWiki\Http\Controllers\PageController@history');
$router->get('/{slug}/edit', 'IslamWiki\Http\Controllers\PageController@edit');
$router->put('/{slug}', 'IslamWiki\Http\Controllers\PageController@update');
$router->delete('/{slug}', 'IslamWiki\Http\Controllers\PageController@destroy');

// Watchlist
$router->post('/{slug}/watch', 'IslamWiki\Http\Controllers\PageController@watch');
$router->delete('/{slug}/unwatch', 'IslamWiki\Http\Controllers\PageController@unwatch');

// Configuration Routes - Version 0.0.20
$router->get('/configuration', 'IslamWiki\Http\Controllers\ConfigurationController@index');
$router->get('/configuration/{category}', 'IslamWiki\Http\Controllers\ConfigurationController@show');
$router->post('/configuration/update', 'IslamWiki\Http\Controllers\ConfigurationController@update');
$router->get('/configuration/export', 'IslamWiki\Http\Controllers\ConfigurationController@export');
$router->post('/configuration/import', 'IslamWiki\Http\Controllers\ConfigurationController@import');
$router->post('/configuration/validate', 'IslamWiki\Http\Controllers\ConfigurationController@validate');
$router->post('/configuration/backup', 'IslamWiki\Http\Controllers\ConfigurationController@createBackup');
$router->post('/configuration/restore', 'IslamWiki\Http\Controllers\ConfigurationController@restoreBackup');
$router->get('/configuration/audit', 'IslamWiki\Http\Controllers\ConfigurationController@auditLog');
$router->get('/configuration/backups', 'IslamWiki\Http\Controllers\ConfigurationController@backups');

// Configuration Builder Routes - Version 0.0.23
$router->get('/configuration/builder', 'IslamWiki\Http\Controllers\ConfigurationController@builder');

// Configuration API Routes
$router->get('/api/configuration', 'IslamWiki\Http\Controllers\ConfigurationController@apiIndex');
$router->get('/api/configuration/{category}', 'IslamWiki\Http\Controllers\ConfigurationController@apiShow');
$router->put('/api/configuration/{key}', 'IslamWiki\Http\Controllers\ConfigurationController@apiUpdate');

// Enhanced Configuration API Routes - Version 0.0.23
$router->get('/api/configuration/templates', 'IslamWiki\Http\Controllers\ConfigurationController@apiTemplates');
$router->post('/api/configuration/templates', 'IslamWiki\Http\Controllers\ConfigurationController@apiCreateTemplate');
$router->post('/api/configuration/templates/apply', 'IslamWiki\Http\Controllers\ConfigurationController@apiApplyTemplate');
$router->post('/api/configuration/bulk', 'IslamWiki\Http\Controllers\ConfigurationController@apiBulkUpdate');
$router->get('/api/configuration/analytics', 'IslamWiki\Http\Controllers\ConfigurationController@apiAnalytics');
$router->post('/api/configuration/validate/advanced', 'IslamWiki\Http\Controllers\ConfigurationController@apiAdvancedValidate');
$router->get('/api/configuration/dependencies/{key}', 'IslamWiki\Http\Controllers\ConfigurationController@apiDependencies');
$router->post('/api/configuration/suggestions', 'IslamWiki\Http\Controllers\ConfigurationController@apiSuggestions');
$router->get('/api/configuration/performance', 'IslamWiki\Http\Controllers\ConfigurationController@apiPerformance');

// Security Routes - Version 0.0.21
$router->get('/security', 'IslamWiki\Http\Controllers\SecurityController@index');
$router->get('/security/audit-log', 'IslamWiki\Http\Controllers\SecurityController@auditLog');
$router->get('/security/approvals', 'IslamWiki\Http\Controllers\SecurityController@approvals');
$router->post('/security/approve', 'IslamWiki\Http\Controllers\SecurityController@approve');
$router->post('/security/reject', 'IslamWiki\Http\Controllers\SecurityController@reject');
$router->post('/security/rotate-key', 'IslamWiki\Http\Controllers\SecurityController@rotateKey');
$router->get('/security/encryption-info', 'IslamWiki\Http\Controllers\SecurityController@encryptionInfo');
$router->get('/security/stats', 'IslamWiki\Http\Controllers\SecurityController@securityStats');

// Security API Routes
$router->get('/api/security/stats', 'IslamWiki\Http\Controllers\SecurityController@securityStats');
$router->get('/api/security/encryption-info', 'IslamWiki\Http\Controllers\SecurityController@encryptionInfo');
$router->post('/api/security/rotate-key', 'IslamWiki\Http\Controllers\SecurityController@rotateKey');

// Quran Routes - Phase 4 Islamic Features Integration
$router->get('/quran', 'IslamWiki\Http\Controllers\QuranController@indexPage');
$router->get('/quran/search', 'IslamWiki\Http\Controllers\QuranController@searchPage');
$router->get('/quran/chapter/{chapter}', 'IslamWiki\Http\Controllers\QuranController@chapterPage');
$router->get('/quran/verse/{chapter}/{verse}', 'IslamWiki\Http\Controllers\QuranController@versePage');
$router->get('/quran/widget/{chapter}/{verse}', 'IslamWiki\Http\Controllers\QuranController@widget');

// Quran API Routes
$router->get('/api/quran/verses', 'IslamWiki\Http\Controllers\QuranController@apiVerses');
$router->get('/api/quran/verses/{id}', 'IslamWiki\Http\Controllers\QuranController@apiVerse');
$router->get('/api/quran/search', 'IslamWiki\Http\Controllers\QuranController@apiSearch');
$router->get('/api/quran/verses/{chapter}/{verse}', 'IslamWiki\Http\Controllers\QuranController@apiVerseByReference');
$router->get('/api/quran/tafsir/{verseId}', 'IslamWiki\Http\Controllers\QuranController@apiTafsir');
$router->get('/api/quran/recitation/{verseId}', 'IslamWiki\Http\Controllers\QuranController@apiRecitation');
$router->get('/api/quran/statistics', 'IslamWiki\Http\Controllers\QuranController@apiStatistics');
$router->get('/api/quran/random', 'IslamWiki\Http\Controllers\QuranController@apiRandomVerse');
$router->get('/api/quran/references/{pageId}', 'IslamWiki\Http\Controllers\QuranController@apiReferences');

// Hadith Routes - Phase 4 Islamic Features Integration
$router->get('/hadith', 'IslamWiki\Http\Controllers\HadithController@indexPage');
$router->get('/hadith/search', 'IslamWiki\Http\Controllers\HadithController@searchPage');
$router->get('/hadith/collection/{collectionId}', 'IslamWiki\Http\Controllers\HadithController@collectionPage');
$router->get('/hadith/{collectionId}/{hadithNumber}', 'IslamWiki\Http\Controllers\HadithController@hadithPage');
$router->get('/hadith/widget/{collectionId}/{hadithNumber}', 'IslamWiki\Http\Controllers\HadithController@widget');

// Hadith API Routes
$router->get('/api/hadith/hadiths', 'IslamWiki\Http\Controllers\HadithController@apiHadiths');
$router->get('/api/hadith/hadiths/{id}', 'IslamWiki\Http\Controllers\HadithController@apiHadith');
$router->get('/api/hadith/search', 'IslamWiki\Http\Controllers\HadithController@apiSearch');
$router->get('/api/hadith/hadiths/{collectionId}/{hadithNumber}', 'IslamWiki\Http\Controllers\HadithController@apiHadithByReference');
$router->get('/api/hadith/chain/{hadithId}', 'IslamWiki\Http\Controllers\HadithController@apiChain');
$router->get('/api/hadith/commentary/{hadithId}', 'IslamWiki\Http\Controllers\HadithController@apiCommentary');
$router->get('/api/hadith/collections', 'IslamWiki\Http\Controllers\HadithController@apiCollections');
$router->get('/api/hadith/statistics', 'IslamWiki\Http\Controllers\HadithController@apiStatistics');
$router->get('/api/hadith/random', 'IslamWiki\Http\Controllers\HadithController@apiRandomHadith');
$router->get('/api/hadith/authenticity/{authenticityLevel}', 'IslamWiki\Http\Controllers\HadithController@apiByAuthenticity');
$router->get('/api/hadith/references/{pageId}', 'IslamWiki\Http\Controllers\HadithController@apiReferences');

// Calendar Routes - Phase 4 Islamic Features Integration
$router->get('/calendar', 'IslamWiki\Http\Controllers\IslamicCalendarController@index');
$router->get('/calendar/month/{year}/{month}', 'IslamWiki\Http\Controllers\IslamicCalendarController@month');
$router->get('/calendar/event/{id}', 'IslamWiki\Http\Controllers\IslamicCalendarController@event');
$router->get('/calendar/widget/{year}/{month}', 'IslamWiki\Http\Controllers\IslamicCalendarController@widget');
$router->get('/calendar/search', 'IslamWiki\Http\Controllers\IslamicCalendarController@search');

// Calendar API Routes
$router->get('/api/calendar/events', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiGetEvents');
$router->get('/api/calendar/events/{id}', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiGetEvent');
$router->get('/api/calendar/convert/{date}', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiConvertDate');
$router->get('/api/calendar/prayer-times/{date}', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiGetPrayerTimes');
$router->get('/api/calendar/statistics', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiGetStatistics');
$router->get('/api/calendar/upcoming', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiGetUpcoming');
$router->get('/api/calendar/search', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiSearchEvents');
$router->post('/api/calendar/events', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiCreateEvent');
$router->put('/api/calendar/events/{id}', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiUpdateEvent');
$router->delete('/api/calendar/events/{id}', 'IslamWiki\Http\Controllers\IslamicCalendarController@apiDeleteEvent');

// Prayer Times Routes - Phase 5 Prayer Times Integration
$router->get('/prayer', 'IslamWiki\Http\Controllers\PrayerTimeController@index');
$router->get('/prayer/search', 'IslamWiki\Http\Controllers\PrayerTimeController@search');
$router->get('/prayer/show/{date}/{locationId}', 'IslamWiki\Http\Controllers\PrayerTimeController@show');
$router->get('/prayer/widget/{widgetKey}', 'IslamWiki\Http\Controllers\PrayerTimeController@widget');
$router->get('/prayer/locations', 'IslamWiki\Http\Controllers\PrayerTimeController@locations');
$router->get('/prayer/preferences', 'IslamWiki\Http\Controllers\PrayerTimeController@preferences');

// Prayer Times API Routes
$router->get('/api/prayer-times/times', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetPrayerTimes');
$router->get('/api/prayer-times/locations', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetLocations');
$router->post('/api/prayer-times/locations', 'IslamWiki\Http\Controllers\PrayerTimeController@apiAddLocation');
$router->get('/api/prayer-times/preferences', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetPreferences');
$router->put('/api/prayer-times/preferences', 'IslamWiki\Http\Controllers\PrayerTimeController@apiUpdatePreferences');
$router->get('/api/prayer-times/qibla', 'IslamWiki\Http\Controllers\PrayerTimeController@apiCalculateQibla');
$router->get('/api/prayer-times/next', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetNextPrayer');
$router->get('/api/prayer-times/statistics', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetStatistics');
$router->get('/api/prayer-times/methods', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetCalculationMethods');
$router->get('/api/prayer-times/names', 'IslamWiki\Http\Controllers\PrayerTimeController@apiGetPrayerNames');

// Bayan Knowledge Graph Routes - Version 0.0.34
$router->get('/bayan', 'IslamWiki\Http\Controllers\BayanController@index');
$router->get('/bayan/search', 'IslamWiki\Http\Controllers\BayanController@search');
$router->get('/bayan/create', 'IslamWiki\Http\Controllers\BayanController@create');
$router->post('/bayan/create', 'IslamWiki\Http\Controllers\BayanController@create');
$router->get('/bayan/node/{id}', 'IslamWiki\Http\Controllers\BayanController@show');
$router->post('/bayan/relationship', 'IslamWiki\Http\Controllers\BayanController@createRelationship');
$router->get('/bayan/statistics', 'IslamWiki\Http\Controllers\BayanController@statistics');
$router->get('/bayan/paths', 'IslamWiki\Http\Controllers\BayanController@findPaths');

$router->get('/test-router-alive', function($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'ROUTER IS ALIVE: ' . date('Y-m-d H:i:s'));
});
