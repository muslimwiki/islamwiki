<?php

declare(strict_types=1);

/**
 * Migration: Add language preferences to user settings
 * 
 * This migration adds language preference support to the user_settings table
 * allowing users to choose their preferred language for browsing IslamWiki.
 */

use IslamWiki\Core\Database\Connection;

class Migration_0020_UserLanguagePreferences
{
    /**
     * @var Connection
     */
    private Connection $db;

    /**
     * Constructor
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        try {
            // Check if user_settings table exists
            $tableExists = $this->db->query("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'user_settings'
            ")->fetch();

            if ($tableExists['count'] == 0) {
                // Create user_settings table if it doesn't exist
                $this->db->exec("
                    CREATE TABLE user_settings (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        language_preference VARCHAR(10) DEFAULT 'en',
                        skin_preference VARCHAR(50) DEFAULT 'Bismillah',
                        theme_preference VARCHAR(20) DEFAULT 'light',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_user (user_id),
                        INDEX idx_user_id (user_id),
                        INDEX idx_language (language_preference)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                echo "✅ Created user_settings table\n";
            } else {
                // Check if language_preference column exists
                $columnExists = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM information_schema.columns 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'user_settings' 
                    AND column_name = 'language_preference'
                ")->fetch();

                if ($columnExists['count'] == 0) {
                    // Add language_preference column
                    $this->db->exec("
                        ALTER TABLE user_settings 
                        ADD COLUMN language_preference VARCHAR(10) DEFAULT 'en' AFTER user_id
                    ");
                    
                    echo "✅ Added language_preference column to user_settings table\n";
                } else {
                    echo "ℹ️  language_preference column already exists\n";
                }
            }

            // Insert default language preferences for existing users
            $this->db->exec("
                INSERT IGNORE INTO user_settings (user_id, language_preference, skin_preference, theme_preference)
                SELECT id, 'en', 'Bismillah', 'light'
                FROM users 
                WHERE id NOT IN (SELECT user_id FROM user_settings)
            ");

            echo "✅ Migration completed successfully\n";
        } catch (\Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Rollback the migration
     */
    public function down(): void
    {
        try {
            // Remove language_preference column
            $this->db->exec("
                ALTER TABLE user_settings 
                DROP COLUMN language_preference
            ");
            
            echo "✅ Rollback completed successfully\n";
        } catch (\Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
} 