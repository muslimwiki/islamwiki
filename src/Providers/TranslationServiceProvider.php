<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Services\TranslationService;
use IslamWiki\Http\Middleware\SubdomainLanguageMiddleware;

/**
 * Translation Service Provider
 * 
 * Registers translation-related services and middleware with the container.
 */
class TranslationServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(AsasContainer $container): void
    {
        // Register TranslationService
        $container->singleton(TranslationService::class, function (AsasContainer $container) {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
            $cache = $container->get(\IslamWiki\Core\Caching\RihlahCaching::class);
            return new TranslationService($logger, $cache);
        });

        // Register SubdomainLanguageMiddleware
        $container->singleton(SubdomainLanguageMiddleware::class, function (AsasContainer $container) {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
            $translationService = $container->get(TranslationService::class);
            return new SubdomainLanguageMiddleware($logger, $translationService);
        });

        // Register aliases
        $container->alias('translation', TranslationService::class);
        $container->alias('language.middleware', SubdomainLanguageMiddleware::class);
    }

    /**
     * Boot the service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Check if Google Translate API key is configured
        $apiKey = $_ENV['GOOGLE_TRANSLATE_API_KEY'] ?? '';
        
        if (empty($apiKey)) {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
            $logger->warning('Google Translate API key not configured. Translation service will use fallback methods.');
        }
    }
} 