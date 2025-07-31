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

namespace IslamWiki\Core\Configuration;

/**
 * ConfigurationManager - Hybrid Configuration System
 * 
 * Manages the hybrid configuration system with LocalSettings.php and IslamSettings.php
 * Provides a unified interface for accessing all configuration settings.
 * 
 * Version: 0.0.18
 * Date: 2025-07-30
 */
class ConfigurationManager
{
    /**
     * @var array Configuration settings cache
     */
    private static array $configCache = [];

    /**
     * @var bool Whether configuration has been loaded
     */
    private static bool $loaded = false;

    /**
     * @var string Path to LocalSettings.php
     */
    private static string $localSettingsPath;

    /**
     * @var string Path to IslamSettings.php
     */
    private static string $islamSettingsPath;

    /**
     * Initialize the configuration manager
     * 
     * @param string $basePath Base path for configuration files
     * @return void
     */
    public static function initialize(string $basePath = null): void
    {
        $basePath = $basePath ?? dirname(__DIR__, 3);
        
        self::$localSettingsPath = $basePath . '/LocalSettings.php';
        self::$islamSettingsPath = $basePath . '/IslamSettings.php';
        
        self::loadConfiguration();
    }

    /**
     * Load configuration from LocalSettings.php and IslamSettings.php
     * 
     * @return void
     * @throws \RuntimeException If LocalSettings.php is not found
     */
    private static function loadConfiguration(): void
    {
        if (self::$loaded) {
            return;
        }

        // Check if LocalSettings.php exists
        if (!file_exists(self::$localSettingsPath)) {
            throw new \RuntimeException(
                'LocalSettings.php not found at: ' . self::$localSettingsPath
            );
        }

        // Load LocalSettings.php first
        self::loadSettingsFile(self::$localSettingsPath);

        // Load IslamSettings.php if it exists (optional override)
        if (file_exists(self::$islamSettingsPath)) {
            self::loadSettingsFile(self::$islamSettingsPath);
        }

        self::$loaded = true;
    }

    /**
     * Load settings from a configuration file
     * 
     * @param string $filePath Path to the configuration file
     * @return void
     */
    private static function loadSettingsFile(string $filePath): void
    {
        // Create a temporary scope to capture variables
        $settings = [];
        
        // Include the file in a controlled scope
        $loadSettings = function() use ($filePath, &$settings) {
            // Capture all variables defined in the file
            $definedVars = get_defined_vars();
            
            // Include the settings file
            include $filePath;
            
            // Get all variables that were defined
            $newVars = get_defined_vars();
            
            // Extract only the new variables (configuration settings)
            foreach ($newVars as $key => $value) {
                if (!isset($definedVars[$key])) {
                    $settings[$key] = $value;
                }
            }
        };
        
        $loadSettings();
        
        // Merge settings into cache
        self::$configCache = array_merge(self::$configCache, $settings);
    }

    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::initialize();
        }

        return self::$configCache[$key] ?? $default;
    }

    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        if (!self::$loaded) {
            self::initialize();
        }

        self::$configCache[$key] = $value;
    }

    /**
     * Check if a configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::initialize();
        }

        return isset(self::$configCache[$key]);
    }

    /**
     * Get all configuration settings
     * 
     * @return array All configuration settings
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::initialize();
        }

        return self::$configCache;
    }

    /**
     * Get database configuration
     * 
     * @return array Database configuration
     */
    public static function getDatabaseConfig(): array
    {
        return [
            'default' => self::get('wgDBtype', 'mysql'),
            'server' => self::get('wgDBserver', '127.0.0.1'),
            'database' => self::get('wgDBname', 'islamwiki'),
            'username' => self::get('wgDBuser', 'root'),
            'password' => self::get('wgDBpassword', ''),
            'port' => self::get('wgDBport', '3306'),
            'prefix' => self::get('wgDBprefix', ''),
            'table_options' => self::get('wgDBTableOptions', ''),
        ];
    }

    /**
     * Get Islamic database configurations
     * 
     * @return array Islamic database configurations
     */
    public static function getIslamicDatabaseConfigs(): array
    {
        return [
            'quran' => [
                'server' => self::get('wgQuranDBserver', self::get('wgDBserver', '127.0.0.1')),
                'database' => self::get('wgQuranDatabase', 'islamwiki_quran'),
                'username' => self::get('wgQuranDBuser', self::get('wgDBuser', 'root')),
                'password' => self::get('wgQuranDBpassword', self::get('wgDBpassword', '')),
                'port' => self::get('wgQuranDBport', self::get('wgDBport', '3306')),
            ],
            'hadith' => [
                'server' => self::get('wgHadithDBserver', self::get('wgDBserver', '127.0.0.1')),
                'database' => self::get('wgHadithDatabase', 'islamwiki_hadith'),
                'username' => self::get('wgHadithDBuser', self::get('wgDBuser', 'root')),
                'password' => self::get('wgHadithDBpassword', self::get('wgDBpassword', '')),
                'port' => self::get('wgHadithDBport', self::get('wgDBport', '3306')),
            ],
            'scholar' => [
                'server' => self::get('wgScholarDBserver', self::get('wgDBserver', '127.0.0.1')),
                'database' => self::get('wgScholarDatabase', 'islamwiki_scholar'),
                'username' => self::get('wgScholarDBuser', self::get('wgDBuser', 'root')),
                'password' => self::get('wgScholarDBpassword', self::get('wgDBpassword', '')),
                'port' => self::get('wgScholarDBport', self::get('wgDBport', '3306')),
            ],
            'wiki' => [
                'server' => self::get('wgWikiDBserver', self::get('wgDBserver', '127.0.0.1')),
                'database' => self::get('wgWikiDatabase', 'islamwiki_wiki'),
                'username' => self::get('wgWikiDBuser', self::get('wgDBuser', 'root')),
                'password' => self::get('wgWikiDBpassword', self::get('wgDBpassword', '')),
                'port' => self::get('wgWikiDBport', self::get('wgDBport', '3306')),
            ],
        ];
    }

    /**
     * Get Islamic feature configurations
     * 
     * @return array Islamic feature configurations
     */
    public static function getIslamicFeatureConfigs(): array
    {
        return [
            'quran' => [
                'enabled' => self::get('wgEnableQuranFeatures', true),
                'api_version' => self::get('wgQuranAPIVersion', 'v1'),
                'rate_limit' => self::get('wgQuranAPIRateLimit', 1000),
                'cache' => self::get('wgQuranAPICache', true),
                'default_translation' => self::get('wgQuranDefaultTranslation', 'en-sahih'),
            ],
            'hadith' => [
                'enabled' => self::get('wgEnableHadithFeatures', true),
                'api_version' => self::get('wgHadithAPIVersion', 'v1'),
                'rate_limit' => self::get('wgHadithAPIRateLimit', 1000),
                'cache' => self::get('wgHadithAPICache', true),
                'default_collection' => self::get('wgHadithDefaultCollection', 'bukhari'),
            ],
            'prayer_times' => [
                'enabled' => self::get('wgEnablePrayerTimes', true),
                'api' => self::get('wgPrayerTimesAPI', 'default'),
                'calculation_method' => self::get('wgPrayerTimesCalculationMethod', 'MWL'),
                'default_location' => self::get('wgPrayerTimesDefaultLocation', 'Mecca'),
            ],
            'islamic_calendar' => [
                'enabled' => self::get('wgEnableIslamicCalendar', true),
                'default_view' => self::get('wgIslamicCalendarDefaultView', 'month'),
                'show_gregorian' => self::get('wgIslamicCalendarShowGregorian', true),
            ],
            'scholar_verification' => [
                'enabled' => self::get('wgEnableScholarVerification', true),
                'required' => self::get('wgScholarVerificationRequired', false),
                'auto_approve' => self::get('wgScholarVerificationAutoApprove', false),
            ],
        ];
    }

    /**
     * Get search configuration
     * 
     * @return array Search configuration
     */
    public static function getSearchConfig(): array
    {
        return [
            'type' => self::get('wgSearchType', 'database'),
            'index_type' => self::get('wgSearchIndexType', 'fulltext'),
            'cache_enabled' => self::get('wgSearchCacheEnabled', true),
            'cache_ttl' => self::get('wgSearchCacheTTL', 3600),
            'max_results' => self::get('wgSearchMaxResults', 100),
            'min_query_length' => self::get('wgSearchMinQueryLength', 2),
            'timeout' => self::get('wgSearchTimeout', 30),
        ];
    }

    /**
     * Get cache configuration
     * 
     * @return array Cache configuration
     */
    public static function getCacheConfig(): array
    {
        return [
            'enabled' => self::get('wgCacheEnabled', true),
            'type' => self::get('wgCacheType', 'redis'),
            'ttl' => self::get('wgCacheTTL', 3600),
            'redis' => [
                'host' => self::get('wgRedisHost', '127.0.0.1'),
                'port' => self::get('wgRedisPort', 6379),
                'password' => self::get('wgRedisPassword', null),
                'database' => self::get('wgRedisDatabase', 0),
            ],
        ];
    }

    /**
     * Get logging configuration
     * 
     * @return array Logging configuration
     */
    public static function getLoggingConfig(): array
    {
        return [
            'level' => self::get('wgLogLevel', 'info'),
            'file' => self::get('wgLogFile', ''),
            'debug_file' => self::get('wgDebugLogFile', ''),
            'error_file' => self::get('wgErrorLogFile', ''),
            'debug' => self::get('wgDebug', false),
            'show_exception_details' => self::get('wgShowExceptionDetails', false),
            'show_sql_errors' => self::get('wgShowSQLErrors', false),
        ];
    }

    /**
     * Get extension configurations
     * 
     * @return array Extension configurations
     */
    public static function getExtensionConfigs(): array
    {
        return [
            'enabled' => self::get('wgEnableExtensions', []),
            'quran' => self::get('wgQuranExtensionSettings', []),
            'hadith' => self::get('wgHadithExtensionSettings', []),
            'prayer_times' => self::get('wgPrayerTimesExtensionSettings', []),
            'islamic_calendar' => self::get('wgIslamicCalendarExtensionSettings', []),
            'scholar_verification' => self::get('wgScholarVerificationExtensionSettings', []),
            'search' => self::get('wgSearchExtensionSettings', []),
        ];
    }

    /**
     * Get Islamic-specific configurations
     * 
     * @return array Islamic-specific configurations
     */
    public static function getIslamicConfigs(): array
    {
        return [
            'content_moderation' => self::get('wgIslamicContentModeration', []),
            'content_categories' => self::get('wgIslamicContentCategories', []),
            'content_templates' => self::get('wgIslamicContentTemplates', []),
            'api_endpoints' => self::get('wgIslamicAPIEndpoints', []),
            'api_auth' => self::get('wgIslamicAPIAuth', []),
            'search_settings' => self::get('wgIslamicSearchSettings', []),
            'search_weights' => self::get('wgIslamicSearchWeights', []),
            'search_filters' => self::get('wgIslamicSearchFilters', []),
            'cache_settings' => self::get('wgIslamicCacheSettings', []),
            'cache_keys' => self::get('wgIslamicCacheKeys', []),
            'log_settings' => self::get('wgIslamicLogSettings', []),
            'log_files' => self::get('wgIslamicLogFiles', []),
            'security_settings' => self::get('wgIslamicSecuritySettings', []),
            'content_validation' => self::get('wgIslamicContentValidation', []),
            'performance_settings' => self::get('wgIslamicPerformanceSettings', []),
            'database_optimization' => self::get('wgIslamicDatabaseOptimization', []),
        ];
    }

    /**
     * Validate configuration settings
     * 
     * @return array Validation results
     */
    public static function validateConfiguration(): array
    {
        $errors = [];
        $warnings = [];

        // Check critical settings
        if (empty(self::get('wgSecretKey')) || self::get('wgSecretKey') === 'your-secret-key-here') {
            $warnings[] = 'Secret key not properly configured';
        }

        if (empty(self::get('wgSessionSecret')) || self::get('wgSessionSecret') === 'your-session-secret-here') {
            $warnings[] = 'Session secret not properly configured';
        }

        // Check database settings
        if (empty(self::get('wgDBserver'))) {
            $errors[] = 'Database server not configured';
        }

        if (empty(self::get('wgDBname'))) {
            $errors[] = 'Database name not configured';
        }

        // Check Islamic database settings
        $islamicConfigs = self::getIslamicDatabaseConfigs();
        foreach ($islamicConfigs as $type => $config) {
            if (empty($config['database'])) {
                $warnings[] = "Islamic {$type} database not configured";
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'valid' => empty($errors),
        ];
    }

    /**
     * Reset configuration cache
     * 
     * @return void
     */
    public static function reset(): void
    {
        self::$configCache = [];
        self::$loaded = false;
    }

    /**
     * Get configuration file paths
     * 
     * @return array Configuration file paths
     */
    public static function getConfigurationPaths(): array
    {
        return [
            'local_settings' => self::$localSettingsPath ?? '',
            'islam_settings' => self::$islamSettingsPath ?? '',
        ];
    }
} 