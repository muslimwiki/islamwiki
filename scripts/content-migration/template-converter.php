<?php
/**
 * Template Converter Script
 * 
 * Converts MediaWiki templates to Enhanced Markdown template system
 * Part of Phase 5: Content Migration & Testing
 * 
 * @version 0.0.3.0
 * @package EnhancedMarkdown
 */

class TemplateConverter
{
    private array $templateMappings = [];
    private array $conversionResults = [];
    private string $outputDir;

    public function __construct()
    {
        $this->outputDir = __DIR__ . '/converted-templates';
        $this->initializeTemplateMappings();
        $this->ensureOutputDirectory();
    }

    /**
     * Initialize template mappings from MediaWiki to Enhanced Markdown
     */
    private function initializeTemplateMappings(): void
    {
        $this->templateMappings = [
            // Common MediaWiki templates
            'infobox' => [
                'pattern' => '/\{\{Infobox\s*\|\s*([^}]+)\}\}/i',
                'replacement' => '{{Infobox|$1}}',
                'description' => 'Convert MediaWiki Infobox to Enhanced Markdown Infobox'
            ],
            'stub' => [
                'pattern' => '/\{\{stub\}\}/i',
                'replacement' => '{{Note|message=This article is a stub and needs expansion}}',
                'description' => 'Convert stub template to note template'
            ],
            'disambiguation' => [
                'pattern' => '/\{\{disambiguation\}\}/i',
                'replacement' => '{{Warning|message=This is a disambiguation page}}',
                'description' => 'Convert disambiguation template to warning template'
            ],
            'main' => [
                'pattern' => '/\{\{main\s*\|\s*([^}]+)\}\}/i',
                'replacement' => '{{Note|message=Main article: [[$1]]}}',
                'description' => 'Convert main template to note with internal link'
            ],
            'see_also' => [
                'pattern' => '/\{\{see\s+also\s*\|\s*([^}]+)\}\}/i',
                'replacement' => '{{Note|message=See also: [[$1]]}}',
                'description' => 'Convert see also template to note with internal link'
            ],
            'quote' => [
                'pattern' => '/\{\{quote\s*\|\s*([^}]+)\}\}/i',
                'replacement' => '> $1',
                'description' => 'Convert quote template to Markdown blockquote'
            ],
            'quran' => [
                'pattern' => '/\{\{quran\s*\|\s*surah\s*=\s*(\d+)\s*\|\s*ayah\s*=\s*(\d+)\s*\}\}/i',
                'replacement' => '{{Quran|surah=$1|ayah=$2}}',
                'description' => 'Convert MediaWiki Quran template to Enhanced Markdown Quran template'
            ],
            'hadith' => [
                'pattern' => '/\{\{hadith\s*\|\s*book\s*=\s*([^|]+)\s*\|\s*number\s*=\s*(\d+)\s*\}\}/i',
                'replacement' => '{{Hadith|book=$1|number=$2}}',
                'description' => 'Convert MediaWiki Hadith template to Enhanced Markdown Hadith template'
            ],
            'scholar' => [
                'pattern' => '/\{\{scholar\s*\|\s*name\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Scholar|name=$1}}',
                'description' => 'Convert MediaWiki Scholar template to Enhanced Markdown Scholar template'
            ],
            'fatwa' => [
                'pattern' => '/\{\{fatwa\s*\|\s*scholar\s*=\s*([^|]+)\s*\|\s*topic\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Fatwa|scholar=$1|topic=$2}}',
                'description' => 'Convert MediaWiki Fatwa template to Enhanced Markdown Fatwa template'
            ],
            'prayer_times' => [
                'pattern' => '/\{\{prayer\s+times\s*\|\s*city\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{PrayerTimes|city=$1}}',
                'description' => 'Convert MediaWiki Prayer Times template to Enhanced Markdown template'
            ],
            'hijri_calendar' => [
                'pattern' => '/\{\{hijri\s+calendar\s*\|\s*date\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{HijriCalendar|date=$1}}',
                'description' => 'Convert MediaWiki Hijri Calendar template to Enhanced Markdown template'
            ],
            'qibla_direction' => [
                'pattern' => '/\{\{qibla\s+direction\s*\|\s*from\s*=\s*([^|]+)\s*\|\s*to\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{QiblaDirection|from=$1|to=$2}}',
                'description' => 'Convert MediaWiki Qibla Direction template to Enhanced Markdown template'
            ],
            'category' => [
                'pattern' => '/\{\{category\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '[Category:$1]',
                'description' => 'Convert MediaWiki category template to Enhanced Markdown category syntax'
            ],
            'reference' => [
                'pattern' => '/\{\{ref\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '<ref>$1</ref>',
                'description' => 'Convert MediaWiki reference template to Enhanced Markdown reference syntax'
            ],
            'external_link' => [
                'pattern' => '/\{\{external\s+link\s*\|\s*url\s*=\s*([^|]+)\s*\|\s*text\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '[$2]($1)',
                'description' => 'Convert MediaWiki external link template to Markdown link syntax'
            ],
            'image' => [
                'pattern' => '/\{\{image\s*\|\s*file\s*=\s*([^|]+)\s*\|\s*alt\s*=\s*([^|]+)\s*\|\s*caption\s*=\s*([^}]+)\s*\}\}/i',
                'replacement' => '![$2]($1 "$3")',
                'description' => 'Convert MediaWiki image template to Markdown image syntax'
            ],
            'table' => [
                'pattern' => '/\{\{table\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Infobox|title=Table|content=$1}}',
                'description' => 'Convert MediaWiki table template to Enhanced Markdown infobox'
            ],
            'warning' => [
                'pattern' => '/\{\{warning\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Warning|message=$1}}',
                'description' => 'Convert MediaWiki warning template to Enhanced Markdown warning template'
            ],
            'note' => [
                'pattern' => '/\{\{note\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Note|message=$1}}',
                'description' => 'Convert MediaWiki note template to Enhanced Markdown note template'
            ],
            'success' => [
                'pattern' => '/\{\{success\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Success|message=$1}}',
                'description' => 'Convert MediaWiki success template to Enhanced Markdown success template'
            ],
            'error' => [
                'pattern' => '/\{\{error\s*\|\s*([^}]+)\s*\}\}/i',
                'replacement' => '{{Error|message=$1}}',
                'description' => 'Convert MediaWiki error template to Enhanced Markdown error template'
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
     * Convert MediaWiki templates in content
     */
    public function convertTemplates(string $content, string $contentType = 'wiki_page'): string
    {
        $originalContent = $content;
        $convertedContent = $content;
        $conversions = [];

        echo "Converting templates in $contentType content...\n";

        foreach ($this->templateMappings as $templateName => $mapping) {
            if (preg_match_all($mapping['pattern'], $convertedContent, $matches, PREG_OFFSET_CAPTURE)) {
                $count = count($matches[0]);
                echo "  - Found $count instances of $templateName template\n";
                
                $conversions[$templateName] = $count;
                
                if (is_callable($mapping['replacement'])) {
                    $convertedContent = preg_replace_callback(
                        $mapping['pattern'],
                        $mapping['replacement'],
                        $convertedContent
                    );
                } else {
                    $convertedContent = preg_replace(
                        $mapping['pattern'],
                        $mapping['replacement'],
                        $convertedContent
                    );
                }
            }
        }

        // Record conversion result
        $this->recordConversionResult($contentType, $originalContent, $convertedContent, $conversions);

        return $convertedContent;
    }

    /**
     * Convert a specific MediaWiki template file
     */
    public function convertTemplateFile(string $templateName, string $templateContent): string
    {
        echo "Converting template: $templateName\n";
        
        $convertedContent = $templateContent;
        
        // Apply template-specific conversions
        foreach ($this->templateMappings as $mappingName => $mapping) {
            if (preg_match_all($mapping['pattern'], $convertedContent, $matches, PREG_OFFSET_CAPTURE)) {
                $count = count($matches[0]);
                echo "  - Applied $mappingName conversion ($count instances)\n";
                
                if (is_callable($mapping['replacement'])) {
                    $convertedContent = preg_replace_callback(
                        $mapping['pattern'],
                        $mapping['replacement'],
                        $convertedContent
                    );
                } else {
                    $convertedContent = preg_replace(
                        $mapping['pattern'],
                        $mapping['replacement'],
                        $convertedContent
                    );
                }
            }
        }

        return $convertedContent;
    }

    /**
     * Convert all templates in a directory
     */
    public function convertTemplateDirectory(string $sourceDir): array
    {
        $results = [];
        
        if (!is_dir($sourceDir)) {
            echo "Source directory does not exist: $sourceDir\n";
            return $results;
        }

        $files = glob($sourceDir . '/*.tmpl');
        echo "Found " . count($files) . " template files to convert\n";

        foreach ($files as $file) {
            $templateName = basename($file, '.tmpl');
            $content = file_get_contents($file);
            
            if ($content !== false) {
                $convertedContent = $this->convertTemplateFile($templateName, $content);
                
                // Save converted template
                $outputFile = $this->outputDir . '/' . $templateName . '.md';
                file_put_contents($outputFile, $convertedContent);
                
                $results[$templateName] = [
                    'source_file' => $file,
                    'output_file' => $outputFile,
                    'original_size' => strlen($content),
                    'converted_size' => strlen($convertedContent),
                    'conversion_success' => true
                ];
                
                echo "  - Converted $templateName -> $outputFile\n";
            } else {
                $results[$templateName] = [
                    'source_file' => $file,
                    'conversion_success' => false,
                    'error' => 'Could not read source file'
                ];
                
                echo "  - Failed to read $templateName\n";
            }
        }

        return $results;
    }

    /**
     * Record conversion result
     */
    private function recordConversionResult(string $contentType, string $originalContent, string $convertedContent, array $conversions): void
    {
        $this->conversionResults[] = [
            'content_type' => $contentType,
            'original_content' => $originalContent,
            'converted_content' => $convertedContent,
            'conversions_applied' => $conversions,
            'original_size' => strlen($originalContent),
            'converted_size' => strlen($convertedContent),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate conversion report
     */
    public function generateReport(): array
    {
        $totalItems = count($this->conversionResults);
        $totalConversions = 0;
        $totalOriginalSize = 0;
        $totalConvertedSize = 0;

        foreach ($this->conversionResults as $result) {
            $totalConversions += array_sum($result['conversions_applied']);
            $totalOriginalSize += $result['original_size'];
            $totalConvertedSize += $result['converted_size'];
        }

        return [
            'total_items' => $totalItems,
            'total_conversions' => $totalConversions,
            'total_original_size' => $totalOriginalSize,
            'total_converted_size' => $totalConvertedSize,
            'conversion_rate' => $totalItems > 0 ? ($totalConversions / $totalItems) * 100 : 0,
            'size_change_percentage' => $totalOriginalSize > 0 ? (($totalConvertedSize - $totalOriginalSize) / $totalOriginalSize) * 100 : 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Save conversion results to file
     */
    public function saveResults(string $filename = 'template-conversion-results.json'): void
    {
        $filepath = $this->outputDir . '/' . $filename;
        $data = [
            'summary' => $this->generateReport(),
            'detailed_results' => $this->conversionResults
        ];
        
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
        echo "Conversion results saved to: $filepath\n";
    }

    /**
     * Get template mappings
     */
    public function getTemplateMappings(): array
    {
        return $this->templateMappings;
    }

    /**
     * Add custom template mapping
     */
    public function addTemplateMapping(string $name, string $pattern, string $replacement, string $description): void
    {
        $this->templateMappings[$name] = [
            'pattern' => $pattern,
            'replacement' => $replacement,
            'description' => $description
        ];
    }
}

// Test the template converter
if (php_sapi_name() === 'cli') {
    echo "=== Template Converter Test ===\n\n";

    $converter = new TemplateConverter();

    // Test content with various MediaWiki templates
    $testContent = <<<CONTENT
This is a test page with various MediaWiki templates:

{{Infobox|title=Test|content=This is a test infobox}}
{{stub}}
{{disambiguation}}
{{main|Quran}}
{{see also|Hadith}}
{{quote|This is a test quote}}
{{quran|surah=1|ayah=1-7}}
{{hadith|book=Bukhari|number=1}}
{{scholar|name=Ibn Sina}}
{{fatwa|scholar=Al-Ghazali|topic=Prayer}}
{{prayer times|city=Mecca}}
{{hijri calendar|date=today}}
{{qibla direction|from=Current Location|to=Mecca}}
{{category|Islamic Studies}}
{{ref|This is a reference}}
{{external link|url=https://example.com|text=Example}}
{{image|file=test.jpg|alt=Test Image|caption=This is a test image}}
{{table|This is table content}}
{{warning|This is a warning}}
{{note|This is a note}}
{{success|This is a success message}}
{{error|This is an error message}}

End of test content.
CONTENT;

    echo "Original content:\n$testContent\n\n";
    echo "---\n\n";

    // Convert templates
    $convertedContent = $converter->convertTemplates($testContent, 'test_content');

    echo "Converted content:\n$convertedContent\n\n";
    echo "---\n\n";

    // Generate and display report
    $report = $converter->generateReport();
    echo "=== Conversion Report ===\n";
    echo "Total items processed: " . $report['total_items'] . "\n";
    echo "Total conversions applied: " . $report['total_conversions'] . "\n";
    echo "Conversion rate: " . number_format($report['conversion_rate'], 1) . "%\n";
    echo "Size change: " . number_format($report['size_change_percentage'], 1) . "%\n";
    echo "Timestamp: " . $report['timestamp'] . "\n\n";

    // Save results
    $converter->saveResults();

    echo "Template conversion test complete!\n";
    echo "Ready to proceed with actual template conversion.\n";
} 