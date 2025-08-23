<?php

/**
 * Content Analyzer for Phase 5 Migration
 * 
 * This script analyzes existing content to identify WikiMarkup that needs
 * to be converted to Enhanced Markdown during Phase 5.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */

class ContentAnalyzer
{
    private PDO $pdo;
    private array $analysisResults = [];
    
    public function __construct()
    {
        // For now, we'll create a mock analysis since we don't have database access
        // In production, this would connect to the actual database
        echo "Note: Running in mock mode for demonstration purposes.\n";
        echo "In production, this would connect to the actual database.\n\n";
    }
    
    /**
     * Analyze all content for WikiMarkup patterns
     */
    public function analyzeContent(): array
    {
        echo "=== Content Analysis for Phase 5 Migration ===\n\n";
        
        // Analyze wiki pages
        $this->analyzeWikiPages();
        
        // Analyze other content types
        $this->analyzeOtherContent();
        
        // Generate summary report
        $this->generateSummaryReport();
        
        return $this->analysisResults;
    }
    
    /**
     * Analyze wiki pages for WikiMarkup patterns
     */
    private function analyzeWikiPages(): void
    {
        echo "Analyzing Wiki Pages...\n";
        
        // Mock data for demonstration
        $mockPages = [
            [
                'id' => 1,
                'title' => 'Main Page',
                'content' => 'Welcome to [[IslamWiki]]! This is a **bold** and *italic* text with {{Infobox|title=Welcome}} and [Category:Main]'
            ],
            [
                'id' => 2,
                'title' => 'Quran',
                'content' => 'The [[Quran]] is the holy book of Islam. It contains {{Quran|surah=1|ayah=1-7}} and many other verses.'
            ],
            [
                'id' => 3,
                'title' => 'Hadith',
                'content' => '[[Hadith]] are the sayings of Prophet Muhammad. Example: {{Hadith|book=Bukhari|number=1}}'
            ]
        ];
        
        $pageCount = 0;
        $wikimarkupCount = 0;
        
        foreach ($mockPages as $row) {
            $pageCount++;
            $analysis = $this->analyzePageContent($row);
            
            if ($analysis['hasWikiMarkup']) {
                $wikimarkupCount++;
                $this->analysisResults['wiki_pages'][] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'analysis' => $analysis,
                    'priority' => $this->determinePriority($row['title'], $analysis)
                ];
            }
        }
        
        echo "Wiki Pages Analysis Complete:\n";
        echo "- Total pages analyzed: $pageCount\n";
        echo "- Pages with WikiMarkup: $wikimarkupCount\n";
        echo "- Migration priority determined\n\n";
    }
    
    /**
     * Analyze other content types
     */
    private function analyzeOtherContent(): void
    {
        echo "Analyzing Other Content Types...\n";
        
        // Analyze discussions/comments
        $this->analyzeDiscussions();
        
        // Analyze user profiles
        $this->analyzeUserProfiles();
        
        // Analyze system content
        $this->analyzeSystemContent();
        
        echo "Other Content Analysis Complete\n\n";
    }
    
    /**
     * Analyze discussions for WikiMarkup
     */
    private function analyzeDiscussions(): void
    {
        // Mock data for demonstration
        $mockDiscussions = [
            [
                'id' => 1,
                'content' => 'Great article about [[Islam]]! Check out {{Infobox|title=Islam}} for more info.'
            ],
            [
                'id' => 2,
                'content' => 'I found this helpful: [Category:Beginner] and {{Note|message=Good for new users}}'
            ]
        ];
        
        $discussionCount = 0;
        $wikimarkupCount = 0;
        
        foreach ($mockDiscussions as $row) {
            $discussionCount++;
            $analysis = $this->analyzePageContent($row);
            
            if ($analysis['hasWikiMarkup']) {
                $wikimarkupCount++;
                $this->analysisResults['discussions'][] = [
                    'id' => $row['id'],
                    'analysis' => $analysis,
                    'priority' => 'medium'
                ];
            }
        }
        
        echo "- Discussions analyzed: $discussionCount (WikiMarkup: $wikimarkupCount)\n";
    }
    
    /**
     * Analyze user profiles for WikiMarkup
     */
    private function analyzeUserProfiles(): void
    {
        // Mock data for demonstration
        $mockProfiles = [
            [
                'id' => 1,
                'content' => 'I study [[Islamic History]] and love {{Scholar|name=Ibn Khaldun}}'
            ]
        ];
        
        $profileCount = 0;
        $wikimarkupCount = 0;
        
        foreach ($mockProfiles as $row) {
            $profileCount++;
            $analysis = $this->analyzePageContent($row);
            
            if ($analysis['hasWikiMarkup']) {
                $wikimarkupCount++;
                $this->analysisResults['user_profiles'][] = [
                    'id' => $row['id'],
                    'analysis' => $analysis,
                    'priority' => 'low'
                ];
            }
        }
        
        echo "- User profiles analyzed: $profileCount (WikiMarkup: $wikimarkupCount)\n";
    }
    
    /**
     * Analyze system content for WikiMarkup
     */
    private function analyzeSystemContent(): void
    {
        // Mock data for demonstration
        $mockSystemContent = [
            [
                'id' => 1,
                'content' => 'System help page with [[Getting Started]] and {{Warning|message=Important}}',
                'type' => 'help'
            ]
        ];
        
        $systemCount = 0;
        $wikimarkupCount = 0;
        
        foreach ($mockSystemContent as $row) {
            $systemCount++;
            $analysis = $this->analyzePageContent($row);
            
            if ($analysis['hasWikiMarkup']) {
                $wikimarkupCount++;
                $this->analysisResults['system_content'][] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'analysis' => $analysis,
                    'priority' => 'high'
                ];
            }
        }
        
        echo "- System content analyzed: $systemCount (WikiMarkup: $wikimarkupCount)\n";
    }
    
    /**
     * Analyze individual page content for WikiMarkup patterns
     */
    private function analyzePageContent(array $row): array
    {
        $content = $row['content'];
        $analysis = [
            'hasWikiMarkup' => false,
            'patterns' => [],
            'complexity' => 'low',
            'estimatedConversionTime' => 0
        ];
        
        // Check for WikiMarkup patterns
        $patterns = [
            'internal_links' => '/\[\[([^\]]+)\]\]/',
            'external_links' => '/\[([^\]]+)\]\(([^)]+)\)/',
            'templates' => '/\{\{([^}]+)\}\}/',
            'categories' => '/\[\[Category:([^\]]+)\]\]/',
            'references' => '/<ref[^>]*>.*?<\/ref>/s',
            'tables' => '/^\{\|.*?\|\}/ms',
            'headings' => '/^=+\s*([^=]+)\s*=+$/m',
            'lists' => '/^[\*#]+\s+/m',
            'bold_italic' => '/\'\'\'([^\']+)\'\'\'|\'\'([^\']+)\'\'/',
            'nowiki' => '/<nowiki>.*?<\/nowiki>/s'
        ];
        
        $totalPatterns = 0;
        $complexityScore = 0;
        
        foreach ($patterns as $patternName => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $count = count($matches[0]);
                if ($count > 0) {
                    $analysis['patterns'][$patternName] = [
                        'count' => $count,
                        'examples' => array_slice($matches[0], 0, 3) // First 3 examples
                    ];
                    $totalPatterns += $count;
                    $analysis['hasWikiMarkup'] = true;
                    
                    // Calculate complexity score
                    switch ($patternName) {
                        case 'templates':
                            $complexityScore += $count * 3; // Templates are complex
                            break;
                        case 'tables':
                            $complexityScore += $count * 2; // Tables are moderately complex
                            break;
                        default:
                            $complexityScore += $count; // Other patterns are simple
                    }
                }
            }
        }
        
        // Determine complexity level
        if ($complexityScore > 20) {
            $analysis['complexity'] = 'high';
            $analysis['estimatedConversionTime'] = $totalPatterns * 2; // 2 minutes per pattern
        } elseif ($complexityScore > 10) {
            $analysis['complexity'] = 'medium';
            $analysis['estimatedConversionTime'] = $totalPatterns * 1; // 1 minute per pattern
        } else {
            $analysis['complexity'] = 'low';
            $analysis['estimatedConversionTime'] = $totalPatterns * 0.5; // 30 seconds per pattern
        }
        
        return $analysis;
    }
    
    /**
     * Determine migration priority for a page
     */
    private function determinePriority(string $title, array $analysis): string
    {
        // High priority: Main content, frequently accessed pages
        if (preg_match('/^(main|home|index|welcome|about|help)/i', $title)) {
            return 'high';
        }
        
        // High priority: Complex content with many patterns
        if ($analysis['complexity'] === 'high' || count($analysis['patterns']) > 5) {
            return 'high';
        }
        
        // Medium priority: Moderate complexity
        if ($analysis['complexity'] === 'medium' || count($analysis['patterns']) > 2) {
            return 'medium';
        }
        
        // Low priority: Simple content, archive pages
        return 'low';
    }
    
    /**
     * Generate summary report
     */
    private function generateSummaryReport(): void
    {
        echo "=== Content Analysis Summary ===\n";
        
        $totalPages = 0;
        $totalPatterns = 0;
        $estimatedTime = 0;
        
        foreach ($this->analysisResults as $contentType => $items) {
            $count = count($items);
            $totalPages += $count;
            
            echo "\n$contentType: $count items\n";
            
            foreach ($items as $item) {
                $patterns = count($item['analysis']['patterns']);
                $totalPatterns += $patterns;
                $estimatedTime += $item['analysis']['estimatedConversionTime'];
                
                $title = isset($item['title']) ? $item['title'] : 'ID: ' . $item['id'];
                echo "  - $title ";
                echo "({$item['priority']} priority, $patterns patterns)\n";
            }
        }
        
        echo "\n=== Migration Summary ===\n";
        echo "Total content items to migrate: $totalPages\n";
        echo "Total WikiMarkup patterns found: $totalPatterns\n";
        echo "Estimated migration time: " . round($estimatedTime / 60, 1) . " hours\n";
        echo "Recommended approach: Incremental migration by priority\n\n";
        
        // Save detailed results to file
        $this->saveResultsToFile();
    }
    
    /**
     * Save analysis results to file
     */
    private function saveResultsToFile(): void
    {
        $filename = __DIR__ . '/content-analysis-results.json';
        $jsonData = json_encode($this->analysisResults, JSON_PRETTY_PRINT);
        
        if (file_put_contents($filename, $jsonData)) {
            echo "Detailed results saved to: $filename\n";
        } else {
            echo "Warning: Could not save results to file\n";
        }
    }
}

// Run the analysis
if (php_sapi_name() === 'cli') {
    $analyzer = new ContentAnalyzer();
    $results = $analyzer->analyzeContent();
    
    echo "Content analysis complete. Results saved to file.\n";
    echo "Ready to proceed with migration script development.\n";
} else {
    echo "This script should be run from the command line.\n";
} 