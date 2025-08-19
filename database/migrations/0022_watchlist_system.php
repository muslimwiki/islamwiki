<?php

declare(strict_types=1);

/**
 * Watchlist System Migration
 * 
 * Creates tables for MediaWiki-style watchlist functionality including:
 * - watchlist (user page watching)
 * - user_preferences (user preferences including watchlist settings)
 * - watchlist_notifications (watchlist change notifications)
 */
class Migration_0022_WatchlistSystem
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function up(): void
    {
        // Watchlist table
        $this->db->exec("
            CREATE TABLE watchlist (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                page_id BIGINT UNSIGNED NOT NULL,
                last_read_at TIMESTAMP NULL,
                notification_preferences JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_page_id (page_id),
                INDEX idx_last_read_at (last_read_at),
                UNIQUE KEY unique_user_page (user_id, page_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // User preferences table
        $this->db->exec("
            CREATE TABLE user_preferences (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                preference_key VARCHAR(100) NOT NULL,
                preference_value TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_preference_key (preference_key),
                UNIQUE KEY unique_user_preference (user_id, preference_key),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Watchlist notifications table
        $this->db->exec("
            CREATE TABLE watchlist_notifications (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                page_id BIGINT UNSIGNED NOT NULL,
                notification_type ENUM('edit', 'new_page', 'deletion', 'restoration') NOT NULL,
                change_summary TEXT,
                editor_username VARCHAR(100),
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_page_id (page_id),
                INDEX idx_is_read (is_read),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Watchlist digests table (for periodic email summaries)
        $this->db->exec("
            CREATE TABLE watchlist_digests (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                digest_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
                digest_date DATE NOT NULL,
                changes_summary JSON NOT NULL,
                is_sent BOOLEAN DEFAULT FALSE,
                sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_digest_type (digest_type),
                INDEX idx_digest_date (digest_date),
                INDEX idx_is_sent (is_sent),
                UNIQUE KEY unique_user_digest (user_id, digest_type, digest_date),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Insert default watchlist preferences for existing users
        $this->db->exec("
            INSERT INTO user_preferences (user_id, preference_key, preference_value)
            SELECT 
                id,
                'watchlist_email_notifications',
                '1'
            FROM users
            WHERE id NOT IN (
                SELECT user_id FROM user_preferences 
                WHERE preference_key = 'watchlist_email_notifications'
            )
        ");
        
        $this->db->exec("
            INSERT INTO user_preferences (user_id, preference_key, preference_value)
            SELECT 
                id,
                'watchlist_browser_notifications',
                '1'
            FROM users
            WHERE id NOT IN (
                SELECT user_id FROM user_preferences 
                WHERE preference_key = 'watchlist_browser_notifications'
            )
        ");
        
        $this->db->exec("
            INSERT INTO user_preferences (user_id, preference_key, preference_value)
            SELECT 
                id,
                'watchlist_digest_frequency',
                'daily'
            FROM users
            WHERE id NOT IN (
                SELECT user_id FROM user_preferences 
                WHERE preference_key = 'watchlist_digest_frequency'
            )
        ");
    }
    
    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS watchlist_digests");
        $this->db->exec("DROP TABLE IF EXISTS watchlist_notifications");
        $this->db->exec("DROP TABLE IF EXISTS user_preferences");
        $this->db->exec("DROP TABLE IF EXISTS watchlist");
    }
} 