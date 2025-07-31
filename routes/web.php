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

// Authentication Routes
$router->get('/login', 'IslamWiki\Http\Controllers\Auth\AuthController@showLogin');
$router->post('/login', 'IslamWiki\Http\Controllers\Auth\AuthController@login');
    
$router->get('/register', 'IslamWiki\Http\Controllers\Auth\AuthController@showRegister');
$router->post('/register', 'IslamWiki\Http\Controllers\Auth\AuthController@register');
    
$router->post('/logout', 'IslamWiki\Http\Controllers\Auth\AuthController@logout');

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

// Profile
$router->get('/profile', 'IslamWiki\Http\Controllers\ProfileController@show');
$router->put('/profile', 'IslamWiki\Http\Controllers\ProfileController@update');
$router->put('/profile/password', 'IslamWiki\Http\Controllers\ProfileController@updatePassword');

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

// Additional page routes
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

// Configuration API Routes
$router->get('/api/configuration', 'IslamWiki\Http\Controllers\ConfigurationController@apiIndex');
$router->get('/api/configuration/{category}', 'IslamWiki\Http\Controllers\ConfigurationController@apiShow');
$router->put('/api/configuration/{key}', 'IslamWiki\Http\Controllers\ConfigurationController@apiUpdate');

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

// Search Routes - Phase 6 Search and Discovery Features
$router->get('/search', 'IslamWiki\Http\Controllers\SearchController@index');
$router->get('/api/search', 'IslamWiki\Http\Controllers\SearchController@apiSearch');
$router->get('/api/search/suggestions', 'IslamWiki\Http\Controllers\SearchController@apiSuggestions');
