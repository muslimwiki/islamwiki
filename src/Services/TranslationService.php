<?php

declare(strict_types=1);

namespace IslamWiki\Services;

use Psr\Log\LoggerInterface;

/**
 * Translation Service
 * 
 * Provides hybrid translation capabilities with Google Translate API integration
 * and local translation memory for better performance and cost management.
 */
class TranslationService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array Simple cache storage
     */
    private array $cache = [];

    /**
     * @var string Google Translate API key
     */
    private string $apiKey;

    /**
     * @var string Google Translate API endpoint
     */
    private string $apiEndpoint = 'https://translation.googleapis.com/language/translate/v2';

    /**
     * @var array Supported languages
     */
    private array $supportedLanguages = [
        'en' => 'English',
        'ar' => 'Arabic',
        'ur' => 'Urdu',
        'tr' => 'Turkish',
        'id' => 'Indonesian',
        'ms' => 'Malay',
        'fa' => 'Persian',
        'he' => 'Hebrew'
    ];

    /**
     * @var array Language direction mapping
     */
    private array $languageDirections = [
        'en' => 'ltr',
        'ar' => 'rtl',
        'ur' => 'rtl',
        'tr' => 'ltr',
        'id' => 'ltr',
        'ms' => 'ltr',
        'fa' => 'rtl',
        'he' => 'rtl'
    ];

    /**
     * @var array Translation memory cache
     */
    private array $translationMemory = [];

    /**
     * Constructor
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->apiKey = $_ENV['GOOGLE_TRANSLATE_API_KEY'] ?? '';
        
        // Load translation memory from cache
        $this->loadTranslationMemory();
    }

    /**
     * Translate text to target language
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        if (empty($text) || $targetLanguage === $sourceLanguage) {
            return $text;
        }

        // Check translation memory first
        $memoryKey = $this->getMemoryKey($text, $sourceLanguage, $targetLanguage);
        if (isset($this->translationMemory[$memoryKey])) {
            $this->logger->debug('Translation found in memory', [
                'text' => substr($text, 0, 50) . '...',
                'target' => $targetLanguage,
                'source' => 'memory'
            ]);
            return $this->translationMemory[$memoryKey];
        }

        // Check cache
        $cacheKey = "translation:{$sourceLanguage}:{$targetLanguage}:" . md5($text);
        if (isset($this->cache[$cacheKey])) {
            $this->logger->debug('Translation found in cache', [
                'text' => substr($text, 0, 50) . '...',
                'target' => $targetLanguage,
                'source' => 'cache'
            ]);
            return $this->cache[$cacheKey];
        }

        // Use Google Translate API
        $translation = $this->translateWithGoogleAPI($text, $targetLanguage, $sourceLanguage);
        
        if ($translation !== null) {
            // Store in cache and memory
            $this->cache[$cacheKey] = $translation;
            $this->translationMemory[$memoryKey] = $translation;
            $this->saveTranslationMemory();
            
            $this->logger->info('Translation completed via Google API', [
                'text' => substr($text, 0, 50) . '...',
                'target' => $targetLanguage,
                'source' => 'google_api'
            ]);
            
            return $translation;
        }

        // Fallback to original text
        $this->logger->warning('Translation failed, returning original text', [
            'text' => substr($text, 0, 50) . '...',
            'target' => $targetLanguage
        ]);
        
        return $text;
    }

    /**
     * Translate multiple texts in batch
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $results = [];
        $batchSize = 50; // Google Translate API batch limit
        
        foreach (array_chunk($texts, $batchSize) as $batch) {
            $batchResults = $this->translateBatchWithGoogleAPI($batch, $targetLanguage, $sourceLanguage);
            $results = array_merge($results, $batchResults);
        }
        
        return $results;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get language direction
     */
    public function getLanguageDirection(string $languageCode): string
    {
        return $this->languageDirections[$languageCode] ?? 'ltr';
    }

    /**
     * Check if language is RTL
     */
    public function isRTL(string $languageCode): bool
    {
        return $this->getLanguageDirection($languageCode) === 'rtl';
    }

    /**
     * Get translation quality score
     */
    public function getTranslationQuality(string $originalText, string $translatedText, string $targetLanguage): float
    {
        // Simple quality scoring based on text length preservation and common patterns
        $originalLength = strlen($originalText);
        $translatedLength = strlen($translatedText);
        
        if ($originalLength === 0) {
            return 0.0;
        }
        
        // Length ratio score (0.5 to 1.5 is acceptable)
        $lengthRatio = $translatedLength / $originalLength;
        $lengthScore = max(0, 1 - abs($lengthRatio - 1));
        
        // Check for common translation artifacts
        $artifactScore = 1.0;
        if (strpos($translatedText, '&lt;') !== false || strpos($translatedText, '&gt;') !== false) {
            $artifactScore *= 0.8; // HTML entities suggest encoding issues
        }
        
        if (strpos($translatedText, '...') !== false && strpos($originalText, '...') === false) {
            $artifactScore *= 0.9; // Unnecessary ellipsis
        }
        
        return min(1.0, $lengthScore * $artifactScore);
    }

    /**
     * Translate with Google Translate API
     */
    private function translateWithGoogleAPI(string $text, string $targetLanguage, string $sourceLanguage): ?string
    {
        if (empty($this->apiKey)) {
            $this->logger->warning('Google Translate API key not configured');
            return null;
        }

        try {
            $url = $this->apiEndpoint . '?key=' . urlencode($this->apiKey);
            
            $data = [
                'q' => $text,
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
                'format' => 'html'
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new \Exception('Failed to connect to Google Translate API');
            }

            $result = json_decode($response, true);
            
            if (isset($result['data']['translations'][0]['translatedText'])) {
                return $result['data']['translations'][0]['translatedText'];
            }

            $this->logger->error('Invalid response from Google Translate API', [
                'response' => $result
            ]);
            
            return null;

        } catch (\Exception $e) {
            $this->logger->error('Google Translate API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate batch with Google Translate API
     */
    private function translateBatchWithGoogleAPI(array $texts, string $targetLanguage, string $sourceLanguage): array
    {
        if (empty($this->apiKey)) {
            $this->logger->warning('Google Translate API key not configured');
            return array_fill(0, count($texts), '');
        }

        try {
            $url = $this->apiEndpoint . '?key=' . urlencode($this->apiKey);
            
            $data = [
                'q' => $texts,
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
                'format' => 'html'
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new \Exception('Failed to connect to Google Translate API');
            }

            $result = json_decode($response, true);
            
            if (isset($result['data']['translations'])) {
                return array_map(function($translation) {
                    return $translation['translatedText'] ?? '';
                }, $result['data']['translations']);
            }

            $this->logger->error('Invalid batch response from Google Translate API', [
                'response' => $result
            ]);
            
            return array_fill(0, count($texts), '');

        } catch (\Exception $e) {
            $this->logger->error('Google Translate API batch error: ' . $e->getMessage());
            return array_fill(0, count($texts), '');
        }
    }

    /**
     * Get memory key for translation
     */
    private function getMemoryKey(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        return md5($sourceLanguage . ':' . $targetLanguage . ':' . $text);
    }

    /**
     * Load translation memory from cache
     */
    private function loadTranslationMemory(): void
    {
        // This method is no longer needed as cache is now an array
    }

    /**
     * Save translation memory to cache
     */
    private function saveTranslationMemory(): void
    {
        // Keep only the most recent 1000 translations in memory
        if (count($this->translationMemory) > 1000) {
            $this->translationMemory = array_slice($this->translationMemory, -1000, 1000, true);
        }
        
        // This method is no longer needed as cache is now an array
    }

    /**
     * Clear translation memory
     */
    public function clearTranslationMemory(): void
    {
        $this->translationMemory = [];
        // This method is no longer needed as cache is now an array
        $this->logger->info('Translation memory cleared');
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(): array
    {
        return [
            'memory_size' => count($this->translationMemory),
            'supported_languages' => count($this->supportedLanguages),
            'api_configured' => !empty($this->apiKey),
            'cache_enabled' => true // Cache is now an array, so it's always enabled
        ];
    }
} 