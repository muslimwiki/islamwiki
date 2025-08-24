<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Providers;

use Container;\Container
use IslamWiki\Extensions\HadithExtension\HadithExtension;
use IslamWiki\Extensions\HadithExtension\Models\HadithCollection;
use IslamWiki\Extensions\HadithExtension\Models\HadithBook;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarrator;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarration;
use IslamWiki\Extensions\HadithExtension\Models\HadithKeyword;

/**
 * HadithServiceProvider
 * 
 * Registers the HadithExtension with the application container
 * and provides Hadith-related services
 */
class HadithServiceProvider
{
    /**
     * Register services
     */
    public function register(Container $container): void
    {
        // Register the main extension
        $container->singleton(HadithExtension::class, function ($container) {
            return new HadithExtension($container);
        });

        // Register Hadith models
        $this->registerModels($container);
        
        // Register services
        $this->registerServices($container);
        
        // Register repositories
        $this->registerRepositories($container);
        
        // Register routes
        $this->registerRoutes($container);
        
        // Register views
        $this->registerViews($container);
        
        // Register middleware
        $this->registerMiddleware($container);
    }
    
    /**
     * Register Hadith models with the container
     */
    protected function registerModels(Container $container): void
    {
        $container->singleton(HadithCollection::class, function ($container) {
            return new HadithCollection($container->get('db'));
        });
        
        $container->singleton(HadithBook::class, function ($container) {
            return new HadithBook($container->get('db'));
        });
        
        $container->singleton(HadithNarrator::class, function ($container) {
            return new HadithNarrator($container->get('db'));
        });
        
        $container->singleton(HadithNarration::class, function ($container) {
            return new HadithNarration($container->get('db'));
        });
        
        $container->singleton(HadithKeyword::class, function ($container) {
            return new HadithKeyword($container->get('db'));
        });
    }
    
    /**
     * Register Hadith services with the container
     */
    protected function registerServices(Container $container): void
    {
        // Register any services here
        // Example:
        // $container->singleton('hadith.search', function ($container) {
        //     return new \App\Services\HadithSearchService(
        //         $container->get(HadithNarration::class),
        //         $container->get(HadithKeyword::class)
        //     );
        // });
    }
    
    /**
     * Register Hadith repositories with the container
     */
    protected function registerRepositories(Container $container): void
    {
        // Register any repositories here
        // Example:
        // $container->singleton('hadith.collection.repository', function ($container) {
        //     return new \App\Repositories\HadithCollectionRepository(
        //         $container->get(HadithCollection::class)
        //     );
        // });
    }
    
    /**
     * Register Hadith routes
     */
    protected function registerRoutes(Container $container): void
    {
        $router = $container->get('router');
        
        if ($router) {
            $router->group(['prefix' => 'hadiths'], function ($router) {
                // Collection routes
                $router->get('/', 'HadithController@index')->name('hadith.index');
                $router->get('/collection/{collection}', 'HadithController@collection')
                    ->name('hadith.collection');
                
                // Book routes
                $router->get('/collection/{collection}/book/{book}', 'HadithController@book')
                    ->name('hadith.book');
                
                // Hadith routes
                $router->get('/collection/{collection}/hadith/{hadith}', 'HadithController@show')
                    ->name('hadith.show');
                
                // Narrator routes
                $router->get('/narrator/{narrator}', 'HadithController@narrator')
                    ->name('hadith.narrator');
                
                // Search routes
                $router->get('/search', 'HadithController@search')
                    ->name('hadith.search');
                
                // API routes
                $router->group(['prefix' => 'api'], function ($router) {
                    $router->get('/search', 'Api\HadithController@search')
                        ->name('api.hadith.search');
                    
                    $router->get('/collections', 'Api\HadithController@collections')
                        ->name('api.hadith.collections');
                    
                    $router->get('/collection/{collection}', 'Api\HadithController@collection')
                        ->name('api.hadith.collection');
                    
                    $router->get('/hadith/{hadith}', 'Api\HadithController@hadith')
                        ->name('api.hadith.show');
                    
                    $router->get('/narrator/{narrator}', 'Api\HadithController@narrator')
                        ->name('api.hadith.narrator');
                });
                
                // Admin routes
                $router->group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function ($router) {
                    $router->get('/hadith', 'Admin\HadithController@index')
                        ->name('admin.hadith.index');
                    
                    $router->get('/hadith/import', 'Admin\HadithController@importForm')
                        ->name('admin.hadith.import.form');
                    
                    $router->post('/hadith/import', 'Admin\HadithController@import')
                        ->name('admin.hadith.import');
                });
            });
        }
    }
    
    /**
     * Register Hadith views
     */
    protected function registerViews(Container $container): void
    {
        $view = $container->get('view');
        
        if ($view) {
            // Register view namespaces
            $view->addNamespace('hadith', __DIR__ . '/../../resources/views/hadith');
            
            // Register view composers
            $view->composer('hadith::*', function ($view) use ($container) {
                // Share common data with all hadith views
                $view->with([
                    'collections' => $container->get(HadithCollection::class)::getActiveCollections($container->get('db')),
                    'currentRoute' => $container->get('request')->path(),
                ]);
            });
        }
    }
    
    /**
     * Register Hadith middleware
     */
    protected function registerMiddleware(Container $container): void
    {
        $middlewareManager = $container->get('middleware');
        
        if ($middlewareManager) {
            // Register Hadith-specific middleware
            // Example:
            // $middlewareManager->alias('hadith.auth', \IslamWiki\Extensions\HadithExtension\Http\Middleware\HadithAuthMiddleware::class);
            // $middlewareManager->alias('hadith.cache', \IslamWiki\Extensions\HadithExtension\Http\Middleware\HadithCacheMiddleware::class);
        }
    }
    
    /**
     * Bootstrap services
     */
    public function boot(Container $container): void
    {
        // Publish configuration
        $this->publishConfig($container);
        
        // Load migrations
        $this->loadMigrations($container);
        
        // Load translations
        $this->loadTranslations($container);
        
        // Register commands
        $this->registerCommands($container);
    }
    
    /**
     * Publish configuration files
     */
    protected function publishConfig(Container $container): void
    {
        $config = $container->get('config');
        
        if ($config) {
            // Merge config from extension with application config
            $config->set('hadith', array_merge(
                require __DIR__ . '/../../config/hadith.php',
                $config->get('hadith', [])
            ));
        }
    }
    
    /**
     * Load database migrations
     */
    protected function loadMigrations(Container $container): void
    {
        $migrationManager = $container->get('migration');
        
        if ($migrationManager) {
            $migrationManager->path(__DIR__ . '/../../database/migrations');
        }
    }
    
    /**
     * Load translations
     */
    protected function loadTranslations(Container $container): void
    {
        $translator = $container->get('translator');
        
        if ($translator) {
            $translator->addNamespace('hadith', __DIR__ . '/../../resources/lang');
        }
    }
    
    /**
     * Register console commands
     */
    protected function registerCommands(Container $container): void
    {
        $console = $container->get('console');
        
        if ($console) {
            // Register Hadith-related console commands
            // Example:
            // $console->command('hadith:import', \IslamWiki\Extensions\HadithExtension\Console\Commands\ImportHadithCommand::class);
            // $console->command('hadith:index', \IslamWiki\Extensions\HadithExtension\Console\Commands\IndexHadithCommand::class);
        }
    }
}
