<?php

declare(strict_types=1);

namespace IslamWiki\Core\Language;

/**
 * Core Language Service
 * 
 * This is part of the core framework and handles:
 * - Language detection from URLs
 * - Language routing and URL generation
 * - RTL support and language properties
 * - Session language management
 */
class LanguageService
{
    /**
     * Supported languages with their properties
     */
    private array $supportedLanguages = [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
            'isRTL' => false,
            'locale' => 'en_US'
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl',
            'isRTL' => true,
            'locale' => 'ar_SA'
        ],
        'tr' => [
            'name' => 'Turkish',
            'native' => 'Türkçe',
            'flag' => '🇹🇷',
            'direction' => 'ltr',
            'isRTL' => false,
            'locale' => 'tr_TR'
        ],
        'ur' => [
            'name' => 'Urdu',
            'native' => 'اردو',
            'flag' => '🇵🇰',
            'direction' => 'rtl',
            'isRTL' => true,
            'locale' => 'ur_PK'
        ],
        'id' => [
            'name' => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'direction' => 'ltr',
            'isRTL' => false,
            'locale' => 'id_ID'
        ],
        'ms' => [
            'name' => 'Malay',
            'native' => 'Bahasa Melayu',
            'flag' => '🇲🇾',
            'direction' => 'ltr',
            'isRTL' => false,
            'locale' => 'ms_MY'
        ],
        'fa' => [
            'name' => 'Persian',
            'native' => 'فارسی',
            'flag' => '🇮🇷',
            'direction' => 'rtl',
            'isRTL' => true,
            'locale' => 'fa_IR'
        ],
        'he' => [
            'name' => 'Hebrew',
            'native' => 'עברית',
            'flag' => '🇮🇱',
            'direction' => 'rtl',
            'isRTL' => true,
            'locale' => 'he_IL'
        ]
    ];

    /**
     * Default language
     */
    private string $defaultLanguage = 'en';

    /**
     * Current language
     */
    private string $currentLanguage;

    /**
     * @var TranslationService|null
     */
    private $translationService = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->currentLanguage = $this->defaultLanguage;
        // Don't create TranslationService here - lazy load it when needed
    }

    /**
     * Extract language from URL path
     */
    public function extractLanguageFromPath(string $path): string
    {
        // Remove leading slash
        $path = ltrim($path, '/');
        
        // Split path into segments
        $segments = explode('/', $path);
        
        // Check if first segment is a language code
        if (!empty($segments[0]) && array_key_exists($segments[0], $this->supportedLanguages)) {
            return $segments[0];
        }
        
        // Default to English if no language path found
        return $this->defaultLanguage;
    }

    /**
     * Set current language
     */
    public function setCurrentLanguage(string $language): void
    {
        if (array_key_exists($language, $this->supportedLanguages)) {
            $this->currentLanguage = $language;
            
            // Update translation service if it exists (only when explicitly requested)
            if ($this->translationService !== null) {
                try {
                    $this->translationService->setLanguage($language);
                } catch (\Exception $e) {
                    // Log error but don't crash
                    error_log("LanguageService: Error updating TranslationService language: " . $e->getMessage());
                }
            }
            
            // Set session language if session is active
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['language'] = $language;
                $_SESSION['language_direction'] = $this->supportedLanguages[$language]['direction'];
                $_SESSION['is_rtl'] = $this->supportedLanguages[$language]['isRTL'];
                $_SESSION['locale'] = $this->supportedLanguages[$language]['locale'];
            }
        }
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get current language info
     */
    public function getCurrentLanguageInfo(): array
    {
        return $this->supportedLanguages[$this->currentLanguage];
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRTL(): bool
    {
        return $this->supportedLanguages[$this->currentLanguage]['isRTL'];
    }

    /**
     * Get current language direction
     */
    public function getCurrentLanguageDirection(): string
    {
        return $this->supportedLanguages[$this->currentLanguage]['direction'];
    }

    /**
     * Get current language locale
     */
    public function getCurrentLanguageLocale(): string
    {
        return $this->supportedLanguages[$this->currentLanguage]['locale'];
    }

    /**
     * Generate URL for specific language
     */
    public function generateLanguageUrl(string $language, string $path = '/'): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'local.islam.wiki';
        
        if ($language === $this->defaultLanguage) {
            // For default language, use base domain
            return $protocol . '://' . $host . $path;
        }
        
        // For other languages, use language path
        return $protocol . '://' . $host . '/' . $language . $path;
    }

    /**
     * Add language prefix to path
     */
    public function addLanguagePrefix(string $path, string $language): string
    {
        if ($language === $this->defaultLanguage) {
            return $path;
        }
        
        return '/' . $language . $path;
    }

    /**
     * Remove language prefix from path
     */
    public function removeLanguagePrefix(string $path): string
    {
        $path = ltrim($path, '/');
        $segments = explode('/', $path);
        
        // If first segment is a language code, remove it
        if (!empty($segments[0]) && array_key_exists($segments[0], $this->supportedLanguages)) {
            array_shift($segments);
            return '/' . implode('/', $segments);
        }
        
        return '/' . $path;
    }

    /**
     * Get all language URLs for current page
     */
    public function getAllLanguageUrls(string $currentPath = '/'): array
    {
        $urls = [];
        
        foreach ($this->supportedLanguages as $code => $info) {
            $urls[$code] = [
                'code' => $code,
                'name' => $info['name'],
                'native' => $info['native'],
                'flag' => $info['flag'],
                'url' => $this->generateLanguageUrl($code, $currentPath),
                'is_current' => $code === $this->currentLanguage,
                'is_rtl' => $info['isRTL'],
                'direction' => $info['direction'],
                'locale' => $info['locale']
            ];
        }
        
        return $urls;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return array_key_exists($language, $this->supportedLanguages);
    }

    /**
     * Get language info by code
     */
    public function getLanguageInfo(string $language): ?array
    {
        return $this->supportedLanguages[$language] ?? null;
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * Get language name by code
     */
    public function getLanguageName(string $language): string
    {
        return $this->supportedLanguages[$language]['name'] ?? 'Unknown';
    }

    /**
     * Get native language name by code
     */
    public function getNativeLanguageName(string $language): string
    {
        return $this->supportedLanguages[$language]['native'] ?? 'Unknown';
    }

    /**
     * Get language flag by code
     */
    public function getLanguageFlag(string $language): string
    {
        return $this->supportedLanguages[$language]['flag'] ?? '🌐';
    }

    /**
     * Get translation service
     */
    public function getTranslationService()
    {
        if (!isset($this->translationService)) {
            // Return null for now to avoid crashes
            $this->translationService = null;
        }
        
        return $this->translationService;
    }
    
    /**
     * Set translation service
     */
    public function setTranslationService(TranslationService $translationService): void
    {
        $this->translationService = $translationService;
        // Update the translation service with current language
        if ($this->currentLanguage) {
            $this->translationService->setLanguage($this->currentLanguage);
        }
    }

    /**
     * Create a fallback translation service
     */
    private function createFallbackTranslationService()
    {
        // Return null for now to avoid crashes
        return null;
    }

    /**
     * Initialize language from session or URL
     */
    public function initializeLanguage(): void
    {
        // Check session first
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $this->setCurrentLanguage($_SESSION['language']);
            return;
        }

        // Check URL path
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $language = $this->extractLanguageFromPath($uri);
        $this->setCurrentLanguage($language);
    }
} 