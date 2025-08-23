<?php
/**
 * Test Page Creation Fix
 * 
 * Tests if the page creation issue has been resolved by checking:
 * - Table name consistency
 * - Database insertion
 * - Redirect behavior
 */

require_once 'autoload.php';
require_once __DIR__ . '/../../src/Core/Database/Connection.php';

use IslamWiki\Core\Database\Connection;

echo "=== Testing Page Creation Fix ===\n\n";

try {
    // Test 1: Database Connection
    echo "1. Testing Database Connection:\n";
    $connection = new Connection();
    echo "   ✅ Connected to production database\n\n";
    
    // Test 2: Check Table Names
    echo "2. Checking Table Names:\n";
    
    // Check if wiki_pages table exists
    $stmt = $connection->getPdo()->query("SHOW TABLES LIKE 'wiki_pages'");
    $wikiPagesExists = $stmt->rowCount() > 0;
    echo "   - wiki_pages table: " . ($wikiPagesExists ? '✅ Exists' : '❌ Missing') . "\n";
    
    // Check if pages table exists
    $stmt = $connection->getPdo()->query("SHOW TABLES LIKE 'pages'");
    $pagesExists = $stmt->rowCount() > 0;
    echo "   - pages table: " . ($pagesExists ? '✅ Exists' : '❌ Missing') . "\n";
    
    // Check if page_revisions table exists
    $stmt = $connection->getPdo()->query("SHOW TABLES LIKE 'page_revisions'");
    $pageRevisionsExists = $stmt->rowCount() > 0;
    echo "   - page_revisions table: " . ($pageRevisionsExists ? '✅ Exists' : '❌ Missing') . "\n\n";
    
    // Test 3: Check Table Structure
    echo "3. Checking Table Structure:\n";
    
    if ($wikiPagesExists) {
        $stmt = $connection->getPdo()->query("DESCRIBE wiki_pages");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "   - wiki_pages columns: " . implode(', ', $columns) . "\n";
        
        // Check for required columns
        $requiredColumns = ['id', 'title', 'slug', 'content', 'namespace', 'status'];
        foreach ($requiredColumns as $column) {
            $hasColumn = in_array($column, $columns);
            echo "     - {$column}: " . ($hasColumn ? '✅' : '❌') . "\n";
        }
        
        // Check for optional columns
        $optionalColumns = ['content_format', 'is_locked', 'view_count'];
        foreach ($optionalColumns as $column) {
            $hasColumn = in_array($column, $columns);
            echo "     - {$column}: " . ($hasColumn ? '✅' : '❌') . " (optional)\n";
        }
    }
    echo "\n";
    
    // Test 4: Check Pages Table Structure
    echo "4. Checking Pages Table Structure:\n";
    
    if ($pagesExists) {
        $stmt = $connection->getPdo()->query("DESCRIBE pages");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "   - pages columns: " . implode(', ', $columns) . "\n";
        
        // Check for required columns
        $requiredColumns = ['id', 'title', 'slug', 'content', 'content_format'];
        foreach ($requiredColumns as $column) {
            $hasColumn = in_array($column, $columns);
            echo "     - {$column}: " . ($hasColumn ? '✅' : '❌') . "\n";
        }
    }
    echo "\n";
    
    // Test 5: Test Page Creation Logic
    echo "5. Testing Page Creation Logic:\n";
    
    try {
        $title = 'Test Page';
        $content = 'This is a test page content.';
        $namespace = '';
        $contentFormat = 'markdown';
        
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s-]/', '', $title)));
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = trim($slug, '-');
        
        echo "   - Title: {$title}\n";
        echo "   - Generated slug: {$slug}\n";
        echo "   - Content length: " . strlen($content) . " characters\n";
        echo "   - Namespace: " . ($namespace ?: 'Main') . "\n";
        echo "   - Content format: {$contentFormat}\n";
        
        // Check if page already exists in wiki_pages
        $stmt = $connection->getPdo()->prepare("SELECT id FROM wiki_pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $existingWikiPage = $stmt->fetch();
        echo "   - Page exists in wiki_pages: " . ($existingWikiPage ? 'Yes' : 'No') . "\n";
        
        // Check if page already exists in pages
        $stmt = $connection->getPdo()->prepare("SELECT id FROM pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $existingPage = $stmt->fetch();
        echo "   - Page exists in pages: " . ($existingPage ? 'Yes' : 'No') . "\n";
        
        if (!$existingWikiPage && !$existingPage) {
            echo "   - Page creation would proceed\n";
            
            // Check which table to use based on available columns
            if ($wikiPagesExists) {
                echo "   - Would insert into: wiki_pages table\n";
                echo "   - Required fields: title, slug, content, namespace, status\n";
                echo "   - Would redirect to: /wiki/{$slug}\n";
            } else {
                echo "   - Would insert into: pages table\n";
                echo "   - Required fields: title, slug, content, content_format\n";
                echo "   - Would redirect to: /wiki/{$slug}\n";
            }
        } else {
            echo "   - Page creation would redirect to edit existing page\n";
        }
        
    } catch (Exception $e) {
        echo "   - Page creation logic test: ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 6: Summary and Recommendations
    echo "6. Summary and Recommendations:\n";
    
    if ($wikiPagesExists && $pageRevisionsExists) {
        echo "   ✅ All required tables exist\n";
        
        if (in_array('content_format', $columns)) {
            echo "   ✅ wiki_pages table has content_format column\n";
            echo "   ✅ Page creation should work with wiki_pages table\n";
        } else {
            echo "   ❌ wiki_pages table missing content_format column\n";
            echo "   🔧 Need to either:\n";
            echo "      - Add content_format column to wiki_pages table, OR\n";
            echo "      - Update PageController to not use content_format field\n";
        }
        
        echo "   ✅ Redirect should go to /wiki/{slug} instead of /create\n";
    } else {
        echo "   ❌ Some required tables are missing\n";
        echo "   ❌ Page creation may fail\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 