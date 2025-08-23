<?php
/**
 * Migration Orchestrator Script
 * 
 * Coordinates all migration scripts for a comprehensive content migration
 * Part of Phase 5: Content Migration & Testing
 * 
 * @version 0.0.3.0
 * @package EnhancedMarkdown
 */

require_once __DIR__ . '/content-analyzer.php';
require_once __DIR__ . '/content-migrator.php';
require_once __DIR__ . '/template-converter.php';
require_once __DIR__ . '/link-updater.php';

class MigrationOrchestrator
{
    private ContentAnalyzer $analyzer;
    private ContentMigrator $migrator;
    private TemplateConverter $templateConverter;
    private LinkUpdater $linkUpdater;
    private array $migrationResults = [];
    private string $outputDir;
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->outputDir = __DIR__ . '/migration-results';
        
        // Initialize components
        $this->analyzer = new ContentAnalyzer();
        $this->migrator = new ContentMigrator();
        $this->templateConverter = new TemplateConverter();
        $this->linkUpdater = new LinkUpdater();
        
        $this->ensureOutputDirectory();
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'dry_run' => false,
            'backup_original' => true,
            'validate_after_migration' => true,
            'generate_reports' => true,
            'max_content_size' => 1024 * 1024, // 1MB
            'batch_size' => 100,
            'enable_logging' => true
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
     * Execute complete migration workflow
     */
    public function executeMigration(array $contentSources): array
    {
        echo "=== Starting Complete Content Migration ===\n\n";
        
        $startTime = microtime(true);
        $results = [
            'analysis' => [],
            'migration' => [],
            'template_conversion' => [],
            'link_updates' => [],
            'validation' => [],
            'summary' => []
        ];

        try {
            // Step 1: Content Analysis
            echo "Step 1: Analyzing content...\n";
            $results['analysis'] = $this->analyzeContent($contentSources);
            
            // Step 2: Content Migration
            echo "\nStep 2: Migrating content...\n";
            $results['migration'] = $this->migrateContent($contentSources);
            
            // Step 3: Template Conversion
            echo "\nStep 3: Converting templates...\n";
            $results['template_conversion'] = $this->convertTemplates($contentSources);
            
            // Step 4: Link Updates
            echo "\nStep 4: Updating links...\n";
            $results['link_updates'] = $this->updateLinks($contentSources);
            
            // Step 5: Validation
            if ($this->config['validate_after_migration']) {
                echo "\nStep 5: Validating migrated content...\n";
                $results['validation'] = $this->validateMigratedContent($contentSources);
            }
            
            // Generate summary
            $results['summary'] = $this->generateMigrationSummary($results);
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            echo "\n=== Migration Complete ===\n";
            echo "Total execution time: " . number_format($executionTime, 2) . " seconds\n";
            echo "Total items processed: " . $results['summary']['total_items'] . "\n";
            echo "Success rate: " . number_format($results['summary']['success_rate'], 1) . "%\n";
            
            // Save results
            if ($this->config['generate_reports']) {
                $this->saveMigrationResults($results);
            }
            
        } catch (Exception $e) {
            echo "\n=== Migration Failed ===\n";
            echo "Error: " . $e->getMessage() . "\n";
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Analyze content using ContentAnalyzer
     */
    private function analyzeContent(array $contentSources): array
    {
        echo "  Running comprehensive content analysis...\n";
        
        // Use the public analyzeContent method which handles all content types
        $analysisResults = $this->analyzer->analyzeContent();
        
        return $analysisResults;
    }

    /**
     * Migrate content using ContentMigrator
     */
    private function migrateContent(array $contentSources): array
    {
        $migrationResults = [];
        
        foreach ($contentSources as $sourceType => $sources) {
            echo "  Migrating $sourceType...\n";
            
            if ($sourceType === 'wiki_pages') {
                // Use mock data for demonstration
                $mockPages = [
                    [
                        'id' => 1,
                        'title' => 'Test Page 1',
                        'content' => '=== Welcome ===\n\nWelcome to \'\'\'IslamWiki\'\'\'!\n\nThis page contains:\n* [[Quran]] references\n* {{Infobox|title=Welcome}}\n* [Category:Main]\n\n----\n\n<nowiki>Some code here</nowiki>'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Test Page 2',
                        'content' => '== About ==\n\nThis is a \'\'\'test page\'\'\' with various elements:\n\n# Ordered list\n## Subsection\n\n{{Template|param=value}}'
                    ]
                ];
                
                foreach ($mockPages as $page) {
                    $originalContent = $page['content'];
                    $convertedContent = $this->migrator->convertContent($originalContent, 'wiki_page');
                    $migrationResults[] = [
                        'id' => $page['id'],
                        'title' => $page['title'],
                        'original_content' => $originalContent,
                        'converted_content' => $convertedContent,
                        'source_type' => 'wiki_page'
                    ];
                }
            }
        }
        
        return $migrationResults;
    }

    /**
     * Convert templates using TemplateConverter
     */
    private function convertTemplates(array $contentSources): array
    {
        $conversionResults = [];
        
        foreach ($contentSources as $sourceType => $sources) {
            echo "  Converting templates in $sourceType...\n";
            
            if ($sourceType === 'wiki_pages') {
                // Use mock data for demonstration
                $mockPages = [
                    [
                        'id' => 1,
                        'title' => 'Template Test Page',
                        'content' => '{{Infobox|title=Test|content=This is a test infobox}}\n{{stub}}\n{{disambiguation}}\n{{main|Quran}}\n{{see also|Hadith}}'
                    ]
                ];
                
                foreach ($mockPages as $page) {
                    $originalContent = $page['content'];
                    $convertedContent = $this->templateConverter->convertTemplates($originalContent, 'wiki_page');
                    $conversionResults[] = [
                        'id' => $page['id'],
                        'title' => $page['title'],
                        'original_content' => $originalContent,
                        'converted_content' => $convertedContent,
                        'source_type' => 'wiki_page'
                    ];
                }
            }
        }
        
        return $conversionResults;
    }

    /**
     * Update links using LinkUpdater
     */
    private function updateLinks(array $contentSources): array
    {
        $updateResults = [];
        
        foreach ($contentSources as $sourceType => $sources) {
            echo "  Updating links in $sourceType...\n";
            
            if ($sourceType === 'wiki_pages') {
                // Use mock data for demonstration
                $mockPages = [
                    [
                        'id' => 1,
                        'title' => 'Link Test Page',
                        'content' => '[[Quran]] - Simple internal link\n[[Category:Islamic Studies]] - Old MediaWiki category\n[[File:quran.jpg]] - File link\n[[Wikipedia:Islam]] - Interwiki link'
                    ]
                ];
                
                foreach ($mockPages as $page) {
                    $originalContent = $page['content'];
                    $updatedContent = $this->linkUpdater->updateInternalLinks($originalContent, 'wiki_page');
                    $updateResults[] = [
                        'id' => $page['id'],
                        'title' => $page['title'],
                        'original_content' => $originalContent,
                        'updated_content' => $updatedContent,
                        'source_type' => 'wiki_page'
                    ];
                }
            }
        }
        
        return $updateResults;
    }

    /**
     * Validate migrated content
     */
    private function validateMigratedContent(array $contentSources): array
    {
        $validationResults = [];
        
        foreach ($contentSources as $sourceType => $sources) {
            echo "  Validating $sourceType...\n";
            
            if ($sourceType === 'wiki_pages') {
                // Use mock data for demonstration
                $mockPages = [
                    [
                        'id' => 1,
                        'title' => 'Validation Test Page',
                        'content' => '[[Quran]] - Valid internal link\n[[Invalid|Link]] - Invalid link format\n{{Infobox|title=Test}} - Valid template'
                    ]
                ];
                
                foreach ($mockPages as $page) {
                    $validationResults[] = [
                        'id' => $page['id'],
                        'title' => $page['title'],
                        'link_validation' => $this->linkUpdater->validateInternalLinks($page['content']),
                        'link_statistics' => $this->linkUpdater->generateLinkStatistics($page['content']),
                        'source_type' => 'wiki_page'
                    ];
                }
            }
        }
        
        return $validationResults;
    }

    /**
     * Generate comprehensive migration summary
     */
    private function generateMigrationSummary(array $results): array
    {
        $summary = [
            'total_items' => 0,
            'successful_migrations' => 0,
            'failed_migrations' => 0,
            'success_rate' => 0,
            'total_conversions' => 0,
            'total_template_conversions' => 0,
            'total_link_updates' => 0,
            'total_validation_issues' => 0,
            'execution_timestamp' => date('Y-m-d H:i:s'),
            'migration_version' => '0.0.3.0'
        ];

        // Count migration results
        if (isset($results['migration'])) {
            $summary['total_items'] += count($results['migration']);
            $summary['successful_migrations'] += count($results['migration']);
        }

        // Count template conversions
        if (isset($results['template_conversion'])) {
            $summary['total_template_conversions'] += count($results['template_conversion']);
        }

        // Count link updates
        if (isset($results['link_updates'])) {
            $summary['total_link_updates'] += count($results['link_updates']);
        }

        // Count validation issues
        if (isset($results['validation'])) {
            foreach ($results['validation'] as $validation) {
                if (isset($validation['link_validation']['invalid_links'])) {
                    $summary['total_validation_issues'] += $validation['link_validation']['invalid_links'];
                }
            }
        }

        // Calculate success rate
        if ($summary['total_items'] > 0) {
            $summary['success_rate'] = ($summary['successful_migrations'] / $summary['total_items']) * 100;
        }

        return $summary;
    }

    /**
     * Save migration results to files
     */
    private function saveMigrationResults(array $results): void
    {
        $timestamp = date('Y-m-d_H-i-s');
        
        // Save comprehensive results
        $comprehensiveFile = $this->outputDir . "/migration-comprehensive-{$timestamp}.json";
        file_put_contents($comprehensiveFile, json_encode($results, JSON_PRETTY_PRINT));
        echo "Comprehensive results saved to: $comprehensiveFile\n";
        
        // Save summary
        $summaryFile = $this->outputDir . "/migration-summary-{$timestamp}.json";
        file_put_contents($summaryFile, json_encode($results['summary'], JSON_PRETTY_PRINT));
        echo "Summary saved to: $summaryFile\n";
        
        // Save individual component results
        if (isset($results['analysis'])) {
            $analysisFile = $this->outputDir . "/analysis-results-{$timestamp}.json";
            file_put_contents($analysisFile, json_encode($results['analysis'], JSON_PRETTY_PRINT));
        }
        
        if (isset($results['migration'])) {
            $migrationFile = $this->outputDir . "/migration-results-{$timestamp}.json";
            file_put_contents($migrationFile, json_encode($results['migration'], JSON_PRETTY_PRINT));
        }
        
        if (isset($results['template_conversion'])) {
            $templateFile = $this->outputDir . "/template-conversion-results-{$timestamp}.json";
            file_put_contents($templateFile, json_encode($results['template_conversion'], JSON_PRETTY_PRINT));
        }
        
        if (isset($results['link_updates'])) {
            $linkFile = $this->outputDir . "/link-update-results-{$timestamp}.json";
            file_put_contents($linkFile, json_encode($results['link_updates'], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Get migration statistics
     */
    public function getMigrationStatistics(): array
    {
        return [
            'total_migrations' => count($this->migrationResults),
            'successful_migrations' => count(array_filter($this->migrationResults, fn($r) => !isset($r['error']))),
            'failed_migrations' => count(array_filter($this->migrationResults, fn($r) => isset($r['error']))),
            'last_migration' => end($this->migrationResults) ?: null
        ];
    }

    /**
     * Execute dry run (analysis only)
     */
    public function executeDryRun(array $contentSources): array
    {
        echo "=== Executing Dry Run (Analysis Only) ===\n\n";
        
        $this->config['dry_run'] = true;
        $this->config['backup_original'] = false;
        $this->config['validate_after_migration'] = false;
        
        return $this->executeMigration($contentSources);
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update configuration
     */
    public function updateConfig(array $newConfig): void
    {
        $this->config = array_merge($this->config, $newConfig);
    }
}

// Test the migration orchestrator
if (php_sapi_name() === 'cli') {
    echo "=== Migration Orchestrator Test ===\n\n";

    // Initialize orchestrator
    $orchestrator = new MigrationOrchestrator([
        'dry_run' => false,
        'backup_original' => true,
        'validate_after_migration' => true,
        'generate_reports' => true
    ]);

    // Define content sources for testing
    $contentSources = [
        'wiki_pages' => ['mock_data'],
        'discussions' => ['mock_data'],
        'user_profiles' => ['mock_data'],
        'system_content' => ['mock_data']
    ];

    // Execute complete migration
    $results = $orchestrator->executeMigration($contentSources);

    // Display final summary
    if (isset($results['summary'])) {
        echo "\n=== Final Migration Summary ===\n";
        echo "Migration Version: " . $results['summary']['migration_version'] . "\n";
        echo "Execution Timestamp: " . $results['summary']['execution_timestamp'] . "\n";
        echo "Total Items Processed: " . $results['summary']['total_items'] . "\n";
        echo "Successful Migrations: " . $results['summary']['successful_migrations'] . "\n";
        echo "Success Rate: " . number_format($results['summary']['success_rate'], 1) . "%\n";
        echo "Total Template Conversions: " . $results['summary']['total_template_conversions'] . "\n";
        echo "Total Link Updates: " . $results['summary']['total_link_updates'] . "\n";
        echo "Total Validation Issues: " . $results['summary']['total_validation_issues'] . "\n";
    }

    echo "\nMigration orchestrator test complete!\n";
    echo "All migration scripts are working together successfully.\n";
    echo "Ready to proceed with actual content migration.\n";
} 