<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Psr\Log\LoggerInterface;
use IslamWiki\Http\Middleware\SubdomainLanguageMiddleware;
use IslamWiki\Services\TranslationService;

/**
 * Language Controller
 * 
 * Handles language switching, redirects, and language-specific functionality.
 */
class LanguageController
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SubdomainLanguageMiddleware
     */
    private SubdomainLanguageMiddleware $languageMiddleware;

    /**
     * @var TranslationService
     */
    private TranslationService $translationService;

    /**
     * Constructor
     */
    public function __construct(
        LoggerInterface $logger,
        SubdomainLanguageMiddleware $languageMiddleware,
        TranslationService $translationService
    ) {
        $this->logger = $logger;
        $this->languageMiddleware = $languageMiddleware;
        $this->translationService = $translationService;
    }

    /**
     * Switch to a specific language
     */
    public function switchLanguage(Request $request, string $language): Response
    {
        if (!$this->languageMiddleware->isLanguageSupported($language)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Unsupported language',
                'supported_languages' => $this->languageMiddleware->getSupportedLanguages()
            ]));
        }

        // Get current path
        $currentPath = $request->getAttribute('params')['path'] ?? '/';
        
        // Generate language-specific URL
        $languageUrl = $this->languageMiddleware->generateLanguageUrl($language, $currentPath);
        
        // Set language preference in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
            $_SESSION['language_direction'] = $this->translationService->getLanguageDirection($language);
            $_SESSION['is_rtl'] = $this->translationService->isRTL($language);
        }

        $this->logger->info('Language switch requested', [
            'from' => $this->languageMiddleware->getCurrentLanguage(),
            'to' => $language,
            'path' => $currentPath,
            'redirect_url' => $languageUrl
        ]);

        // Redirect to language-specific subdomain
        return new Response(302, ['Location' => $languageUrl], '');
    }

    /**
     * Get current language information
     */
    public function getCurrentLanguage(Request $request): Response
    {
        $language = $this->languageMiddleware->getCurrentLanguage();
        $direction = $this->languageMiddleware->getCurrentLanguageDirection();
        $isRTL = $this->languageMiddleware->isCurrentLanguageRTL();

        $data = [
            'language' => $language,
            'direction' => $direction,
            'is_rtl' => $isRTL,
            'name' => $this->languageMiddleware->getSupportedLanguages()[$language] ?? 'Unknown',
            'base_domain' => $this->languageMiddleware->getBaseDomain()
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    /**
     * Get all available languages with URLs
     */
    public function getAvailableLanguages(Request $request): Response
    {
        $currentPath = $request->getAttribute('params')['path'] ?? '/';
        $languages = $this->languageMiddleware->getAllLanguageUrls($currentPath);

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($languages));
    }

    /**
     * Translate text to current language
     */
    public function translateText(Request $request): Response
    {
        $body = $request->getParsedBody();
        $text = $body['text'] ?? '';
        $targetLanguage = $body['target_language'] ?? $this->languageMiddleware->getCurrentLanguage();
        $sourceLanguage = $body['source_language'] ?? 'en';

        if (empty($text)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Text is required'
            ]));
        }

        $translatedText = $this->translationService->translate($text, $targetLanguage, $sourceLanguage);
        $quality = $this->translationService->getTranslationQuality($text, $translatedText, $targetLanguage);

        $response = [
            'original_text' => $text,
            'translated_text' => $translatedText,
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'quality_score' => $quality,
            'translation_source' => 'hybrid'
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    /**
     * Translate multiple texts in batch
     */
    public function translateBatch(Request $request): Response
    {
        $body = $request->getParsedBody();
        $texts = $body['texts'] ?? [];
        $targetLanguage = $body['target_language'] ?? $this->languageMiddleware->getCurrentLanguage();
        $sourceLanguage = $body['source_language'] ?? 'en';

        if (empty($texts) || !is_array($texts)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Texts array is required'
            ]));
        }

        $translatedTexts = $this->translationService->translateBatch($texts, $targetLanguage, $sourceLanguage);
        
        // Calculate quality scores
        $qualityScores = [];
        foreach ($texts as $index => $originalText) {
            $translatedText = $translatedTexts[$index] ?? '';
            $qualityScores[$index] = $this->translationService->getTranslationQuality($originalText, $translatedText, $targetLanguage);
        }

        $response = [
            'original_texts' => $texts,
            'translated_texts' => $translatedTexts,
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'quality_scores' => $qualityScores,
            'translation_source' => 'hybrid'
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(Request $request): Response
    {
        $stats = $this->translationService->getTranslationStats();
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($stats));
    }

    /**
     * Clear translation memory
     */
    public function clearTranslationMemory(Request $request): Response
    {
        $this->translationService->clearTranslationMemory();
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'message' => 'Translation memory cleared successfully'
        ]));
    }

    /**
     * Detect language from text
     */
    public function detectLanguage(Request $request): Response
    {
        $body = $request->getParsedBody();
        $text = $body['text'] ?? '';

        if (empty($text)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Text is required'
            ]));
        }

        // Simple language detection based on character sets
        $detectedLanguage = $this->detectLanguageFromText($text);
        
        $response = [
            'text' => substr($text, 0, 100) . (strlen($text) > 100 ? '...' : ''),
            'detected_language' => $detectedLanguage,
            'confidence' => $this->calculateLanguageConfidence($text, $detectedLanguage)
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    /**
     * Simple language detection from text
     */
    private function detectLanguageFromText(string $text): string
    {
        // Count Arabic characters
        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        
        // Count Hebrew characters
        $hebrewCount = preg_match_all('/[\x{0590}-\x{05FF}]/u', $text);
        
        // Count Persian characters (extends Arabic)
        $persianCount = preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $text);
        
        // Count Urdu characters (extends Arabic)
        $urduCount = preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $text);
        
        // Count Turkish characters
        $turkishCount = preg_match_all('/[çğıöşüÇĞIİÖŞÜ]/u', $text);
        
        // Count Indonesian/Malay characters
        $indonesianCount = preg_match_all('/[ng]/i', $text);

        $totalLength = strlen($text);
        
        if ($totalLength === 0) {
            return 'en';
        }

        // Calculate percentages
        $arabicPercent = ($arabicCount / $totalLength) * 100;
        $hebrewPercent = ($hebrewCount / $totalLength) * 100;
        $persianPercent = ($persianCount / $totalLength) * 100;
        $urduPercent = ($urduCount / $totalLength) * 100;
        $turkishPercent = ($turkishCount / $totalLength) * 100;
        $indonesianPercent = ($indonesianCount / $totalLength) * 100;

        // Determine language based on highest percentage
        if ($arabicPercent > 20) return 'ar';
        if ($hebrewPercent > 20) return 'he';
        if ($persianPercent > 20) return 'fa';
        if ($urduPercent > 20) return 'ur';
        if ($turkishPercent > 15) return 'tr';
        if ($indonesianPercent > 10) return 'id';

        // Default to English
        return 'en';
    }

    /**
     * Calculate confidence in language detection
     */
    private function calculateLanguageConfidence(string $text, string $detectedLanguage): float
    {
        $totalLength = strlen($text);
        if ($totalLength === 0) return 0.0;

        $characterCounts = [
            'ar' => preg_match_all('/[\x{0600}-\x{06FF}]/u', $text),
            'he' => preg_match_all('/[\x{0590}-\x{05FF}]/u', $text),
            'fa' => preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $text),
            'ur' => preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $text),
            'tr' => preg_match_all('/[çğıöşüÇĞIİÖŞÜ]/u', $text),
            'id' => preg_match_all('/[ng]/i', $text),
            'en' => preg_match_all('/[a-zA-Z]/', $text)
        ];

        $detectedCount = $characterCounts[$detectedLanguage] ?? 0;
        $confidence = ($detectedCount / $totalLength) * 100;

        return min(1.0, $confidence / 100);
    }
} 