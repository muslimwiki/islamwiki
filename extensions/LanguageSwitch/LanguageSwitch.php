<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\LanguageSwitch;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * LanguageSwitch Extension
 *
 * Provides comprehensive language switching functionality with support for
 * Arabic (RTL) and English (LTR) languages, with extensibility for more languages.
 */
class LanguageSwitch extends Extension
{
    /**
     * @var ShahidLogger Logger instance
     */
    private ShahidLogger $logger;

    /**
     * @var array Supported languages configuration
     */
    private array $supportedLanguages = [];

    /**
     * @var string Default language
     */
    private string $defaultLanguage = 'en';

    /**
     * @var array Extended language support
     */
    private array $extendedLanguages = [
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
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadDependencies();
        $this->loadConfiguration();
        $this->setupHooks();
        $this->setupResources();
    }

    /**
     * Load extension dependencies
     */
    private function loadDependencies(): void
    {
        $this->logger = new ShahidLogger($this->getExtensionPath() . '/logs');
    }

    /**
     * Load extension configuration
     */
    private function loadConfiguration(): void
    {
        $config = $this->getConfig();
        $this->supportedLanguages = $config['supportedLanguages'] ?? ['en', 'ar'];
        $this->defaultLanguage = $config['defaultLanguage'] ?? 'en';
    }

    /**
     * Setup extension hooks
     */
    private function setupHooks(): void
    {
        $hookManager = $this->getHookManager();
        
        if ($hookManager) {
            $hookManager->register('ContentParse', [$this, 'onContentParse']);
            $hookManager->register('PageDisplay', [$this, 'onPageDisplay']);
            $hookManager->register('ComposeViewGlobals', [$this, 'onComposeViewGlobals']);
        }
    }

    /**
     * Setup extension resources
     */
    private function setupResources(): void
    {
        // CSS and JS will be loaded automatically by the extension system
        $this->logger->info('LanguageSwitch extension resources loaded');
    }

    /**
     * Content parse hook handler
     */
    public function onContentParse(array $data): array
    {
        // Process content for language-specific formatting
        $this->logger->debug('LanguageSwitch: Processing content for language support');
        return $data;
    }

    /**
     * Page display hook handler
     */
    public function onPageDisplay(array $data): array
    {
        // Add language-specific page elements
        $this->logger->debug('LanguageSwitch: Adding language elements to page');
        return $data;
    }

    /**
     * Compose view globals hook handler
     */
    public function onComposeViewGlobals(array $globals): array
    {
        // Add language switching data to view globals
        $globals['language_switch'] = [
            'current_language' => $this->getCurrentLanguage(),
            'supported_languages' => $this->getSupportedLanguagesData(),
            'default_language' => $this->defaultLanguage,
            'is_rtl' => $this->isCurrentLanguageRTL()
        ];

        $this->logger->debug('LanguageSwitch: Added language data to view globals');
        return $globals;
    }

    /**
     * Get current language from session or default
     */
    private function getCurrentLanguage(): string
    {
        // For now, return default language
        // Later this can be enhanced to read from session/cookies
        return $this->defaultLanguage;
    }

    /**
     * Get supported languages data for the view
     */
    private function getSupportedLanguagesData(): array
    {
        $languages = [];
        
        foreach ($this->supportedLanguages as $code) {
            $languages[$code] = [
                'code' => $code,
                'name' => $this->getLanguageName($code),
                'native_name' => $this->getLanguageNativeName($code),
                'flag' => $this->getLanguageFlag($code),
                'is_rtl' => $this->isLanguageRTL($code)
            ];
        }
        
        return $languages;
    }

    /**
     * Get language display name
     */
    private function getLanguageName(string $code): string
    {
        $names = [
            'en' => 'English',
            'ar' => 'Arabic'
        ];
        
        return $names[$code] ?? $code;
    }

    /**
     * Get language native name
     */
    private function getLanguageNativeName(string $code): string
    {
        $nativeNames = [
            'en' => 'English',
            'ar' => 'العربية'
        ];
        
        return $nativeNames[$code] ?? $this->getLanguageName($code);
    }

    /**
     * Get language flag emoji
     */
    private function getLanguageFlag(string $code): string
    {
        $flags = [
            'en' => '🇺🇸',
            'ar' => '🇸🇦'
        ];
        
        return $flags[$code] ?? '🌐';
    }

    /**
     * Check if language is RTL
     */
    private function isLanguageRTL(string $code): bool
    {
        $rtlLanguages = ['ar', 'ur', 'fa', 'he'];
        return in_array($code, $rtlLanguages);
    }

    /**
     * Check if current language is RTL
     */
    private function isCurrentLanguageRTL(): bool
    {
        return $this->isLanguageRTL($this->getCurrentLanguage());
    }
} 