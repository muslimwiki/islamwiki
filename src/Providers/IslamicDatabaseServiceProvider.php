<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use IslamWiki\Core\Asas;

/**
 * Islamic Database Service Provider
 * 
 * Registers and configures the Islamic database manager with separate
 * connections for Quran, Hadith, Wiki, and Scholar databases.
 */
class IslamicDatabaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(Asas $container): void
    {
        // Register the Islamic database manager
        $container->singleton(IslamicDatabaseManager::class, function (Container $container) {
            $config = $container->get('config');
            $dbConfig = $config->get('database.connections', []);
            
            // Extract Islamic database configurations
            $islamicConfigs = [
                'quran' => $dbConfig['quran'] ?? [],
                'hadith' => $dbConfig['hadith'] ?? [],
                'wiki' => $dbConfig['wiki'] ?? [],
                'scholar' => $dbConfig['scholar'] ?? [],
            ];
            
            return new IslamicDatabaseManager($islamicConfigs);
        });

        // Register individual database connections for easy access
        $container->singleton('db.quran', function (Container $container) {
            $manager = $container->get(IslamicDatabaseManager::class);
            return $manager->getQuranConnection();
        });

        $container->singleton('db.hadith', function (Container $container) {
            $manager = $container->get(IslamicDatabaseManager::class);
            return $manager->getHadithConnection();
        });

        $container->singleton('db.wiki', function (Container $container) {
            $manager = $container->get(IslamicDatabaseManager::class);
            return $manager->getWikiConnection();
        });

        $container->singleton('db.scholar', function (Container $container) {
            $manager = $container->get(IslamicDatabaseManager::class);
            return $manager->getScholarConnection();
        });
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot(Container $container): void
    {
        // Test database connections on boot
        try {
            $manager = $container->get(IslamicDatabaseManager::class);
            $results = $manager->testConnections();
            
            // Log connection status
            foreach ($results as $type => $result) {
                if ($result['status'] === 'connected') {
                    error_log("[IslamicDatabase] {$type} database connected successfully");
                } else {
                    error_log("[IslamicDatabase] {$type} database connection failed: " . $result['error']);
                }
            }
        } catch (\Exception $e) {
            error_log("[IslamicDatabase] Error testing connections: " . $e->getMessage());
        }
    }
} 