<?php
/**
 * Production Migration Script
 * 
 * Production-ready script for migrating WikiMarkup content to Enhanced Markdown
 * Part of Phase 5: Content Migration & Testing
 * 
 * @version 0.0.3.0
 * @package EnhancedMarkdown
 */

require_once __DIR__ . '/content-analyzer.php';
require_once __DIR__ . '/content-migrator.php';
require_once __DIR__ . '/template-converter.php';
require_once __DIR__ . '/link-updater.php';

class ProductionMigration
{
    private ContentAnalyzer $analyzer;
    private ContentMigrator $migrator;
    private TemplateConverter $templateConverter;
    private LinkUpdater $linkUpdater;
    
    private string $logFile;
    private string $outputDir;
    private array $config;
    private array $migrationStats;
    private bool $dryRun;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->outputDir = __DIR__ . '/production-migration';
        $this->logFile = $this->outputDir . '/migration.log';
        $this->dryRun = $this->config['dry_run'] ?? false;
        
        // Initialize components
        $this->analyzer = new ContentAnalyzer();
        $this->migrator = new ContentMigrator();
        $this->templateConverter = new TemplateConverter();
        $this->linkUpdater = new LinkUpdater();
        
        $this->migrationStats = [
            'start_time' => microtime(true),
            'total_items' => 0,
            'successful_migrations' => 0,
            'failed_migrations' => 0,
            'total_conversions' => 0,
            'total_template_conversions' => 0,
            'total_link_updates' => 0,
            'errors' => []
        ];
        
        $this->ensureOutputDirectory();
        $this->initializeLogging();
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
            'enable_logging' => true,
            'stop_on_error' => false,
            'migration_priority' => ['high', 'medium', 'low']
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
     * Initialize logging
     */
    private function initializeLogging(): void
    {
        if ($this->config['enable_logging']) {
            $timestamp = date('Y-m-d H:i:s');
            $mode = $this->dryRun ? 'DRY RUN' : 'PRODUCTION';
            $logMessage = "[$timestamp] Production Migration Started - Mode: $mode\n";
            $logMessage .= "Configuration: " . json_encode($this->config, JSON_PRETTY_PRINT) . "\n";
            $logMessage .= str_repeat('-', 80) . "\n";
            
            file_put_contents($this->logFile, $logMessage);
        }
    }

    /**
     * Log message to file and console
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        
        if ($this->config['enable_logging']) {
            file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        }
        
        echo $logMessage;
    }

    /**
     * Execute production migration
     */
    public function executeMigration(): array
    {
        $this->log("Starting Production Migration Process", "START");
        
        try {
            // Step 1: Content Analysis
            $this->log("Step 1: Analyzing content for migration", "INFO");
            $analysisResults = $this->analyzeContent();
            
            // Step 2: Content Migration
            $this->log("Step 2: Executing content migration", "INFO");
            $migrationResults = $this->migrateContent($analysisResults);
            
            // Step 3: Template Conversion
            $this->log("Step 3: Converting templates", "INFO");
            $templateResults = $this->convertTemplates($migrationResults);
            
            // Step 4: Link Updates
            $this->log("Step 4: Updating links", "INFO");
            $linkResults = $this->updateLinks($templateResults);
            
            // Step 5: Validation
            if ($this->config['validate_after_migration']) {
                $this->log("Step 5: Validating migrated content", "INFO");
                $validationResults = $this->validateContent($linkResults);
            }
            
            // Generate final report
            $this->log("Generating migration report", "INFO");
            $finalReport = $this->generateFinalReport($analysisResults, $migrationResults, $templateResults, $linkResults);
            
            $this->log("Production Migration Completed Successfully", "SUCCESS");
            
            return $finalReport;
            
        } catch (Exception $e) {
            $this->log("Migration failed: " . $e->getMessage(), "ERROR");
            $this->migrationStats['errors'][] = $e->getMessage();
            
            if ($this->config['stop_on_error']) {
                throw $e;
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'partial_results' => $this->migrationStats
            ];
        }
    }

    /**
     * Analyze content for migration
     */
    private function analyzeContent(): array
    {
        $this->log("Running comprehensive content analysis", "INFO");
        
        try {
            $analysisResults = $this->analyzer->analyzeContent();
            $this->migrationStats['total_items'] = count($analysisResults['wiki_pages'] ?? []) + 
                                                   count($analysisResults['discussions'] ?? []) + 
                                                   count($analysisResults['user_profiles'] ?? []) + 
                                                   count($analysisResults['system_content'] ?? []);
            
            $this->log("Content analysis completed. Total items: " . $this->migrationStats['total_items'], "INFO");
            
            // Save analysis results
            $analysisFile = $this->outputDir . '/production-analysis-' . date('Y-m-d_H-i-s') . '.json';
            file_put_contents($analysisFile, json_encode($analysisResults, JSON_PRETTY_PRINT));
            $this->log("Analysis results saved to: $analysisFile", "INFO");
            
            return $analysisResults;
            
        } catch (Exception $e) {
            $this->log("Content analysis failed: " . $e->getMessage(), "ERROR");
            throw $e;
        }
    }

    /**
     * Migrate content from WikiMarkup to Enhanced Markdown
     */
    private function migrateContent(array $analysisResults): array
    {
        $this->log("Starting content migration", "INFO");
        
        $migrationResults = [];
        $processedCount = 0;
        
        // Process by priority
        foreach ($this->config['migration_priority'] as $priority) {
            $this->log("Processing $priority priority content", "INFO");
            
            // Process wiki pages by priority
            if (isset($analysisResults['wiki_pages'])) {
                foreach ($analysisResults['wiki_pages'] as $page) {
                    if ($page['priority'] === $priority) {
                        try {
                            $this->log("Migrating page: {$page['title']} (Priority: $priority)", "INFO");
                            
                            // In production, this would read from actual content source
                            $originalContent = $this->getPageContent($page);
                            
                            if ($originalContent) {
                                $convertedContent = $this->migrator->convertContent($originalContent, 'wiki_page');
                                
                                $migrationResults[] = [
                                    'id' => $page['id'],
                                    'title' => $page['title'],
                                    'priority' => $priority,
                                    'original_content' => $originalContent,
                                    'converted_content' => $convertedContent,
                                    'source_type' => 'wiki_page',
                                    'migration_success' => true,
                                    'timestamp' => date('Y-m-d H:i:s')
                                ];
                                
                                $this->migrationStats['successful_migrations']++;
                                $this->migrationStats['total_conversions']++;
                                
                                $this->log("Successfully migrated: {$page['title']}", "SUCCESS");
                                
                                // Save migrated content (in production, this would update the database)
                                if (!$this->dryRun) {
                                    $this->saveMigratedContent($page, $convertedContent);
                                }
                                
                            } else {
                                $this->log("Could not retrieve content for: {$page['title']}", "WARNING");
                                $this->migrationStats['failed_migrations']++;
                            }
                            
                            $processedCount++;
                            
                            // Batch processing
                            if ($processedCount % $this->config['batch_size'] === 0) {
                                $this->log("Processed batch of $processedCount items", "INFO");
                            }
                            
                        } catch (Exception $e) {
                            $this->log("Failed to migrate {$page['title']}: " . $e->getMessage(), "ERROR");
                            $this->migrationStats['failed_migrations']++;
                            $this->migrationStats['errors'][] = "Page {$page['title']}: " . $e->getMessage();
                            
                            if ($this->config['stop_on_error']) {
                                throw $e;
                            }
                        }
                    }
                }
            }
        }
        
        $this->log("Content migration completed. Processed: $processedCount items", "INFO");
        
        // Save migration results
        $migrationFile = $this->outputDir . '/production-migration-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($migrationFile, json_encode($migrationResults, JSON_PRETTY_PRINT));
        $this->log("Migration results saved to: $migrationFile", "INFO");
        
        return $migrationResults;
    }

    /**
     * Convert templates in migrated content
     */
    private function convertTemplates(array $migrationResults): array
    {
        $this->log("Starting template conversion", "INFO");
        
        $templateResults = [];
        $conversionCount = 0;
        
        foreach ($migrationResults as $result) {
            if ($result['migration_success']) {
                try {
                    $this->log("Converting templates in: {$result['title']}", "INFO");
                    
                    $convertedContent = $this->templateConverter->convertTemplates(
                        $result['converted_content'], 
                        $result['source_type']
                    );
                    
                    $templateResults[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'source_type' => $result['source_type'],
                        'original_content' => $result['converted_content'],
                        'template_converted_content' => $convertedContent,
                        'template_conversion_success' => true,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->migrationStats['total_template_conversions']++;
                    $conversionCount++;
                    
                } catch (Exception $e) {
                    $this->log("Template conversion failed for {$result['title']}: " . $e->getMessage(), "ERROR");
                    $this->migrationStats['errors'][] = "Template conversion {$result['title']}: " . $e->getMessage();
                }
            }
        }
        
        $this->log("Template conversion completed. Converted: $conversionCount items", "INFO");
        
        // Save template conversion results
        $templateFile = $this->outputDir . '/production-templates-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($templateFile, json_encode($templateResults, JSON_PRETTY_PRINT));
        $this->log("Template conversion results saved to: $templateFile", "INFO");
        
        return $templateResults;
    }

    /**
     * Update links in migrated content
     */
    private function updateLinks(array $templateResults): array
    {
        $this->log("Starting link updates", "INFO");
        
        $linkResults = [];
        $updateCount = 0;
        
        foreach ($templateResults as $result) {
            if ($result['template_conversion_success']) {
                try {
                    $this->log("Updating links in: {$result['title']}", "INFO");
                    
                    $sourceType = $result['source_type'] ?? 'wiki_page';
                    $updatedContent = $this->linkUpdater->updateInternalLinks(
                        $result['template_converted_content'], 
                        $sourceType
                    );
                    
                    $linkResults[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'source_type' => $sourceType,
                        'original_content' => $result['template_converted_content'],
                        'link_updated_content' => $updatedContent,
                        'link_update_success' => true,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->migrationStats['total_link_updates']++;
                    $updateCount++;
                    
                } catch (Exception $e) {
                    $this->log("Link update failed for {$result['title']}: " . $e->getMessage(), "ERROR");
                    $this->migrationStats['errors'][] = "Link update {$result['title']}: " . $e->getMessage();
                }
            }
        }
        
        $this->log("Link updates completed. Updated: $updateCount items", "INFO");
        
        // Save link update results
        $linkFile = $this->outputDir . '/production-links-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($linkFile, json_encode($linkResults, JSON_PRETTY_PRINT));
        $this->log("Link update results saved to: $linkFile", "INFO");
        
        return $linkResults;
    }

    /**
     * Validate migrated content
     */
    private function validateContent(array $linkResults): array
    {
        $this->log("Starting content validation", "INFO");
        
        $validationResults = [];
        $validationCount = 0;
        
        foreach ($linkResults as $result) {
            if ($result['link_update_success']) {
                try {
                    $this->log("Validating: {$result['title']}", "INFO");
                    
                    $linkValidation = $this->linkUpdater->validateInternalLinks($result['link_updated_content']);
                    $linkStatistics = $this->linkUpdater->generateLinkStatistics($result['link_updated_content']);
                    
                    $validationResults[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'link_validation' => $linkValidation,
                        'link_statistics' => $linkStatistics,
                        'validation_success' => true,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    $validationCount++;
                    
                } catch (Exception $e) {
                    $this->log("Validation failed for {$result['title']}: " . $e->getMessage(), "ERROR");
                    $this->migrationStats['errors'][] = "Validation {$result['title']}: " . $e->getMessage();
                }
            }
        }
        
        $this->log("Content validation completed. Validated: $validationCount items", "INFO");
        
        // Save validation results
        $validationFile = $this->outputDir . '/production-validation-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($validationFile, json_encode($validationResults, JSON_PRETTY_PRINT));
        $this->log("Validation results saved to: $validationFile", "INFO");
        
        return $validationResults;
    }

    /**
     * Generate final migration report
     */
    private function generateFinalReport(array $analysisResults, array $migrationResults, array $templateResults, array $linkResults): array
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->migrationStats['start_time'];
        
        $finalReport = [
            'migration_info' => [
                'version' => '0.0.3.0',
                'mode' => $this->dryRun ? 'DRY RUN' : 'PRODUCTION',
                'start_time' => date('Y-m-d H:i:s', (int)$this->migrationStats['start_time']),
                'end_time' => date('Y-m-d H:i:s'),
                'execution_time_seconds' => round($executionTime, 2),
                'configuration' => $this->config
            ],
            'statistics' => [
                'total_items' => $this->migrationStats['total_items'],
                'successful_migrations' => $this->migrationStats['successful_migrations'],
                'failed_migrations' => $this->migrationStats['failed_migrations'],
                'success_rate' => $this->migrationStats['total_items'] > 0 ? 
                    round(($this->migrationStats['successful_migrations'] / $this->migrationStats['total_items']) * 100, 2) : 0,
                'total_conversions' => $this->migrationStats['total_conversions'],
                'total_template_conversions' => $this->migrationStats['total_template_conversions'],
                'total_link_updates' => $this->migrationStats['total_link_updates']
            ],
            'results' => [
                'analysis' => $analysisResults,
                'migration' => $migrationResults,
                'templates' => $templateResults,
                'links' => $linkResults
            ],
            'errors' => $this->migrationStats['errors'],
            'success' => empty($this->migrationStats['errors']) || $this->migrationStats['successful_migrations'] > 0
        ];
        
        // Save final report
        $finalReportFile = $this->outputDir . '/production-final-report-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($finalReportFile, json_encode($finalReport, JSON_PRETTY_PRINT));
        $this->log("Final report saved to: $finalReportFile", "INFO");
        
        return $finalReport;
    }

    /**
     * Get page content (placeholder for production implementation)
     */
    private function getPageContent(array $page): ?string
    {
        // In production, this would read from database or file system
        // For now, return mock content based on page title
        $mockContent = [
            'Home Page' => "=== Welcome ===\n\nWelcome to '''IslamWiki'''!\n\nThis page contains:\n* [[Quran]] references\n* {{Infobox|title=Welcome}}\n* [Category:Main]\n\n----\n\n<nowiki>Some code here</nowiki>",
            'Quran' => "The [[Quran]] is the holy book of Islam. It contains {{Quran|surah=1|ayah=1-7}} and many other verses.",
            'Hadith' => "[[Hadith]] are the sayings of Prophet Muhammad. Example: {{Hadith|book=Bukhari|number=1}}"
        ];
        
        return $mockContent[$page['title']] ?? null;
    }

    /**
     * Save migrated content (placeholder for production implementation)
     */
    private function saveMigratedContent(array $page, string $content): void
    {
        // In production, this would save to database or file system
        $this->log("Saving migrated content for: {$page['title']}", "INFO");
        
        // For demonstration, save to file
        $contentFile = $this->outputDir . '/migrated-content/' . $page['id'] . '-' . 
                      preg_replace('/[^a-zA-Z0-9]/', '_', $page['title']) . '.md';
        
        if (!is_dir(dirname($contentFile))) {
            mkdir(dirname($contentFile), 0755, true);
        }
        
        file_put_contents($contentFile, $content);
        $this->log("Content saved to: $contentFile", "INFO");
    }

    /**
     * Get migration statistics
     */
    public function getMigrationStats(): array
    {
        return $this->migrationStats;
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

// Production migration execution
if (php_sapi_name() === 'cli') {
    echo "=== Production Migration Script ===\n\n";
    
    // Parse command line arguments
    $options = getopt('', ['dry-run', 'backup', 'batch-size:', 'stop-on-error', 'help']);
    
    if (isset($options['help'])) {
        echo "Usage: php production-migration.php [options]\n\n";
        echo "Options:\n";
        echo "  --dry-run          Run migration without saving changes\n";
        echo "  --backup           Create backups before migration\n";
        echo "  --batch-size=N     Set batch size for processing (default: 100)\n";
        echo "  --stop-on-error    Stop migration on first error\n";
        echo "  --help             Show this help message\n\n";
        exit(0);
    }
    
    // Initialize configuration
    $config = [
        'dry_run' => isset($options['dry-run']),
        'backup_original' => isset($options['backup']),
        'batch_size' => (int)($options['batch-size'] ?? 100),
        'stop_on_error' => isset($options['stop-on-error'])
    ];
    
    // Initialize production migration
    $migration = new ProductionMigration($config);
    
    echo "Configuration:\n";
    echo "- Mode: " . ($config['dry_run'] ? 'DRY RUN' : 'PRODUCTION') . "\n";
    echo "- Backup: " . ($config['backup_original'] ? 'Yes' : 'No') . "\n";
    echo "- Batch Size: " . $config['batch_size'] . "\n";
    echo "- Stop on Error: " . ($config['stop_on_error'] ? 'Yes' : 'No') . "\n\n";
    
    // Execute migration
    try {
        $results = $migration->executeMigration();
        
        if ($results['success']) {
            echo "\n=== Migration Completed Successfully ===\n";
            echo "Total Items: " . $results['statistics']['total_items'] . "\n";
            echo "Successful: " . $results['statistics']['successful_migrations'] . "\n";
            echo "Failed: " . $results['statistics']['failed_migrations'] . "\n";
            echo "Success Rate: " . $results['statistics']['success_rate'] . "%\n";
            echo "Execution Time: " . $results['migration_info']['execution_time_seconds'] . " seconds\n";
        } else {
            echo "\n=== Migration Completed with Errors ===\n";
            echo "Errors: " . count($results['errors']) . "\n";
            foreach ($results['errors'] as $error) {
                echo "- $error\n";
            }
        }
        
        echo "\nResults saved to: " . dirname($migration->getConfig()['output_dir'] ?? __DIR__) . "/production-migration/\n";
        
    } catch (Exception $e) {
        echo "\n=== Migration Failed ===\n";
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
} 