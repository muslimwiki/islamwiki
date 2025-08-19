<?php

/**
 * Recreate Core Tables
 * 
 * This script recreates the missing core tables that should exist
 * but were somehow lost or not properly created.
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

try {
    $db = new Connection();
    echo "✅ Database connection successful\n";
    
    // Check which core tables are missing
    $requiredTables = [
        'pages',
        'page_revisions', 
        'user_watchlist',
        'categories',
        'page_categories',
        'media_files'
    ];
    
    echo "Checking required core tables:\n";
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $db->select("SHOW TABLES LIKE '$table'");
        if (count($result) > 0) {
            echo "  ✅ $table: EXISTS\n";
        } else {
            echo "  ❌ $table: MISSING\n";
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "\n🎉 All core tables exist! No action needed.\n";
        exit(0);
    }
    
    echo "\n🔧 Recreating missing tables...\n";
    
    // Recreate pages table
    if (in_array('pages', $missingTables)) {
        echo "Creating pages table...\n";
        $sql = "CREATE TABLE pages (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ pages table created\n";
    }
    
    // Recreate page_revisions table
    if (in_array('page_revisions', $missingTables)) {
        echo "Creating page_revisions table...\n";
        $sql = "CREATE TABLE page_revisions (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ page_revisions table created\n";
    }
    
    // Recreate user_watchlist table
    if (in_array('user_watchlist', $missingTables)) {
        echo "Creating user_watchlist table...\n";
        $sql = "CREATE TABLE user_watchlist (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            page_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY unique_user_page (user_id, page_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ user_watchlist table created\n";
    }
    
    // Recreate categories table
    if (in_array('categories', $missingTables)) {
        echo "Creating categories table...\n";
        $sql = "CREATE TABLE categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ categories table created\n";
    }
    
    // Recreate page_categories table
    if (in_array('page_categories', $missingTables)) {
        echo "Creating page_categories table...\n";
        $sql = "CREATE TABLE page_categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY unique_page_category (page_id, category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ page_categories table created\n";
    }
    
    // Recreate media_files table
    if (in_array('media_files', $missingTables)) {
        echo "Creating media_files table...\n";
        $sql = "CREATE TABLE media_files (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            size BIGINT UNSIGNED NOT NULL,
            path VARCHAR(500) NOT NULL,
            alt_text TEXT NULL,
            caption TEXT NULL,
            uploaded_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            INDEX idx_filename (filename),
            INDEX idx_mime_type (mime_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->statement($sql);
        echo "  ✅ media_files table created\n";
    }
    
    echo "\n🎉 All missing core tables have been recreated!\n";
    
    // Verify tables were created
    echo "\nVerifying table creation:\n";
    foreach ($requiredTables as $table) {
        $result = $db->select("SHOW TABLES LIKE '$table'");
        if (count($result) > 0) {
            echo "  ✅ $table: EXISTS\n";
        } else {
            echo "  ❌ $table: STILL MISSING\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 