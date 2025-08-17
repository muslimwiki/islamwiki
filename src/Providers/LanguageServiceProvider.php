<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Language\LanguageService;
use IslamWiki\Core\Language\TranslationService;
use IslamWiki\Core\Http\Middleware\LanguageMiddleware;
use IslamWiki\Core\View\TwigTranslationExtension;

/**
 * Language Service Provider
 *
 * Registers the core language system with the application container.
 * Provides language detection, routing, and management services.
 */
class LanguageServiceProvider
{
    /**
     * Register language services with the container.
     */
    public function register(AsasContainer $container): void
    {
        error_log("LanguageServiceProvider: Starting registration");
        
        // Register TranslationService first
        $container->singleton(TranslationService::class, function () {
            error_log("LanguageServiceProvider: Creating TranslationService");
            return new TranslationService('en'); // Default to English
        });
        
        // Register LanguageService as singleton, and connect it to TranslationService
        $container->singleton(LanguageService::class, function (AsasContainer $container) {
            error_log("LanguageServiceProvider: Creating LanguageService");
            $languageService = new LanguageService();
            // Connect the TranslationService to LanguageService
            $translationService = $container->get(TranslationService::class);
            $languageService->setTranslationService($translationService);
            error_log("LanguageServiceProvider: Connected TranslationService to LanguageService");
            return $languageService;
        });

        // Register LanguageMiddleware
        $container->singleton(LanguageMiddleware::class, function (AsasContainer $container) {
            error_log("LanguageServiceProvider: Creating LanguageMiddleware");
            $languageService = $container->get(LanguageService::class);
            return new LanguageMiddleware($languageService);
        });
        
        // Register TwigTranslationExtension
        $container->singleton(TwigTranslationExtension::class, function (AsasContainer $container) {
            error_log("LanguageServiceProvider: Creating TwigTranslationExtension");
            $translationService = $container->get(TranslationService::class);
            $extension = new TwigTranslationExtension($translationService);
            error_log("LanguageServiceProvider: TwigTranslationExtension created successfully");
            return $extension;
        });

        // Register language configuration
        $container->singleton('language.config', function () {
            return [
                'default_language' => 'en',
                'supported_languages' => [
                    'en', 'ar', 'tr', 'ur', 'id', 'ms', 'fa', 'he'
                ],
                'rtl_languages' => ['ar', 'ur', 'fa', 'he'],
                'fallback_language' => 'en',
                'auto_detect' => true,
                'session_storage' => true,
                'url_prefix' => true,
                'cookie_name' => 'islamwiki_language',
                'cookie_expiry' => 31536000, // 1 year
            ];
        });
        error_log("LanguageServiceProvider: Registration completed");
    }

    /**
     * Boot the language service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Initialize language service
        $languageService = $container->get(LanguageService::class);
        $languageService->initializeLanguage();

        // Log that language system is ready
        error_log('LanguageServiceProvider: Language system booted successfully');
    }
} 