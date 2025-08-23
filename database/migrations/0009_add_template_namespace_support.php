<?php
/**
 * Migration: Add Template Namespace Support
 * 
 * Adds proper namespace support for templates in the Enhanced Markdown system.
 * Templates are stored as pages in the Template namespace following MediaWiki's approach.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */

require_once __DIR__ . '/../../src/Core/Database/Connection.php';

use IslamWiki\Core\Database\Connection;

/**
 * Add namespace column to pages table and create template-specific indexes
 */
function migrate_template_namespace_support()
{
    $connection = new Connection();
    
    echo "Adding template namespace support...\n";
    
    try {
        // Add namespace column if it doesn't exist
        $connection->query("
            ALTER TABLE wiki_pages 
            ADD COLUMN IF NOT EXISTS namespace VARCHAR(50) DEFAULT 'Main' AFTER title
        ");
        
        // Add index for namespace queries
        $connection->query("
            CREATE INDEX IF NOT EXISTS idx_wiki_pages_namespace ON wiki_pages(namespace)
        ");
        
        // Add composite index for namespace + title queries
        $connection->query("
            CREATE INDEX IF NOT EXISTS idx_wiki_pages_namespace_title ON wiki_pages(namespace, title)
        ");
        
        // Add index for template lookups
        $connection->query("
            CREATE INDEX IF NOT EXISTS idx_wiki_pages_template_lookup ON wiki_pages(namespace, title, status)
        ");
        
        // Insert some default templates
        insertDefaultTemplates($connection);
        
        echo "✅ Template namespace support added successfully\n";
        
    } catch (Exception $e) {
        echo "❌ Error adding template namespace support: " . $e->getMessage() . "\n";
        throw $e;
    }
}

/**
 * Insert default templates for the system
 */
function insertDefaultTemplates($connection)
{
    echo "Inserting default templates...\n";
    
    $defaultTemplates = [
        [
            'name' => 'Good article',
            'content' => '<div class="template article-quality"><i class="fas fa-star"></i> This is a good article</div>',
            'description' => 'Indicates that an article has been reviewed and meets good article criteria'
        ],
        [
            'name' => 'About',
            'content' => '<div class="template about-template"><p>This article is about <strong>{{{1}}}</strong>{{#if:{{{3}}}|. For other uses, see <a href="/wiki/{{{3}}}">{{{3}}}</a>|}}.</p></div>',
            'description' => 'Disambiguation notice for articles that could refer to multiple topics'
        ],
        [
            'name' => 'Infobox',
            'content' => '<div class="template infobox"><h3>{{{title|Article Title}}}</h3><div class="infobox-content">{{{1}}}</div></div>',
            'description' => 'Basic infobox template for displaying structured information'
        ],
        [
            'name' => 'Stub',
            'content' => '<div class="template stub"><i class="fas fa-exclamation-triangle"></i> This article is a stub. You can help IslamWiki by expanding it.</div>',
            'description' => 'Indicates that an article needs expansion'
        ],
        [
            'name' => 'Warning',
            'content' => '<div class="template warning"><i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> {{{1}}}</div>',
            'description' => 'Displays a warning message'
        ],
        [
            'name' => 'Note',
            'content' => '<div class="template note"><i class="fas fa-info-circle"></i> <strong>Note:</strong> {{{1}}}</div>',
            'description' => 'Displays an informational note'
        ],
        [
            'name' => 'Cquote',
            'content' => '<blockquote class="template cquote"><p>{{{1}}}</p>{{#if:{{{2}}}|<cite>— {{{2}}}</cite>|}}</blockquote>',
            'description' => 'Displays a styled quotation with optional source attribution'
        ],
        [
            'name' => 'Reflist',
            'content' => '<div class="template reflist"><h2>References</h2><ol class="references">{{{1|References will appear here}}}</ol></div>',
            'description' => 'Container for reference lists'
        ],
        [
            'name' => 'Islam portal',
            'content' => '<div class="template portal"><a href="/wiki/Portal:Islam"><i class="fas fa-star-and-crescent"></i> Islam portal</a></div>',
            'description' => 'Link to the Islam portal'
        ]
    ];
    
    foreach ($defaultTemplates as $template) {
        $title = 'Template:' . $template['name'];
        $slug = strtolower(str_replace([' ', ':'], ['-', '-'], $title));
        
        // Check if template already exists
        $stmt = $connection->prepare("SELECT id FROM wiki_pages WHERE title = ? AND namespace = 'Template'");
        $stmt->execute([$title]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $connection->query("
                INSERT INTO wiki_pages (title, namespace, content, slug, status, created_at, updated_at) 
                VALUES (?, 'Template', ?, ?, 'published', NOW(), NOW())
            ", [
                $title,
                $template['content'],
                $slug
            ]);
            
            echo "  ✅ Created template: {$template['name']}\n";
        } else {
            echo "  ⏭️  Template already exists: {$template['name']}\n";
        }
    }
}

/**
 * Rollback the template namespace support
 */
function rollback_template_namespace_support()
{
    $connection = new Connection();
    
    echo "Rolling back template namespace support...\n";
    
    try {
        // Remove indexes
        $connection->query("DROP INDEX IF EXISTS idx_wiki_pages_template_lookup");
        $connection->query("DROP INDEX IF EXISTS idx_wiki_pages_namespace_title");
        $connection->query("DROP INDEX IF EXISTS idx_wiki_pages_namespace");
        
        // Remove template pages
        $connection->query("DELETE FROM wiki_pages WHERE namespace = 'Template'");
        
        // Remove namespace column
        $connection->query("ALTER TABLE wiki_pages DROP COLUMN IF EXISTS namespace");
        
        echo "✅ Template namespace support rolled back successfully\n";
        
    } catch (Exception $e) {
        echo "❌ Error rolling back template namespace support: " . $e->getMessage() . "\n";
        throw $e;
    }
}

// Run migration if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    migrate_template_namespace_support();
} 