<?php

require_once 'vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

// Database configuration
$dbConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

try {
    $db = new Connection($dbConfig);
    
    echo "=== Testing Page Retrieval ===\n";
    
    // Test finding the page
    $page = \IslamWiki\Models\Page::findBySlug('about-islam-wiki', $db);
    
    if ($page) {
        echo "✅ Page found:\n";
        echo "  - ID: " . $page->getAttribute('id') . "\n";
        echo "  - Title: " . $page->getAttribute('title') . "\n";
        echo "  - Slug: " . $page->getAttribute('slug') . "\n";
        echo "  - Content: " . $page->getAttribute('content') . "\n";
        echo "  - Namespace: '" . $page->getAttribute('namespace') . "'\n";
    } else {
        echo "❌ Page not found\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
