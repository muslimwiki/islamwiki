<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\TranslatorExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Language\LanguageService;

/**
 * Translator Extension
 * 
 * Provides translation functionality between different language versions of content.
 * Shows translation sidebar on pages like /en/wiki/allah to enable translation to /ar/wiki/allah
 */
class TranslatorExtension extends Extension
{
    /**
     * @var LanguageService
     */
    private LanguageService $languageService;

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->languageService = $this->container->get(LanguageService::class);
        $this->setupTranslationHooks();
    }

    /**
     * Setup translation-related hooks
     */
    private function setupTranslationHooks(): void
    {
        $hookManager = $this->getHookManager();
        
        if ($hookManager) {
            // Hook into page display to add translation sidebar
            $hookManager->register('PageDisplay', [$this, 'onPageDisplay']);
            
            // Hook into content parsing for translation opportunities
            $hookManager->register('ContentParse', [$this, 'onContentParse']);
            
            // Hook into sidebar rendering to add translation widget
            $hookManager->register('SidebarRender', [$this, 'onSidebarRender']);
        }
    }

    /**
     * Hook: PageDisplay - Add translation functionality to page display
     */
    public function onPageDisplay(array $pageData, array $context): array
    {
        // Add translation metadata to page data
        $pageData['translation_info'] = $this->getTranslationInfo($pageData, $context);
        
        return $pageData;
    }

    /**
     * Hook: ContentParse - Process content for translation opportunities
     */
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        // Add translation markers to content
        $content = $this->addTranslationMarkers($content);
        
        return $content;
    }

    /**
     * Hook: SidebarRender - Add translation sidebar widget
     */
    public function onSidebarRender(array $sidebarData, array $context): array
    {
        // Add translation widget to sidebar
        $sidebarData['translator_widget'] = $this->renderTranslatorWidget($context);
        
        return $sidebarData;
    }

    /**
     * Get translation information for a page
     */
    private function getTranslationInfo(array $pageData, array $context): array
    {
        $currentLanguage = $this->languageService->getCurrentLanguage();
        $currentPath = $context['current_path'] ?? '/';
        
        // Get all available language versions for this page
        $languageUrls = $this->languageService->getAllLanguageUrls($currentPath);
        
        // Find the source language (usually English) and target languages
        $sourceLanguage = $this->getSourceLanguage($currentLanguage);
        $targetLanguages = $this->getTargetLanguages($currentLanguage);
        
        return [
            'current_language' => $currentLanguage,
            'source_language' => $sourceLanguage,
            'target_languages' => $targetLanguages,
            'language_urls' => $languageUrls,
            'can_translate' => $this->canUserTranslate(),
            'translation_status' => $this->getTranslationStatus($pageData, $currentLanguage)
        ];
    }

    /**
     * Get source language for translation
     */
    private function getSourceLanguage(string $currentLanguage): string
    {
        // Default source language is English
        $defaultSource = 'en';
        
        // If current language is not English, use it as source
        if ($currentLanguage !== 'en') {
            return $currentLanguage;
        }
        
        return $defaultSource;
    }

    /**
     * Get target languages for translation
     */
    private function getTargetLanguages(string $currentLanguage): array
    {
        $supportedLanguages = $this->languageService->getSupportedLanguages();
        $targetLanguages = [];
        
        foreach ($supportedLanguages as $code => $info) {
            if ($code !== $currentLanguage) {
                $targetLanguages[$code] = $info;
            }
        }
        
        return $targetLanguages;
    }

    /**
     * Check if current user can translate
     */
    private function canUserTranslate(): bool
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check user permissions (simplified for now)
        return true;
    }

    /**
     * Get translation status for a page
     */
    private function getTranslationStatus(array $pageData, string $language): array
    {
        // This would check the database for existing translations
        // For now, return a basic structure
        return [
            'has_translation' => false,
            'translation_quality' => 0.0,
            'last_updated' => null,
            'translator' => null
        ];
    }

    /**
     * Add translation markers to content
     */
    private function addTranslationMarkers(string $content): string
    {
        // Add translation markers for important terms
        $content = preg_replace(
            '/(\b(Allah|God|Prophet|Muhammad|Islam|Muslim|Quran|Hadith|Sunnah|Shariah|Halal|Haram)\b)/i',
            '<span class="translatable-term" data-term="$1">$1</span>',
            $content
        );
        
        return $content;
    }

    /**
     * Render the translator widget for sidebar
     */
    private function renderTranslatorWidget(array $context): string
    {
        $currentLanguage = $this->languageService->getCurrentLanguage();
        $currentPath = $context['current_path'] ?? '/';
        
        // Get available languages for this page
        $languageUrls = $this->languageService->getAllLanguageUrls($currentPath);
        
        // Render the widget template
        return $this->renderTemplate('translator-sidebar', [
            'current_language' => $currentLanguage,
            'language_urls' => $languageUrls,
            'can_translate' => $this->canUserTranslate(),
            'page_path' => $this->languageService->removeLanguagePrefix($currentPath)
        ]);
    }

    /**
     * Render a template with the given data
     */
    private function renderTemplate(string $templateName, array $data): string
    {
        $templatePath = $this->getExtensionPath() . '/templates/' . $templateName . '.twig';
        
        if (file_exists($templatePath)) {
            // Simple template rendering for now
            $content = file_get_contents($templatePath);
            
            // Replace variables
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $content = str_replace('{{ ' . $key . ' }}', $value, $content);
                }
            }
            
            return $content;
        }
        
        return '';
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if extension is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
} 