<?php

declare(strict_types=1);

namespace IslamWiki\Services;

/**
 * Translation Service
 * 
 * Provides language support functionality including RTL detection,
 * language direction, and translation management.
 */
class TranslationService
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
            'isRTL' => false
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl',
            'isRTL' => true
        ],
        'tr' => [
            'name' => 'Turkish',
            'native' => 'Türkçe',
            'flag' => '🇹🇷',
            'direction' => 'ltr',
            'isRTL' => false
        ],
        'ur' => [
            'name' => 'Urdu',
            'native' => 'اردو',
            'flag' => '🇵🇰',
            'direction' => 'rtl',
            'isRTL' => true
        ],
        'id' => [
            'name' => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'direction' => 'ltr',
            'isRTL' => false
        ],
        'ms' => [
            'name' => 'Malay',
            'native' => 'Bahasa Melayu',
            'flag' => '🇲🇾',
            'direction' => 'ltr',
            'isRTL' => false
        ],
        'fa' => [
            'name' => 'Persian',
            'native' => 'فارسی',
            'flag' => '🇮🇷',
            'direction' => 'rtl',
            'isRTL' => true
        ],
        'he' => [
            'name' => 'Hebrew',
            'native' => 'עברית',
            'flag' => '🇮🇱',
            'direction' => 'rtl',
            'isRTL' => true
        ]
    ];

    /**
     * Default language
     */
    private string $defaultLanguage = 'en';

    /**
     * Check if a language is RTL
     */
    public function isRTL(string $language): bool
    {
        return $this->supportedLanguages[$language]['isRTL'] ?? false;
    }

    /**
     * Get language direction
     */
    public function getLanguageDirection(string $language): string
    {
        return $this->supportedLanguages[$language]['direction'] ?? 'ltr';
    }

    /**
     * Get language name
     */
    public function getLanguageName(string $language): string
    {
        return $this->supportedLanguages[$language]['name'] ?? 'Unknown';
    }

    /**
     * Get native language name
     */
    public function getNativeLanguageName(string $language): string
    {
        return $this->supportedLanguages[$language]['native'] ?? 'Unknown';
    }

    /**
     * Get language flag
     */
    public function getLanguageFlag(string $language): string
    {
        return $this->supportedLanguages[$language]['flag'] ?? '🌐';
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return array_key_exists($language, $this->supportedLanguages);
    }

    /**
     * Get all supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * Get language info
     */
    public function getLanguageInfo(string $language): ?array
    {
        return $this->supportedLanguages[$language] ?? null;
    }
} 