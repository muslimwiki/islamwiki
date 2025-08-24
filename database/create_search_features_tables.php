<?php

/**
 * Create Search Features Tables
 *
 * This script creates the necessary database tables for advanced search features:
 * - saved_searches: User's saved search queries
 * - search_history: Search query history
 * - search_analytics: Search performance analytics
 * - search_suggestions: Cached search suggestions
 * - page_categories: Page categorization for search
 *
 * @package IslamWiki\Database
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: 'islamwiki';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "✅ Database connection established successfully\n";
    
    // Create saved_searches table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS saved_searches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            tags VARCHAR(500),
            query_string TEXT NOT NULL,
            is_public BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_public (is_public),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created saved_searches table\n";
    
    // Create search_history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS search_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            query VARCHAR(500) NOT NULL,
            filters JSON,
            result_count INT DEFAULT 0,
            search_time_ms INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_query (query(100)),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created search_history table\n";
    
    // Create search_analytics table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS search_analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query VARCHAR(500) NOT NULL,
            user_id INT,
            domain VARCHAR(50),
            filters JSON,
            result_count INT DEFAULT 0,
            search_time_ms INT DEFAULT 0,
            db_queries INT DEFAULT 0,
            memory_usage_mb DECIMAL(8,2) DEFAULT 0,
            performance_score VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_query (query(100)),
            INDEX idx_user_id (user_id),
            INDEX idx_domain (domain),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created search_analytics table\n";
    
    // Create search_suggestions table for caching popular searches
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS search_suggestions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query VARCHAR(500) NOT NULL,
            type VARCHAR(50) NOT NULL,
            count INT DEFAULT 1,
            relevance_score INT DEFAULT 0,
            last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_query_type (query, type),
            INDEX idx_type (type),
            INDEX idx_relevance (relevance_score),
            INDEX idx_last_used (last_used)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created search_suggestions table\n";
    
    // Create page_categories table if it doesn't exist (for category search)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS page_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT NOT NULL,
            category_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_page_id (page_id),
            INDEX idx_category_name (category_name),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created page_categories table\n";
    
    // Insert some sample categories for testing
    $pdo->exec("
        INSERT IGNORE INTO page_categories (page_id, category_name) VALUES
        (1, 'Islamic History'),
        (1, 'Quran'),
        (1, 'Hadith'),
        (1, 'Fiqh'),
        (1, 'Aqeedah'),
        (1, 'Tasawwuf'),
        (1, 'Islamic Sciences'),
        (1, 'Biography'),
        (1, 'Contemporary Issues'),
        (1, 'Islamic Education')
    ");
    echo "✅ Inserted sample categories\n";
    
    // Insert some sample search suggestions
    $pdo->exec("
        INSERT IGNORE INTO search_suggestions (query, type, count, relevance_score) VALUES
        ('islamic history', 'wiki', 15, 85),
        ('quran verses', 'quran', 23, 90),
        ('hadith collection', 'hadith', 18, 88),
        ('islamic law', 'fiqh', 12, 82),
        ('muslim scholars', 'biography', 20, 87),
        ('islamic architecture', 'wiki', 8, 75),
        ('islamic art', 'wiki', 6, 70),
        ('islamic medicine', 'wiki', 9, 78),
        ('islamic astronomy', 'wiki', 7, 72),
        ('islamic mathematics', 'wiki', 5, 68)
    ");
    echo "✅ Inserted sample search suggestions\n";
    
    echo "\n🎉 All search features tables created successfully!\n";
    echo "\nTables created:\n";
    echo "- saved_searches: User's saved search queries\n";
    echo "- search_history: Search query history\n";
    echo "- search_analytics: Search performance analytics\n";
    echo "- search_suggestions: Cached search suggestions\n";
    echo "- page_categories: Page categorization for search\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 