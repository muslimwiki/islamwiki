<?php

declare(strict_types=1);

namespace IslamWiki\Core\View;

use IslamWiki\Core\Language\TranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig Extension for Translation
 * 
 * Provides translation functions in Twig templates:
 * - {{ __('nav.home') }} - Translate a key
 * - {{ _t('nav.home', 'Home') }} - Translate with fallback
 * - {{ _n('count', '1 item', '{{count}} items') }} - Pluralization
 */
class TwigTranslationExtension extends AbstractExtension
{
    /**
     * @var TranslationService
     */
    private TranslationService $translationService;

    /**
     * Constructor
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Get functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('__', [$this, 'translate']),
            new TwigFunction('_t', [$this, 'translateWithFallback']),
            new TwigFunction('_n', [$this, 'pluralize']),
            new TwigFunction('_l', [$this, 'getLanguage']),
            new TwigFunction('_d', [$this, 'getDirection']),
        ];
    }

    /**
     * Translate a key
     */
    public function translate(string $key, array $params = []): string
    {
        return $this->translationService->translate($key, $params);
    }

    /**
     * Translate with fallback
     */
    public function translateWithFallback(string $key, string $fallback, array $params = []): string
    {
        $translation = $this->translationService->translate($key, $params);
        
        // If translation is the same as key, return fallback
        if ($translation === $key) {
            return $fallback;
        }
        
        return $translation;
    }

    /**
     * Pluralize based on count
     */
    public function pluralize(int $count, string $singular, string $plural, array $params = []): string
    {
        $key = $count === 1 ? $singular : $plural;
        $params['count'] = $count;
        
        return $this->translationService->translate($key, $params);
    }

    /**
     * Get current language code
     */
    public function getLanguage(): string
    {
        return $this->translationService->getCurrentLanguage();
    }

    /**
     * Get current language direction
     */
    public function getDirection(): string
    {
        $language = $this->translationService->getCurrentLanguage();
        
        // RTL languages
        $rtlLanguages = ['ar', 'ur', 'fa', 'he'];
        
        return in_array($language, $rtlLanguages) ? 'rtl' : 'ltr';
    }

    /**
     * Get the translation service
     */
    public function getTranslationService(): TranslationService
    {
        return $this->translationService;
    }

    /**
     * Update the language of the translation service
     */
    public function updateLanguage(string $language): void
    {
        $this->translationService->setLanguage($language);
    }
} 