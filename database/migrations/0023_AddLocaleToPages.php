<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migration;

/**
 * Migration to add locale field to pages table
 * 
 * This allows filtering pages by language/locale
 */
class Migration_0023_AddLocaleToPages extends Migration
{
    public function up(): void
    {
        // Add locale column to pages table
        $this->db->exec("
            ALTER TABLE pages 
            ADD COLUMN locale VARCHAR(5) NOT NULL DEFAULT 'en' 
            AFTER namespace
        ");
        
        // Add index for better performance
        $this->db->exec("
            CREATE INDEX idx_pages_locale ON pages(locale)
        ");
        
        // Update existing pages to have locale 'en' (default)
        $this->db->exec("
            UPDATE pages 
            SET locale = 'en' 
            WHERE locale IS NULL OR locale = ''
        ");
    }

    public function down(): void
    {
        $this->db->exec("
            DROP INDEX idx_pages_locale ON pages
        ");
        
        $this->db->exec("
            ALTER TABLE pages 
            DROP COLUMN locale
        ");
    }
} 