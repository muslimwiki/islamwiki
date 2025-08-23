<?php

/**
 * Content Migrator for Phase 5 Migration
 * 
 * This script converts WikiMarkup content to Enhanced Markdown
 * during Phase 5 of the Markdown Wiki Standardization project.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */

class ContentMigrator
{
    private array $migrationResults = [];
    private array $conversionRules = [];
    
    public function __construct()
    {
        $this->initializeConversionRules();
    }
    
    /**
     * Initialize conversion rules from WikiMarkup to Enhanced Markdown
     */
    private function initializeConversionRules(): void
    {
        $this->conversionRules = [
            // Headings: === Heading === -> # Heading
            'headings' => [
                'pattern' => '/^=+\s*([^=]+)\s*=+$/m',
                'replacement' => function($matches) {
                    $level = strlen($matches[0]) - strlen(trim($matches[0], '='));
                    $text = trim($matches[1]);
                    return str_repeat('#', $level) . ' ' . $text;
                }
            ],
            
            // Bold: '''text''' -> **text**
            'bold' => [
                'pattern' => '/\'\'\'([^\']+)\'\'\'/',
                'replacement' => '**$1**'
            ],
            
            // Italic: ''text'' -> *text*
            'italic' => [
                'pattern' => '/\'\'([^\']+)\'\'/',
                'replacement' => '*$1*'
            ],
            
            // Internal links: [[Page Name]] -> [[Page Name]]
            'internal_links' => [
                'pattern' => '/\[\[([^\]]+)\]\]/',
                'replacement' => '[[$1]]'
            ],
            
            // Internal links with display: [[Page|Display]] -> [[Page|Display]]
            'internal_links_display' => [
                'pattern' => '/\[\[([^|]+)\|([^\]]+)\]\]/',
                'replacement' => '[[$1|$2]]'
            ],
            
            // External links: [text](url) -> [text](url) (already Markdown)
            'external_links' => [
                'pattern' => '/\[([^\]]+)\]\(([^)]+)\)/',
                'replacement' => '[$1]($2)'
            ],
            
            // Categories: [[Category:Name]] -> [Category:Name]
            'categories' => [
                'pattern' => '/\[\[Category:([^\]]+)\]\]/',
                'replacement' => '[Category:$1]'
            ],
            
            // Templates: {{Template|params}} -> {{Template|params}} (already Enhanced Markdown)
            'templates' => [
                'pattern' => '/\{\{([^}]+)\}\}/',
                'replacement' => '{{$1}}'
            ],
            
            // References: <ref>content</ref> -> <ref>content</ref> (already Enhanced Markdown)
            'references' => [
                'pattern' => '/<ref[^>]*>.*?<\/ref>/s',
                'replacement' => function($matches) {
                    return $matches[0]; // Keep as is
                }
            ],
            
            // Tables: {|...|} -> Markdown table syntax
            'tables' => [
                'pattern' => '/^\{\|.*?\|\}/ms',
                'replacement' => function($matches) {
                    return $this->convertTableToMarkdown($matches[0]);
                }
            ],
            
            // Lists: * item -> - item
            'unordered_lists' => [
                'pattern' => '/^[\*]+\s+/m',
                'replacement' => function($matches) {
                    $level = strlen($matches[0]) - strlen(trim($matches[0], '*'));
                    return str_repeat('  ', $level - 1) . '- ';
                }
            ],
            
            // Lists: # item -> 1. item
            'ordered_lists' => [
                'pattern' => '/^[#]+\s+/m',
                'replacement' => function($matches) {
                    $level = strlen($matches[0]) - strlen(trim($matches[0], '#'));
                    return str_repeat('  ', $level - 1) . '1. ';
                }
            ],
            
            // Nowiki: <nowiki>content</nowiki> -> `content`
            'nowiki' => [
                'pattern' => '/<nowiki>(.*?)<\/nowiki>/s',
                'replacement' => '`$1`'
            ],
            
            // Horizontal rules: ---- -> ---
            'horizontal_rules' => [
                'pattern' => '/^----+$/m',
                'replacement' => '---'
            ]
        ];
    }
    
    /**
     * Convert WikiMarkup content to Enhanced Markdown
     */
    public function convertContent(string $content, string $contentType = 'wiki_page'): string
    {
        $originalContent = $content;
        $convertedContent = $content;
        
        echo "Converting $contentType content...\n";
        
        // Apply conversion rules
        foreach ($this->conversionRules as $ruleName => $rule) {
            if (is_callable($rule['replacement'])) {
                $convertedContent = preg_replace_callback(
                    $rule['pattern'],
                    $rule['replacement'],
                    $convertedContent
                );
            } else {
                $convertedContent = preg_replace(
                    $rule['pattern'],
                    $rule['replacement'],
                    $convertedContent
                );
            }
            
            // Check if any changes were made
            if ($convertedContent !== $content) {
                echo "  - Applied $ruleName conversion\n";
                $content = $convertedContent;
            }
        }
        
        // Post-processing cleanup
        $convertedContent = $this->postProcessContent($convertedContent);
        
        // Record migration result
        $this->recordMigrationResult($contentType, $originalContent, $convertedContent);
        
        return $convertedContent;
    }
    
    /**
     * Convert MediaWiki table syntax to Markdown table
     */
    private function convertTableToMarkdown(string $tableContent): string
    {
        $lines = explode("\n", $tableContent);
        $markdownTable = [];
        $headers = [];
        $rows = [];
        
        $inTable = false;
        $inHeader = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line === '{|') {
                $inTable = true;
                continue;
            }
            
            if ($line === '|}') {
                $inTable = false;
                break;
            }
            
            if (!$inTable) {
                continue;
            }
            
            if (strpos($line, '|+') === 0) {
                // Table caption
                $caption = trim(substr($line, 2));
                $markdownTable[] = "*Table: $caption*";
                continue;
            }
            
            if (strpos($line, '!') === 0) {
                // Header row
                $inHeader = true;
                $headers = [];
                $cells = explode('!!', $line);
                foreach ($cells as $cell) {
                    $cell = trim($cell, '!');
                    $headers[] = $cell;
                }
                continue;
            }
            
            if (strpos($line, '|') === 0) {
                // Data row
                $cells = explode('|', $line);
                array_shift($cells); // Remove first empty element
                
                if ($inHeader) {
                    // Add header row
                    $markdownTable[] = '| ' . implode(' | ', $headers) . ' |';
                    $markdownTable[] = '| ' . str_repeat('--- | ', count($headers)) . '--- |';
                    $inHeader = false;
                }
                
                // Add data row
                $markdownTable[] = '| ' . implode(' | ', $cells) . ' |';
            }
        }
        
        return implode("\n", $markdownTable);
    }
    
    /**
     * Post-process converted content
     */
    private function postProcessContent(string $content): string
    {
        // Remove excessive blank lines
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Fix list numbering for ordered lists
        $content = $this->fixOrderedListNumbering($content);
        
        // Ensure proper spacing around headings
        $content = preg_replace('/([^\n])\n(#+ )/', "$1\n\n$2", $content);
        
        return $content;
    }
    
    /**
     * Fix ordered list numbering
     */
    private function fixOrderedListNumbering(string $content): string
    {
        $lines = explode("\n", $content);
        $fixedLines = [];
        $listCounters = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(\s*)(\d+)\.\s/', $line, $matches)) {
                $indent = $matches[1];
                $level = strlen($indent) / 2 + 1;
                
                if (!isset($listCounters[$level])) {
                    $listCounters[$level] = 0;
                }
                $listCounters[$level]++;
                
                $fixedLines[] = $indent . $listCounters[$level] . '. ' . substr($line, strlen($matches[0]));
            } else {
                $fixedLines[] = $line;
            }
        }
        
        return implode("\n", $fixedLines);
    }
    
    /**
     * Record migration result
     */
    private function recordMigrationResult(string $contentType, string $original, string $converted): void
    {
        $this->migrationResults[] = [
            'type' => $contentType,
            'original_length' => strlen($original),
            'converted_length' => strlen($converted),
            'changes_made' => $original !== $converted,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get migration statistics
     */
    public function getMigrationStats(): array
    {
        $totalItems = count($this->migrationResults);
        $itemsWithChanges = count(array_filter($this->migrationResults, fn($r) => $r['changes_made']));
        $totalOriginalLength = array_sum(array_column($this->migrationResults, 'original_length'));
        $totalConvertedLength = array_sum(array_column($this->migrationResults, 'converted_length'));
        
        return [
            'total_items' => $totalItems,
            'items_with_changes' => $itemsWithChanges,
            'total_original_length' => $totalOriginalLength,
            'total_converted_length' => $totalConvertedLength,
            'conversion_rate' => $totalItems > 0 ? ($itemsWithChanges / $totalItems) * 100 : 0
        ];
    }
    
    /**
     * Generate migration report
     */
    public function generateMigrationReport(): void
    {
        $stats = $this->getMigrationStats();
        
        echo "\n=== Migration Report ===\n";
        echo "Total items processed: {$stats['total_items']}\n";
        echo "Items with changes: {$stats['items_with_changes']}\n";
        echo "Conversion rate: " . round($stats['conversion_rate'], 1) . "%\n";
        echo "Total original content length: {$stats['total_original_length']} characters\n";
        echo "Total converted content length: {$stats['total_converted_length']} characters\n";
        
        if ($stats['items_with_changes'] > 0) {
            echo "\nMigration completed successfully!\n";
            echo "All WikiMarkup content has been converted to Enhanced Markdown.\n";
        } else {
            echo "\nNo changes were needed.\n";
            echo "Content was already in the correct format.\n";
        }
    }
    
    /**
     * Save migration results to file
     */
    public function saveMigrationResults(): void
    {
        $filename = __DIR__ . '/migration-results.json';
        $data = [
            'migration_results' => $this->migrationResults,
            'statistics' => $this->getMigrationStats(),
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '0.0.3.0'
        ];
        
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        
        if (file_put_contents($filename, $jsonData)) {
            echo "\nMigration results saved to: $filename\n";
        } else {
            echo "\nWarning: Could not save migration results to file\n";
        }
    }
}

// Test the migrator with sample content
if (php_sapi_name() === 'cli') {
    echo "=== Content Migrator Test ===\n\n";
    
    $migrator = new ContentMigrator();
    
    // Test content samples
    $testContent = [
        'wiki_page' => "=== Welcome ===\n\nWelcome to '''IslamWiki'''!\n\nThis page contains:\n* [[Quran]] references\n* {{Infobox|title=Welcome}}\n* [Category:Main]\n\n----\n\n<nowiki>Some code here</nowiki>",
        'discussion' => "Great article about [[Islam]]! Check out {{Infobox|title=Islam}} for more info.",
        'user_profile' => "I study '''[[Islamic History]]''' and love {{Scholar|name=Ibn Khaldun}}"
    ];
    
    foreach ($testContent as $type => $content) {
        echo "Original $type content:\n";
        echo "```\n$content\n```\n\n";
        
        $converted = $migrator->convertContent($content, $type);
        
        echo "Converted $type content:\n";
        echo "```\n$converted\n```\n\n";
        echo "---\n\n";
    }
    
    // Generate report
    $migrator->generateMigrationReport();
    $migrator->saveMigrationResults();
    
    echo "\nContent migration test complete!\n";
    echo "Ready to proceed with actual content migration.\n";
} else {
    echo "This script should be run from the command line.\n";
} 