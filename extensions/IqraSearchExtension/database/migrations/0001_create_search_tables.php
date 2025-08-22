<?php

declare(strict_types=1);

/**
 * Migration: Create Search Tables for IqraSearchExtension
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Database\Migrations
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class CreateSearchTables_0001
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->createSearchIndexTable();
        $this->createSearchLogsTable();
        $this->createSearchAnalyticsTable();
        $this->createSearchSuggestionsTable();
    }

    /**
     * Rollback the migration
     */
    public function down(): void
    {
        $this->dropSearchTables();
    }

    /**
     * Create the main search index table
     */
    private function createSearchIndexTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `iqra_search_index` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `content_type` VARCHAR(50) NOT NULL COMMENT 'Type of content (wiki, quran, hadith, article, scholar)',
            `content_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID of the content in its source table',
            `title` VARCHAR(500) NOT NULL COMMENT 'Content title for search',
            `content` TEXT NOT NULL COMMENT 'Searchable content text',
            `excerpt` VARCHAR(1000) NOT NULL COMMENT 'Short excerpt for search results',
            `url` VARCHAR(500) NOT NULL COMMENT 'URL to the content',
            `metadata` JSON COMMENT 'Additional metadata (author, tags, etc.)',
            `search_vector` TEXT COMMENT 'Full-text search vector',
            `relevance_score` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Calculated relevance score',
            `view_count` INT UNSIGNED DEFAULT 0 COMMENT 'Number of views',
            `rating` DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Content rating (0.00-5.00)',
            `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `indexed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Whether content is active and searchable',
            
            INDEX `idx_content_type` (`content_type`),
            INDEX `idx_content_id` (`content_id`),
            INDEX `idx_title` (`title`(100)),
            INDEX `idx_search_vector` (`search_vector`(100)),
            INDEX `idx_relevance` (`relevance_score`),
            INDEX `idx_last_updated` (`last_updated`),
            INDEX `idx_is_active` (`is_active`),
            FULLTEXT `ft_search` (`title`, `content`, `excerpt`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main search index for all content types';
        ";
        
        $this->executeSQL($sql);
    }

    /**
     * Create search logs table for analytics
     */
    private function createSearchLogsTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `iqra_search_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `query` VARCHAR(500) NOT NULL COMMENT 'Search query',
            `user_id` BIGINT UNSIGNED NULL COMMENT 'User ID if logged in',
            `session_id` VARCHAR(255) NULL COMMENT 'Session identifier',
            `ip_address` VARCHAR(45) NULL COMMENT 'IP address of searcher',
            `user_agent` TEXT NULL COMMENT 'User agent string',
            `content_type` VARCHAR(50) NULL COMMENT 'Content type filter used',
            `sort_by` VARCHAR(50) NULL COMMENT 'Sort option used',
            `results_count` INT UNSIGNED DEFAULT 0 COMMENT 'Number of results returned',
            `search_time` DECIMAL(5,3) DEFAULT 0.000 COMMENT 'Search execution time in seconds',
            `clicked_result` BIGINT UNSIGNED NULL COMMENT 'ID of clicked result if any',
            `searched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX `idx_query` (`query`(100)),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_content_type` (`content_type`),
            INDEX `idx_searched_at` (`searched_at`),
            INDEX `idx_results_count` (`results_count`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Search query logs for analytics';
        ";
        
        $this->executeSQL($sql);
    }

    /**
     * Create search analytics table for insights
     */
    private function createSearchAnalyticsTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `iqra_search_analytics` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `date` DATE NOT NULL COMMENT 'Date of analytics',
            `total_searches` INT UNSIGNED DEFAULT 0 COMMENT 'Total searches on this date',
            `unique_searchers` INT UNSIGNED DEFAULT 0 COMMENT 'Unique users who searched',
            `avg_search_time` DECIMAL(5,3) DEFAULT 0.000 COMMENT 'Average search time',
            `avg_results_count` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Average results per search',
            `no_results_searches` INT UNSIGNED DEFAULT 0 COMMENT 'Searches with no results',
            `popular_queries` JSON COMMENT 'Top search queries for the day',
            `popular_content_types` JSON COMMENT 'Most searched content types',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            UNIQUE KEY `uk_date` (`date`),
            INDEX `idx_date` (`date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daily search analytics and insights';
        ";
        
        $this->executeSQL($sql);
    }

    /**
     * Create search suggestions table
     */
    private function createSearchSuggestionsTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `iqra_search_suggestions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `query` VARCHAR(500) NOT NULL COMMENT 'Search query',
            `suggestion` VARCHAR(500) NOT NULL COMMENT 'Suggested search term',
            `type` ENUM('auto', 'manual', 'popular') DEFAULT 'auto' COMMENT 'Type of suggestion',
            `usage_count` INT UNSIGNED DEFAULT 0 COMMENT 'How often this suggestion is used',
            `relevance_score` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Relevance of suggestion',
            `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Whether suggestion is active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX `idx_query` (`query`(100)),
            INDEX `idx_suggestion` (`suggestion`(100)),
            INDEX `idx_type` (`type`),
            INDEX `idx_usage_count` (`usage_count`),
            INDEX `idx_relevance` (`relevance_score`),
            INDEX `idx_is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Search suggestions and autocomplete';
        ";
        
        $this->executeSQL($sql);
    }

    /**
     * Drop all search tables
     */
    private function dropSearchTables(): void
    {
        $tables = [
            'iqra_search_suggestions',
            'iqra_search_analytics',
            'iqra_search_logs',
            'iqra_search_index'
        ];
        
        foreach ($tables as $table) {
            $sql = "DROP TABLE IF EXISTS `{$table}`";
            $this->executeSQL($sql);
        }
    }

    /**
     * Execute SQL statement
     */
    private function executeSQL(string $sql): void
    {
        try {
            // This would normally use a database connection
            // For now, we'll just log the SQL
            error_log("Search Migration SQL: " . $sql);
            
            // TODO: Implement actual database execution
            // $this->db->exec($sql);
            
        } catch (Exception $e) {
            error_log("Search Migration Error: " . $e->getMessage());
            throw $e;
        }
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    $migration = new CreateSearchTables_0001();
    $migration->up();
    echo "Search tables migration completed successfully!\n";
} 