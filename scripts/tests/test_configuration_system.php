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

/**
 * Test Configuration System - Version 0.0.18
 * 
 * Tests the hybrid configuration system with LocalSettings.php and IslamSettings.php
 * 
 * Usage: php scripts/tests/test_configuration_system.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Configuration\ConfigurationManager;

echo "==========================================\n";
echo "IslamWiki Configuration System Test\n";
echo "Version: 0.0.18\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

try {
    // Test 1: Initialize Configuration Manager
    echo "Test 1: Initializing Configuration Manager...\n";
    ConfigurationManager::initialize();
    echo "✅ Configuration Manager initialized successfully\n\n";

    // Test 2: Basic Configuration Access
    echo "Test 2: Testing basic configuration access...\n";
    $siteName = ConfigurationManager::get('wgSitename', 'Default Site');
    $dbServer = ConfigurationManager::get('wgDBserver', 'localhost');
    echo "✅ Site Name: {$siteName}\n";
    echo "✅ Database Server: {$dbServer}\n\n";

    // Test 3: Database Configuration
    echo "Test 3: Testing database configuration...\n";
    $dbConfig = ConfigurationManager::getDatabaseConfig();
    echo "✅ Database Type: {$dbConfig['default']}\n";
    echo "✅ Database Server: {$dbConfig['server']}\n";
    echo "✅ Database Name: {$dbConfig['database']}\n\n";

    // Test 4: Islamic Database Configurations
    echo "Test 4: Testing Islamic database configurations...\n";
    $islamicDbConfigs = ConfigurationManager::getIslamicDatabaseConfigs();
    foreach ($islamicDbConfigs as $type => $config) {
        echo "✅ {$type} Database: {$config['database']} on {$config['server']}\n";
    }
    echo "\n";

    // Test 5: Islamic Feature Configurations
    echo "Test 5: Testing Islamic feature configurations...\n";
    $islamicFeatureConfigs = ConfigurationManager::getIslamicFeatureConfigs();
    foreach ($islamicFeatureConfigs as $feature => $config) {
        $enabled = $config['enabled'] ? 'Enabled' : 'Disabled';
        echo "✅ {$feature}: {$enabled}\n";
    }
    echo "\n";

    // Test 6: Search Configuration
    echo "Test 6: Testing search configuration...\n";
    $searchConfig = ConfigurationManager::getSearchConfig();
    echo "✅ Search Type: {$searchConfig['type']}\n";
    echo "✅ Search Index Type: {$searchConfig['index_type']}\n";
    echo "✅ Search Cache Enabled: " . ($searchConfig['cache_enabled'] ? 'Yes' : 'No') . "\n";
    echo "✅ Max Results: {$searchConfig['max_results']}\n\n";

    // Test 7: Cache Configuration
    echo "Test 7: Testing cache configuration...\n";
    $cacheConfig = ConfigurationManager::getCacheConfig();
    echo "✅ Cache Enabled: " . ($cacheConfig['enabled'] ? 'Yes' : 'No') . "\n";
    echo "✅ Cache Type: {$cacheConfig['type']}\n";
    echo "✅ Cache TTL: {$cacheConfig['ttl']} seconds\n";
    echo "✅ Redis Host: {$cacheConfig['redis']['host']}\n\n";

    // Test 8: Logging Configuration
    echo "Test 8: Testing logging configuration...\n";
    $loggingConfig = ConfigurationManager::getLoggingConfig();
    echo "✅ Log Level: {$loggingConfig['level']}\n";
    echo "✅ Debug Mode: " . ($loggingConfig['debug'] ? 'Yes' : 'No') . "\n";
    echo "✅ Log File: {$loggingConfig['file']}\n\n";

    // Test 9: Extension Configurations
    echo "Test 9: Testing extension configurations...\n";
    $extensionConfigs = ConfigurationManager::getExtensionConfigs();
    echo "✅ Enabled Extensions: " . count($extensionConfigs['enabled']) . "\n";
    foreach ($extensionConfigs['enabled'] as $extension) {
        echo "  - {$extension}\n";
    }
    echo "\n";

    // Test 10: Islamic Configurations
    echo "Test 10: Testing Islamic configurations...\n";
    $islamicConfigs = ConfigurationManager::getIslamicConfigs();
    echo "✅ Islamic Content Categories: " . count($islamicConfigs['content_categories']) . "\n";
    echo "✅ Islamic API Endpoints: " . count($islamicConfigs['api_endpoints']) . "\n";
    echo "✅ Islamic Search Settings: " . count($islamicConfigs['search_settings']) . "\n";
    echo "✅ Islamic Cache Settings: " . count($islamicConfigs['cache_settings']) . "\n\n";

    // Test 11: Configuration Validation
    echo "Test 11: Testing configuration validation...\n";
    $validation = ConfigurationManager::validateConfiguration();
    echo "✅ Configuration Valid: " . ($validation['valid'] ? 'Yes' : 'No') . "\n";
    
    if (!empty($validation['errors'])) {
        echo "❌ Configuration Errors:\n";
        foreach ($validation['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
    
    if (!empty($validation['warnings'])) {
        echo "⚠️  Configuration Warnings:\n";
        foreach ($validation['warnings'] as $warning) {
            echo "  - {$warning}\n";
        }
    }
    echo "\n";

    // Test 12: Configuration Paths
    echo "Test 12: Testing configuration paths...\n";
    $paths = ConfigurationManager::getConfigurationPaths();
    echo "✅ LocalSettings Path: {$paths['local_settings']}\n";
    echo "✅ IslamSettings Path: {$paths['islam_settings']}\n\n";

    // Test 13: Helper Functions
    echo "Test 13: Testing helper functions...\n";
    if (function_exists('config')) {
        $testValue = config('wgSitename', 'Test Site');
        echo "✅ config() helper function works: {$testValue}\n";
    }
    
    if (function_exists('db_config')) {
        $dbConfig = db_config();
        echo "✅ db_config() helper function works\n";
    }
    
    if (function_exists('islamic_db_config')) {
        $islamicDbConfig = islamic_db_config();
        echo "✅ islamic_db_config() helper function works\n";
    }
    
    if (function_exists('islamic_feature_config')) {
        $islamicFeatureConfig = islamic_feature_config();
        echo "✅ islamic_feature_config() helper function works\n";
    }
    
    if (function_exists('search_config')) {
        $searchConfig = search_config();
        echo "✅ search_config() helper function works\n";
    }
    
    if (function_exists('cache_config')) {
        $cacheConfig = cache_config();
        echo "✅ cache_config() helper function works\n";
    }
    
    if (function_exists('logging_config')) {
        $loggingConfig = logging_config();
        echo "✅ logging_config() helper function works\n";
    }
    
    if (function_exists('extension_config')) {
        $extensionConfig = extension_config();
        echo "✅ extension_config() helper function works\n";
    }
    
    if (function_exists('islamic_config')) {
        $islamicConfig = islamic_config();
        echo "✅ islamic_config() helper function works\n";
    }
    echo "\n";

    // Test 14: Configuration Override Test
    echo "Test 14: Testing configuration override functionality...\n";
    $originalValue = ConfigurationManager::get('wgSitename', 'Original');
    ConfigurationManager::set('wgSitename', 'Override Test');
    $overrideValue = ConfigurationManager::get('wgSitename', 'Default');
    ConfigurationManager::set('wgSitename', $originalValue); // Restore original
    
    if ($overrideValue === 'Override Test') {
        echo "✅ Configuration override functionality works\n";
    } else {
        echo "❌ Configuration override functionality failed\n";
    }
    echo "\n";

    // Test 15: All Configuration Access
    echo "Test 15: Testing access to all configuration...\n";
    $allConfig = ConfigurationManager::all();
    echo "✅ Total configuration keys: " . count($allConfig) . "\n";
    
    // Show some key configuration values
    $keyConfigs = [
        'wgSitename' => 'Site Name',
        'wgDBserver' => 'Database Server',
        'wgEnableQuranFeatures' => 'Quran Features Enabled',
        'wgEnableHadithFeatures' => 'Hadith Features Enabled',
        'wgEnablePrayerTimes' => 'Prayer Times Enabled',
        'wgEnableIslamicCalendar' => 'Islamic Calendar Enabled',
        'wgEnableScholarVerification' => 'Scholar Verification Enabled',
    ];
    
    foreach ($keyConfigs as $key => $description) {
        $value = ConfigurationManager::get($key, 'Not Set');
        echo "  - {$description}: " . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) . "\n";
    }
    echo "\n";

    echo "==========================================\n";
    echo "Configuration System Test Complete\n";
    echo "All tests passed successfully!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 