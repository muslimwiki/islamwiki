<?php

declare(strict_types=1);

/**
 * Execute Analytics Migration
 * Creates the advanced analytics tables
 */

require_once __DIR__ . '/connect.php';

echo "🚀 Executing Analytics Migration\n";
echo "================================\n\n";

try {
    $db = new SearchDatabaseConnection();
    $connection = $db->getConnection();
    
    echo "✅ Database connection established\n";
    
    // Create Search Logs Table
    echo "📋 Creating iqra_search_logs table...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS `iqra_search_logs` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `query` VARCHAR(500) NOT NULL COMMENT 'Search query',
        `user_id` BIGINT UNSIGNED NULL COMMENT 'User ID (if logged in)',
        `content_type` VARCHAR(50) NOT NULL DEFAULT 'all' COMMENT 'Content type filter',
        `sort_by` VARCHAR(50) NOT NULL DEFAULT 'relevance' COMMENT 'Sort field',
        `sort_order` VARCHAR(10) NOT NULL DEFAULT 'desc' COMMENT 'Sort order',
        `results_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of results found',
        `response_time` DECIMAL(8,3) NOT NULL DEFAULT 0.000 COMMENT 'Response time in seconds',
        `user_agent` TEXT COMMENT 'User agent string',
        `ip_address` VARCHAR(45) NOT NULL COMMENT 'Client IP address',
        `search_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When search was performed',
        `session_id` VARCHAR(255) NULL COMMENT 'Session identifier',
        
        INDEX `idx_query` (`query`(100)),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_content_type` (`content_type`),
        INDEX `idx_search_timestamp` (`search_timestamp`),
        INDEX `idx_session_id` (`session_id`(100)),
        INDEX `idx_response_time` (`response_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $connection->exec($sql);
    echo "  ✅ Table 'iqra_search_logs' created successfully\n";
    
    // Create Search Result Clicks Table
    echo "📋 Creating iqra_search_clicks table...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS `iqra_search_clicks` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `search_log_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reference to search log',
        `content_id` BIGINT UNSIGNED NOT NULL COMMENT 'Content ID that was clicked',
        `content_type` VARCHAR(50) NOT NULL COMMENT 'Type of content clicked',
        `url` VARCHAR(500) NOT NULL COMMENT 'URL that was clicked',
        `click_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When click occurred',
        `user_agent` TEXT COMMENT 'User agent string',
        `ip_address` VARCHAR(45) NOT NULL COMMENT 'Client IP address',
        
        INDEX `idx_search_log_id` (`search_log_id`),
        INDEX `idx_content_id` (`content_id`),
        INDEX `idx_content_type` (`content_type`),
        INDEX `idx_click_timestamp` (`click_timestamp`),
        FOREIGN KEY (`search_log_id`) REFERENCES `iqra_search_logs`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $connection->exec($sql);
    echo "  ✅ Table 'iqra_search_clicks' created successfully\n";
    
    // Create Search User Preferences Table
    echo "📋 Creating iqra_search_preferences table...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS `iqra_search_preferences` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'User ID',
        `preference_key` VARCHAR(100) NOT NULL COMMENT 'Preference key',
        `preference_value` TEXT COMMENT 'Preference value',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        UNIQUE KEY `uk_user_preference` (`user_id`, `preference_key`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_preference_key` (`preference_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $connection->exec($sql);
    echo "  ✅ Table 'iqra_search_preferences' created successfully\n";
    
    // Create Search Performance Metrics Table
    echo "📋 Creating iqra_search_performance table...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS `iqra_search_performance` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `metric_date` DATE NOT NULL COMMENT 'Date for metrics',
        `total_searches` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total searches performed',
        `unique_users` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Unique users searching',
        `avg_response_time` DECIMAL(8,3) NOT NULL DEFAULT 0.000 COMMENT 'Average response time',
        `avg_results_count` DECIMAL(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Average results per search',
        `search_success_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage of successful searches',
        `click_through_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Click-through rate',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        UNIQUE KEY `uk_metric_date` (`metric_date`),
        INDEX `idx_metric_date` (`metric_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $connection->exec($sql);
    echo "  ✅ Table 'iqra_search_performance' created successfully\n";
    
    // Insert sample performance data
    echo "📊 Inserting sample performance data...\n";
    $sql = "
    INSERT INTO iqra_search_performance (
        metric_date, total_searches, unique_users, avg_response_time, 
        avg_results_count, search_success_rate, click_through_rate
    ) VALUES (
        CURDATE(), 12500, 850, 0.085, 8.5, 95.2, 78.5
    ) ON DUPLICATE KEY UPDATE
        total_searches = VALUES(total_searches),
        unique_users = VALUES(unique_users),
        avg_response_time = VALUES(avg_response_time),
        avg_results_count = VALUES(avg_results_count),
        search_success_rate = VALUES(search_success_rate),
        click_through_rate = VALUES(click_through_rate)
    ";
    
    $connection->exec($sql);
    echo "  ✅ Sample performance data inserted\n";
    
    echo "\n🎉 Analytics migration completed successfully!\n";
    echo "✅ All advanced analytics tables created\n";
    echo "✅ Sample data inserted\n";
    echo "✅ Ready for advanced search features\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 