<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Engines;

/**
 * Hadith Template Engine
 * 
 * Handles Hadith-specific template rendering for IslamWiki including:
 * - Hadith templates: {{Hadith|book=Bukhari|number=1}}
 * - Hadith chain templates: {{Hadith|chain=Abu Hurairah → Prophet Muhammad}}
 * - Hadith grade templates: {{Hadith|grade=Sahih|narrator=Abu Hurairah}}
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class HadithTemplateEngine
{
    private array $hadithBooks = [];
    private array $hadithGrades = [];
    
    public function __construct()
    {
        $this->initializeHadithBooks();
        $this->initializeHadithGrades();
    }
    
    /**
     * Render Hadith template
     * 
     * @param array $params Template parameters
     * @return string Rendered HTML content
     */
    public function render(array $params): string
    {
        $book = $params['book'] ?? null;
        $number = $params['number'] ?? null;
        $grade = $params['grade'] ?? null;
        $narrator = $params['narrator'] ?? null;
        $chain = $params['chain'] ?? null;
        $format = $params['format'] ?? 'full';
        
        if ($chain) {
            return $this->renderHadithChain($chain, $format);
        }
        
        if ($grade) {
            return $this->renderHadithGrade($grade, $narrator, $format);
        }
        
        if ($book && $number) {
            return $this->renderHadith($book, $number, $grade, $format);
        }
        
        return $this->renderError('Invalid Hadith template parameters');
    }
    
    /**
     * Render Hadith with book and number
     */
    private function renderHadith(string $book, string $number, ?string $grade, string $format): string
    {
        $bookInfo = $this->getBookInfo($book);
        
        if (!$bookInfo) {
            return $this->renderError('Invalid Hadith book: ' . $book);
        }
        
        $html = '<div class="hadith" data-book="' . htmlspecialchars($book) . '" data-number="' . htmlspecialchars($number) . '">';
        
        if ($format === 'full') {
            $html .= '<div class="hadith-header">';
            $html .= '<h4>Hadith from ' . htmlspecialchars($bookInfo['name']) . '</h4>';
            $html .= '<div class="hadith-reference">Number ' . htmlspecialchars($number) . '</div>';
            if ($grade) {
                $html .= '<div class="hadith-grade grade-' . strtolower($grade) . '">' . htmlspecialchars($grade) . '</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="hadith-content">';
            $html .= '<div class="arabic-text">' . $this->getHadithArabic($book, $number) . '</div>';
            $html .= '<div class="translation-text">' . $this->getHadithTranslation($book, $number) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="hadith-footer">';
            $html .= '<a href="/hadith/' . htmlspecialchars($book) . '/' . htmlspecialchars($number) . '" class="hadith-link">View Full Hadith</a>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="hadith-reference">' . htmlspecialchars($bookInfo['short']) . ':' . htmlspecialchars($number) . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Hadith chain
     */
    private function renderHadithChain(string $chain, string $format): string
    {
        $html = '<div class="hadith-chain">';
        
        if ($format === 'full') {
            $html .= '<div class="chain-header">';
            $html .= '<h4>Hadith Chain of Transmission</h4>';
            $html .= '</div>';
            
            $html .= '<div class="chain-content">';
            $html .= '<div class="chain-text">' . htmlspecialchars($chain) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="chain-footer">';
            $html .= '<span class="chain-info">Isnad (Chain of Narrators)</span>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="hadith-chain-compact">' . htmlspecialchars($chain) . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Hadith grade
     */
    private function renderHadithGrade(string $grade, ?string $narrator, string $format): string
    {
        $gradeInfo = $this->getGradeInfo($grade);
        
        $html = '<div class="hadith-grade-display grade-' . strtolower($grade) . '">';
        
        if ($format === 'full') {
            $html .= '<div class="grade-header">';
            $html .= '<h4>Hadith Grade: ' . htmlspecialchars($grade) . '</h4>';
            $html .= '</div>';
            
            $html .= '<div class="grade-content">';
            $html .= '<div class="grade-description">' . htmlspecialchars($gradeInfo['description']) . '</div>';
            if ($narrator) {
                $html .= '<div class="grade-narrator">Narrated by: ' . htmlspecialchars($narrator) . '</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="grade-footer">';
            $html .= '<span class="grade-level">Reliability: ' . htmlspecialchars($gradeInfo['level']) . '</span>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="grade-compact">' . htmlspecialchars($grade) . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get book information
     */
    private function getBookInfo(string $book): ?array
    {
        return $this->hadithBooks[$book] ?? null;
    }
    
    /**
     * Get grade information
     */
    private function getGradeInfo(string $grade): ?array
    {
        return $this->hadithGrades[$grade] ?? null;
    }
    
    /**
     * Get Hadith Arabic text
     */
    private function getHadithArabic(string $book, string $number): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return '<span class="arabic">Hadith Arabic text...</span>';
    }
    
    /**
     * Get Hadith translation
     */
    private function getHadithTranslation(string $book, string $number): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return 'Hadith translation text...';
    }
    
    /**
     * Render error message
     */
    private function renderError(string $message): string
    {
        return '<div class="hadith-error">
            <span class="error-icon">⚠️</span>
            <span class="error-message">' . htmlspecialchars($message) . '</span>
        </div>';
    }
    
    /**
     * Initialize Hadith books
     */
    private function initializeHadithBooks(): void
    {
        $this->hadithBooks = [
            'bukhari' => ['name' => 'Sahih al-Bukhari', 'short' => 'Bukhari', 'author' => 'Muhammad al-Bukhari'],
            'muslim' => ['name' => 'Sahih Muslim', 'short' => 'Muslim', 'author' => 'Muslim ibn al-Hajjaj'],
            'abudawud' => ['name' => 'Sunan Abi Dawud', 'short' => 'Abu Dawud', 'author' => 'Abu Dawud'],
            'tirmidhi' => ['name' => 'Jami at-Tirmidhi', 'short' => 'Tirmidhi', 'author' => 'Muhammad al-Tirmidhi'],
            'nasai' => ['name' => 'Sunan an-Nasai', 'short' => 'Nasai', 'author' => 'Ahmad an-Nasai'],
            'ibnmajah' => ['name' => 'Sunan Ibn Majah', 'short' => 'Ibn Majah', 'author' => 'Ibn Majah']
        ];
    }
    
    /**
     * Initialize Hadith grades
     */
    private function initializeHadithGrades(): void
    {
        $this->hadithGrades = [
            'Sahih' => ['description' => 'Authentic', 'level' => 'Highest'],
            'Hasan' => ['description' => 'Good', 'level' => 'High'],
            'Daif' => ['description' => 'Weak', 'level' => 'Low'],
            'Mawdu' => ['description' => 'Fabricated', 'level' => 'Rejected']
        ];
    }
    
    /**
     * Get available Hadith books
     */
    public function getAvailableBooks(): array
    {
        return $this->hadithBooks;
    }
    
    /**
     * Get available Hadith grades
     */
    public function getAvailableGrades(): array
    {
        return $this->hadithGrades;
    }
} 