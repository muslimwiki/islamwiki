<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Multilingual Search Service
 * Supports Arabic, English, and other languages with proper RTL support
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class MultilingualSearch
{
    private Connection $db;
    private LoggerInterface $logger;
    private array $supportedLanguages;
    private array $languageConfigs;
    private array $translationCache;

    public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->translationCache = [];
        $this->initializeLanguages();
    }

    /**
     * Initialize supported languages and configurations
     */
    private function initializeLanguages(): void
    {
        $this->supportedLanguages = [
            'ar' => [
                'name' => 'العربية',
                'english_name' => 'Arabic',
                'direction' => 'rtl',
                'locale' => 'ar_SA',
                'charset' => 'utf8mb4',
                'enabled' => true
            ],
            'en' => [
                'name' => 'English',
                'english_name' => 'English',
                'direction' => 'ltr',
                'locale' => 'en_US',
                'charset' => 'utf8mb4',
                'enabled' => true
            ],
            'ur' => [
                'name' => 'اردو',
                'english_name' => 'Urdu',
                'direction' => 'rtl',
                'locale' => 'ur_PK',
                'charset' => 'utf8mb4',
                'enabled' => true
            ],
            'tr' => [
                'name' => 'Türkçe',
                'english_name' => 'Turkish',
                'direction' => 'ltr',
                'locale' => 'tr_TR',
                'charset' => 'utf8mb4',
                'enabled' => true
            ],
            'id' => [
                'name' => 'Bahasa Indonesia',
                'english_name' => 'Indonesian',
                'direction' => 'ltr',
                'locale' => 'id_ID',
                'charset' => 'utf8mb4',
                'enabled' => true
            ]
        ];

        $this->languageConfigs = [
            'ar' => [
                'stemming' => true,
                'diacritics' => true,
                'arabic_numbers' => true,
                'search_weights' => [
                    'title' => 3.0,
                    'content' => 1.0,
                    'arabic_text' => 2.5,
                    'translation' => 1.5
                ]
            ],
            'en' => [
                'stemming' => true,
                'diacritics' => false,
                'arabic_numbers' => false,
                'search_weights' => [
                    'title' => 3.0,
                    'content' => 1.0,
                    'arabic_text' => 1.0,
                    'translation' => 2.0
                ]
            ],
            'ur' => [
                'stemming' => true,
                'diacritics' => true,
                'arabic_numbers' => true,
                'search_weights' => [
                    'title' => 3.0,
                    'content' => 1.0,
                    'arabic_text' => 2.0,
                    'translation' => 1.5
                ]
            ]
        ];
    }

    /**
     * Perform multilingual search
     */
    public function search(string $query, string $language = 'en', array $options = []): array
    {
        try {
            $this->logger->info("Performing multilingual search", [
                'query' => $query,
                'language' => $language,
                'options' => $options
            ]);

            // Validate language
            if (!isset($this->supportedLanguages[$language])) {
                $language = 'en'; // Default to English
            }

            // Process query for the specific language
            $processedQuery = $this->processQueryForLanguage($query, $language);
            
            // Perform search in multiple languages if requested
            $searchLanguages = $this->getSearchLanguages($language, $options);
            
            $results = [];
            foreach ($searchLanguages as $searchLang) {
                $langResults = $this->searchInLanguage($processedQuery, $searchLang, $options);
                $results = array_merge($results, $langResults);
            }

            // Apply language-specific ranking
            $results = $this->applyLanguageRanking($results, $language);
            
            // Remove duplicates and sort
            $results = $this->deduplicateAndSort($results);
            
            $this->logger->info("Multilingual search completed", [
                'query' => $query,
                'language' => $language,
                'results_count' => count($results)
            ]);

            return $results;

        } catch (\Exception $e) {
            $this->logger->error("Multilingual search failed", [
                'error' => $e->getMessage(),
                'query' => $query,
                'language' => $language
            ]);

            return [];
        }
    }

    /**
     * Process query for specific language
     */
    private function processQueryForLanguage(string $query, string $language): string
    {
        $processedQuery = $query;

        switch ($language) {
            case 'ar':
                $processedQuery = $this->processArabicQuery($query);
                break;
                
            case 'ur':
                $processedQuery = $this->processUrduQuery($query);
                break;
                
            case 'en':
            default:
                $processedQuery = $this->processEnglishQuery($query);
                break;
        }

        return $processedQuery;
    }

    /**
     * Process Arabic query
     */
    private function processArabicQuery(string $query): string
    {
        // Remove diacritics if configured
        if (!$this->languageConfigs['ar']['diacritics']) {
            $query = $this->removeArabicDiacritics($query);
        }

        // Normalize Arabic text
        $query = $this->normalizeArabicText($query);

        // Handle Arabic numbers
        if ($this->languageConfigs['ar']['arabic_numbers']) {
            $query = $this->convertArabicNumbers($query);
        }

        return $query;
    }

    /**
     * Process Urdu query
     */
    private function processUrduQuery(string $query): string
    {
        // Normalize Urdu text
        $query = $this->normalizeUrduText($query);

        // Handle Urdu numbers
        if ($this->languageConfigs['ur']['arabic_numbers']) {
            $query = $this->convertArabicNumbers($query);
        }

        return $query;
    }

    /**
     * Process English query
     */
    private function processEnglishQuery(string $query): string
    {
        // Basic English text processing
        $query = trim($query);
        $query = strtolower($query);

        // Remove special characters but keep spaces
        $query = preg_replace('/[^a-z0-9\s]/', ' ', $query);
        $query = preg_replace('/\s+/', ' ', $query);

        return $query;
    }

    /**
     * Remove Arabic diacritics
     */
    private function removeArabicDiacritics(string $text): string
    {
        $diacritics = [
            'َ', 'ُ', 'ِ', 'ّ', 'ْ', 'ً', 'ٌ', 'ٍ', 'ٰ', 'ٱ'
        ];

        return str_replace($diacritics, '', $text);
    }

    /**
     * Normalize Arabic text
     */
    private function normalizeArabicText(string $text): string
    {
        // Normalize Arabic characters
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace(['ى'], 'ي', $text);
        $text = str_replace(['ة'], 'ه', $text);

        return $text;
    }

    /**
     * Normalize Urdu text
     */
    private function normalizeUrduText(string $text): string
    {
        // Basic Urdu text normalization
        $text = str_replace(['ے'], 'ی', $text);
        
        return $text;
    }

    /**
     * Convert Arabic/Urdu numbers to English
     */
    private function convertArabicNumbers(string $text): string
    {
        $arabicNumbers = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
        ];

        return str_replace(array_keys($arabicNumbers), array_values($arabicNumbers), $text);
    }

    /**
     * Get search languages based on options
     */
    private function getSearchLanguages(string $primaryLanguage, array $options): array
    {
        $languages = [$primaryLanguage];

        // Add cross-language search if enabled
        if (isset($options['cross_language']) && $options['cross_language']) {
            $languages = array_keys($this->supportedLanguages);
        }

        // Add specific languages if specified
        if (isset($options['languages']) && is_array($options['languages'])) {
            $languages = array_merge($languages, $options['languages']);
        }

        // Remove duplicates and ensure all languages are supported
        $languages = array_unique($languages);
        $languages = array_filter($languages, function($lang) {
            return isset($this->supportedLanguages[$lang]) && $this->supportedLanguages[$lang]['enabled'];
        });

        return $languages;
    }

    /**
     * Search in specific language
     */
    private function searchInLanguage(string $query, string $language, array $options): array
    {
        try {
            $config = $this->languageConfigs[$language] ?? $this->languageConfigs['en'];
            
            // Build search query with language-specific weights
            $searchQuery = $this->buildLanguageSearchQuery($query, $language, $config);
            
            // Execute search
            $sql = "
                SELECT 
                    id, title, content, type, url, language, relevance_score,
                    MATCH(title, content) AGAINST(? IN BOOLEAN MODE) as text_relevance,
                    last_updated
                FROM iqra_search_index 
                WHERE is_active = TRUE 
                AND language = ?
                AND MATCH(title, content) AGAINST(? IN BOOLEAN MODE)
                ORDER BY text_relevance DESC, relevance_score DESC
                LIMIT ?
            ";
            
            $limit = $options['limit'] ?? 20;
            $params = [$searchQuery, $language, $searchQuery, $limit];
            
            $results = $this->db->query($sql, $params);
            
            // Apply language-specific scoring
            foreach ($results as &$result) {
                $result['language_score'] = $this->calculateLanguageScore($result, $language, $config);
                $result['final_score'] = $result['text_relevance'] * $result['language_score'];
            }
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to search in language {$language}", [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            return [];
        }
    }

    /**
     * Build language-specific search query
     */
    private function buildLanguageSearchQuery(string $query, string $language, array $config): string
    {
        $searchTerms = explode(' ', $query);
        $weightedTerms = [];
        
        foreach ($searchTerms as $term) {
            if (strlen($term) < 2) continue;
            
            // Apply language-specific weights
            $weight = $this->getTermWeight($term, $language, $config);
            $weightedTerms[] = "+{$term}*^{$weight}";
        }
        
        return implode(' ', $weightedTerms);
    }

    /**
     * Get term weight for language
     */
    private function getTermWeight(string $term, string $language, array $config): float
    {
        $baseWeight = 1.0;
        
        // Check if term contains Arabic/Urdu characters
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $term)) {
            $baseWeight *= 1.5; // Boost Arabic/Urdu terms
        }
        
        // Apply language-specific weights
        if (isset($config['search_weights'])) {
            $baseWeight *= $config['search_weights']['content'];
        }
        
        return $baseWeight;
    }

    /**
     * Calculate language-specific score
     */
    private function calculateLanguageScore(array $result, string $language, array $config): float
    {
        $score = 1.0;
        
        // Boost content in the user's preferred language
        if ($result['language'] === $language) {
            $score *= 1.5;
        }
        
        // Boost Arabic text for Arabic searches
        if ($language === 'ar' && $this->containsArabicText($result['content'])) {
            $score *= 1.3;
        }
        
        // Boost recent content
        $daysOld = (time() - strtotime($result['last_updated'])) / (24 * 60 * 60);
        if ($daysOld < 30) {
            $score *= 1.2;
        }
        
        return $score;
    }

    /**
     * Check if content contains Arabic text
     */
    private function containsArabicText(string $content): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $content);
    }

    /**
     * Apply language-specific ranking
     */
    private function applyLanguageRanking(array $results, string $primaryLanguage): array
    {
        foreach ($results as &$result) {
            // Boost results in primary language
            if ($result['language'] === $primaryLanguage) {
                $result['final_score'] *= 1.5;
            }
            
            // Boost results with mixed language content
            if ($this->hasMixedLanguageContent($result)) {
                $result['final_score'] *= 1.2;
            }
        }
        
        return $results;
    }

    /**
     * Check if result has mixed language content
     */
    private function hasMixedLanguageContent(array $result): bool
    {
        $content = $result['title'] . ' ' . $result['content'];
        
        $hasArabic = preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $content);
        $hasEnglish = preg_match('/[a-zA-Z]/', $content);
        
        return $hasArabic && $hasEnglish;
    }

    /**
     * Remove duplicates and sort results
     */
    private function deduplicateAndSort(array $results): array
    {
        // Remove duplicates based on content ID
        $uniqueResults = [];
        $seenIds = [];
        
        foreach ($results as $result) {
            if (!in_array($result['id'], $seenIds)) {
                $uniqueResults[] = $result;
                $seenIds[] = $result['id'];
            }
        }
        
        // Sort by final score
        usort($uniqueResults, function($a, $b) {
            return ($b['final_score'] ?? 0) <=> ($a['final_score'] ?? 0);
        });
        
        return $uniqueResults;
    }

    /**
     * Get language suggestions for a query
     */
    public function getLanguageSuggestions(string $query): array
    {
        $suggestions = [];
        
        foreach ($this->supportedLanguages as $code => $language) {
            if (!$language['enabled']) continue;
            
            $suggestions[] = [
                'code' => $code,
                'name' => $language['name'],
                'english_name' => $language['english_name'],
                'direction' => $language['direction'],
                'url' => "/search?q=" . urlencode($query) . "&lang={$code}"
            ];
        }
        
        return $suggestions;
    }

    /**
     * Detect query language
     */
    public function detectQueryLanguage(string $query): string
    {
        // Count Arabic/Urdu characters
        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $query);
        
        // Count English characters
        $englishCount = preg_match_all('/[a-zA-Z]/', $query);
        
        // Count numbers
        $numberCount = preg_match_all('/[0-9]/', $query);
        
        // Determine primary language
        if ($arabicCount > $englishCount) {
            return 'ar'; // Arabic
        } elseif ($englishCount > $arabicCount) {
            return 'en'; // English
        } else {
            // Mixed or neutral, default to English
            return 'en';
        }
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get language configuration
     */
    public function getLanguageConfig(string $language): array
    {
        return $this->languageConfigs[$language] ?? $this->languageConfigs['en'];
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return isset($this->supportedLanguages[$language]) && 
               $this->supportedLanguages[$language]['enabled'];
    }

    /**
     * Get language direction
     */
    public function getLanguageDirection(string $language): string
    {
        return $this->supportedLanguages[$language]['direction'] ?? 'ltr';
    }

    /**
     * Translate search interface text
     */
    public function translate(string $text, string $language): string
    {
        $translations = [
            'search' => [
                'en' => 'Search',
                'ar' => 'بحث',
                'ur' => 'تلاش',
                'tr' => 'Ara',
                'id' => 'Cari'
            ],
            'search_placeholder' => [
                'en' => 'Search Islamic knowledge...',
                'ar' => 'ابحث في المعرفة الإسلامية...',
                'ur' => 'اسلامی علم تلاش کریں...',
                'tr' => 'İslami bilgi ara...',
                'id' => 'Cari pengetahuan Islam...'
            ],
            'search_button' => [
                'en' => 'Search',
                'ar' => 'بحث',
                'ur' => 'تلاش',
                'tr' => 'Ara',
                'id' => 'Cari'
            ],
            'results_found' => [
                'en' => 'results found',
                'ar' => 'نتيجة وجدت',
                'ur' => 'نتائج ملے',
                'tr' => 'sonuç bulundu',
                'id' => 'hasil ditemukan'
            ]
        ];
        
        if (isset($translations[$text][$language])) {
            return $translations[$text][$language];
        }
        
        return $text; // Return original text if no translation found
    }

    /**
     * Clear translation cache
     */
    public function clearCache(): void
    {
        $this->translationCache = [];
        $this->logger->info('Multilingual search cache cleared');
    }
} 