<?php
declare(strict_types=1);
/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use IslamWiki\Core\Database\Migrations\Migration;

class CreateSearchIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add full-text search indexes to pages table
        $this->db->exec("
            ALTER TABLE pages 
            ADD FULLTEXT INDEX ft_pages_title_content (title, content)
        ");

        // Add full-text search indexes to verses table (Quran database)
        $this->db->exec("
            ALTER TABLE verses 
            ADD FULLTEXT INDEX ft_verses_arabic_translation (arabic_text, translation)
        ");

        // Add full-text search indexes to hadiths table (Hadith database)
        $this->db->exec("
            ALTER TABLE hadiths 
            ADD FULLTEXT INDEX ft_hadiths_arabic_translation_narrator (arabic_text, translation, narrator)
        ");

        // Add full-text search indexes to islamic_events table (Calendar database)
        $this->db->exec("
            ALTER TABLE islamic_events 
            ADD FULLTEXT INDEX ft_events_title_description_arabic (title, description, arabic_title)
        ");

        // Add full-text search indexes to user_locations table (Prayer database)
        $this->db->exec("
            ALTER TABLE user_locations 
            ADD FULLTEXT INDEX ft_locations_city_country_name (city, country, location_name)
        ");

        // Create search statistics table
        $this->db->exec("
            CREATE TABLE search_statistics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                query VARCHAR(255) NOT NULL,
                search_type ENUM('all', 'pages', 'quran', 'hadith', 'calendar', 'prayer') NOT NULL,
                results_count INT NOT NULL DEFAULT 0,
                search_time_ms INT NOT NULL DEFAULT 0,
                user_id INT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_query (query),
                INDEX idx_search_type (search_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create search suggestions table
        $this->db->exec("
            CREATE TABLE search_suggestions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                query VARCHAR(255) NOT NULL,
                suggestion_type ENUM('page', 'quran', 'hadith', 'calendar', 'prayer') NOT NULL,
                suggestion_text VARCHAR(500) NOT NULL,
                suggestion_url VARCHAR(500) NOT NULL,
                click_count INT NOT NULL DEFAULT 0,
                relevance_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_query (query),
                INDEX idx_suggestion_type (suggestion_type),
                INDEX idx_relevance_score (relevance_score),
                INDEX idx_click_count (click_count)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create search cache table for performance
        $this->db->exec("
            CREATE TABLE search_cache (
                id INT AUTO_INCREMENT PRIMARY KEY,
                query_hash VARCHAR(64) NOT NULL,
                query_text TEXT NOT NULL,
                search_type ENUM('all', 'pages', 'quran', 'hadith', 'calendar', 'prayer') NOT NULL,
                results_data LONGTEXT NOT NULL,
                results_count INT NOT NULL DEFAULT 0,
                cache_hits INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                INDEX idx_query_hash (query_hash),
                INDEX idx_search_type (search_type),
                INDEX idx_expires_at (expires_at),
                UNIQUE KEY unique_query (query_hash, search_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create search analytics table
        $this->db->exec("
            CREATE TABLE search_analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                date DATE NOT NULL,
                total_searches INT NOT NULL DEFAULT 0,
                unique_users INT NOT NULL DEFAULT 0,
                avg_results_per_search DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                avg_search_time_ms DECIMAL(8,2) NOT NULL DEFAULT 0.00,
                most_popular_queries JSON NULL,
                search_type_distribution JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_date (date),
                UNIQUE KEY unique_date (date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert initial search suggestions
        $this->insertInitialSuggestions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop search tables
        $this->db->exec("DROP TABLE IF EXISTS search_analytics");
        $this->db->exec("DROP TABLE IF EXISTS search_cache");
        $this->db->exec("DROP TABLE IF EXISTS search_suggestions");
        $this->db->exec("DROP TABLE IF EXISTS search_statistics");

        // Drop full-text indexes
        $this->db->exec("ALTER TABLE pages DROP INDEX ft_pages_title_content");
        $this->db->exec("ALTER TABLE verses DROP INDEX ft_verses_arabic_translation");
        $this->db->exec("ALTER TABLE hadiths DROP INDEX ft_hadiths_arabic_translation_narrator");
        $this->db->exec("ALTER TABLE islamic_events DROP INDEX ft_events_title_description_arabic");
        $this->db->exec("ALTER TABLE user_locations DROP INDEX ft_locations_city_country_name");
    }

    /**
     * Insert initial search suggestions
     */
    private function insertInitialSuggestions(): void
    {
        $suggestions = [
            // Quran suggestions
            ['quran', 'Al-Fatiha', '/quran/chapter/1', 0.95],
            ['quran', 'Al-Baqarah', '/quran/chapter/2', 0.94],
            ['quran', 'Yasin', '/quran/chapter/36', 0.93],
            ['quran', 'Al-Kahf', '/quran/chapter/18', 0.92],
            ['quran', 'Ar-Rahman', '/quran/chapter/55', 0.91],
            
            // Hadith suggestions
            ['hadith', 'Sahih Bukhari', '/hadith/collection/1', 0.95],
            ['hadith', 'Sahih Muslim', '/hadith/collection/2', 0.94],
            ['hadith', 'Abu Dawud', '/hadith/collection/3', 0.93],
            ['hadith', 'Tirmidhi', '/hadith/collection/4', 0.92],
            ['hadith', 'Nasai', '/hadith/collection/5', 0.91],
            
            // Calendar suggestions
            ['calendar', 'Ramadan', '/calendar/search?q=Ramadan', 0.95],
            ['calendar', 'Eid al-Fitr', '/calendar/search?q=Eid al-Fitr', 0.94],
            ['calendar', 'Eid al-Adha', '/calendar/search?q=Eid al-Adha', 0.93],
            ['calendar', 'Mawlid', '/calendar/search?q=Mawlid', 0.92],
            ['calendar', 'Laylat al-Qadr', '/calendar/search?q=Laylat al-Qadr', 0.91],
            
            // Prayer suggestions
            ['prayer', 'Mecca', '/prayer/search?q=Mecca', 0.95],
            ['prayer', 'Medina', '/prayer/search?q=Medina', 0.94],
            ['prayer', 'Jerusalem', '/prayer/search?q=Jerusalem', 0.93],
            ['prayer', 'Istanbul', '/prayer/search?q=Istanbul', 0.92],
            ['prayer', 'Cairo', '/prayer/search?q=Cairo', 0.91],
            
            // Page suggestions
            ['page', 'Islam', '/Islam', 0.95],
            ['page', 'Quran', '/Quran', 0.94],
            ['page', 'Hadith', '/Hadith', 0.93],
            ['page', 'Prayer', '/Prayer', 0.92],
            ['page', 'Calendar', '/Calendar', 0.91]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO search_suggestions (query, suggestion_type, suggestion_text, suggestion_url, relevance_score)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($suggestions as $suggestion) {
            $stmt->execute($suggestion);
        }
    }
} 