<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migration;

/**
 * Create Quran tables migration
 * 
 * Creates the basic structure for Quran data including surahs, ayahs, and Juz
 */
class CreateQuranTables extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Create quran_surahs table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_surahs (
                surah_number INT PRIMARY KEY,
                name_arabic VARCHAR(100) NOT NULL,
                name_english VARCHAR(100) NOT NULL,
                name_translation VARCHAR(100) NOT NULL,
                revelation_type ENUM('Meccan', 'Medinan') NOT NULL,
                verses_count INT NOT NULL,
                rukus_count INT,
                sajda_ayahs TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_ayahs table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_ayahs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                surah_number INT NOT NULL,
                ayah_number INT NOT NULL,
                text TEXT NOT NULL,
                juz INT NOT NULL,
                page INT NOT NULL,
                hizb INT,
                ruku INT,
                sajda BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (surah_number) REFERENCES quran_surahs(surah_number),
                UNIQUE KEY unique_ayah (surah_number, ayah_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_juz table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_juz (
                juz_number INT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                start_surah INT NOT NULL,
                start_ayah INT NOT NULL,
                end_surah INT NOT NULL,
                end_ayah INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_translations table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_translations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ayah_id INT NOT NULL,
                translator VARCHAR(100) NOT NULL,
                language VARCHAR(10) NOT NULL,
                translation TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ayah_id) REFERENCES quran_ayahs(id),
                UNIQUE KEY unique_translation (ayah_id, translator, language)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_tafsir table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_tafsir (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ayah_id INT NOT NULL,
                mufassir VARCHAR(100) NOT NULL,
                language VARCHAR(10) NOT NULL,
                tafsir_text TEXT NOT NULL,
                source VARCHAR(200),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ayah_id) REFERENCES quran_ayahs(id),
                UNIQUE KEY unique_tafsir (ayah_id, mufassir, language)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_recitations table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_recitations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ayah_id INT NOT NULL,
                reciter VARCHAR(100) NOT NULL,
                audio_url VARCHAR(500),
                audio_file VARCHAR(200),
                duration INT,
                quality VARCHAR(20) DEFAULT 'high',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ayah_id) REFERENCES quran_ayahs(id),
                UNIQUE KEY unique_recitation (ayah_id, reciter)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create quran_bookmarks table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS quran_bookmarks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                ayah_id INT NOT NULL,
                note TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (ayah_id) REFERENCES quran_ayahs(id),
                UNIQUE KEY unique_bookmark (user_id, ayah_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create indexes for better performance
        $this->db->exec("CREATE INDEX idx_quran_ayahs_juz ON quran_ayahs(juz)");
        $this->db->exec("CREATE INDEX idx_quran_ayahs_page ON quran_ayahs(page)");
        $this->db->exec("CREATE INDEX idx_quran_ayahs_surah ON quran_ayahs(surah_number)");
        $this->db->exec("CREATE INDEX idx_quran_translations_lang ON quran_translations(language)");
        $this->db->exec("CREATE INDEX idx_quran_translations_translator ON quran_translations(translator)");
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Drop tables in reverse order
        $this->db->exec("DROP TABLE IF EXISTS quran_bookmarks");
        $this->db->exec("DROP TABLE IF EXISTS quran_recitations");
        $this->db->exec("DROP TABLE IF EXISTS quran_tafsir");
        $this->db->exec("DROP TABLE IF EXISTS quran_translations");
        $this->db->exec("DROP TABLE IF EXISTS quran_juz");
        $this->db->exec("DROP TABLE IF EXISTS quran_ayahs");
        $this->db->exec("DROP TABLE IF EXISTS quran_surahs");
    }
}
