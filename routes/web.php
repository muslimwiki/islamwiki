<?php

declare(strict_types=1);

use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Http\Controllers\Auth\LoginController;
use IslamWiki\Http\Controllers\Auth\RegisterController;
use IslamWiki\Http\Controllers\Auth\ForgotPasswordController;
use IslamWiki\Http\Controllers\Auth\ResetPasswordController;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\ProfileController;
use IslamWiki\Http\Controllers\DashboardController;
use IslamWiki\Http\Controllers\HomeController;
use IslamWiki\Http\Controllers\AssetController;
use IslamWiki\Http\Controllers\WikiController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var SabilRouting $router */

// Homepage
// error_log("Loading route: / -> HomeController@index");
$router->get('/', 'IslamWiki\Http\Controllers\HomeController@index');

// Docs routes (must come before any catch-alls)
$router->get('/docs', 'IslamWiki\\Http\\Controllers\\DocsController@index');
$router->get('/docs/{path:.*}', 'IslamWiki\\Http\\Controllers\\DocsController@show');

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
$router->get('/debug-session', function ($request) use ($router) {
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
    } catch (\Exception $e) {
        $html = "<h1>Exception in /debug-session</h1>";
        $html .= "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        return new \IslamWiki\Core\Http\Response(500, [], $html);
    }
});

// Asset serving routes (serve files from resources through application)
$router->get('/assets/css/{filename}', 'IslamWiki\\Http\\Controllers\\AssetController@serveCss');
$router->get('/assets/js/{filename}', 'IslamWiki\\Http\\Controllers\\AssetController@serveJs');

// Skin asset routes
$router->get('/skins/{skin}/css/{filename}', 'IslamWiki\\Http\\Controllers\\AssetController@serveSkinCss');
$router->get('/skins/{skin}/js/{filename}', 'IslamWiki\\Http\\Controllers\\AssetController@serveSkinJs');

// Dashboard
$router->get('/dashboard', 'IslamWiki\\Http\\Controllers\\DashboardController@index');

// Profile Routes
$router->get('/profile', 'IslamWiki\\Http\\Controllers\\ProfileController@show'); // Private profile
$router->post('/profile/update', 'IslamWiki\\Http\\Controllers\\ProfileController@update');
$router->get('/user/{username}', 'IslamWiki\\Http\\Controllers\\ProfileController@showPublic'); // Public profile
$router->post('/profile/privacy-settings', 'IslamWiki\\Http\\Controllers\\ProfileController@updatePrivacySettings');
$router->post('/profile/customization-settings', 'IslamWiki\\Http\\Controllers\\ProfileController@updateCustomizationSettings');

// Settings (Temporarily unprotected for debugging)
$router->get('/settings', 'IslamWiki\\Http\\Controllers\\SettingsController@index');
$router->post('/settings/skin', 'IslamWiki\\Http\\Controllers\\SettingsController@updateSkin');
$router->get('/settings/skins', 'IslamWiki\\Http\\Controllers\\SettingsController@getAvailableSkins');
$router->get('/settings/skin/{name}', 'IslamWiki\\Http\\Controllers\\SettingsController@getSkinInfo');

// Test endpoint for debugging
$router->post('/test-skin-update', function ($request) {
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

    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'application/json'],
        json_encode($response)
    );
});

// Test routes for debugging
$router->get('/test', 'IslamWiki\\Http\\Controllers\\TestController@test');
$router->get('/test/error', 'IslamWiki\\Http\\Controllers\\TestController@testError');
$router->get('/test/debug', 'IslamWiki\\Http\\Controllers\\TestController@testDebug');

// Test closure route for router debugging
$router->get('/test-closure', function ($request) {
    error_log('Test closure route called');
    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'text/plain'],
        'Test closure route works!'
    );
});

// Pages - Redirect to Wiki for consolidation
$router->get('/pages', function ($request) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => '/wiki'], '');
});

$router->get('/pages/create', function ($request) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => '/wiki/create'], '');
});

$router->post('/pages', function ($request) use ($router) {
    // Redirect POST to wiki with same data
    $body = $request->getBody()->getContents();
    // Ensure form content type
    $headers = $request->getHeaders();
    $headers['Content-Type'] = ['application/x-www-form-urlencoded'];
    $wikiRequest = $request->withUri(
        $request->getUri()->withPath('/wiki')
    )->withBody(new \IslamWiki\Core\Http\Stream($body));

    return $router->handle($wikiRequest);
});

$router->get('/pages/{slug}', function ($request, $slug) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/wiki/{$slug}"], '');
});

$router->get('/pages/{slug}/edit', function ($request, $slug) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/wiki/{$slug}/edit"], '');
});

$router->put('/pages/{slug}', function ($request, $slug) use ($router) {
    // Redirect PUT to wiki with same data
    $body = $request->getBody()->getContents();
    // Ensure form content type
    $headers = $request->getHeaders();
    $headers['Content-Type'] = ['application/x-www-form-urlencoded'];
    $wikiRequest = $request->withUri(
        $request->getUri()->withPath("/wiki/{$slug}")
    )->withBody(new \IslamWiki\Core\Http\Stream($body));

    return $router->handle($wikiRequest);
});

$router->delete('/pages/{slug}', function ($request, $slug) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/wiki/{$slug}"], '');
});

$router->get('/pages/{slug}/history', function ($request, $slug) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/wiki/{$slug}/history"], '');
});

// Wiki Routes - Content Management (must come before catch-all routes)
$router->get('/wiki', 'IslamWiki\\Http\\Controllers\\WikiController@index');
$router->get('/wiki/create', 'IslamWiki\\Http\\Controllers\\WikiController@create');
$router->post('/wiki', 'IslamWiki\\Http\\Controllers\\WikiController@store');
$router->get('/wiki/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@show');
$router->get('/wiki/{slug}/edit', 'IslamWiki\\Http\\Controllers\\WikiController@edit');
$router->put('/wiki/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@update');
$router->delete('/wiki/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@destroy');
$router->get('/wiki/{slug}/history', 'IslamWiki\\Http\\Controllers\\WikiController@history');
$router->post('/wiki/{slug}/watch', 'IslamWiki\\Http\\Controllers\\WikiController@watch');
$router->delete('/wiki/{slug}/unwatch', 'IslamWiki\\Http\\Controllers\\WikiController@unwatch');

// Wiki API Routes
$router->get('/api/wiki/pages', 'IslamWiki\\Http\\Controllers\\WikiController@apiIndex');
$router->get('/api/wiki/pages/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@apiShow');
$router->post('/api/wiki/pages', 'IslamWiki\\Http\\Controllers\\WikiController@apiStore');
$router->put('/api/wiki/pages/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@apiUpdate');
$router->delete('/api/wiki/pages/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@apiDestroy');

// Special namespace routes (MediaWiki-style) - must precede catch-all routes
$router->get('/Special', 'IslamWiki\\Http\\Controllers\\SpecialController@index');
$router->get('/Special:{page}', 'IslamWiki\\Http\\Controllers\\SpecialController@handle');
// lowercase aliases
$router->get('/special', 'IslamWiki\\Http\\Controllers\\SpecialController@index');
$router->get('/special:{page}', 'IslamWiki\\Http\\Controllers\\SpecialController@handle');

// Quran namespace shorthand: redirect to Quran search
$router->get('/Quran:{query}', function ($request, $query) {
    return new \IslamWiki\Core\Http\Response(
        302,
        ['Location' => '/quran/search?q=' . urlencode($query)],
        ''
    );
});
// lowercase alias
$router->get('/quran:{query}', function ($request, $query) {
    return new \IslamWiki\Core\Http\Response(
        302,
        ['Location' => '/quran/search?q=' . urlencode($query)],
        ''
    );
});

// Quran Routes - Handled by QuranExtension
// Note: Quran routes are registered by the QuranExtension when the router becomes available

// Hadith Routes - Phase 4 Islamic Features Integration
$router->get('/hadith', 'IslamWiki\\Http\\Controllers\\HadithController@index');
$router->get('/hadith/search', 'IslamWiki\\Http\\Controllers\\HadithController@searchPage');
$router->get('/hadith/collection/{collectionId}', 'IslamWiki\\Http\\Controllers\\HadithController@collectionPage');
$router->get('/hadith/{collectionId}/{hadithNumber}', 'IslamWiki\\Http\\Controllers\\HadithController@hadithPage');
$router->get('/hadith/widget/{collectionId}/{hadithNumber}', 'IslamWiki\\Http\\Controllers\\HadithController@widget');

// Hadith API Routes
$router->get('/api/hadith/hadiths', 'IslamWiki\\Http\\Controllers\\HadithController@apiHadiths');
$router->get('/api/hadith/hadiths/{id}', 'IslamWiki\\Http\\Controllers\\HadithController@apiHadith');
$router->get('/api/hadith/search', 'IslamWiki\\Http\\Controllers\\HadithController@apiSearch');
$router->get('/api/hadith/hadiths/{collectionId}/{hadithNumber}', 'IslamWiki\\Http\\Controllers\\HadithController@apiHadithByReference');
$router->get('/api/hadith/chain/{hadithId}', 'IslamWiki\\Http\\Controllers\\HadithController@apiChain');
$router->get('/api/hadith/commentary/{hadithId}', 'IslamWiki\\Http\\Controllers\\HadithController@apiCommentary');
$router->get('/api/hadith/collections', 'IslamWiki\\Http\\Controllers\\HadithController@apiCollections');
$router->get('/api/hadith/statistics', 'IslamWiki\\Http\\Controllers\\HadithController@apiStatistics');
$router->get('/api/hadith/random', 'IslamWiki\\Http\\Controllers\\HadithController@apiRandomHadith');
$router->get('/api/hadith/authenticity/{authenticityLevel}', 'IslamWiki\\Http\\Controllers\\HadithController@apiByAuthenticity');
$router->get('/api/hadith/references/{pageId}', 'IslamWiki\\Http\\Controllers\\HadithController@apiReferences');

// Calendar Routes - Phase 4 Islamic Features Integration
$router->get('/calendar', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@index');
$router->get('/calendar/month/{year}/{month}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@month');
$router->get('/calendar/event/{id}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@event');
$router->get('/calendar/widget/{year}/{month}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@widget');
$router->get('/calendar/search', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@search');

// Calendar API Routes
$router->get('/api/calendar/events', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiGetEvents');
$router->get('/api/calendar/events/{id}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiGetEvent');
$router->get('/api/calendar/convert/{date}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiConvertDate');
$router->get('/api/calendar/prayer-times/{date}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiGetPrayerTimes');
$router->get('/api/calendar/statistics', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiGetStatistics');

// Sciences Routes
$router->get('/sciences', 'IslamWiki\\Http\\Controllers\\SciencesController@index');
$router->get('/sciences/{category}', 'IslamWiki\\Http\\Controllers\\SciencesController@category');

// Community Routes
$router->get('/community', 'IslamWiki\\Http\\Controllers\\CommunityController@index');
$router->get('/community/users', 'IslamWiki\\Http\\Controllers\\CommunityController@users');
$router->get('/community/activity', 'IslamWiki\\Http\\Controllers\\CommunityController@activity');
$router->get('/community/contribute', 'IslamWiki\\Http\\Controllers\\CommunityController@contribute');
$router->get('/community/my-contributions', 'IslamWiki\\Http\\Controllers\\CommunityController@myContributions');
$router->get('/community/moderation', 'IslamWiki\\Http\\Controllers\\CommunityController@moderation');
$router->get('/community/discussions', 'IslamWiki\\Http\\Controllers\\CommunityController@discussions');
$router->get('/community/discussions/create', 'IslamWiki\\Http\\Controllers\\CommunityController@createDiscussion');
$router->get('/community/discussions/{id}', 'IslamWiki\\Http\\Controllers\\CommunityController@showDiscussion');
$router->get('/community/profile/{userId}', 'IslamWiki\\Http\\Controllers\\CommunityController@profile');
$router->get('/api/calendar/upcoming', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiGetUpcoming');
$router->get('/api/calendar/search', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiSearchEvents');
$router->post('/api/calendar/events', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiCreateEvent');
$router->put('/api/calendar/events/{id}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiUpdateEvent');
$router->delete('/api/calendar/events/{id}', 'IslamWiki\\Http\\Controllers\\IslamicCalendarController@apiDeleteEvent');

// Salah Routes - Phase 5 Salah Times Integration
$router->get('/salah', 'IslamWiki\\Http\\Controllers\\SalahTimeController@index');
$router->get('/salah/search', 'IslamWiki\\Http\\Controllers\\SalahTimeController@search');
$router->get('/salah/show/{date}/{locationId}', 'IslamWiki\\Http\\Controllers\\SalahTimeController@show');
$router->get('/salah/widget/{widgetKey}', 'IslamWiki\\Http\\Controllers\\SalahTimeController@widget');
$router->get('/salah/locations', 'IslamWiki\\Http\\Controllers\\SalahTimeController@locations');
$router->get('/salah/preferences', 'IslamWiki\\Http\\Controllers\\SalahTimeController@preferences');

// Salah Times API Routes
$router->get('/api/salah-times/times', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetPrayerTimes');
$router->get('/api/salah-times/locations', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetLocations');
$router->post('/api/salah-times/locations', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiAddLocation');
$router->get('/api/salah-times/preferences', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetPreferences');
$router->put('/api/salah-times/preferences', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiUpdatePreferences');
$router->get('/api/salah-times/qibla', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiCalculateQibla');
$router->get('/api/salah-times/next', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetNextPrayer');
$router->get('/api/salah-times/statistics', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetStatistics');
$router->get('/api/salah-times/methods', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetCalculationMethods');
$router->get('/api/salah-times/names', 'IslamWiki\\Http\\Controllers\\SalahTimeController@apiGetPrayerNames');

// Bayan Knowledge Graph Routes - Version 0.0.34
$router->get('/bayan', 'IslamWiki\\Http\\Controllers\\BayanController@index');
$router->get('/bayan/search', 'IslamWiki\\Http\\Controllers\\BayanController@search');
$router->get('/bayan/create', 'IslamWiki\\Http\\Controllers\\BayanController@create');
$router->post('/bayan/create', 'IslamWiki\\Http\\Controllers\\BayanController@create');
$router->get('/bayan/node/{id}', 'IslamWiki\\Http\\Controllers\\BayanController@show');
$router->post('/bayan/relationship', 'IslamWiki\\Http\\Controllers\\BayanController@createRelationship');
$router->get('/bayan/statistics', 'IslamWiki\\Http\\Controllers\\BayanController@statistics');
$router->get('/bayan/paths', 'IslamWiki\\Http\\Controllers\\BayanController@findPaths');

// Watch/Unwatch Pages - Consolidated (duplicates removed)
// Note: Watch/unwatch routes are already defined in the wiki routes section above

$router->get('/test-router-alive', function ($request) {
    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'text/plain'],
        'ROUTER IS ALIVE: ' . date('Y-m-d H:i:s')
    );
});

// Test route to check if AssetController is working
$router->get('/test-asset', 'IslamWiki\\Http\\Controllers\\AssetController@test');

// Simple test route without AssetController
$router->get('/test-simple', function ($request) {
    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'text/plain'],
        'Simple test route working!'
    );
});

// Test wiki route
$router->get('/wiki-test', function ($request) {
    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'text/plain'],
        'Wiki test route working!'
    );
});



// Quran backward compatibility redirect routes - MUST come before main routes
// These are more specific patterns that won't conflict with the main routes
$router->get('/quran/surah/{surah}', function ($request, $surah) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/quran/{$surah}"], '');
});
$router->get('/quran/surah/{surah}/ayah/{ayah}', function ($request, $surah, $ayah) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/quran/{$surah}/{$ayah}"], '');
});
$router->get('/quran/surah/{surah}/info', function ($request, $surah) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/quran/{$surah}/info"], '');
});

// Quran API backward compatibility redirect routes - MUST come before main routes
$router->get('/api/quran/surah/{surah}', function ($request, $surah) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/api/quran/{$surah}"], '');
});
$router->get('/api/quran/ayahs/{chapter}/{ayah}', function ($request, $chapter, $ayah) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => "/api/quran/{$chapter}/{$ayah}"], '');
});

// Debug route to test route matching
$router->get('/debug-route-check', function() {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Debug route is working!');
});

// Quran Routes - Must come before catch-all routes to prevent interference
$router->get('/quran', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@indexPage');
$router->get('/quran/search', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@searchPage');

// Specific routes before parameterized routes to ensure they take precedence
$router->get('/quran/juz/{juz}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@juzPage');
$router->get('/quran/page/{page}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@pagePage');

// More specific routes before less specific ones
$router->get('/quran/{surah}/info', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@surahInfoPage');

// Surah and ayah routes - order is important
$router->map(['GET', 'HEAD'], '/quran/{surah}/{ayah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@ayahPage');
$router->get('/quran/{surah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@surahPage');

// Test route (can be removed in production)
$router->get('/quran-ayah/{surah}/{ayah}', function ($request, $surah, $ayah) {
    return new \IslamWiki\Core\Http\Response(
        200,
        ['Content-Type' => 'text/html'],
        "Quran Ayah Test: Surah {$surah}, Ayah {$ayah}"
    );
});

// Quran API routes
$router->get('/api/quran', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiAyahs');
$router->get('/api/quran/ayahs', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiAyahs');
$router->get('/api/quran/ayahs/{id}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiAyah');
$router->get('/api/quran/search', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiSearch');
$router->get('/api/quran/{surah}/{ayah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiAyahByReference');
$router->get('/api/quran/tafsir/{ayahId}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiTafsir');
$router->get('/api/quran/recitation/{ayahId}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiRecitation');
$router->get('/api/quran/statistics', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiStatistics');
$router->get('/api/quran/random', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiRandomAyah');
$router->get('/api/quran/references/{pageId}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiReferences');
$router->get('/api/quran/surahs', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiSurah');
$router->get('/api/quran/juz', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiJuz');
$router->get('/api/quran/juz/{juz}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiJuzDetail');
$router->get('/api/quran/pages', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiPages');
$router->get('/api/quran/page/{page}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@apiPageDetail');

// Quran page routes
$router->get('/quran', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@indexPage');
$router->get('/quran/{surah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@surahPage');
$router->get('/quran/{surah}/{ayah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@ayahPage');

// Quran widget routes
$router->get('/quran/widget/{surah}/{ayah}', 'IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController@widget');

// Debug route for testing
$router->get('/quran-debug', function($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Debug route is working!');
});

// Catch-all legacy wiki routes (must be last)
$router->get('/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@show');
$router->get('/{slug}/edit', 'IslamWiki\\Http\\Controllers\\WikiController@edit');
$router->put('/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@update');
$router->delete('/{slug}', 'IslamWiki\\Http\\Controllers\\WikiController@destroy');
$router->get('/{slug}/history', 'IslamWiki\\Http\\Controllers\\WikiController@history');
// Note: Watch/unwatch routes are handled by the wiki routes above
