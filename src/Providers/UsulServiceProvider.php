<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Knowledge\UsulKnowledge;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;

/**
 * Usul Service Provider
 * 
 * Registers the Usul knowledge system with the application container.
 * Usul (أصول) means "principles" or "roots" in Arabic, especially in Islamic
 * jurisprudence (uṣūl al-fiqh), representing the foundational principles of Islamic knowledge.
 */
class UsulServiceProvider
{
    /**
     * Register Usul knowledge services with the container.
     */
    public function register(AsasContainer $container): void
    {
        // Register Usul as singleton
        $container->singleton(Usul::class, function () use ($container) {
            $logger = $container->get(ShahidLogger::class);
            $db = $container->get(Connection::class);
            return new UsulKnowledge($container, $logger, $db);
        });

        // Register Usul with alias for easier access
        $container->alias('knowledge', Usul::class);
        $container->alias('usul', Usul::class);

        // Register knowledge system configuration
        $container->singleton('knowledge.config', function () {
            return [
                'root_systems' => [
                    'quranic' => ['enabled' => true, 'confidence_threshold' => 0.5],
                    'hadith' => ['enabled' => true, 'confidence_threshold' => 0.5],
                    'fiqh' => ['enabled' => true, 'confidence_threshold' => 0.5],
                ],
                'classifications' => [
                    'hadith' => ['enabled' => true],
                    'scholars' => ['enabled' => true],
                    'topics' => ['enabled' => true],
                ],
                'ontologies' => [
                    'islamic_concepts' => ['enabled' => true],
                    'quranic_verses' => ['enabled' => true],
                    'hadith_chain' => ['enabled' => true],
                ],
                'schema_layers' => [
                    'content' => ['enabled' => true],
                    'relationships' => ['enabled' => true],
                    'metadata' => ['enabled' => true],
                ],
            ];
        });
    }

    /**
     * Boot the Usul service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Log that Usul knowledge system is ready
        try {
            $logger = $container->get(ShahidLogger::class);
            $logger->info('Usul knowledge system initialized', [
                'system' => 'Usul',
                'version' => '0.0.40',
                'features' => [
                    'root_systems' => ['quranic', 'hadith', 'fiqh'],
                    'classifications' => ['hadith', 'scholars', 'topics'],
                    'ontologies' => ['islamic_concepts', 'quranic_verses', 'hadith_chain'],
                    'schema_layers' => ['content', 'relationships', 'metadata']
                ]
            ]);
        } catch (\Exception $e) {
            // If logger is not available, just continue without logging
            error_log('Usul knowledge system initialized (logger not available)');
        }
    }
} 