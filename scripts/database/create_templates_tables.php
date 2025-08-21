<?php

declare(strict_types=1);

/**
 * Create Templates Tables Script
 * 
 * This script creates the necessary database tables for the template system
 * using direct SQL since the migration system has some issues.
 * 
 * Usage: php scripts/database/create_templates_tables.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

// Initialize database connection
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

try {
    $connection = new Connection($config);
    $pdo = $connection->getPdo();
    echo "✅ Database connection established\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// SQL statements to create the tables
$sqlStatements = [
    // Template categories table
    "CREATE TABLE IF NOT EXISTS template_categories (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        icon VARCHAR(50),
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Templates table
    "CREATE TABLE IF NOT EXISTS templates (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) UNIQUE NOT NULL,
        content TEXT NOT NULL,
        parameters JSON,
        description VARCHAR(255),
        category VARCHAR(100),
        author VARCHAR(100),
        is_active BOOLEAN DEFAULT TRUE,
        is_system BOOLEAN DEFAULT FALSE,
        usage_count INT DEFAULT 0,
        last_used_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category_active (category, is_active),
        INDEX idx_name_active (name, is_active),
        INDEX idx_name_description (name, description)
    )",
    
    // Template usage tracking table
    "CREATE TABLE IF NOT EXISTS template_usage (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        template_id BIGINT NOT NULL,
        page_id BIGINT NULL,
        page_slug VARCHAR(255),
        parameters_used JSON,
        user_agent VARCHAR(255),
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_template_created (template_id, created_at),
        INDEX idx_page_created (page_id, created_at),
        FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
        FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE SET NULL
    )"
];

echo "📝 Creating template tables...\n";

foreach ($sqlStatements as $sql) {
    try {
        $pdo->exec($sql);
        echo "  ✅ Table created successfully\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "  ⚠️  Table already exists\n";
        } else {
            echo "  ❌ Error creating table: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n🎉 Template tables creation completed!\n";
echo "\n🔧 Next steps:\n";
echo "  1. Seed default templates: php scripts/templates/seed_default_templates.php\n";
echo "  2. Test templates in wiki pages\n";
echo "  3. Create custom templates as needed\n"; 