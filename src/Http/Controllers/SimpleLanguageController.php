<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Simple Language Controller
 * 
 * Handles language switching and translation requests without complex dependencies.
 * This is a simplified version that works with the current routing system.
 */
class SimpleLanguageController
{
    /**
     * @var array Supported languages configuration
     */
    private array $supportedLanguages = [
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸', 'direction' => 'ltr', 'isRTL' => false],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇸🇦', 'direction' => 'rtl', 'isRTL' => true],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'flag' => '🇵🇰', 'direction' => 'rtl', 'isRTL' => true],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷', 'direction' => 'ltr', 'isRTL' => false],
        'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'direction' => 'ltr', 'isRTL' => false],
        'ms' => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'flag' => '🇲🇾', 'direction' => 'ltr', 'isRTL' => false],
        'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'flag' => '🇮🇷', 'direction' => 'rtl', 'isRTL' => true],
        'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'flag' => '🇮🇱', 'direction' => 'rtl', 'isRTL' => true]
    ];

    /**
     * @var string Default language
     */
    private string $defaultLanguage = 'en';

    /**
     * @var string Base domain
     */
    private string $baseDomain = 'local.islam.wiki';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Extract base domain from environment or use default
        $this->baseDomain = $_ENV['BASE_DOMAIN'] ?? 'local.islam.wiki';
    }

    /**
     * Get current language information
     */
    public function getCurrentLanguage(Request $request): Response
    {
        $language = $this->detectLanguageFromRequest($request);
        $languageData = $this->supportedLanguages[$language] ?? $this->supportedLanguages[$this->defaultLanguage];

        $data = [
            'language' => $language,
            'direction' => $languageData['direction'],
            'is_rtl' => $languageData['isRTL'],
            'name' => $languageData['name'],
            'native' => $languageData['native'],
            'flag' => $languageData['flag'],
            'base_domain' => $this->baseDomain
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    /**
     * Get all available languages with URLs
     */
    public function getAvailableLanguages(Request $request): Response
    {
        $currentPath = $request->getAttribute('params')['path'] ?? '/';
        $languages = [];

        foreach ($this->supportedLanguages as $code => $lang) {
            $languages[$code] = [
                'code' => $code,
                'name' => $lang['name'],
                'native' => $lang['native'],
                'flag' => $lang['flag'],
                'direction' => $lang['direction'],
                'is_rtl' => $lang['isRTL'],
                'url' => $this->generateLanguageUrl($code, $currentPath)
            ];
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($languages));
    }

    /**
     * Switch to a specific language
     */
    public function switchLanguage(Request $request, string $language): Response
    {
        if (!isset($this->supportedLanguages[$language])) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Unsupported language',
                'supported_languages' => array_keys($this->supportedLanguages)
            ]));
        }

        // Get current path
        $currentPath = $request->getAttribute('params')['path'] ?? '/';
        
        // Generate language-specific URL
        $languageUrl = $this->generateLanguageUrl($language, $currentPath);
        
        // Set language preference in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
            $_SESSION['language_direction'] = $this->supportedLanguages[$language]['direction'];
            $_SESSION['is_rtl'] = $this->supportedLanguages[$language]['isRTL'];
        }

        // Store in localStorage via JavaScript
        $response = [
            'success' => true,
            'language' => $language,
            'redirect_url' => $languageUrl,
            'message' => 'Language switched successfully'
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    /**
     * Translate text to current language
     */
    public function translateText(Request $request): Response
    {
        // Get raw input and parse JSON
        $rawInput = file_get_contents('php://input');
        $body = json_decode($rawInput, true) ?: [];
        
        $text = $body['text'] ?? '';
        $targetLanguage = $body['target_language'] ?? $this->defaultLanguage;
        $sourceLanguage = $body['source_language'] ?? 'en';

        if (empty($text)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Text is required',
                'received_data' => $body,
                'raw_input' => $rawInput
            ]));
        }

        // For now, return a simple response
        // In the future, this will integrate with Google Translate API
        $response = [
            'original_text' => $text,
            'translated_text' => $text, // Placeholder for actual translation
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'quality_score' => 1.0,
            'translation_method' => 'placeholder'
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(Request $request): Response
    {
        $stats = [
            'total_languages' => count($this->supportedLanguages),
            'supported_languages' => array_keys($this->supportedLanguages),
            'default_language' => $this->defaultLanguage,
            'base_domain' => $this->baseDomain,
            'system_status' => 'operational',
            'translation_service' => 'placeholder',
            'cache_status' => 'enabled'
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($stats));
    }

    /**
     * Handle language-specific home page
     */
    public function languageHome(Request $request, string $language): Response
    {
        if (!isset($this->supportedLanguages[$language])) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Language not supported',
                'supported_languages' => array_keys($this->supportedLanguages)
            ]));
        }

        // Set language in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
            $_SESSION['language_direction'] = $this->supportedLanguages[$language]['direction'];
            $_SESSION['is_rtl'] = $this->supportedLanguages[$language]['isRTL'];
        }

        $languageData = $this->supportedLanguages[$language];
        
        $data = [
            'language' => $language,
            'direction' => $languageData['direction'],
            'is_rtl' => $languageData['isRTL'],
            'name' => $languageData['name'],
            'native' => $languageData['native'],
            'flag' => $languageData['flag'],
            'message' => 'Language-specific home page',
            'current_host' => $_SERVER['HTTP_HOST'] ?? $this->baseDomain
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    /**
     * Detect language from request
     */
    private function detectLanguageFromRequest(Request $request): string
    {
        // Check session first
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $sessionLang = $_SESSION['language'];
            if (isset($this->supportedLanguages[$sessionLang])) {
                return $sessionLang;
            }
        }

        // Check URI path for language prefix
        $uri = $request->getUri()->getPath();
        $uri = ltrim($uri, '/');
        $segments = explode('/', $uri);
        
        // Check if first segment is a language code
        if (!empty($segments[0]) && isset($this->supportedLanguages[$segments[0]])) {
            return $segments[0];
        }

        // Check hostname for subdomain (legacy support)
        $host = $request->getHeaderLine('Host');
        if ($host) {
            foreach (array_keys($this->supportedLanguages) as $code) {
                if (strpos($host, $code . '.') === 0) {
                    return $code;
                }
            }
        }

        // Check Accept-Language header
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        if ($acceptLanguage) {
            $languages = explode(',', $acceptLanguage);
            foreach ($languages as $lang) {
                $lang = trim(explode(';', $lang)[0]);
                $langCode = substr($lang, 0, 2);
                if (isset($this->supportedLanguages[$langCode])) {
                    return $langCode;
                }
            }
        }

        return $this->defaultLanguage;
    }

    /**
     * Generate language-specific URL
     */
    private function generateLanguageUrl(string $language, string $path): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $this->baseDomain;
        
        if ($language === $this->defaultLanguage) {
            // For default language, use base domain
            return "{$protocol}://{$host}{$path}";
        }

        // For other languages, use language path
        return "{$protocol}://{$host}/{$language}{$path}";
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return isset($this->supportedLanguages[$language]);
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get base domain
     */
    public function getBaseDomain(): string
    {
        return $this->baseDomain;
    }
} 