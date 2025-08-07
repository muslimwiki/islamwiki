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

require_once __DIR__ . '/../../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;
use IslamWiki\Models\User;
use IslamWiki\Models\Page;
use IslamWiki\Models\Revision;

/**
 * Database Integration Test
 *
 * This test verifies that the database integration is working properly.
 * It tests:
 * - Database connection
 * - Migration system
 * - Model operations
 * - User authentication
 * - Page operations
 */
class DatabaseIntegrationTest
{
    private Connection $connection;
    private Migrator $migrator;

    public function __construct()
    {
        $this->setupDatabase();
    }

    /**
     * Set up the database connection and run migrations
     */
    private function setupDatabase(): void
    {
        echo "🔧 Setting up database for testing...\n";

        // Get database configuration
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? 'islamwiki_test';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // Create test database
        $this->createTestDatabase($host, $port, $username, $password, $database, $charset);

        // Connect to test database
        $this->connection = new Connection([
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => $charset,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ]);

        // Set up migrator
        $migrationPath = __DIR__ . '/../../../database/migrations';
        $this->migrator = new Migrator($this->connection, $migrationPath);

        // Run migrations
        $this->migrator->run();

        echo "✅ Database setup completed\n";
    }

    /**
     * Create test database
     */
    private function createTestDatabase(string $host, string $port, string $username, string $password, string $database, string $charset): void
    {
        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};charset={$charset}",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");

            echo "✅ Test database '{$database}' created\n";
        } catch (PDOException $e) {
            throw new Exception("Failed to create test database: " . $e->getMessage());
        }
    }

    /**
     * Run all integration tests
     */
    public function runTests(): void
    {
        echo "\n🧪 Running Database Integration Tests\n";
        echo "=====================================\n\n";

        $tests = [
            'testUserCreation',
            'testUserAuthentication',
            'testPageCreation',
            'testPageRetrieval',
            'testPageUpdate',
            'testRevisionTracking',
            'testUserRelationships',
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $test) {
            try {
                $this->$test();
                echo "✅ {$test} - PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ {$test} - FAILED: " . $e->getMessage() . "\n";
                $failed++;
            }
        }

        echo "\n📊 Test Results\n";
        echo "===============\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        echo "Total: " . ($passed + $failed) . "\n\n";

        if ($failed > 0) {
            echo "❌ Some tests failed. Please check the errors above.\n";
            exit(1);
        } else {
            echo "🎉 All tests passed! Database integration is working correctly.\n";
        }
    }

    /**
     * Test user creation
     */
    private function testUserCreation(): void
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'display_name' => 'Test User',
            'is_admin' => false,
            'is_active' => true,
        ];

        $user = new User($this->connection, $userData);
        $user->save();

        if (!$user->exists()) {
            throw new Exception("User was not saved properly");
        }

        // Verify user can be retrieved
        $retrievedUser = User::find($user->getAttribute('id'), $this->connection);
        if (!$retrievedUser) {
            throw new Exception("Could not retrieve created user");
        }

        if ($retrievedUser->getAttribute('username') !== 'testuser') {
            throw new Exception("Retrieved user has wrong username");
        }
    }

    /**
     * Test user authentication
     */
    private function testUserAuthentication(): void
    {
        $user = User::findByUsername('testuser', $this->connection);
        if (!$user) {
            throw new Exception("Test user not found");
        }

        // Test password verification
        if (!$user->verifyPassword('password123')) {
            throw new Exception("Password verification failed");
        }

        if ($user->verifyPassword('wrongpassword')) {
            throw new Exception("Password verification should have failed");
        }

        // Test admin status
        if ($user->isAdmin()) {
            throw new Exception("User should not be admin");
        }
    }

    /**
     * Test page creation
     */
    private function testPageCreation(): void
    {
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '# Test Page Content\n\nThis is a test page.',
            'content_format' => 'markdown',
            'namespace' => '',
        ];

        $page = new Page($this->connection, $pageData);
        $page->save();

        if (!$page->exists()) {
            throw new Exception("Page was not saved properly");
        }

        // Verify page can be retrieved
        $retrievedPage = Page::find($page->getAttribute('id'), $this->connection);
        if (!$retrievedPage) {
            throw new Exception("Could not retrieve created page");
        }

        if ($retrievedPage->getAttribute('title') !== 'Test Page') {
            throw new Exception("Retrieved page has wrong title");
        }
    }

    /**
     * Test page retrieval
     */
    private function testPageRetrieval(): void
    {
        $page = Page::findBySlug('test-page', $this->connection);
        if (!$page) {
            throw new Exception("Could not find page by slug");
        }

        $pageByTitle = Page::findByTitle('Test Page', $this->connection);
        if (!$pageByTitle) {
            throw new Exception("Could not find page by title");
        }

        if ($page->getAttribute('id') !== $pageByTitle->getAttribute('id')) {
            throw new Exception("Page retrieval by slug and title returned different results");
        }
    }

    /**
     * Test page update
     */
    private function testPageUpdate(): void
    {
        $page = Page::findBySlug('test-page', $this->connection);
        if (!$page) {
            throw new Exception("Test page not found for update");
        }

        $originalContent = $page->getAttribute('content');
        $newContent = '# Updated Test Page\n\nThis content has been updated.';

        $page->setAttribute('content', $newContent);
        $page->save();

        // Verify update
        $updatedPage = Page::find($page->getAttribute('id'), $this->connection);
        if ($updatedPage->getAttribute('content') !== $newContent) {
            throw new Exception("Page content was not updated properly");
        }
    }

    /**
     * Test revision tracking
     */
    private function testRevisionTracking(): void
    {
        $page = Page::findBySlug('test-page', $this->connection);
        if (!$page) {
            throw new Exception("Test page not found for revision test");
        }

        $user = User::findByUsername('testuser', $this->connection);
        if (!$user) {
            throw new Exception("Test user not found for revision test");
        }

        // Create a revision
        $revisionData = [
            'page_id' => $page->getAttribute('id'),
            'user_id' => $user->getAttribute('id'),
            'title' => $page->getAttribute('title'),
            'content' => $page->getAttribute('content'),
            'content_format' => $page->getAttribute('content_format'),
            'comment' => 'Test revision',
            'is_minor_edit' => false,
        ];

        $revision = new Revision($this->connection, $revisionData);
        $revision->save();

        if (!$revision->exists()) {
            throw new Exception("Revision was not saved properly");
        }

        // Verify revision can be retrieved
        $retrievedRevision = Revision::find($revision->getAttribute('id'), $this->connection);
        if (!$retrievedRevision) {
            throw new Exception("Could not retrieve created revision");
        }

        if ($retrievedRevision->getAttribute('page_id') !== $page->getAttribute('id')) {
            throw new Exception("Revision has wrong page_id");
        }
    }

    /**
     * Test user relationships
     */
    private function testUserRelationships(): void
    {
        $user = User::findByUsername('testuser', $this->connection);
        if (!$user) {
            throw new Exception("Test user not found for relationship test");
        }

        // Test user contributions
        $contributions = $user->getContributions(10);
        if (!is_array($contributions)) {
            throw new Exception("User contributions should return an array");
        }

        // Test user profile methods
        $displayName = $user->getDisplayName();
        if ($displayName !== 'Test User') {
            throw new Exception("User display name is incorrect");
        }

        $profileUrl = $user->getProfileUrl();
        if (empty($profileUrl)) {
            throw new Exception("User profile URL should not be empty");
        }
    }
}

// Run the tests if this script is executed directly
if (php_sapi_name() === 'cli') {
    $test = new DatabaseIntegrationTest();
    $test->runTests();
}
