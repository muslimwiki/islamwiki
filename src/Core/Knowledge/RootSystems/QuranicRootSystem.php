<?php
declare(strict_types=1);

namespace IslamWiki\Core\Knowledge\RootSystems;

use IslamWiki\Core\Knowledge\Interfaces\RootSystemInterface;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Quranic Root System
 * 
 * Extracts principles from Qur'anic verses using Arabic root analysis.
 */
class QuranicRootSystem implements RootSystemInterface
{
    private Connection $db;
    private ShahidLogger $logger;
    
    /**
     * Create a new Qur'anic root system.
     */
    public function __construct(Connection $db, ShahidLogger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * Extract principles from Qur'anic text.
     */
    public function extractPrinciples(string $text): array
    {
        try {
            $this->logger->info('Extracting Qur\'anic principles', [
                'text_length' => strlen($text),
            ]);
            
            $principles = [];
            
            // Extract Arabic roots (3-letter patterns)
            $roots = $this->extractArabicRoots($text);
            
            foreach ($roots as $root) {
                $principle = $this->analyzeRoot($root);
                if ($principle) {
                    $principles[] = $principle;
                }
            }
            
            // Extract thematic principles
            $thematicPrinciples = $this->extractThematicPrinciples($text);
            $principles = array_merge($principles, $thematicPrinciples);
            
            $this->logger->info('Qur\'anic principles extracted', [
                'principles_count' => count($principles),
                'roots_found' => count($roots),
            ]);
            
            return $principles;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to extract Qur\'anic principles', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Extract Arabic roots from text.
     */
    private function extractArabicRoots(string $text): array
    {
        $roots = [];
        
        // Simple pattern matching for Arabic 3-letter roots
        // This is a basic implementation - could be enhanced with proper Arabic morphology
        preg_match_all('/[\u0600-\u06FF]{3,}/u', $text, $matches);
        
        foreach ($matches[0] as $match) {
            if (strlen($match) >= 3) {
                $root = substr($match, 0, 3);
                if ($this->isValidArabicRoot($root)) {
                    $roots[] = $root;
                }
            }
        }
        
        return array_unique($roots);
    }
    
    /**
     * Check if a string is a valid Arabic root.
     */
    private function isValidArabicRoot(string $root): bool
    {
        // Basic validation - could be enhanced with proper Arabic grammar rules
        return strlen($root) === 3 && preg_match('/^[\u0600-\u06FF]{3}$/u', $root);
    }
    
    /**
     * Analyze a root and extract its principle.
     */
    private function analyzeRoot(string $root): ?array
    {
        // Look up root in database for known principles
        $principle = $this->db->select(
            'SELECT * FROM usul_quranic_roots WHERE root = ?',
            [$root]
        );
        
        if (!empty($principle)) {
            return [
                'type' => 'root',
                'root' => $root,
                'principle' => $principle[0]['principle'],
                'meaning' => $principle[0]['meaning'],
                'confidence' => 0.8,
            ];
        }
        
        // Fallback to basic analysis
        return [
            'type' => 'root',
            'root' => $root,
            'principle' => 'Basic Qur\'anic principle',
            'meaning' => 'Extracted from Arabic root analysis',
            'confidence' => 0.3,
        ];
    }
    
    /**
     * Extract thematic principles from text.
     */
    private function extractThematicPrinciples(string $text): array
    {
        $principles = [];
        
        // Define common Qur'anic themes
        $themes = [
            'tawhid' => ['توحيد', 'الله', 'رب', 'إله'],
            'adl' => ['عدل', 'قسط', 'حق'],
            'ihsan' => ['إحسان', 'خير', 'بر'],
            'taqwa' => ['تقوى', 'خشية', 'خوف'],
        ];
        
        foreach ($themes as $theme => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $principles[] = [
                        'type' => 'theme',
                        'theme' => $theme,
                        'keyword' => $keyword,
                        'principle' => $this->getThemePrinciple($theme),
                        'confidence' => 0.7,
                    ];
                }
            }
        }
        
        return $principles;
    }
    
    /**
     * Get principle for a theme.
     */
    private function getThemePrinciple(string $theme): string
    {
        $principles = [
            'tawhid' => 'Monotheism and divine unity',
            'adl' => 'Justice and fairness',
            'ihsan' => 'Excellence and doing good',
            'taqwa' => 'God-consciousness and piety',
        ];
        
        return $principles[$theme] ?? 'Islamic principle';
    }
    
    /**
     * Get root system type.
     */
    public function getType(): string
    {
        return 'quranic';
    }
    
    /**
     * Validate text for Qur'anic analysis.
     */
    public function validateText(string $text): bool
    {
        // Check if text contains Arabic characters
        return preg_match('/[\u0600-\u06FF]/u', $text) === 1;
    }
} 