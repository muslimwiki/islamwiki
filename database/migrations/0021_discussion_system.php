<?php

declare(strict_types=1);

/**
 * Discussion System Migration
 * 
 * Creates tables for MediaWiki-style discussion pages including:
 * - discussions (main discussion threads)
 * - discussion_history (discussion edit history)
 * - discussion_notifications (user notifications)
 */
class Migration_0021_DiscussionSystem
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function up(): void
    {
        // Discussions table
        $this->db->exec("
            CREATE TABLE discussions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                parent_id BIGINT UNSIGNED NULL,
                content TEXT NOT NULL,
                signature TEXT NOT NULL,
                edit_count INT UNSIGNED DEFAULT 0,
                is_sticky BOOLEAN DEFAULT FALSE,
                is_locked BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                edited_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL,
                INDEX idx_page_id (page_id),
                INDEX idx_user_id (user_id),
                INDEX idx_parent_id (parent_id),
                INDEX idx_created_at (created_at),
                INDEX idx_is_sticky (is_sticky),
                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES discussions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Discussion history table
        $this->db->exec("
            CREATE TABLE discussion_history (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                discussion_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                content TEXT NOT NULL,
                signature TEXT NOT NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_discussion_id (discussion_id),
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Discussion notifications table
        $this->db->exec("
            CREATE TABLE discussion_notifications (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                discussion_id BIGINT UNSIGNED NOT NULL,
                notification_type ENUM('reply', 'mention', 'edit', 'moderation') NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_discussion_id (discussion_id),
                INDEX idx_is_read (is_read),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Discussion mentions table (for @username mentions)
        $this->db->exec("
            CREATE TABLE discussion_mentions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                discussion_id BIGINT UNSIGNED NOT NULL,
                mentioned_user_id BIGINT UNSIGNED NOT NULL,
                mentioned_by_user_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_discussion_id (discussion_id),
                INDEX idx_mentioned_user_id (mentioned_user_id),
                INDEX idx_mentioned_by_user_id (mentioned_by_user_id),
                FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
                FOREIGN KEY (mentioned_user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (mentioned_by_user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS discussion_mentions");
        $this->db->exec("DROP TABLE IF EXISTS discussion_notifications");
        $this->db->exec("DROP TABLE IF EXISTS discussion_history");
        $this->db->exec("DROP TABLE IF EXISTS discussions");
    }
} 