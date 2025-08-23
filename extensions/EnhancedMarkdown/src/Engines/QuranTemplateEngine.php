<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Engines;

/**
 * Quran Template Engine
 * 
 * Handles Quran-specific template rendering for IslamWiki including:
 * - Quran verse templates: {{Quran|surah=1|ayah=1-7}}
 * - Quran chapter templates: {{Quran|surah=1}}
 * - Translation support and formatting
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class QuranTemplateEngine
{
    private array $quranData = [];
    private array $translations = [];
    
    public function __construct()
    {
        $this->initializeQuranData();
        $this->initializeTranslations();
    }
    
    /**
     * Render Quran template
     * 
     * @param array $params Template parameters
     * @return string Rendered HTML content
     */
    public function render(array $params): string
    {
        $surah = $params['surah'] ?? null;
        $ayah = $params['ayah'] ?? null;
        $translation = $params['translation'] ?? 'en';
        $format = $params['format'] ?? 'full';
        
        if (!$surah) {
            return $this->renderError('Surah parameter is required');
        }
        
        if ($ayah) {
            return $this->renderVerse($surah, $ayah, $translation, $format);
        } else {
            return $this->renderChapter($surah, $translation, $format);
        }
    }
    
    /**
     * Render Quran verse
     */
    private function renderVerse(string $surah, string $ayah, string $translation, string $format): string
    {
        $surahNumber = (int)$surah;
        $surahName = $this->getSurahName($surahNumber);
        
        if (!$surahName) {
            return $this->renderError('Invalid surah number: ' . $surah);
        }
        
        $html = '<div class="quran-verse" data-surah="' . $surahNumber . '" data-ayah="' . htmlspecialchars($ayah) . '">';
        
        if ($format === 'full') {
            $html .= '<div class="verse-header">';
            $html .= '<h4>Quran ' . htmlspecialchars($surahName) . ' (' . $surahNumber . ')</h4>';
            $html .= '<div class="verse-reference">Ayah ' . htmlspecialchars($ayah) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="verse-content">';
            $html .= '<div class="arabic-text">' . $this->getArabicText($surahNumber, $ayah) . '</div>';
            $html .= '<div class="translation-text">' . $this->getTranslation($surahNumber, $ayah, $translation) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="verse-footer">';
            $html .= '<a href="/quran/' . $surahNumber . '/' . htmlspecialchars($ayah) . '" class="verse-link">View Full Verse</a>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="quran-reference">' . htmlspecialchars($surahName) . ':' . htmlspecialchars($ayah) . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Quran chapter
     */
    private function renderChapter(string $surah, string $translation, string $format): string
    {
        $surahNumber = (int)$surah;
        $surahName = $this->getSurahName($surahNumber);
        
        if (!$surahName) {
            return $this->renderError('Invalid surah number: ' . $surah);
        }
        
        $html = '<div class="quran-chapter" data-surah="' . $surahNumber . '">';
        
        if ($format === 'full') {
            $html .= '<div class="chapter-header">';
            $html .= '<h4>Quran ' . htmlspecialchars($surahName) . ' (' . $surahNumber . ')</h4>';
            $html .= '<div class="chapter-info">';
            $html .= '<span class="chapter-type">' . $this->getChapterType($surahNumber) . '</span>';
            $html .= '<span class="verse-count">' . $this->getVerseCount($surahNumber) . ' verses</span>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<div class="chapter-content">';
            $html .= '<div class="arabic-text">' . $this->getChapterArabic($surahNumber) . '</div>';
            $html .= '<div class="translation-text">' . $this->getChapterTranslation($surahNumber, $translation) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="chapter-footer">';
            $html .= '<a href="/quran/' . $surahNumber . '" class="chapter-link">Read Full Chapter</a>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="quran-reference">' . htmlspecialchars($surahName) . ' (' . $surahNumber . ')</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get surah name by number
     */
    private function getSurahName(int $surahNumber): ?string
    {
        return $this->quranData[$surahNumber]['name'] ?? null;
    }
    
    /**
     * Get chapter type (Meccan/Medinan)
     */
    private function getChapterType(int $surahNumber): string
    {
        return $this->quranData[$surahNumber]['type'] ?? 'Unknown';
    }
    
    /**
     * Get verse count for a chapter
     */
    private function getVerseCount(int $surahNumber): int
    {
        return $this->quranData[$surahNumber]['verses'] ?? 0;
    }
    
    /**
     * Get Arabic text for a verse
     */
    private function getArabicText(int $surahNumber, string $ayah): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return '<span class="arabic">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>';
    }
    
    /**
     * Get Arabic text for a chapter
     */
    private function getChapterArabic(int $surahNumber): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return '<span class="arabic">Chapter Arabic text...</span>';
    }
    
    /**
     * Get translation for a verse
     */
    private function getTranslation(int $surahNumber, string $ayah, string $translation): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return 'In the name of Allah, the Entirely Merciful, the Especially Merciful.';
    }
    
    /**
     * Get translation for a chapter
     */
    private function getChapterTranslation(int $surahNumber, string $translation): string
    {
        // Placeholder - in real implementation, this would fetch from database
        return 'Chapter translation text...';
    }
    
    /**
     * Render error message
     */
    private function renderError(string $message): string
    {
        return '<div class="quran-error">
            <span class="error-icon">⚠️</span>
            <span class="error-message">' . htmlspecialchars($message) . '</span>
        </div>';
    }
    
    /**
     * Initialize Quran data
     */
    private function initializeQuranData(): void
    {
        // Basic surah information - in real implementation, this would come from database
        $this->quranData = [
            1 => ['name' => 'Al-Fatiha', 'type' => 'Meccan', 'verses' => 7],
            2 => ['name' => 'Al-Baqarah', 'type' => 'Medinan', 'verses' => 286],
            3 => ['name' => 'Aal-Imran', 'type' => 'Medinan', 'verses' => 200],
            // Add more surahs as needed
        ];
    }
    
    /**
     * Initialize translations
     */
    private function initializeTranslations(): void
    {
        $this->translations = [
            'en' => 'English',
            'ar' => 'Arabic',
            'ur' => 'Urdu',
            'tr' => 'Turkish',
            'id' => 'Indonesian',
            'ms' => 'Malay'
        ];
    }
    
    /**
     * Get available translations
     */
    public function getAvailableTranslations(): array
    {
        return $this->translations;
    }
    
    /**
     * Get Quran data
     */
    public function getQuranData(): array
    {
        return $this->quranData;
    }
} 