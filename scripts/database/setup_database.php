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

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

/**
 * Database Setup Script
 * 
 * This script helps set up the database for IslamWiki.
 * It will create the database if it doesn't exist and run migrations.
 */

class DatabaseSetup
{
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $charset;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    /**
     * Run the database setup
     */
    public function run(): void
    {
        echo "🚀 IslamWiki Database Setup\n";
        echo "==========================\n\n";

        try {
            // Test connection without database
            $this->testConnection();
            
            // Create database if it doesn't exist
            $this->createDatabase();
            
            // Connect to the specific database
            $connection = $this->connectToDatabase();
            
            // Run migrations
            $this->runMigrations($connection);
            
            // Create sample data
            $this->createSampleData($connection);
            
            echo "\n✅ Database setup completed successfully!\n";
            echo "You can now access IslamWiki at: http://localhost\n";
            
        } catch (Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            exit(1);
        }
    }

    /**
     * Test database connection
     */
    private function testConnection(): void
    {
        echo "🔍 Testing database connection...\n";
        
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};port={$this->port};charset={$this->charset}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            echo "✅ Database connection successful\n";
            
        } catch (PDOException $e) {
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        }
    }

    /**
     * Create the database if it doesn't exist
     */
    private function createDatabase(): void
    {
        echo "📦 Creating database '{$this->database}' if it doesn't exist...\n";
        
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};port={$this->port};charset={$this->charset}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET {$this->charset} COLLATE {$this->charset}_unicode_ci");
            
            echo "✅ Database '{$this->database}' is ready\n";
            
        } catch (PDOException $e) {
            throw new Exception("Failed to create database: " . $e->getMessage());
        }
    }

    /**
     * Connect to the specific database
     */
    private function connectToDatabase(): Connection
    {
        echo "🔌 Connecting to database '{$this->database}'...\n";
        
        try {
            $connection = new Connection([
                'driver' => 'mysql',
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database,
                'username' => $this->username,
                'password' => $this->password,
                'charset' => $this->charset,
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            ]);
            
            echo "✅ Connected to database successfully\n";
            return $connection;
            
        } catch (Exception $e) {
            throw new Exception("Failed to connect to database '{$this->database}': " . $e->getMessage());
        }
    }

    /**
     * Drop existing tables if they exist
     */
    private function dropExistingTables(Connection $connection): void
    {
        echo "🗑️  Dropping existing tables...\n";
        
        try {
            $tables = [
                'page_categories',
                'user_watchlist', 
                'page_revisions',
                'pages',
                'categories',
                'media_files',
                'users',
                'migrations'
            ];
            
            foreach ($tables as $table) {
                $connection->getPdo()->exec("DROP TABLE IF EXISTS `{$table}`");
            }
            
            echo "✅ Existing tables dropped\n";
            
        } catch (Exception $e) {
            echo "⚠️  Warning: Failed to drop existing tables: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Run database migrations
     */
    private function runMigrations(Connection $connection): void
    {
        echo "🔄 Running database migrations...\n";
        
        try {
            // Drop existing tables first
            $this->dropExistingTables($connection);
            
            $migrationPath = __DIR__ . '/../database/migrations';
            $migrator = new Migrator($connection, $migrationPath);
            
            $migrator->run();
            
            echo "✅ Migrations completed successfully\n";
            
        } catch (Exception $e) {
            throw new Exception("Failed to run migrations: " . $e->getMessage());
        }
    }

    /**
     * Create sample data for development
     */
    private function createSampleData(Connection $connection): void
    {
        echo "📝 Creating sample data...\n";
        
        try {
            // Create sample user
            $this->createSampleUser($connection);
            
            // Create sample pages
            $this->createSamplePages($connection);
            
            echo "✅ Sample data created successfully\n";
            
        } catch (Exception $e) {
            echo "⚠️  Warning: Failed to create sample data: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Create a sample user
     */
    private function createSampleUser(Connection $connection): void
    {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT IGNORE INTO users (username, email, password_hash, display_name, is_admin, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $connection->getPdo()->prepare($sql)->execute([
            'admin',
            'admin@islamwiki.local',
            $password,
            'Administrator',
            1
        ]);
        
        echo "  ✅ Created sample admin user (admin/admin123)\n";
    }

    /**
     * Create sample pages
     */
    private function createSamplePages(Connection $connection): void
    {
        $pages = [
            [
                'title' => 'Welcome to IslamWiki',
                'slug' => 'welcome',
                'content' => "# Welcome to IslamWiki\n\nThis is your Islamic knowledge base and resource center.\n\n## Features\n\n- Modern, responsive design\n- Easy page editing\n- User authentication\n- Page history tracking\n\n## Getting Started\n\n1. Create an account\n2. Start editing pages\n3. Contribute to the community",
                'content_format' => 'markdown'
            ],
            [
                'title' => 'About Islam',
                'slug' => 'about-islam',
                'content' => "# About Islam\n\nIslam is a monotheistic Abrahamic religion that originated in the 7th century CE in the Arabian Peninsula.\n\n## Core Beliefs\n\n- **Tawhid**: Belief in the oneness of God\n- **Prophethood**: Belief in the prophets and messengers\n- **Hereafter**: Belief in the Day of Judgment\n\n## Five Pillars\n\n1. Shahada (Declaration of Faith)\n2. Salah (Prayer)\n3. Zakat (Charity)\n4. Sawm (Fasting)\n5. Hajj (Pilgrimage)",
                'content_format' => 'markdown'
            ]
        ];
        
        foreach ($pages as $page) {
            $sql = "INSERT IGNORE INTO pages (title, slug, content, content_format, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $connection->getPdo()->prepare($sql)->execute([
                $page['title'],
                $page['slug'],
                $page['content'],
                $page['content_format']
            ]);
        }
        
        echo "  ✅ Created sample pages\n";
    }
}

// Run the setup if this script is executed directly
if (php_sapi_name() === 'cli') {
    $setup = new DatabaseSetup();
    $setup->run();
} 