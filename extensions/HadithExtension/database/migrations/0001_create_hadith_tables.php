<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migration;

/**
 * Create Hadith tables migration
 * 
 * Creates the basic structure for Hadith data including collections, books, and narrations
 */
class CreateHadithTables extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Create hadith_collections table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_collections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                name_ar VARCHAR(100) NOT NULL,
                author VARCHAR(100) NOT NULL,
                author_ar VARCHAR(100) NOT NULL,
                description TEXT,
                total_hadith INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_collection (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_books table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_books (
                id INT AUTO_INCREMENT PRIMARY KEY,
                collection_id INT NOT NULL,
                book_number INT NOT NULL,
                name VARCHAR(200) NOT NULL,
                name_ar VARCHAR(200) NOT NULL,
                description TEXT,
                total_hadith INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (collection_id) REFERENCES hadith_collections(id) ON DELETE CASCADE,
                UNIQUE KEY unique_book (collection_id, book_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_narrators table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_narrators (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                name_ar VARCHAR(200) NOT NULL,
                biography TEXT,
                biography_ar TEXT,
                birth_year INT,
                death_year INT,
                era VARCHAR(100),
                reliability_grade VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_narrator (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_narrations table (main hadith content)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_narrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                collection_id INT NOT NULL,
                book_id INT NOT NULL,
                hadith_number INT NOT NULL,
                hadith_number_secondary VARCHAR(50),
                text_arabic TEXT NOT NULL,
                text_arabic_diacless TEXT,
                text_english TEXT,
                text_urdu TEXT,
                text_indonesian TEXT,
                text_turkish TEXT,
                text_malay TEXT,
                grade VARCHAR(100),
                explanation TEXT,
                reference_book VARCHAR(100),
                reference_page VARCHAR(50),
                reference_hadith VARCHAR(50),
                is_muttafaqun_alayh BOOLEAN DEFAULT FALSE,
                is_sahih BOOLEAN DEFAULT FALSE,
                is_hasan BOOLEAN DEFAULT FALSE,
                is_daif BOOLEAN DEFAULT FALSE,
                is_mawdu BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (collection_id) REFERENCES hadith_collections(id) ON DELETE CASCADE,
                FOREIGN KEY (book_id) REFERENCES hadith_books(id) ON DELETE CASCADE,
                UNIQUE KEY unique_hadith (collection_id, hadith_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_narrator_chains table (for isnad)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_narrator_chains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hadith_id INT NOT NULL,
                narrator_id INT NOT NULL,
                narrator_order INT NOT NULL,
                is_primary_narrator BOOLEAN DEFAULT FALSE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (hadith_id) REFERENCES hadith_narrations(id) ON DELETE CASCADE,
                FOREIGN KEY (narrator_id) REFERENCES hadith_narrators(id) ON DELETE CASCADE,
                UNIQUE KEY unique_chain (hadith_id, narrator_id, narrator_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_keywords table for search
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_keywords (
                id INT AUTO_INCREMENT PRIMARY KEY,
                keyword VARCHAR(100) NOT NULL,
                language VARCHAR(10) NOT NULL,
                hadith_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_keyword (keyword, language)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_keyword_mappings table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_keyword_mappings (
                hadith_id INT NOT NULL,
                keyword_id INT NOT NULL,
                relevance_score FLOAT DEFAULT 1.0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (hadith_id, keyword_id),
                FOREIGN KEY (hadith_id) REFERENCES hadith_narrations(id) ON DELETE CASCADE,
                FOREIGN KEY (keyword_id) REFERENCES hadith_keywords(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create hadith_favorites table for user bookmarks
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hadith_favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                hadith_id INT NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (hadith_id) REFERENCES hadith_narrations(id) ON DELETE CASCADE,
                UNIQUE KEY unique_favorite (user_id, hadith_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create indexes for better performance
        $this->db->exec("CREATE INDEX idx_hadith_collection ON hadith_narrations(collection_id)");
        $this->db->exec("CREATE INDEX idx_hadith_book ON hadith_narrations(book_id)");
        $this->db->exec("CREATE INDEX idx_hadith_number ON hadith_narrations(hadith_number)");
        $this->db->exec("CREATE INDEX idx_hadith_grade ON hadith_narrations(grade)");
        $this->db->exec("CREATE INDEX idx_hadith_keyword ON hadith_keywords(keyword, language)");
        $this->db->exec("CREATE INDEX idx_hadith_keyword_mapping ON hadith_keyword_mappings(keyword_id, hadith_id)");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation
        $this->db->exec("DROP TABLE IF EXISTS hadith_favorites");
        $this->db->exec("DROP TABLE IF EXISTS hadith_keyword_mappings");
        $this->db->exec("DROP TABLE IF EXISTS hadith_keywords");
        $this->db->exec("DROP TABLE IF EXISTS hadith_narrator_chains");
        $this->db->exec("DROP TABLE IF EXISTS hadith_narrations");
        $this->db->exec("DROP TABLE IF EXISTS hadith_narrators");
        $this->db->exec("DROP TABLE IF EXISTS hadith_books");
        $this->db->exec("DROP TABLE IF EXISTS hadith_collections");
    }
}
