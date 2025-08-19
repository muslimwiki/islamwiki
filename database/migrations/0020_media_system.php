<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migration;

/**
 * Media System Migration
 * 
 * Creates tables for MediaWiki-style media handling including:
 * - media (main media files)
 * - media_categories (media categorization)
 * - media_tags (media tagging)
 * - media_history (media edit history)
 */
class Migration_0020_MediaSystem extends Migration
{
    public function up(): void
    {
        // Media files table
        $this->db->exec("
            CREATE TABLE media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                original_name VARCHAR(255) NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                media_type ENUM('image', 'audio', 'video', 'document', 'other') NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL,
                extension VARCHAR(20) NOT NULL,
                upload_path VARCHAR(500) NOT NULL,
                width INT UNSIGNED NULL,
                height INT UNSIGNED NULL,
                duration INT UNSIGNED NULL,
                uploaded_by BIGINT UNSIGNED NOT NULL,
                updated_by BIGINT UNSIGNED NULL,
                view_count INT UNSIGNED DEFAULT 0,
                download_count INT UNSIGNED DEFAULT 0,
                is_public BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_media_type (media_type),
                INDEX idx_uploaded_by (uploaded_by),
                INDEX idx_created_at (created_at),
                INDEX idx_filename (filename),
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Media categories table
        $this->db->exec("
            CREATE TABLE media_categories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                category VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_media_id (media_id),
                INDEX idx_category (category),
                UNIQUE KEY unique_media_category (media_id, category),
                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Media tags table
        $this->db->exec("
            CREATE TABLE media_tags (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                tag VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_media_id (media_id),
                INDEX idx_tag (tag),
                UNIQUE KEY unique_media_tag (media_id, tag),
                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Media history table
        $this->db->exec("
            CREATE TABLE media_history (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                action ENUM('upload', 'edit', 'delete', 'restore') NOT NULL,
                old_values JSON NULL,
                new_values JSON NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_media_id (media_id),
                INDEX idx_user_id (user_id),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Media usage table (tracks where media is used)
        $this->db->exec("
            CREATE TABLE media_usage (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                media_id BIGINT UNSIGNED NOT NULL,
                page_id BIGINT UNSIGNED NULL,
                usage_type ENUM('embedded', 'linked', 'referenced') NOT NULL,
                context TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_media_id (media_id),
                INDEX idx_page_id (page_id),
                INDEX idx_usage_type (usage_type),
                FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS media_usage");
        $this->db->exec("DROP TABLE IF EXISTS media_history");
        $this->db->exec("DROP TABLE IF EXISTS media_tags");
        $this->db->exec("DROP TABLE IF EXISTS media_categories");
        $this->db->exec("DROP TABLE IF EXISTS media");
    }
} 