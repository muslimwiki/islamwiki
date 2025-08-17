<?php

declare(strict_types=1);

namespace IslamWiki\Core\Language;

/**
 * Translation Service for IslamWiki
 * 
 * This service handles:
 * - Loading translation files
 * - Translating interface text
 * - Managing translation keys
 * - Fallback to default language
 */
class TranslationService
{
    /**
     * @var array Loaded translations
     */
    private array $translations = [];

    /**
     * @var string Current language code
     */
    private string $currentLanguage;

    /**
     * @var string Default language code
     */
    private string $defaultLanguage = 'en';

    /**
     * @var string Path to translation files
     */
    private string $translationsPath;

    /**
     * Constructor
     */
    public function __construct(string $currentLanguage = 'en')
    {
        $this->currentLanguage = $currentLanguage;
        $this->translationsPath = $this->getTranslationsPath();
        $this->loadTranslations();
    }
    
    /**
     * Get translations path
     */
    private function getTranslationsPath(): string
    {
        // Try to get BASE_PATH from global scope
        if (defined('BASE_PATH')) {
            return BASE_PATH . '/languages/locale';
        }
        
        // Fallback: try to find the project root
        $currentDir = __DIR__;
        $projectRoot = dirname($currentDir, 4); // Go up 4 levels from TranslationService.php
        
        if (is_dir($projectRoot . '/languages/locale')) {
            return $projectRoot . '/languages/locale';
        }
        
        // If all else fails, use current directory
        return dirname($currentDir, 4) . '/languages/locale';
    }

    /**
     * Load translations for current language
     */
    private function loadTranslations(): void
    {
        try {
            // Load current language translations
            $this->loadLanguageTranslations($this->currentLanguage);
            
            // Load default language as fallback
            if ($this->currentLanguage !== $this->defaultLanguage) {
                $this->loadLanguageTranslations($this->defaultLanguage);
            }
        } catch (\Exception $e) {
            // Log error but don't crash
            error_log("TranslationService: Error loading translations: " . $e->getMessage());
            
            // Ensure we have at least empty translations
            $this->translations = [];
        }
    }

    /**
     * Load translations for a specific language
     */
    private function loadLanguageTranslations(string $language): void
    {
        try {
            $translationFile = $this->translationsPath . '/' . $language . '.json';
            
            if (file_exists($translationFile)) {
                $translations = json_decode(file_get_contents($translationFile), true);
                if (is_array($translations)) {
                    $this->translations[$language] = $translations;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't crash
            error_log("TranslationService: Error loading {$language} translations: " . $e->getMessage());
        }
    }

    /**
     * Translate a message key
     */
    public function translate(string $key, array $params = []): string
    {
        try {
            // Try current language first
            $message = $this->getMessage($this->currentLanguage, $key);
            
            // Fallback to default language
            if ($message === null && $this->currentLanguage !== $this->defaultLanguage) {
                $message = $this->getMessage($this->defaultLanguage, $key);
            }
            
            // If still no message, return the key
            if ($message === null) {
                return $key;
            }
            
            // Replace parameters
            return $this->replaceParameters($message, $params);
        } catch (\Exception $e) {
            // Log error but don't crash
            error_log("TranslationService: Error translating key '{$key}': " . $e->getMessage());
            
            // Return the key as fallback
            return $key;
        }
    }

    /**
     * Get message from specific language
     */
    private function getMessage(string $language, string $key): ?string
    {
        $keys = explode('.', $key);
        $value = $this->translations[$language] ?? [];
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return is_string($value) ? $value : null;
    }

    /**
     * Replace parameters in message
     */
    private function replaceParameters(string $message, array $params): string
    {
        foreach ($params as $key => $value) {
            $message = str_replace('{{' . $key . '}}', $value, $message);
        }
        
        return $message;
    }

    /**
     * Set current language
     */
    public function setLanguage(string $language): void
    {
        if ($this->currentLanguage !== $language) {
            $this->currentLanguage = $language;
            $this->translations = [];
            $this->loadTranslations();
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
     * Check if translation exists
     */
    public function hasTranslation(string $key): bool
    {
        return $this->getMessage($this->currentLanguage, $key) !== null;
    }

    /**
     * Get all available translation keys for current language
     */
    public function getAvailableKeys(): array
    {
        return array_keys($this->translations[$this->currentLanguage] ?? []);
    }
} 