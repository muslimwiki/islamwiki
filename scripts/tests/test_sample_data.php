<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
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

declare(strict_types=1);

/**
 * Test Sample Data Creation
 *
 * This script tests the sample data creation functionality.
 * Usage: php scripts/test_sample_data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

echo "IslamWiki Sample Data Test\n";
echo "=========================\n\n";

try {
    // Create database connection directly
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

    // Test sample data creation
    echo "📝 Creating sample data...\n";

    // Create sample user
    echo "  Creating sample user...\n";
    $password = password_hash('admin123', PASSWORD_DEFAULT);

    $sql = "INSERT IGNORE INTO users (username, email, password, display_name, is_admin, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

    $connection->statement($sql, [
        'admin',
        'admin@islamwiki.local',
        $password,
        'Administrator',
        1
    ]);

    echo "  ✅ Created sample admin user (admin/admin123)\n";

    // Create sample pages
    echo "  Creating sample pages...\n";
    $pages = [
        [
            'title' => 'Welcome to IslamWiki',
            'slug' => 'welcome',
            'content' => '# Welcome to IslamWiki

This is a sample page to get you started with IslamWiki.

## Features

- **User Management**: Register, login, and manage user profiles
- **Page Creation**: Create and edit wiki pages with Markdown support
- **Revision History**: Track changes to pages over time
- **Categories**: Organize content with categories
- **Media Uploads**: Upload and manage images and files

## Getting Started

1. Create an account or login
2. Start creating pages
3. Explore the wiki!

*This is a sample page created during setup.*',
            'content_format' => 'markdown',
            'namespace' => 'main'
        ],
        [
            'title' => 'About IslamWiki',
            'slug' => 'about',
            'content' => '# About IslamWiki

IslamWiki is an open-source wiki platform designed for Islamic knowledge sharing and collaboration.

## Mission

Our mission is to provide a platform for sharing Islamic knowledge, research, and resources in a collaborative and accessible way.

## Technology

Built with modern web technologies:
- PHP 8.3+
- MySQL/MariaDB
- Twig templating
- Bootstrap CSS

## Contributing

This is an open-source project. Contributions are welcome!

*This is a sample page created during setup.*',
            'content_format' => 'markdown',
            'namespace' => 'main'
        ]
    ];

    foreach ($pages as $page) {
        $sql = "INSERT IGNORE INTO pages (title, slug, content, content_format, namespace, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        $connection->statement($sql, [
            $page['title'],
            $page['slug'],
            $page['content'],
            $page['content_format'],
            $page['namespace']
        ]);

        echo "  ✅ Created page: {$page['title']}\n";
    }

    echo "✅ Sample data created successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
