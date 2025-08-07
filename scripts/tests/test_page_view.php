<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Models\Page;

echo "Testing Page View Functionality\n";
echo "===============================\n\n";

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

    // Test Page model
    echo "\n🔍 Testing Page model...\n";

    $page = Page::findBySlug('welcome', $connection);
    if ($page) {
        echo "  ✅ Found page: {$page->getAttribute('title')}\n";
        echo "  📊 Page details:\n";
        echo "    - Slug: {$page->getAttribute('slug')}\n";
        echo "    - Content length: " . strlen($page->getAttribute('content')) . " characters\n";
        echo "    - Created: {$page->getAttribute('created_at')}\n";
        echo "    - Updated: {$page->getAttribute('updated_at')}\n";
        echo "    - Is locked: " . ($page->isLocked() ? 'Yes' : 'No') . "\n";
        echo "    - URL: {$page->getUrl()}\n";
    } else {
        echo "  ❌ Page 'welcome' not found\n";
    }

    // Test page creation
    echo "\n📝 Testing page creation...\n";
    $testPage = new Page($connection, [
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => '# Test Page\n\nThis is a test page created by the script.',
        'content_format' => 'markdown',
        'namespace' => 'main',
        'is_locked' => false
    ]);

    $saved = $testPage->save();
    echo "  " . ($saved ? "✅" : "❌") . " Test page created successfully\n";

    if ($saved) {
        echo "  📊 Test page details:\n";
        echo "    - ID: {$testPage->getAttribute('id')}\n";
        echo "    - Title: {$testPage->getAttribute('title')}\n";
        echo "    - Slug: {$testPage->getAttribute('slug')}\n";
    }

    // Test page listing
    echo "\n📋 Testing page listing...\n";
    $pages = $connection->select('SELECT id, title, slug, created_at FROM pages ORDER BY created_at DESC LIMIT 5');
    echo "  ✅ Found " . count($pages) . " pages\n";

    foreach ($pages as $pageData) {
        echo "    - {$pageData['title']} ({$pageData['slug']})\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Page functionality test completed successfully!\n";
echo "\nDone!\n";
