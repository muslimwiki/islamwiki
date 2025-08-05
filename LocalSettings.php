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
 * LocalSettings.php - Main Configuration File
 * 
 * This file contains the main configuration settings for IslamWiki.
 * It follows MediaWiki-inspired structure while incorporating modern PHP practices.
 * 
 * Version: 0.0.34
 * Date: 2025-08-02
 */

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    try {
        if (class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        }
    } catch (Exception $e) {
        // Dotenv not available, continue without it
    }
}

// Helper function to get environment variables with defaults
if (!function_exists('env')) {
    function env($key, $default = null) {
        try {
            return $_ENV[$key] ?? $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

/**
 * Database server settings
 */
$wgDBserver = env('DB_HOST', '127.0.0.1');
$wgDBname = env('DB_DATABASE', 'islamwiki');
$wgDBuser = env('DB_USERNAME', 'root');
$wgDBpassword = env('DB_PASSWORD', '');
$wgDBport = env('DB_PORT', '3306');

/**
 * Database type and configuration
 */
$wgDBtype = env('DB_CONNECTION', 'mysql');
$wgDBprefix = env('DB_PREFIX', '');
$wgDBTableOptions = 'ENGINE=InnoDB, DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

// ============================================================================
// ISLAMIC DATABASE CONFIGURATION
// ============================================================================

/**
 * Quran Database Configuration
 */
$wgQuranDatabase = env('QURAN_DB_DATABASE', 'islamwiki_quran');
$wgQuranDBserver = env('QURAN_DB_HOST', $wgDBserver);
$wgQuranDBuser = env('QURAN_DB_USERNAME', $wgDBuser);
$wgQuranDBpassword = env('QURAN_DB_PASSWORD', $wgDBpassword);
$wgQuranDBport = env('QURAN_DB_PORT', $wgDBport);

/**
 * Hadith Database Configuration
 */
$wgHadithDatabase = env('HADITH_DB_DATABASE', 'islamwiki_hadith');
$wgHadithDBserver = env('HADITH_DB_HOST', $wgDBserver);
$wgHadithDBuser = env('HADITH_DB_USERNAME', $wgDBuser);
$wgHadithDBpassword = env('HADITH_DB_PASSWORD', $wgDBpassword);
$wgHadithDBport = env('HADITH_DB_PORT', $wgDBport);

/**
 * Scholar Database Configuration
 */
$wgScholarDatabase = env('SCHOLAR_DB_DATABASE', 'islamwiki_scholar');
$wgScholarDBserver = env('SCHOLAR_DB_HOST', $wgDBserver);
$wgScholarDBuser = env('SCHOLAR_DB_USERNAME', $wgDBuser);
$wgScholarDBpassword = env('SCHOLAR_DB_PASSWORD', $wgDBpassword);
$wgScholarDBport = env('SCHOLAR_DB_PORT', $wgDBport);

/**
 * Wiki Database Configuration
 */
$wgWikiDatabase = env('WIKI_DB_DATABASE', 'islamwiki_wiki');
$wgWikiDBserver = env('WIKI_DB_HOST', $wgDBserver);
$wgWikiDBuser = env('WIKI_DB_USERNAME', $wgDBuser);
$wgWikiDBpassword = env('WIKI_DB_PASSWORD', $wgDBpassword);
$wgWikiDBport = env('WIKI_DB_PORT', $wgDBport);

// ============================================================================
// SKIN CONFIGURATION
// ============================================================================

/**
 * Available skins - similar to MediaWiki's $wgValidSkinNames
 * Add skins here to make them available for selection
 * 
 * NOTE: Muslim skin temporarily disabled to focus on Bismillah skin development
 */
$wgValidSkins = [
    'Bismillah' => 'Bismillah',
    // 'Muslim' => 'Muslim', // Temporarily disabled
];

/**
 * Active skin configuration
 * 
 * Set the active skin for the site. Available options:
 * - 'Bismillah': Default Islamic-themed skin with traditional design and beautiful gradients
 * 
 * NOTE: Muslim skin temporarily disabled to focus on Bismillah skin development
 * 
 * To change skins dynamically, use $skinManager->setActiveSkin('SkinName') instead.
 */
$wgActiveSkin = 'Bismillah';

/**
 * Default skin for new users
 */
$wgDefaultSkin = 'Bismillah';

/**
 * Skin configuration options
 */
$wgSkinConfig = [
    'enable_animations' => env('SKIN_ANIMATIONS', true),
    'enable_gradients' => env('SKIN_GRADIENTS', true),
    'enable_dark_theme' => env('SKIN_DARK_THEME', false),
];

/**
 * Skin-specific settings
 */
//$wgBismillahSkinSettings = [
//    'gradient_enabled' => true,
//    'islamic_patterns' => true,
//    'arabic_font' => true,
//];





// ============================================================================
// APPLICATION CONFIGURATION
// ============================================================================

/**
 * Site configuration
 */
$wgSitename = env('SITE_NAME', 'IslamWiki');
$wgMetaNamespace = env('META_NAMESPACE', 'IslamWiki');
$wgLanguageCode = env('LANGUAGE_CODE', 'en');

/**
 * URL configuration
 */
$wgServer = env('APP_URL', 'https://local.islam.wiki');
$wgScriptPath = env('SCRIPT_PATH', '');
$wgArticlePath = env('ARTICLE_PATH', '/wiki/$1');

/**
 * Security configuration
 */
$wgSecretKey = env('APP_KEY', 'islamwiki-secret-key-' . bin2hex(random_bytes(32)));
$wgSessionSecret = env('SESSION_SECRET', 'islamwiki-session-secret-' . bin2hex(random_bytes(32)));
$wgUpgradeKey = env('UPGRADE_KEY', 'islamwiki-upgrade-key-' . bin2hex(random_bytes(32)));

/**
 * File upload configuration
 */
$wgEnableUploads = env('ENABLE_UPLOADS', true);
$wgUploadDirectory = env('UPLOAD_DIRECTORY', __DIR__ . '/storage/uploads');
$wgUploadPath = env('UPLOAD_PATH', '/uploads');

// ============================================================================
// ISLAMIC FEATURES CONFIGURATION
// ============================================================================

/**
 * Quran Features
 */
$wgEnableQuranFeatures = env('ENABLE_QURAN_FEATURES', true);
$wgQuranAPIVersion = env('QURAN_API_VERSION', 'v1');
$wgQuranAPIRateLimit = env('QURAN_API_RATE_LIMIT', 1000);
$wgQuranAPICache = env('QURAN_API_CACHE', false);
$wgQuranDefaultTranslation = env('QURAN_DEFAULT_TRANSLATION', 'en-sahih');

/**
 * Hadith Features
 */
$wgEnableHadithFeatures = env('ENABLE_HADITH_FEATURES', true);
$wgHadithAPIVersion = env('HADITH_API_VERSION', 'v1');
$wgHadithAPIRateLimit = env('HADITH_API_RATE_LIMIT', 1000);
$wgHadithAPICache = env('HADITH_API_CACHE', false);
$wgHadithDefaultCollection = env('HADITH_DEFAULT_COLLECTION', 'bukhari');

/**
 * Prayer Times Features
 */
$wgEnablePrayerTimes = env('ENABLE_PRAYER_TIMES', true);
$wgPrayerTimesAPI = env('PRAYER_TIMES_API', 'default');
$wgPrayerTimesCalculationMethod = env('PRAYER_TIMES_CALCULATION_METHOD', 'MWL');
$wgPrayerTimesDefaultLocation = env('PRAYER_TIMES_DEFAULT_LOCATION', 'Mecca');

/**
 * Islamic Calendar Features
 */
$wgEnableIslamicCalendar = env('ENABLE_ISLAMIC_CALENDAR', true);
$wgIslamicCalendarDefaultView = env('ISLAMIC_CALENDAR_DEFAULT_VIEW', 'month');
$wgIslamicCalendarShowGregorian = env('ISLAMIC_CALENDAR_SHOW_GREGORIAN', true);

/**
 * Scholar Verification Features
 */
$wgEnableScholarVerification = env('ENABLE_SCHOLAR_VERIFICATION', true);
$wgScholarVerificationRequired = env('SCHOLAR_VERIFICATION_REQUIRED', false);
$wgScholarVerificationAutoApprove = env('SCHOLAR_VERIFICATION_AUTO_APPROVE', false);

// ============================================================================
// SEARCH CONFIGURATION
// ============================================================================

/**
 * Search engine configuration
 */
$wgSearchType = env('SEARCH_TYPE', 'database');
$wgSearchIndexType = env('SEARCH_INDEX_TYPE', 'fulltext');
$wgSearchCacheEnabled = env('SEARCH_CACHE_ENABLED', false);
$wgSearchCacheTTL = env('SEARCH_CACHE_TTL', 3600);

/**
 * Search limits and performance
 */
$wgSearchMaxResults = env('SEARCH_MAX_RESULTS', 100);
$wgSearchMinQueryLength = env('SEARCH_MIN_QUERY_LENGTH', 2);
$wgSearchTimeout = env('SEARCH_TIMEOUT', 30);

// ============================================================================
// CACHE CONFIGURATION
// ============================================================================

/**
 * Redis configuration
 */
$wgRedisHost = env('REDIS_HOST', '127.0.0.1');
$wgRedisPort = env('REDIS_PORT', 6379);
$wgRedisPassword = env('REDIS_PASSWORD', null);
$wgRedisDatabase = env('REDIS_DB', 0);

/**
 * Cache configuration
 */
$wgCacheEnabled = env('CACHE_ENABLED', false);
$wgCacheType = env('CACHE_TYPE', 'redis');
$wgCacheTTL = env('CACHE_TTL', 3600);

// ============================================================================
// LOGGING CONFIGURATION
// ============================================================================

/**
 * Logging settings
 */
$wgLogLevel = env('LOG_LEVEL', 'info');
$wgLogFile = env('LOG_FILE', __DIR__ . '/storage/logs/islamwiki.log');
$wgDebugLogFile = env('DEBUG_LOG_FILE', __DIR__ . '/storage/logs/debug.log');
$wgErrorLogFile = env('ERROR_LOG_FILE', __DIR__ . '/storage/logs/error.log');

/**
 * Debug settings
 */
$wgDebug = env('APP_DEBUG', false);
$wgShowExceptionDetails = env('SHOW_EXCEPTION_DETAILS', false);
$wgShowSQLErrors = env('SHOW_SQL_ERRORS', false);

// ============================================================================
// EXTENSION CONFIGURATION
// ============================================================================

/**
 * Enabled extensions
 */
$wgEnableExtensions = [
    'QuranExtension',
    'HadithExtension', 
    'PrayerTimesExtension',
    'IslamicCalendarExtension',
    'ScholarVerificationExtension',
    'SearchExtension',
];

/**
 * Extension-specific settings
 */
$wgQuranExtensionSettings = [
    'api_enabled' => true,
    'widget_enabled' => true,
    'search_enabled' => true,
];

$wgHadithExtensionSettings = [
    'api_enabled' => true,
    'widget_enabled' => true,
    'search_enabled' => true,
];

$wgPrayerTimesExtensionSettings = [
    'api_enabled' => true,
    'widget_enabled' => true,
    'calculation_method' => 'MWL',
];

$wgIslamicCalendarExtensionSettings = [
    'api_enabled' => true,
    'widget_enabled' => true,
    'default_view' => 'month',
];

$wgScholarVerificationExtensionSettings = [
    'auto_approve' => false,
    'verification_required' => false,
    'admin_approval_required' => true,
];

$wgSearchExtensionSettings = [
    'fulltext_enabled' => true,
    'suggestions_enabled' => true,
    'analytics_enabled' => true,
];



// ============================================================================
// LOAD ISLAMIC SETTINGS OVERRIDE
// ============================================================================

/**
 * Load Islamic-specific settings override if it exists
 * This allows for Islamic-specific configuration without modifying main settings
 */
$wgIslamSettingsFile = __DIR__ . '/IslamSettings.php';
if (file_exists($wgIslamSettingsFile)) {
    require_once $wgIslamSettingsFile;
}

// ============================================================================
// FINAL CONFIGURATION VALIDATION
// ============================================================================

/**
 * Validate critical configuration settings
 */
if (empty($wgSecretKey) || $wgSecretKey === 'your-secret-key-here') {
    error_log('Warning: Secret key not properly configured in LocalSettings.php');
}

if (empty($wgSessionSecret) || $wgSessionSecret === 'your-session-secret-here') {
    error_log('Warning: Session secret not properly configured in LocalSettings.php');
}

/**
 * Set default timezone
 */
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/**
 * Error reporting configuration
 */
if ($wgDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// CONFIGURATION COMPLETE
// ============================================================================

/**
 * Configuration loading complete
 * All settings are now available for use throughout the application
 */ 