<?php
/**
 * Link Updater Script
 * 
 * Updates internal link formats and ensures consistency across migrated content
 * Part of Phase 5: Content Migration & Testing
 * 
 * @version 0.0.3.0
 * @package EnhancedMarkdown
 */

class LinkUpdater
{
    private array $linkPatterns = [];
    private array $updateResults = [];
    private string $outputDir;
    private array $linkMappings = [];

    public function __construct()
    {
        $this->outputDir = __DIR__ . '/updated-links';
        $this->initializeLinkPatterns();
        $this->ensureOutputDirectory();
    }

    /**
     * Initialize link patterns for detection and updating
     */
    private function initializeLinkPatterns(): void
    {
        $this->linkPatterns = [
            // Internal links: [[Page Name]] or [[Page|Display]]
            'internal_links' => [
                'pattern' => '/\[\[([^\]]+)\]\]/',
                'description' => 'Internal wiki links'
            ],
            // Old MediaWiki style links: [[Page Name|Display Text]]
            'old_internal_links' => [
                'pattern' => '/\[\[([^|]+)\|([^\]]+)\]\]/',
                'description' => 'Old MediaWiki internal links with display text'
            ],
            // External links: [URL] or [URL Text]
            'external_links' => [
                'pattern' => '/\[([^\]]+)\]\(([^)]+)\)/',
                'description' => 'Markdown external links'
            ],
            // Old MediaWiki external links: [URL Text]
            'old_external_links' => [
                'pattern' => '/\[([^\]]+)\]/',
                'description' => 'Old MediaWiki external links'
            ],
            // Category links: [Category:Name]
            'category_links' => [
                'pattern' => '/\[Category:([^\]]+)\]/',
                'description' => 'Category links'
            ],
            // Old MediaWiki categories: [[Category:Name]]
            'old_categories' => [
                'pattern' => '/\[\[Category:([^\]]+)\]\]/',
                'description' => 'Old MediaWiki category format'
            ],
            // File links: [[File:filename.jpg]]
            'file_links' => [
                'pattern' => '/\[\[File:([^\]]+)\]\]/',
                'description' => 'File links'
            ],
            // Image links: [[Image:filename.jpg]]
            'image_links' => [
                'pattern' => '/\[\[Image:([^\]]+)\]\]/',
                'description' => 'Image links'
            ],
            // Template links: {{Template}}
            'template_links' => [
                'pattern' => '/\{\{([^}]+)\}\}/',
                'description' => 'Template references'
            ],
            // Interwiki links: [[Wikipedia:Page]]
            'interwiki_links' => [
                'pattern' => '/\[\[([^:]+):([^\]]+)\]\]/',
                'description' => 'Interwiki links'
            ]
        ];
    }

    /**
     * Ensure output directory exists
     */
    private function ensureOutputDirectory(): void
    {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Update internal links in content
     */
    public function updateInternalLinks(string $content, string $contentType = 'wiki_page'): string
    {
        $originalContent = $content;
        $updatedContent = $content;
        $updates = [];

        echo "Updating internal links in $contentType content...\n";

        // Update old MediaWiki category format to new format
        $updatedContent = preg_replace_callback(
            '/\[\[Category:([^\]]+)\]\]/',
            function($matches) use (&$updates) {
                $updates['categories'] = ($updates['categories'] ?? 0) + 1;
                return '[Category:' . $matches[1] . ']';
            },
            $updatedContent
        );

        // Update old MediaWiki internal links with display text
        $updatedContent = preg_replace_callback(
            '/\[\[([^|]+)\|([^\]]+)\]\]/',
            function($matches) use (&$updates) {
                $updates['internal_links'] = ($updates['internal_links'] ?? 0) + 1;
                return '[[' . $matches[1] . '|' . $matches[2] . ']]';
            },
            $updatedContent
        );

        // Update file links to Markdown image syntax
        $updatedContent = preg_replace_callback(
            '/\[\[File:([^\]]+)\]\]/',
            function($matches) use (&$updates) {
                $updates['file_links'] = ($updates['file_links'] ?? 0) + 1;
                $filename = $matches[1];
                $altText = pathinfo($filename, PATHINFO_FILENAME);
                return '![' . $altText . '](' . $filename . ')';
            },
            $updatedContent
        );

        // Update image links to Markdown image syntax
        $updatedContent = preg_replace_callback(
            '/\[\[Image:([^\]]+)\]\]/',
            function($matches) use (&$updates) {
                $updates['image_links'] = ($updates['image_links'] ?? 0) + 1;
                $filename = $matches[1];
                $altText = pathinfo($filename, PATHINFO_FILENAME);
                return '![' . $altText . '](' . $filename . ')';
            },
            $updatedContent
        );

        // Update interwiki links to external links
        $updatedContent = preg_replace_callback(
            '/\[\[([^:]+):([^\]]+)\]\]/',
            function($matches) use (&$updates) {
                $updates['interwiki_links'] = ($updates['interwiki_links'] ?? 0) + 1;
                $wiki = $matches[1];
                $page = $matches[2];
                
                // Map common interwiki prefixes to URLs
                $wikiUrls = [
                    'wikipedia' => 'https://en.wikipedia.org/wiki/',
                    'wiktionary' => 'https://en.wiktionary.org/wiki/',
                    'wikibooks' => 'https://en.wikibooks.org/wiki/',
                    'wikiquote' => 'https://en.wikiquote.org/wiki/',
                    'wikisource' => 'https://en.wikisource.org/wiki/',
                    'wikimedia' => 'https://commons.wikimedia.org/wiki/'
                ];
                
                $baseUrl = $wikiUrls[strtolower($wiki)] ?? "https://$wiki.org/wiki/";
                return '[' . $page . '](' . $baseUrl . urlencode($page) . ')';
            },
            $updatedContent
        );

        // Record update result
        $this->recordUpdateResult($contentType, $originalContent, $updatedContent, $updates);

        return $updatedContent;
    }

    /**
     * Validate internal links for consistency
     */
    public function validateInternalLinks(string $content): array
    {
        $validation = [
            'total_links' => 0,
            'valid_links' => 0,
            'invalid_links' => 0,
            'issues' => []
        ];

        // Find all internal links
        preg_match_all('/\[\[([^\]]+)\]\]/', $content, $matches);

        foreach ($matches[1] as $link) {
            $validation['total_links']++;
            
            // Check for common issues
            $issues = [];
            
            // Check for empty links
            if (trim($link) === '') {
                $issues[] = 'Empty link';
                $validation['invalid_links']++;
            }
            // Check for links with only whitespace
            elseif (preg_match('/^\s+$/', $link)) {
                $issues[] = 'Link contains only whitespace';
                $validation['invalid_links']++;
            }
            // Check for links with invalid characters
            elseif (preg_match('/[<>"|{}]/', $link)) {
                $issues[] = 'Link contains invalid characters';
                $validation['invalid_links']++;
            }
            // Check for extremely long links
            elseif (strlen($link) > 255) {
                $issues[] = 'Link is too long';
                $validation['invalid_links']++;
            }
            else {
                $validation['valid_links']++;
            }

            if (!empty($issues)) {
                $validation['issues'][] = [
                    'link' => $link,
                    'issues' => $issues
                ];
            }
        }

        return $validation;
    }

    /**
     * Generate link statistics
     */
    public function generateLinkStatistics(string $content): array
    {
        $stats = [];

        foreach ($this->linkPatterns as $type => $pattern) {
            $count = preg_match_all($pattern['pattern'], $content);
            $stats[$type] = [
                'count' => $count,
                'description' => $pattern['description']
            ];
        }

        return $stats;
    }

    /**
     * Create link mapping for navigation
     */
    public function createLinkMapping(string $content): array
    {
        $mapping = [];
        
        // Extract internal links
        preg_match_all('/\[\[([^\]]+)\]\]/', $content, $matches);
        
        foreach ($matches[1] as $link) {
            $parts = explode('|', $link, 2);
            $pageName = trim($parts[0]);
            $displayText = isset($parts[1]) ? trim($parts[1]) : $pageName;
            
            if (!isset($mapping[$pageName])) {
                $mapping[$pageName] = [
                    'display_text' => $displayText,
                    'occurrences' => 0,
                    'first_seen' => null
                ];
            }
            
            $mapping[$pageName]['occurrences']++;
            
            if ($mapping[$pageName]['first_seen'] === null) {
                $mapping[$pageName]['first_seen'] = date('Y-m-d H:i:s');
            }
        }

        return $mapping;
    }

    /**
     * Update links in a file
     */
    public function updateLinksInFile(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            echo "File not found: $filepath\n";
            return false;
        }

        $content = file_get_contents($filepath);
        if ($content === false) {
            echo "Could not read file: $filepath\n";
            return false;
        }

        $filename = basename($filepath);
        $updatedContent = $this->updateInternalLinks($content, $filename);

        // Save updated content
        $outputFile = $this->outputDir . '/' . $filename;
        if (file_put_contents($outputFile, $updatedContent) === false) {
            echo "Could not write updated file: $outputFile\n";
            return false;
        }

        echo "Updated links in $filename -> $outputFile\n";
        return true;
    }

    /**
     * Update links in a directory
     */
    public function updateLinksInDirectory(string $sourceDir, array $extensions = ['md', 'txt', 'html']): array
    {
        $results = [];
        
        if (!is_dir($sourceDir)) {
            echo "Source directory does not exist: $sourceDir\n";
            return $results;
        }

        $pattern = $sourceDir . '/*.{' . implode(',', $extensions) . '}';
        $files = glob($pattern, GLOB_BRACE);
        
        echo "Found " . count($files) . " files to process\n";

        foreach ($files as $file) {
            $filename = basename($file);
            $success = $this->updateLinksInFile($file);
            
            $results[$filename] = [
                'source_file' => $file,
                'output_file' => $this->outputDir . '/' . $filename,
                'success' => $success
            ];
        }

        return $results;
    }

    /**
     * Record update result
     */
    private function recordUpdateResult(string $contentType, string $originalContent, string $updatedContent, array $updates): void
    {
        $this->updateResults[] = [
            'content_type' => $contentType,
            'original_content' => $originalContent,
            'updated_content' => $updatedContent,
            'updates_applied' => $updates,
            'original_size' => strlen($originalContent),
            'updated_size' => strlen($updatedContent),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate update report
     */
    public function generateReport(): array
    {
        $totalItems = count($this->updateResults);
        $totalUpdates = 0;
        $totalOriginalSize = 0;
        $totalUpdatedSize = 0;

        foreach ($this->updateResults as $result) {
            $totalUpdates += array_sum($result['updates_applied']);
            $totalOriginalSize += $result['original_size'];
            $totalUpdatedSize += $result['updated_size'];
        }

        return [
            'total_items' => $totalItems,
            'total_updates' => $totalUpdates,
            'total_original_size' => $totalOriginalSize,
            'total_updated_size' => $totalUpdatedSize,
            'update_rate' => $totalItems > 0 ? ($totalUpdates / $totalItems) : 0,
            'size_change_percentage' => $totalOriginalSize > 0 ? (($totalUpdatedSize - $totalOriginalSize) / $totalOriginalSize) * 100 : 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Save update results to file
     */
    public function saveResults(string $filename = 'link-update-results.json'): void
    {
        $filepath = $this->outputDir . '/' . $filename;
        $data = [
            'summary' => $this->generateReport(),
            'detailed_results' => $this->updateResults
        ];
        
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
        echo "Update results saved to: $filepath\n";
    }

    /**
     * Get link patterns
     */
    public function getLinkPatterns(): array
    {
        return $this->linkPatterns;
    }

    /**
     * Add custom link pattern
     */
    public function addLinkPattern(string $name, string $pattern, string $description): void
    {
        $this->linkPatterns[$name] = [
            'pattern' => $pattern,
            'description' => $description
        ];
    }
}

// Test the link updater
if (php_sapi_name() === 'cli') {
    echo "=== Link Updater Test ===\n\n";

    $updater = new LinkUpdater();

    // Test content with various link formats
    $testContent = <<<CONTENT
This is a test page with various link formats:

## Internal Links
- [[Quran]] - Simple internal link
- [[Hadith|Islamic Traditions]] - Internal link with display text
- [[Category:Islamic Studies]] - Old MediaWiki category format

## File and Image Links
- [[File:quran.jpg]] - File link
- [[Image:hadith.jpg]] - Image link

## External and Interwiki Links
- [External Link](https://example.com) - Markdown external link
- [[Wikipedia:Islam]] - Interwiki link
- [[Wiktionary:Quran]] - Another interwiki link

## Templates
- {{Infobox|title=Test}} - Template reference
- {{Quran|surah=1|ayah=1-7}} - Islamic template

## Mixed Content
This page contains a mix of [[old]] and [[new|formats]] for [[links]].
CONTENT;

    echo "Original content:\n$testContent\n\n";
    echo "---\n\n";

    // Update links
    $updatedContent = $updater->updateInternalLinks($testContent, 'test_content');

    echo "Updated content:\n$updatedContent\n\n";
    echo "---\n\n";

    // Generate link statistics
    $stats = $updater->generateLinkStatistics($testContent);
    echo "=== Link Statistics ===\n";
    foreach ($stats as $type => $data) {
        echo "$type: {$data['count']} - {$data['description']}\n";
    }
    echo "\n";

    // Validate internal links
    $validation = $updater->validateInternalLinks($updatedContent);
    echo "=== Link Validation ===\n";
    echo "Total links: {$validation['total_links']}\n";
    echo "Valid links: {$validation['valid_links']}\n";
    echo "Invalid links: {$validation['invalid_links']}\n";
    
    if (!empty($validation['issues'])) {
        echo "\nIssues found:\n";
        foreach ($validation['issues'] as $issue) {
            echo "  - Link '{$issue['link']}': " . implode(', ', $issue['issues']) . "\n";
        }
    }
    echo "\n";

    // Create link mapping
    $mapping = $updater->createLinkMapping($updatedContent);
    echo "=== Link Mapping ===\n";
    foreach ($mapping as $pageName => $data) {
        echo "  - $pageName: {$data['occurrences']} occurrences\n";
    }
    echo "\n";

    // Generate and display report
    $report = $updater->generateReport();
    echo "=== Update Report ===\n";
    echo "Total items processed: " . $report['total_items'] . "\n";
    echo "Total updates applied: " . $report['total_updates'] . "\n";
    echo "Update rate: " . number_format($report['update_rate'], 1) . " per item\n";
    echo "Size change: " . number_format($report['size_change_percentage'], 1) . "%\n";
    echo "Timestamp: " . $report['timestamp'] . "\n\n";

    // Save results
    $updater->saveResults();

    echo "Link update test complete!\n";
    echo "Ready to proceed with actual link updates.\n";
} 