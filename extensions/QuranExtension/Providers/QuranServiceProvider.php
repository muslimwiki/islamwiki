<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Providers;

use Container;\Container
use IslamWiki\Extensions\QuranExtension\QuranExtension;
use IslamWiki\Extensions\QuranExtension\Models\QuranAyah;
use IslamWiki\Extensions\QuranExtension\Models\QuranSurah;
use App\Services\QuranService;

/**
 * QuranServiceProvider
 * 
 * Registers the QuranExtension with the application container
 * and provides Quran-related services
 */
class QuranServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        // Register the main extension
        $container->singleton(QuranExtension::class, function ($container) {
            return new QuranExtension($container);
        });

        // Register Quran models
        $container->singleton(QuranAyah::class, function ($container) {
            return new QuranAyah($container->get('db'));
        });

        $container->singleton(QuranSurah::class, function ($container) {
            return new QuranSurah($container->get('db'));
        });

        // Register Quran service
        $container->singleton(QuranService::class, function ($container) {
            return new QuranService(
                $container->get(QuranAyah::class),
                $container->get(QuranSurah::class)
            );
        });

        // Register Quran extension configuration
        $container->singleton('quran.config', function () {
            return [
                'default_language' => 'en',
                'default_translator' => 'Saheeh International',
                'search_limit' => 50,
                'enable_tafsir' => false,
                'enable_recitation' => false,
                'audio_quality' => 'high',
                'cache_duration' => 3600, // 1 hour
                'max_search_results' => 100,
                'enable_bookmarks' => true,
                'enable_sharing' => true,
                'enable_download' => false,
                'recitation_sources' => [
                    'default' => 'mishary_rashid_alafasy',
                    'available' => [
                        'mishary_rashid_alafasy' => 'Mishary Rashid Alafasy',
                        'abdul_rahman_al_sudais' => 'Abdul Mercyn Al-Sudais',
                        'saad_al_ghamdi' => 'Saad Al-Ghamdi',
                        'maher_al_mueaqly' => 'Maher Al-Mueaqly'
                    ]
                ],
                'translation_sources' => [
                    'en' => [
                        'Saheeh International' => 'Saheeh International',
                        'Pickthall' => 'Pickthall',
                        'Yusuf Ali' => 'Yusuf Ali',
                        'Shakir' => 'Shakir',
                        'Hilali & Khan' => 'Hilali & Khan'
                    ],
                    'ar' => [
                        'Tafsir Al-Tabari' => 'Tafsir Al-Tabari',
                        'Tafsir Ibn Kathir' => 'Tafsir Ibn Kathir',
                        'Tafsir Al-Qurtubi' => 'Tafsir Al-Qurtubi'
                    ],
                    'ur' => [
                        'Fateh Muhammad Jalandhri' => 'Fateh Muhammad Jalandhri',
                        'Syed Zeeshan Haider Jawadi' => 'Syed Zeeshan Haider Jawadi'
                    ]
                ]
            ];
        });

        // Register Quran routes
        $this->registerRoutes($container);

        // Register Quran views
        $this->registerViews($container);

        // Register Quran middleware
        $this->registerMiddleware($container);
    }

    /**
     * Boot services
     */
    public function boot(Container $container): void
    {
        // Initialize the extension
        $extension = $container->get(QuranExtension::class);
        $extension->onInitialize();

        // Register Quran assets
        $this->registerAssets($container);

        // Register Quran commands
        $this->registerCommands($container);
    }

    /**
     * Register Quran routes
     */
    protected function registerRoutes(Container $container): void
    {
        // Routes are now registered by QuranExtension.php to avoid duplication
        // This method is kept for future use if needed
    }

    /**
     * Register Quran views
     */
    protected function registerViews(Container $container): void
    {
        $viewManager = $container->get('view');
        
        if ($viewManager) {
            // Register Quran view namespace
            $viewManager->addNamespace('quran', __DIR__ . '/../../resources/views');
            
            // Register Quran view composers
            $viewManager->composer('quran::*', function ($view) use ($container) {
                $view->with('quranConfig', $container->get('quran.config'));
                $view->with('quranService', $container->get(QuranService::class));
            });
        }
    }

    /**
     * Register Quran middleware
     */
    protected function registerMiddleware(Container $container): void
    {
        $middlewareManager = $container->get('middleware');
        
        if ($middlewareManager) {
            // Register Quran-specific middleware
            $middlewareManager->alias('quran.auth', \IslamWiki\Extensions\QuranExtension\Http\Middleware\QuranAuthMiddleware::class);
            $middlewareManager->alias('quran.cache', \IslamWiki\Extensions\QuranExtension\Http\Middleware\QuranCacheMiddleware::class);
        }
    }

    /**
     * Register Quran assets
     */
    protected function registerAssets(Container $container): void
    {
        $assetManager = $container->get('assets');
        
        if ($assetManager) {
            // Register Quran CSS
            $assetManager->css('quran', [
                'quran/css/quran.css' => [
                    'path' => __DIR__ . '/../../resources/assets/css/quran.css',
                    'version' => '1.0.0'
                ]
            ]);

            // Register Quran JavaScript
            $assetManager->js('quran', [
                'quran/js/quran.js' => [
                    'path' => __DIR__ . '/../../resources/assets/js/quran.js',
                    'version' => '1.0.0'
                ]
            ]);
        }
    }

    /**
     * Register Quran commands
     */
    protected function registerCommands(Container $container): void
    {
        $commandManager = $container->get('commands');
        
        if ($commandManager) {
            // Register Quran import command
            $commandManager->register(\IslamWiki\Extensions\QuranExtension\Console\Commands\ImportQuranCommand::class);
            
            // Register Quran cache command
            $commandManager->register(\IslamWiki\Extensions\QuranExtension\Console\Commands\CacheQuranCommand::class);
            
            // Register Quran stats command
            $commandManager->register(\IslamWiki\Extensions\QuranExtension\Console\Commands\QuranStatsCommand::class);
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            QuranExtension::class,
            QuranAyah::class,
            QuranSurah::class,
            QuranService::class,
            'quran.config'
        ];
    }
}
