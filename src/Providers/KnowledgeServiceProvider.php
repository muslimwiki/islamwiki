<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use Container;\Container
use Knowledge;\Knowledge
use Logger;\Logger
use IslamWiki\Core\Database\Connection;

/**
 * Knowledge Service Provider
 *
 * Registers the Knowledge knowledge system with the application container.
 * Knowledge (أصول) means "principles" or "roots" in Arabic, especially in Islamic
 * jurisprudence (uṣūl al-fiqh), representing the foundational principles of Islamic knowledge.
 */
class KnowledgeServiceProvider
{
    /**
     * Register Knowledge knowledge services with the container.
     */
    public function register(Container $container): void
    {
        // Register Knowledge as singleton with lazy dependency resolution
        $container->set(Knowledge::class, function () use ($container) {
            // Only resolve dependencies when the service is actually requested
            $logger = $container->get(Logger::class);
            $db = $container->get(Connection::class);
            return new Knowledge($container, $logger, $db);
        });

        // Register Knowledge with alias for easier access
        $container->alias('knowledge', Knowledge::class);
        $container->alias('usul', Knowledge::class);

        // Register knowledge system configuration
        $container->set('knowledge.config', function () {
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
                    'quranic_ayahs' => ['enabled' => true],
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
     * Boot the Knowledge service provider.
     */
    public function boot(Container $container): void
    {
        // Log that Knowledge knowledge system is ready
        try {
            $logger = $container->get(Logger::class);
            $logger->info('Knowledge knowledge system initialized', [
                'system' => 'Knowledge',
                'version' => '0.0.40',
                'features' => [
                    'root_systems' => ['quranic', 'hadith', 'fiqh'],
                    'classifications' => ['hadith', 'scholars', 'topics'],
                    'ontologies' => ['islamic_concepts', 'quranic_ayahs', 'hadith_chain'],
                    'schema_layers' => ['content', 'relationships', 'metadata']
                ]
            ]);
        } catch (\Exception $e) {
            // If logger is not available, just continue without logging
            error_log('Knowledge knowledge system initialized (logger not available)');
        }
    }
}
