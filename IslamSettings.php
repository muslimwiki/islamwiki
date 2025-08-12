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
 * IslamSettings.php - Islamic-Specific Configuration Override
 *
 * This file contains Islamic-specific configuration settings that override
 * the main LocalSettings.php configuration. This allows for Islamic-specific
 * customization without modifying the main configuration file.
 *
 * This file is OPTIONAL and will only be loaded if it exists.
 *
 * Version: 0.0.18
 * Date: 2025-07-30
 */

// ============================================================================
// ISLAMIC DATABASE OVERRIDES
// ============================================================================

/**
 * Override Quran database settings for Islamic-specific requirements
 */
// $wgQuranDatabase = 'custom_quran_db';
// $wgQuranDBserver = 'custom-quran-server.com';
// $wgQuranDBuser = 'quran_user';
// $wgQuranDBpassword = 'secure_quran_password';

/**
 * Override Hadith database settings for Islamic-specific requirements
 */
// $wgHadithDatabase = 'custom_hadith_db';
// $wgHadithDBserver = 'custom-hadith-server.com';
// $wgHadithDBuser = 'hadith_user';
// $wgHadithDBpassword = 'secure_hadith_password';

/**
 * Override Scholar database settings for Islamic-specific requirements
 */
// $wgScholarDatabase = 'custom_scholar_db';
// $wgScholarDBserver = 'custom-scholar-server.com';
// $wgScholarDBuser = 'scholar_user';
// $wgScholarDBpassword = 'secure_scholar_password';

// ============================================================================
// ISLAMIC FEATURE OVERRIDES
// ============================================================================

/**
 * Quran Feature Overrides
 */
// $wgQuranAPIVersion = 'v2';
// $wgQuranAPIRateLimit = 5000;
// $wgQuranAPICache = false;
// $wgQuranDefaultTranslation = 'en-pickthall';

/**
 * Hadith Feature Overrides
 */
// $wgHadithAPIVersion = 'v2';
// $wgHadithAPIRateLimit = 5000;
// $wgHadithAPICache = false;
// $wgHadithDefaultCollection = 'muslim';

/**
 * Prayer Times Feature Overrides
 */
// $wgPrayerTimesAPI = 'custom_prayer_api';
// $wgPrayerTimesCalculationMethod = 'ISNA';
// $wgPrayerTimesDefaultLocation = 'Medina';

/**
 * Islamic Calendar Feature Overrides
 */
// $wgIslamicCalendarDefaultView = 'year';
// $wgIslamicCalendarShowGregorian = false;

/**
 * Scholar Verification Feature Overrides
 */
// $wgScholarVerificationRequired = true;
// $wgScholarVerificationAutoApprove = true;

// ============================================================================
// ISLAMIC CONTENT CONFIGURATION
// ============================================================================

/**
 * Islamic content moderation settings
 */
$wgIslamicContentModeration = [
    'require_scholar_approval' => false,
    'auto_approve_verified_scholars' => true,
    'moderation_queue_enabled' => true,
    'content_quality_threshold' => 0.7,
];

/**
 * Islamic content categorization
 */
$wgIslamicContentCategories = [
    'quran_studies' => true,
    'hadith_studies' => true,
    'fiqh' => true,
    'aqeedah' => true,
    'seerah' => true,
    'islamic_history' => true,
    'contemporary_issues' => true,
];

/**
 * Islamic content templates
 */
$wgIslamicContentTemplates = [
    'quran_ayah' => 'templates/quran/ayah.twig',
    'hadith_narration' => 'templates/hadith/narration.twig',
    'scholar_profile' => 'templates/scholar/profile.twig',
    'prayer_times' => 'templates/prayer/times.twig',
    'islamic_event' => 'templates/calendar/event.twig',
    'fatwa' => 'templates/fatwa/detail.twig',
    'islamic_article' => 'templates/article/islamic.twig',
];

// ============================================================================
// ISLAMIC API CONFIGURATION
// ============================================================================

/**
 * Islamic API endpoints configuration
 */
$wgIslamicAPIEndpoints = [
    'quran' => [
        'base_url' => '/api/quran',
        'version' => 'v1',
        'rate_limit' => 1000,
        'cache_ttl' => 3600,
    ],
    'hadith' => [
        'base_url' => '/api/hadith',
        'version' => 'v1',
        'rate_limit' => 1000,
        'cache_ttl' => 3600,
    ],
    'prayer_times' => [
        'base_url' => '/api/prayer-times',
        'version' => 'v1',
        'rate_limit' => 500,
        'cache_ttl' => 1800,
    ],
    'islamic_calendar' => [
        'base_url' => '/api/islamic-calendar',
        'version' => 'v1',
        'rate_limit' => 500,
        'cache_ttl' => 7200,
    ],
    'scholar_verification' => [
        'base_url' => '/api/scholar-verification',
        'version' => 'v1',
        'rate_limit' => 100,
        'cache_ttl' => 86400,
    ],
];

/**
 * Islamic API authentication settings
 */
$wgIslamicAPIAuth = [
    'require_authentication' => false,
    'api_key_required' => false,
    'rate_limit_by_ip' => true,
    'rate_limit_by_user' => true,
];

// ============================================================================
// ISLAMIC SEARCH CONFIGURATION
// ============================================================================

/**
 * Islamic search settings
 */
$wgIslamicSearchSettings = [
    'search_quran_ayahs' => true,
    'search_hadith_narrations' => true,
    'search_scholar_profiles' => true,
    'search_islamic_articles' => true,
    'search_prayer_times' => false,
    'search_islamic_events' => true,
    'search_fatwas' => true,
    'search_islamic_books' => true,
];

/**
 * Islamic search weights
 */
$wgIslamicSearchWeights = [
    'quran_ayah' => 1.0,
    'hadith_narration' => 0.9,
    'scholar_profile' => 0.8,
    'islamic_article' => 0.7,
    'islamic_event' => 0.6,
    'fatwa' => 0.8,
    'islamic_book' => 0.7,
];

/**
 * Islamic search filters
 */
$wgIslamicSearchFilters = [
    'content_type' => true,
    'language' => true,
    'scholar_verification' => true,
    'content_quality' => true,
    'date_range' => true,
    'geographic_location' => true,
];

// ============================================================================
// ISLAMIC CACHE CONFIGURATION
// ============================================================================

/**
 * Islamic-specific cache settings
 */
$wgIslamicCacheSettings = [
    'quran_cache_ttl' => 86400,      // 24 hours
    'hadith_cache_ttl' => 86400,     // 24 hours
    'prayer_times_cache_ttl' => 1800, // 30 minutes
    'calendar_cache_ttl' => 7200,    // 2 hours
    'scholar_cache_ttl' => 604800,   // 1 week
    'search_cache_ttl' => 3600,      // 1 hour
];

/**
 * Islamic cache keys
 */
$wgIslamicCacheKeys = [
    'quran_ayah' => 'quran:ayah:{surah}:{ayah}',
    'hadith_narration' => 'hadith:narration:{collection}:{number}',
    'prayer_times' => 'prayer:times:{location}:{date}',
    'islamic_calendar' => 'calendar:islamic:{year}:{month}',
    'scholar_profile' => 'scholar:profile:{id}',
    'search_results' => 'search:results:{query}:{filters}',
];

// ============================================================================
// ISLAMIC LOGGING CONFIGURATION
// ============================================================================

/**
 * Islamic-specific logging settings
 */
$wgIslamicLogSettings = [
    'log_quran_searches' => true,
    'log_hadith_searches' => true,
    'log_prayer_time_requests' => true,
    'log_scholar_verifications' => true,
    'log_islamic_content_edits' => true,
    'log_api_requests' => true,
];

/**
 * Islamic log files
 */
$wgIslamicLogFiles = [
    'quran_log' => __DIR__ . '/storage/logs/quran.log',
    'hadith_log' => __DIR__ . '/storage/logs/hadith.log',
    'prayer_log' => __DIR__ . '/storage/logs/prayer.log',
    'scholar_log' => __DIR__ . '/storage/logs/scholar.log',
    'islamic_content_log' => __DIR__ . '/storage/logs/islamic_content.log',
    'api_log' => __DIR__ . '/storage/logs/api.log',
];

// ============================================================================
// ISLAMIC SECURITY CONFIGURATION
// ============================================================================

/**
 * Islamic-specific security settings
 */
$wgIslamicSecuritySettings = [
    'require_scholar_verification_for_edits' => false,
    'require_authentication_for_api' => false,
    'rate_limit_islamic_apis' => true,
    'log_suspicious_activity' => true,
    'block_malicious_content' => true,
];

/**
 * Islamic content validation
 */
$wgIslamicContentValidation = [
    'validate_quran_references' => true,
    'validate_hadith_references' => true,
    'validate_scholar_credentials' => true,
    'validate_islamic_content' => true,
    'auto_flag_suspicious_content' => true,
];

// ============================================================================
// ISLAMIC PERFORMANCE CONFIGURATION
// ============================================================================

/**
 * Islamic-specific performance settings
 */
$wgIslamicPerformanceSettings = [
    'enable_quran_caching' => true,
    'enable_hadith_caching' => true,
    'enable_prayer_times_caching' => true,
    'enable_calendar_caching' => true,
    'enable_search_caching' => true,
    'optimize_islamic_queries' => true,
    'use_islamic_indexes' => true,
];

/**
 * Islamic database optimization
 */
$wgIslamicDatabaseOptimization = [
    'quran_connection_pool' => 5,
    'hadith_connection_pool' => 5,
    'scholar_connection_pool' => 3,
    'wiki_connection_pool' => 10,
    'enable_query_logging' => false,
    'enable_slow_query_logging' => true,
];

// ============================================================================
// ISLAMIC EXTENSION OVERRIDES
// ============================================================================

/**
 * Override extension settings for Islamic-specific requirements
 */
$wgQuranExtensionSettings = array_merge($wgQuranExtensionSettings ?? [], [
    'enable_arabic_support' => true,
    'enable_multiple_translations' => true,
    'enable_ayah_audio' => true,
    'enable_ayah_highlights' => true,
]);

$wgHadithExtensionSettings = array_merge($wgHadithExtensionSettings ?? [], [
    'enable_chain_analysis' => true,
    'enable_narrator_profiles' => true,
    'enable_authenticity_grading' => true,
    'enable_multiple_collections' => true,
]);

$wgPrayerTimesExtensionSettings = array_merge($wgPrayerTimesExtensionSettings ?? [], [
    'enable_multiple_calculation_methods' => true,
    'enable_location_based_times' => true,
    'enable_notification_system' => true,
    'enable_qibla_direction' => true,
]);

$wgIslamicCalendarExtensionSettings = array_merge($wgIslamicCalendarExtensionSettings ?? [], [
    'enable_event_management' => true,
    'enable_gregorian_conversion' => true,
    'enable_islamic_holidays' => true,
    'enable_custom_events' => true,
]);

$wgScholarVerificationExtensionSettings = array_merge($wgScholarVerificationExtensionSettings ?? [], [
    'enable_credential_verification' => true,
    'enable_peer_review' => true,
    'enable_verification_badges' => true,
    'enable_scholar_networking' => true,
]);

$wgSearchExtensionSettings = array_merge($wgSearchExtensionSettings ?? [], [
    'enable_islamic_suggestions' => true,
    'enable_arabic_search' => true,
    'enable_semantic_search' => true,
    'enable_search_analytics' => true,
]);

// ============================================================================
// ISLAMIC SETTINGS COMPLETE
// ============================================================================

/**
 * Islamic settings override complete
 * All Islamic-specific settings are now available and will override
 * the corresponding settings from LocalSettings.php
 */
