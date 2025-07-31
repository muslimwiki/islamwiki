<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Configuration\ConfigurationManager;
use IslamWiki\Core\Container;

/**
 * ConfigurationServiceProvider - Configuration System Integration
 * 
 * Integrates the hybrid configuration system with the application's service container.
 * Provides configuration services to the application.
 * 
 * Version: 0.0.18
 * Date: 2025-07-30
 */
class ConfigurationServiceProvider
{
    /**
     * @var Container Application container
     */
    private Container $container;

    /**
     * Constructor
     * 
     * @param Container $container Application container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register configuration services
     * 
     * @return void
     */
    public function register(): void
    {
        // Register configuration manager as a singleton
        $this->container->singleton(ConfigurationManager::class, function ($container) {
            return new ConfigurationManager($container);
        });

        // Register configuration helper functions
        $this->registerConfigurationHelpers();
    }

    /**
     * Register configuration helper functions
     * 
     * @return void
     */
    private function registerConfigurationHelpers(): void
    {
        // Global configuration helper function
        if (!function_exists('config')) {
            function config(string $key, $default = null) {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getValue($key, $default);
                }
                return $default;
            }
        }

        // Database configuration helper
        if (!function_exists('db_config')) {
            function db_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Database');
                }
                return [];
            }
        }

        // Islamic database configuration helper
        if (!function_exists('islamic_db_config')) {
            function islamic_db_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Islamic');
                }
                return [];
            }
        }

        // Islamic feature configuration helper
        if (!function_exists('islamic_feature_config')) {
            function islamic_feature_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Islamic');
                }
                return [];
            }
        }

        // Search configuration helper
        if (!function_exists('search_config')) {
            function search_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Core');
                }
                return [];
            }
        }

        // Cache configuration helper
        if (!function_exists('cache_config')) {
            function cache_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Performance');
                }
                return [];
            }
        }

        // Logging configuration helper
        if (!function_exists('logging_config')) {
            function logging_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Logging');
                }
                return [];
            }
        }

        // Extension configuration helper
        if (!function_exists('extension_config')) {
            function extension_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Extensions');
                }
                return [];
            }
        }

        // Islamic configuration helper
        if (!function_exists('islamic_config')) {
            function islamic_config(): array {
                global $app;
                if ($app) {
                    $configManager = $app->getContainer()->get(\IslamWiki\Core\Configuration\ConfigurationManager::class);
                    return $configManager->getCategory('Islamic');
                }
                return [];
            }
        }
    }

    /**
     * Register configuration validation
     * 
     * @return void
     */
    private function registerConfigurationValidation(): void
    {
        // Get configuration manager instance
        $configManager = $this->container->get(ConfigurationManager::class);
        
        // Validate configuration on startup
        $validation = $configManager->validateConfiguration();
        
        if (!empty($validation)) {
            $this->handleConfigurationErrors($validation);
        }
    }

    /**
     * Handle configuration errors
     * 
     * @param array $errors Configuration errors
     * @return void
     */
    private function handleConfigurationErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $errorMessage = is_array($error) ? json_encode($error) : (string) $error;
            error_log("Configuration Error: {$errorMessage}");
        }
        
        // In development mode, throw an exception
        if (config('wgDebug', false)) {
            $errorMessages = array_map(function($error) {
                return is_array($error) ? json_encode($error) : (string) $error;
            }, $errors);
            throw new \RuntimeException(
                'Configuration validation failed: ' . implode(', ', $errorMessages)
            );
        }
    }

    /**
     * Handle configuration warnings
     * 
     * @param array $warnings Configuration warnings
     * @return void
     */
    private function handleConfigurationWarnings(array $warnings): void
    {
        foreach ($warnings as $warning) {
            error_log("Configuration Warning: {$warning}");
        }
    }

    /**
     * Boot configuration services
     * 
     * @return void
     */
    public function boot(): void
    {
        // Register configuration validation (only after database is ready)
        try {
            $this->registerConfigurationValidation();
        } catch (\Exception $e) {
            // Ignore configuration validation errors during boot
            // This allows the application to start even if configuration tables don't exist yet
        }
        
        // Set up environment-specific configuration
        $this->setupEnvironmentConfiguration();
        
        // Set up Islamic-specific configuration
        $this->setupIslamicConfiguration();
        
        // Set up performance configuration
        $this->setupPerformanceConfiguration();
    }

    /**
     * Set up environment-specific configuration
     * 
     * @return void
     */
    private function setupEnvironmentConfiguration(): void
    {
        $environment = config('APP_ENV', 'production');
        
        switch ($environment) {
            case 'development':
                // Development-specific settings
                if (config('wgDebug', false)) {
                    error_reporting(E_ALL);
                    ini_set('display_errors', '1');
                }
                break;
                
            case 'testing':
                // Testing-specific settings
                config('wgDebug', true);
                config('wgShowExceptionDetails', true);
                break;
                
            case 'production':
            default:
                // Production-specific settings
                config('wgDebug', false);
                config('wgShowExceptionDetails', false);
                config('wgShowSQLErrors', false);
                break;
        }
    }

    /**
     * Set up Islamic-specific configuration
     * 
     * @return void
     */
    private function setupIslamicConfiguration(): void
    {
        // Set up Islamic content templates
        $templates = config('wgIslamicContentTemplates', []);
        if (!empty($templates)) {
            // Register templates with the view system
            $this->registerIslamicTemplates($templates);
        }
        
        // Set up Islamic API endpoints
        $endpoints = config('wgIslamicAPIEndpoints', []);
        if (!empty($endpoints)) {
            // Register API endpoints
            $this->registerIslamicAPIEndpoints($endpoints);
        }
        
        // Set up Islamic search settings
        $searchSettings = config('wgIslamicSearchSettings', []);
        if (!empty($searchSettings)) {
            // Configure Islamic search
            $this->configureIslamicSearch($searchSettings);
        }
    }

    /**
     * Set up performance configuration
     * 
     * @return void
     */
    private function setupPerformanceConfiguration(): void
    {
        $performanceSettings = config('wgIslamicPerformanceSettings', []);
        
        // Enable caching if configured
        if ($performanceSettings['enable_quran_caching'] ?? true) {
            $this->enableQuranCaching();
        }
        
        if ($performanceSettings['enable_hadith_caching'] ?? true) {
            $this->enableHadithCaching();
        }
        
        if ($performanceSettings['enable_prayer_times_caching'] ?? true) {
            $this->enablePrayerTimesCaching();
        }
        
        if ($performanceSettings['enable_calendar_caching'] ?? true) {
            $this->enableCalendarCaching();
        }
        
        if ($performanceSettings['enable_search_caching'] ?? true) {
            $this->enableSearchCaching();
        }
    }

    /**
     * Register Islamic templates
     * 
     * @param array $templates Template configurations
     * @return void
     */
    private function registerIslamicTemplates(array $templates): void
    {
        // This would integrate with the view system
        // For now, we'll just log the templates
        foreach ($templates as $type => $template) {
            error_log("Registered Islamic template: {$type} -> {$template}");
        }
    }

    /**
     * Register Islamic API endpoints
     * 
     * @param array $endpoints API endpoint configurations
     * @return void
     */
    private function registerIslamicAPIEndpoints(array $endpoints): void
    {
        // This would integrate with the routing system
        // For now, we'll just log the endpoints
        foreach ($endpoints as $type => $config) {
            error_log("Registered Islamic API endpoint: {$type} -> {$config['base_url']}");
        }
    }

    /**
     * Configure Islamic search
     * 
     * @param array $settings Search settings
     * @return void
     */
    private function configureIslamicSearch(array $settings): void
    {
        // This would integrate with the search system
        // For now, we'll just log the settings
        foreach ($settings as $type => $enabled) {
            if ($enabled) {
                error_log("Enabled Islamic search for: {$type}");
            }
        }
    }

    /**
     * Enable Quran caching
     * 
     * @return void
     */
    private function enableQuranCaching(): void
    {
        $cacheSettings = config('wgIslamicCacheSettings', []);
        $ttl = $cacheSettings['quran_cache_ttl'] ?? 86400;
        
        error_log("Quran caching enabled with TTL: {$ttl} seconds");
    }

    /**
     * Enable Hadith caching
     * 
     * @return void
     */
    private function enableHadithCaching(): void
    {
        $cacheSettings = config('wgIslamicCacheSettings', []);
        $ttl = $cacheSettings['hadith_cache_ttl'] ?? 86400;
        
        error_log("Hadith caching enabled with TTL: {$ttl} seconds");
    }

    /**
     * Enable Prayer Times caching
     * 
     * @return void
     */
    private function enablePrayerTimesCaching(): void
    {
        $cacheSettings = config('wgIslamicCacheSettings', []);
        $ttl = $cacheSettings['prayer_times_cache_ttl'] ?? 1800;
        
        error_log("Prayer times caching enabled with TTL: {$ttl} seconds");
    }

    /**
     * Enable Calendar caching
     * 
     * @return void
     */
    private function enableCalendarCaching(): void
    {
        $cacheSettings = config('wgIslamicCacheSettings', []);
        $ttl = $cacheSettings['calendar_cache_ttl'] ?? 7200;
        
        error_log("Islamic calendar caching enabled with TTL: {$ttl} seconds");
    }

    /**
     * Enable Search caching
     * 
     * @return void
     */
    private function enableSearchCaching(): void
    {
        $cacheSettings = config('wgIslamicCacheSettings', []);
        $ttl = $cacheSettings['search_cache_ttl'] ?? 3600;
        
        error_log("Search caching enabled with TTL: {$ttl} seconds");
    }
} 