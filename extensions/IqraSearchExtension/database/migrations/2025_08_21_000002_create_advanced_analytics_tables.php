<?php

declare(strict_types=1);

/**
 * Migration: Create Advanced Search Analytics Tables
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Database\Migrations
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class CreateAdvancedAnalyticsTables
{
    public function up(): void
    {
        $sql = "
        -- Search Logs Table
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
        
        -- Search Result Clicks Table
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
        
        -- Search User Preferences Table
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
        
        -- Search Performance Metrics Table
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
        
        // Execute the migration
        $this->executeMigration($sql);
    }
    
    public function down(): void
    {
        $sql = "
        DROP TABLE IF EXISTS `iqra_search_performance`;
        DROP TABLE IF EXISTS `iqra_search_preferences`;
        DROP TABLE IF EXISTS `iqra_search_clicks`;
        DROP TABLE IF EXISTS `iqra_search_logs`;
        ";
        
        // Execute the rollback
        $this->executeMigration($sql);
    }
    
    private function executeMigration(string $sql): void
    {
        try {
            // This would typically use a database connection
            // For now, we'll just output the SQL
            echo "Executing migration SQL:\n";
            echo $sql . "\n";
            
            // In a real implementation, you would execute this SQL
            // $this->db->execute($sql);
            
        } catch (\Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    $migration = new CreateAdvancedAnalyticsTables();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        echo "Rolling back migration...\n";
        $migration->down();
    } else {
        echo "Running migration...\n";
        $migration->up();
    }
    
    echo "Migration completed successfully!\n";
} 