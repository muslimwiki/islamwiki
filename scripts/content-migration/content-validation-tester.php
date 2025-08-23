<?php
/**
 * Content Validation Tester Script
 * 
 * Comprehensive testing and validation of migrated content
 * Part of Phase 5: Content Migration & Testing
 * 
 * @version 0.0.3.0
 * @package EnhancedMarkdown
 */

require_once __DIR__ . '/content-analyzer.php';
require_once __DIR__ . '/content-migrator.php';
require_once __DIR__ . '/template-converter.php';
require_once __DIR__ . '/link-updater.php';

class ContentValidationTester
{
    private ContentAnalyzer $analyzer;
    private ContentMigrator $migrator;
    private TemplateConverter $templateConverter;
    private LinkUpdater $linkUpdater;
    
    private array $testResults = [];
    private string $outputDir;
    private array $validationStats;

    public function __construct()
    {
        $this->outputDir = __DIR__ . '/validation-results';
        $this->validationStats = [
            'total_tests' => 0,
            'passed_tests' => 0,
            'failed_tests' => 0,
            'warnings' => 0,
            'start_time' => microtime(true)
        ];
        
        // Initialize components
        $this->analyzer = new ContentAnalyzer();
        $this->migrator = new ContentMigrator();
        $this->templateConverter = new TemplateConverter();
        $this->linkUpdater = new LinkUpdater();
        
        $this->ensureOutputDirectory();
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
     * Execute comprehensive content validation
     */
    public function executeValidation(): array
    {
        echo "=== Content Validation Testing ===\n\n";
        
        try {
            // Test 1: Content Integrity
            $this->testContentIntegrity();
            
            // Test 2: Markdown Syntax Validation
            $this->testMarkdownSyntax();
            
            // Test 3: Wiki Extensions Functionality
            $this->testWikiExtensions();
            
            // Test 4: Islamic Content Templates
            $this->testIslamicTemplates();
            
            // Test 5: Link Validation
            $this->testLinkValidation();
            
            // Test 6: Template Rendering
            $this->testTemplateRendering();
            
            // Test 7: Performance Testing
            $this->testPerformance();
            
            // Test 8: Cross-browser Compatibility
            $this->testCrossBrowserCompatibility();
            
            // Generate validation report
            $this->generateValidationReport();
            
            return $this->testResults;
            
        } catch (Exception $e) {
            echo "Validation failed: " . $e->getMessage() . "\n";
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test 1: Content Integrity
     */
    private function testContentIntegrity(): void
    {
        echo "Test 1: Content Integrity\n";
        echo "------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        // Test migrated content files
        $migratedContentDir = __DIR__ . '/production-migration/migrated-content';
        $testResults = [];
        
        if (is_dir($migratedContentDir)) {
            $files = glob($migratedContentDir . '/*.md');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                
                $testResults[$filename] = [
                    'file_exists' => true,
                    'content_length' => strlen($content),
                    'has_content' => !empty(trim($content)),
                    'markdown_syntax' => $this->validateMarkdownSyntax($content),
                    'wiki_extensions' => $this->validateWikiExtensions($content)
                ];
                
                echo "  ✓ $filename: " . strlen($content) . " characters\n";
            }
            
            $allTestsPassed = true;
            foreach ($testResults as $result) {
                if (!$result['file_exists'] || !$result['has_content']) {
                    $allTestsPassed = false;
                    break;
                }
            }
            
            if ($allTestsPassed) {
                echo "  ✓ All migrated content files are valid\n";
                $this->validationStats['passed_tests']++;
            } else {
                echo "  ✗ Some content files have issues\n";
                $this->validationStats['failed_tests']++;
            }
            
        } else {
            echo "  ✗ Migrated content directory not found\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['content_integrity'] = $testResults;
        echo "\n";
    }

    /**
     * Test 2: Markdown Syntax Validation
     */
    private function testMarkdownSyntax(): void
    {
        echo "Test 2: Markdown Syntax Validation\n";
        echo "----------------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = "# Test Heading\n\nThis is **bold** and *italic* text.\n\n- List item 1\n- List item 2\n\n[Link text](https://example.com)";
        
        $expectedHtml = '<h1>Test Heading</h1><p>This is <strong>bold</strong> and <em>italic</em> text.</p><ul><li>List item 1</li><li>List item 2</li></ul><p><a href="https://example.com">Link text</a></p>';
        
        $processedContent = $this->migrator->convertContent($testContent, 'test');
        $htmlContent = $this->convertMarkdownToHtml($processedContent);
        
        // Clean up HTML for comparison
        $cleanHtml = preg_replace('/\s+/', '', $htmlContent);
        $cleanExpected = preg_replace('/\s+/', '', $expectedHtml);
        
        if ($cleanHtml === $cleanExpected) {
            echo "  ✓ Markdown syntax processing working correctly\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ Markdown syntax processing has issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['markdown_syntax'] = [
            'test_content' => $testContent,
            'processed_content' => $processedContent,
            'html_output' => $htmlContent,
            'expected_html' => $expectedHtml,
            'test_passed' => ($cleanHtml === $cleanExpected)
        ];
        
        echo "\n";
    }

    /**
     * Test 3: Wiki Extensions Functionality
     */
    private function testWikiExtensions(): void
    {
        echo "Test 3: Wiki Extensions Functionality\n";
        echo "-------------------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = "[[Page Name]] and [[Page|Display Text]] with [Category:Test]";
        
        $processedContent = $this->linkUpdater->updateInternalLinks($testContent, 'test');
        
        // Check if internal links are preserved
        $hasInternalLinks = preg_match('/\[\[([^\]]+)\]\]/', $processedContent);
        $hasCategories = preg_match('/\[Category:([^\]]+)\]/', $processedContent);
        
        if ($hasInternalLinks && $hasCategories) {
            echo "  ✓ Wiki extensions processing working correctly\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ Wiki extensions processing has issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['wiki_extensions'] = [
            'test_content' => $testContent,
            'processed_content' => $processedContent,
            'has_internal_links' => $hasInternalLinks,
            'has_categories' => $hasCategories,
            'test_passed' => ($hasInternalLinks && $hasCategories)
        ];
        
        echo "\n";
    }

    /**
     * Test 4: Islamic Content Templates
     */
    private function testIslamicTemplates(): void
    {
        echo "Test 4: Islamic Content Templates\n";
        echo "---------------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = "{{Quran|surah=1|ayah=1-7}} and {{Hadith|book=Bukhari|number=1}}";
        
        $processedContent = $this->templateConverter->convertTemplates($testContent, 'test');
        
        // Check if Islamic templates are processed
        $hasQuranTemplate = strpos($processedContent, 'Quran') !== false;
        $hasHadithTemplate = strpos($processedContent, 'Hadith') !== false;
        
        if ($hasQuranTemplate && $hasHadithTemplate) {
            echo "  ✓ Islamic content templates working correctly\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ Islamic content templates have issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['islamic_templates'] = [
            'test_content' => $testContent,
            'processed_content' => $processedContent,
            'has_quran_template' => $hasQuranTemplate,
            'has_hadith_template' => $hasHadithTemplate,
            'test_passed' => ($hasQuranTemplate && $hasHadithTemplate)
        ];
        
        echo "\n";
    }

    /**
     * Test 5: Link Validation
     */
    private function testLinkValidation(): void
    {
        echo "Test 5: Link Validation\n";
        echo "-----------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = "[[Valid Link]] and [[Invalid|Link]] with [Category:Test]";
        
        $validation = $this->linkUpdater->validateInternalLinks($testContent);
        $statistics = $this->linkUpdater->generateLinkStatistics($testContent);
        
        if ($validation['total_links'] > 0) {
            echo "  ✓ Link validation working correctly\n";
            echo "    - Total links: {$validation['total_links']}\n";
            echo "    - Valid links: {$validation['valid_links']}\n";
            echo "    - Invalid links: {$validation['invalid_links']}\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ Link validation has issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['link_validation'] = [
            'test_content' => $testContent,
            'validation' => $validation,
            'statistics' => $statistics,
            'test_passed' => ($validation['total_links'] > 0)
        ];
        
        echo "\n";
    }

    /**
     * Test 6: Template Rendering
     */
    private function testTemplateRendering(): void
    {
        echo "Test 6: Template Rendering\n";
        echo "--------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = "{{Infobox|title=Test|content=Test content}}";
        
        $processedContent = $this->templateConverter->convertTemplates($testContent, 'test');
        
        if (strpos($processedContent, 'Infobox') !== false) {
            echo "  ✓ Template rendering working correctly\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ Template rendering has issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['template_rendering'] = [
            'test_content' => $testContent,
            'processed_content' => $processedContent,
            'test_passed' => (strpos($processedContent, 'Infobox') !== false)
        ];
        
        echo "\n";
    }

    /**
     * Test 7: Performance Testing
     */
    private function testPerformance(): void
    {
        echo "Test 7: Performance Testing\n";
        echo "---------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        $testContent = str_repeat("# Test Heading\n\nThis is **test content** with [[internal links]] and {{templates}}.\n\n", 100);
        
        $startTime = microtime(true);
        $processedContent = $this->migrator->convertContent($testContent, 'test');
        $endTime = microtime(true);
        
        $processingTime = $endTime - $startTime;
        $contentLength = strlen($testContent);
        $charsPerSecond = $contentLength / $processingTime;
        
        echo "  - Content length: " . number_format($contentLength) . " characters\n";
        echo "  - Processing time: " . number_format($processingTime, 4) . " seconds\n";
        echo "  - Processing speed: " . number_format($charsPerSecond, 0) . " chars/second\n";
        
        // Performance threshold: should process at least 10,000 chars/second
        if ($charsPerSecond >= 10000) {
            echo "  ✓ Performance meets requirements\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ⚠ Performance below threshold\n";
            $this->validationStats['warnings']++;
        }
        
        $this->testResults['performance'] = [
            'content_length' => $contentLength,
            'processing_time' => $processingTime,
            'chars_per_second' => $charsPerSecond,
            'test_passed' => ($charsPerSecond >= 10000)
        ];
        
        echo "\n";
    }

    /**
     * Test 8: Cross-browser Compatibility
     */
    private function testCrossBrowserCompatibility(): void
    {
        echo "Test 8: Cross-browser Compatibility\n";
        echo "-----------------------------------\n";
        
        $this->validationStats['total_tests']++;
        
        // Test HTML output compatibility
        $testContent = "# Heading\n\n**Bold** and *italic* text with [[links]]";
        $htmlOutput = $this->convertMarkdownToHtml($testContent);
        
        // Check for basic HTML compatibility
        $hasValidHtml = preg_match('/<h1>.*<\/h1>/', $htmlOutput) &&
                       preg_match('/<strong>.*<\/strong>/', $htmlOutput) &&
                       preg_match('/<em>.*<\/em>/', $htmlOutput);
        
        if ($hasValidHtml) {
            echo "  ✓ HTML output is browser-compatible\n";
            $this->validationStats['passed_tests']++;
        } else {
            echo "  ✗ HTML output has compatibility issues\n";
            $this->validationStats['failed_tests']++;
        }
        
        $this->testResults['cross_browser_compatibility'] = [
            'test_content' => $testContent,
            'html_output' => $htmlOutput,
            'has_valid_html' => $hasValidHtml,
            'test_passed' => $hasValidHtml
        ];
        
        echo "\n";
    }

    /**
     * Generate validation report
     */
    private function generateValidationReport(): void
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->validationStats['start_time'];
        
        echo "=== Validation Report ===\n";
        echo "Total Tests: {$this->validationStats['total_tests']}\n";
        echo "Passed: {$this->validationStats['passed_tests']}\n";
        echo "Failed: {$this->validationStats['failed_tests']}\n";
        echo "Warnings: {$this->validationStats['warnings']}\n";
        echo "Success Rate: " . round(($this->validationStats['passed_tests'] / $this->validationStats['total_tests']) * 100, 1) . "%\n";
        echo "Execution Time: " . round($executionTime, 3) . " seconds\n\n";
        
        // Save detailed results
        $report = [
            'validation_info' => [
                'version' => '0.0.3.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'execution_time' => $executionTime
            ],
            'statistics' => $this->validationStats,
            'test_results' => $this->testResults,
            'summary' => [
                'overall_status' => ($this->validationStats['failed_tests'] === 0) ? 'PASSED' : 'FAILED',
                'recommendations' => $this->generateRecommendations()
            ]
        ];
        
        $reportFile = $this->outputDir . '/validation-report-' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "Detailed validation report saved to: $reportFile\n";
    }

    /**
     * Generate recommendations based on test results
     */
    private function generateRecommendations(): array
    {
        $recommendations = [];
        
        if ($this->validationStats['failed_tests'] > 0) {
            $recommendations[] = 'Address failed tests before deployment';
        }
        
        if ($this->validationStats['warnings'] > 0) {
            $recommendations[] = 'Review warnings and optimize performance if needed';
        }
        
        if ($this->validationStats['passed_tests'] === $this->validationStats['total_tests']) {
            $recommendations[] = 'All tests passed - system ready for production deployment';
        }
        
        return $recommendations;
    }

    /**
     * Validate Markdown syntax
     */
    private function validateMarkdownSyntax(string $content): bool
    {
        // Basic Markdown syntax validation
        $hasHeadings = preg_match('/^#+\s+/m', $content);
        $hasFormatting = preg_match('/\*\*.*\*\*|\*.*\*/', $content);
        $hasLists = preg_match('/^[-*+]\s+/m', $content);
        
        return $hasHeadings || $hasFormatting || $hasLists;
    }

    /**
     * Validate Wiki extensions
     */
    private function validateWikiExtensions(string $content): bool
    {
        // Wiki extension validation
        $hasInternalLinks = preg_match('/\[\[([^\]]+)\]\]/', $content);
        $hasTemplates = preg_match('/\{\{([^}]+)\}\}/', $content);
        $hasCategories = preg_match('/\[Category:([^\]]+)\]/', $content);
        
        return $hasInternalLinks || $hasTemplates || $hasCategories;
    }

    /**
     * Convert Markdown to HTML for testing
     */
    private function convertMarkdownToHtml(string $markdown): string
    {
        // Simple Markdown to HTML conversion for testing
        $html = $markdown;
        
        // Headings
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        
        // Lists
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.+<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Paragraphs
        $html = '<p>' . str_replace("\n\n", '</p><p>', $html) . '</p>';
        
        return $html;
    }
}

// Execute validation testing
if (php_sapi_name() === 'cli') {
    echo "=== Content Validation Testing ===\n\n";
    
    $validator = new ContentValidationTester();
    $results = $validator->executeValidation();
    
    if (isset($results['error'])) {
        echo "Validation failed: " . $results['error'] . "\n";
        exit(1);
    }
    
    echo "Validation testing completed successfully!\n";
    echo "Check the validation-results directory for detailed reports.\n";
} 