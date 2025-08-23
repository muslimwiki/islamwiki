<?php
/**
 * Migration: Add content_format column to wiki_pages table
 * 
 * This migration adds the missing content_format column to the wiki_pages table
 * to support the page creation functionality.
 */

require_once __DIR__ . '/../../src/Core/Database/Connection.php';

use IslamWiki\Core\Database\Connection;

echo "=== Adding content_format column to wiki_pages table ===\n\n";

try {
    $connection = new Connection();
    echo "✅ Connected to database\n";
    
    // Check if content_format column already exists
    $stmt = $connection->getPdo()->query("SHOW COLUMNS FROM wiki_pages LIKE 'content_format'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✅ content_format column already exists in wiki_pages table\n";
    } else {
        // Add content_format column
        echo "🔧 Adding content_format column to wiki_pages table...\n";
        
        $sql = "ALTER TABLE wiki_pages ADD COLUMN content_format VARCHAR(50) DEFAULT 'markdown' AFTER content";
        $connection->getPdo()->exec($sql);
        
        echo "✅ content_format column added successfully\n";
        
        // Update existing records to have 'markdown' as default
        echo "🔧 Updating existing records with default content_format...\n";
        
        $updateSql = "UPDATE wiki_pages SET content_format = 'markdown' WHERE content_format IS NULL";
        $affectedRows = $connection->getPdo()->exec($updateSql);
        
        echo "✅ Updated {$affectedRows} existing records with default content_format\n";
    }
    
    // Verify the column structure
    echo "\n🔍 Verifying table structure...\n";
    $stmt = $connection->getPdo()->query("DESCRIBE wiki_pages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $contentFormatColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'content_format') {
            $contentFormatColumn = $column;
            break;
        }
    }
    
    if ($contentFormatColumn) {
        echo "✅ content_format column details:\n";
        echo "   - Field: {$contentFormatColumn['Field']}\n";
        echo "   - Type: {$contentFormatColumn['Type']}\n";
        echo "   - Null: {$contentFormatColumn['Null']}\n";
        echo "   - Default: {$contentFormatColumn['Default']}\n";
        echo "   - Key: {$contentFormatColumn['Key']}\n";
    }
    
    // Test page creation logic
    echo "\n🧪 Testing page creation logic...\n";
    
    $testData = [
        'title' => 'Test Page for Migration',
        'content' => 'This is a test page to verify the migration.',
        'content_format' => 'markdown',
        'namespace' => '',
        'status' => 'published'
    ];
    
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s-]/', '', $testData['title'])));
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = trim($slug, '-');
    
    echo "   - Test title: {$testData['title']}\n";
    echo "   - Generated slug: {$slug}\n";
    echo "   - Content format: {$testData['content_format']}\n";
    
    // Check if test page exists
    $stmt = $connection->getPdo()->prepare("SELECT id FROM wiki_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $existingPage = $stmt->fetch();
    
    if ($existingPage) {
        echo "   - Test page already exists, skipping insertion\n";
    } else {
        // Try to insert test page
        echo "   - Inserting test page...\n";
        
        $insertSql = "INSERT INTO wiki_pages (title, slug, content, content_format, namespace, status, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $connection->getPdo()->prepare($insertSql);
        $success = $stmt->execute([
            $testData['title'],
            $slug,
            $testData['content'],
            $testData['content_format'],
            $testData['namespace'],
            $testData['status']
        ]);
        
        if ($success) {
            echo "   ✅ Test page inserted successfully\n";
            
            // Clean up test page
            $deleteSql = "DELETE FROM wiki_pages WHERE slug = ?";
            $stmt = $connection->getPdo()->prepare($deleteSql);
            $stmt->execute([$slug]);
            echo "   🧹 Test page cleaned up\n";
        } else {
            echo "   ❌ Failed to insert test page\n";
        }
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "✅ content_format column is now available in wiki_pages table\n";
    echo "✅ Page creation should now work correctly\n";
    echo "✅ Redirect should go to /wiki/{slug} instead of /create\n";
    
} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 