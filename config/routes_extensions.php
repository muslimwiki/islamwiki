<?php

declare(strict_types=1);

/**
 * Extension Routes Configuration
 * 
 * Routes for all enabled extensions including SafaSkinExtension,
 * QuranExtension, HadithExtension, and others.
 * 
 * @package IslamWiki\Config
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// SafaSkinExtension Routes
if (class_exists('IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController')) {
    $router->group(['prefix' => '/admin/skins', 'middleware' => ['auth', 'admin']], function ($router) {
        $router->get('/', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'index'])
               ->name('admin.skins.index');
        $router->get('/gallery', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'gallery'])
               ->name('admin.skins.gallery');
        $router->get('/customize/{skin}', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'customize'])
               ->name('admin.skins.customize');
        $router->post('/switch', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'switchSkin'])
               ->name('admin.skins.switch');
        $router->post('/save-customization', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'saveCustomization'])
               ->name('admin.skins.save-customization');
    });

    // Public skin preview routes
    $router->group(['prefix' => '/skins', 'middleware' => ['auth']], function ($router) {
        $router->get('/preview/{skin}', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'publicPreview'])
               ->name('skins.public-preview');
        $router->get('/info/{skin}', [IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController::class, 'skinInfo'])
               ->name('skins.info');
    });
}

// QuranExtension Routes
if (class_exists('IslamWiki\Extensions\QuranExtension\Controllers\QuranController')) {
    $router->group(['prefix' => '/quran'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\QuranExtension\Controllers\QuranController::class, 'index'])
               ->name('quran.index');
        $router->get('/surah/{id}', [IslamWiki\Extensions\QuranExtension\Controllers\QuranController::class, 'showSurah'])
               ->name('quran.surah');
        $router->get('/ayah/{surah}/{ayah}', [IslamWiki\Extensions\QuranExtension\Controllers\QuranController::class, 'showAyah'])
               ->name('quran.ayah');
        $router->get('/search', [IslamWiki\Extensions\QuranExtension\Controllers\QuranController::class, 'search'])
               ->name('quran.search');
    });
}

// HadithExtension Routes
if (class_exists('IslamWiki\Extensions\HadithExtension\Controllers\HadithController')) {
    $router->group(['prefix' => '/hadith'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\HadithExtension\Controllers\HadithController::class, 'index'])
               ->name('hadith.index');
        $router->get('/collection/{collection}', [IslamWiki\Extensions\HadithExtension\Controllers\HadithController::class, 'showCollection'])
               ->name('hadith.collection');
        $router->get('/hadith/{id}', [IslamWiki\Extensions\HadithExtension\Controllers\HadithController::class, 'showHadith'])
               ->name('hadith.show');
        $router->get('/search', [IslamWiki\Extensions\HadithExtension\Controllers\HadithController::class, 'search'])
               ->name('hadith.search');
    });
}

// SalahTime Extension Routes
if (class_exists('IslamWiki\Extensions\SalahTime\Controllers\SalahTimeController')) {
    $router->group(['prefix' => '/salah'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\SalahTime\Controllers\SalahTimeController::class, 'index'])
               ->name('salah.index');
        $router->get('/times/{city}', [IslamWiki\Extensions\SalahTime\Controllers\SalahTimeController::class, 'getTimes'])
               ->name('salah.times');
        $router->get('/qibla', [IslamWiki\Extensions\SalahTime\Controllers\SalahTimeController::class, 'qiblaDirection'])
               ->name('salah.qibla');
    });
}

// HijriCalendar Extension Routes
if (class_exists('IslamWiki\Extensions\HijriCalendar\Controllers\HijriCalendarController')) {
    $router->group(['prefix' => '/calendar'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\HijriCalendar\Controllers\HijriCalendarController::class, 'index'])
               ->name('calendar.index');
        $router->get('/convert', [IslamWiki\Extensions\HijriCalendar\Controllers\HijriCalendarController::class, 'convert'])
               ->name('calendar.convert');
        $router->get('/events', [IslamWiki\Extensions\HijriCalendar\Controllers\HijriCalendarController::class, 'events'])
               ->name('calendar.events');
    });
}

// DashboardExtension Routes
if (class_exists('IslamWiki\Extensions\DashboardExtension\Controllers\DashboardController')) {
    $router->group(['prefix' => '/dashboard', 'middleware' => ['auth']], function ($router) {
        $router->get('/', [IslamWiki\Extensions\DashboardExtension\Controllers\DashboardController::class, 'index'])
               ->name('dashboard.index');
        $router->get('/widgets', [IslamWiki\Extensions\DashboardExtension\Controllers\DashboardController::class, 'widgets'])
               ->name('dashboard.widgets');
        $router->post('/widgets/update', [IslamWiki\Extensions\DashboardExtension\Controllers\DashboardController::class, 'updateWidgets'])
               ->name('dashboard.widgets.update');
    });
}

// EnhancedMarkdown Extension Routes
if (class_exists('IslamWiki\Extensions\EnhancedMarkdown\Controllers\MarkdownController')) {
    $router->group(['prefix' => '/markdown'], function ($router) {
        $router->post('/preview', [IslamWiki\Extensions\EnhancedMarkdown\Controllers\MarkdownController::class, 'preview'])
               ->name('markdown.preview');
        $router->post('/convert', [IslamWiki\Extensions\EnhancedMarkdown\Controllers\MarkdownController::class, 'convert'])
               ->name('markdown.convert');
    });
}

// MarkdownDocsViewer Extension Routes
if (class_exists('IslamWiki\Extensions\MarkdownDocsViewer\Controllers\DocsController')) {
    $router->group(['prefix' => '/docs'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\MarkdownDocsViewer\Controllers\DocsController::class, 'index'])
               ->name('docs.index');
        $router->get('/{path}', [IslamWiki\Extensions\MarkdownDocsViewer\Controllers\DocsController::class, 'show'])
               ->name('docs.show');
    });
}

// GitIntegration Extension Routes
if (class_exists('IslamWiki\Extensions\GitIntegration\Controllers\GitController')) {
    $router->group(['prefix' => '/git', 'middleware' => ['auth', 'admin']], function ($router) {
        $router->get('/', [IslamWiki\Extensions\GitIntegration\Controllers\GitController::class, 'index'])
               ->name('git.index');
        $router->get('/status', [IslamWiki\Extensions\GitIntegration\Controllers\GitController::class, 'status'])
               ->name('git.status');
        $router->post('/commit', [IslamWiki\Extensions\GitIntegration\Controllers\GitController::class, 'commit'])
               ->name('git.commit');
        $router->post('/push', [IslamWiki\Extensions\GitIntegration\Controllers\GitController::class, 'push'])
               ->name('git.push');
    });
}

// TranslatorExtension Routes
if (class_exists('IslamWiki\Extensions\TranslatorExtension\Controllers\TranslatorController')) {
    $router->group(['prefix' => '/translate'], function ($router) {
        $router->get('/', [IslamWiki\Extensions\TranslatorExtension\Controllers\TranslatorController::class, 'index'])
               ->name('translate.index');
        $router->post('/translate', [IslamWiki\Extensions\TranslatorExtension\Controllers\TranslatorController::class, 'translate'])
               ->name('translate.translate');
        $router->get('/languages', [IslamWiki\Extensions\TranslatorExtension\Controllers\TranslatorController::class, 'languages'])
               ->name('translate.languages');
    });
}

// WikiMarkupExtension Routes (disabled - now handled by WikiExtension)
// if (class_exists('IslamWiki\Extensions\WikiMarkupExtension\Controllers\WikiController')) {
//     $router->group(['prefix' => '/wiki'], function ($router) {
//         $router->get('/', [IslamWiki\Extensions\WikiMarkupExtension\Controllers\WikiController::class, 'index'])
//                ->name('wiki.index');
//         $router->get('/page/{title}', [IslamWiki\Extensions\WikiMarkupExtension\Controllers\WikiController::class, 'showPage'])
//                ->name('wiki.page');
//         $router->get('/edit/{title}', [IslamWiki\Extensions\WikiMarkupExtension\Controllers\WikiController::class, 'editPage'])
//                ->name('wiki.edit');
//         $router->post('/save', [IslamWiki\Extensions\WikiMarkupExtension\Controllers\WikiController::class, 'savePage'])
//                ->name('wiki.save');
//     });
// }

// API Routes for Extensions
$router->group(['prefix' => '/api/extensions', 'middleware' => ['auth']], function ($router) {
    
    // Extension status and information
    $router->get('/status', function () {
        return json_encode([
            'status' => 'success',
            'extensions' => [
                'SafaSkinExtension' => class_exists('IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController'),
                'QuranExtension' => class_exists('IslamWiki\Extensions\QuranExtension\Controllers\QuranController'),
                'HadithExtension' => class_exists('IslamWiki\Extensions\HadithExtension\Controllers\HadithController'),
                'SalahTime' => class_exists('IslamWiki\Extensions\SalahTime\Controllers\SalahTimeController'),
                'HijriCalendar' => class_exists('IslamWiki\Extensions\HijriCalendar\Controllers\HijriCalendarController'),
                'DashboardExtension' => class_exists('IslamWiki\Extensions\DashboardExtension\Controllers\DashboardController'),
            ]
        ]);
    })->name('api.extensions.status');
    
    // Extension configuration
    $router->get('/config/{extension}', function ($extension) {
        // Return extension configuration
        return json_encode(['status' => 'success', 'extension' => $extension]);
    })->name('api.extensions.config');
});

// Fallback route for undefined extension routes
$router->fallback(function () {
    return new IslamWiki\Core\Http\Response(
        'Extension route not found',
        404,
        ['Content-Type' => 'text/plain']
    );
}); 