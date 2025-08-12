<?php

/**
 * Create Missing Tables Script
 *
 * This script creates the missing tables from the initial schema
 * without affecting existing tables.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

echo "Creating Missing Tables\n";
echo "======================\n\n";

try {
    // Create database connection
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Define the missing tables and their SQL
    $tables = [
        'pages' => "CREATE TABLE pages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            content_format VARCHAR(20) DEFAULT 'markdown',
            namespace VARCHAR(50) DEFAULT '',
            parent_id BIGINT UNSIGNED NULL,
            is_locked BOOLEAN DEFAULT FALSE,
            view_count INT DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            INDEX idx_namespace_slug (namespace, slug)
        )",

        'page_revisions' => "CREATE TABLE page_revisions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            content_format VARCHAR(20) DEFAULT 'markdown',
            comment TEXT NULL,
            is_minor_edit BOOLEAN DEFAULT FALSE,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            INDEX idx_page_created (page_id, created_at)
        )",

        'categories' => "CREATE TABLE categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )",

        'page_categories' => "CREATE TABLE page_categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY unique_page_category (page_id, category_id)
        )",

        'media_files' => "CREATE TABLE media_files (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            filename VARCHAR(255) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            size INT NOT NULL,
            width INT NULL,
            height INT NULL,
            alt_text VARCHAR(255) NULL,
            description TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            INDEX idx_filename (filename)
        )",

        'user_watchlist' => "CREATE TABLE user_watchlist (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            page_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY unique_user_page (user_id, page_id)
        )"
    ];

    // Create each missing table
    foreach ($tables as $tableName => $sql) {
        // Check if table already exists
        $result = $connection->select("SHOW TABLES LIKE '$tableName'");

        if (count($result) > 0) {
            echo "⚠️  Table '$tableName' already exists, skipping...\n";
            continue;
        }

        echo "Creating table '$tableName'...\n";
        $connection->statement($sql);
        echo "✅ Table '$tableName' created successfully\n";
    }

    // Mark the initial migration as completed
    echo "\nMarking migration as completed...\n";
    $connection->statement(
        "INSERT INTO migrations (migration, batch, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
        ['0001_initial_schema', 1]
    );
    echo "✅ Migration 0001_initial_schema marked as completed\n";

    echo "\n🎉 All missing tables created successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
