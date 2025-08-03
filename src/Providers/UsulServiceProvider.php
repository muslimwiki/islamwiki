<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Asas;
use IslamWiki\Core\Knowledge\Usul;
use IslamWiki\Core\Logging\Shahid;
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
    public function register(Asas $container): void
    {
        // Register Usul as singleton
        $container->singleton(Usul::class, function () use ($container) {
            $logger = $container->get(Shahid::class);
            $db = $container->get(Connection::class);
            return new Usul($container, $logger, $db);
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
    public function boot(Asas $container): void
    {
        // Log that Usul knowledge system is ready
        $logger = $container->get(Shahid::class);
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
    }
} 