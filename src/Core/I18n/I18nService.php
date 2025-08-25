<?php

declare(strict_types=1);

namespace IslamWiki\Core\I18n;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Internationalization Service
 * 
 * Handles language detection, routing, and internationalization
 * for the IslamWiki platform.
 */
class I18nService
{
    /**
     * @var Container
     */
    protected Container $container;
    
    /**
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * @var array
     */
    protected array $config;
    
    /**
     * @var string
     */
    protected string $currentLanguage;
    
    /**
     * @var array
     */
    protected array $supportedLanguages;
    
    /**
     * Constructor
     */
    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->loadConfig();
    }
    
    /**
     * Load i18n configuration
     */
    protected function loadConfig(): void
    {
        $configPath = dirname(__DIR__, 2) . '/i18n/config.php';
        if (file_exists($configPath)) {
            error_log("I18nService::loadConfig - Config file found: " . $configPath);
            $this->config = require $configPath;
            $this->supportedLanguages = $this->config['languages'];
        } else {
            error_log("I18nService::loadConfig - Config file NOT found: " . $configPath);
            $this->logger->error('i18n config file not found', ['path' => $configPath]);
            $this->config = [];
            $this->supportedLanguages = [];
        }
    }
    
    /**
     * Detect language from request
     */
    public function detectLanguage(string $requestUri, array $serverParams = []): string
    {
        $methods = $this->config['detection']['methods'] ?? ['url', 'session', 'browser', 'default'];
        
        foreach ($methods as $method) {
            $language = match ($method) {
                'url' => $this->detectFromUrl($requestUri),
                'session' => $this->detectFromSession(),
                'browser' => $this->detectFromBrowser($serverParams),
                'default' => $this->getDefaultLanguage(),
                default => null,
            };
            
            if ($language && $this->isLanguageSupported($language)) {
                $this->currentLanguage = $language;
                $this->logger->info('Language detected', ['method' => $method, 'language' => $language]);
                return $language;
            }
        }
        
        // Fallback to default
        $this->currentLanguage = $this->getDefaultLanguage();
        return $this->currentLanguage;
    }
    
    /**
     * Detect language from URL path
     */
    protected function detectFromUrl(string $requestUri): ?string
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!$path) {
            return null;
        }
        
        // Extract language code from path (/en/wiki/Home -> en)
        $segments = explode('/', trim($path, '/'));
        if (isset($segments[0]) && $this->isLanguageSupported($segments[0])) {
            return $segments[0];
        }
        
        return null;
    }
    
    /**
     * Detect language from session
     */
    protected function detectFromSession(): ?string
    {
        if ($this->container->has('session')) {
            $session = $this->container->get('session');
            if (method_exists($session, 'get') && $session->get('language')) {
                return $session->get('language');
            }
        }
        
        return null;
    }
    
    /**
     * Detect language from browser Accept-Language header
     */
    protected function detectFromBrowser(array $serverParams): ?string
    {
        $acceptLanguage = $serverParams['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $code = trim($parts[0]);
            $quality = 1.0;
            
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = (float) substr($parts[1], 2);
            }
            
            $languages[$code] = $quality;
        }
        
        // Sort by quality
        arsort($languages);
        
        // Find first supported language
        foreach ($languages as $code => $quality) {
            $shortCode = substr($code, 0, 2);
            if ($this->isLanguageSupported($shortCode)) {
                return $shortCode;
            }
        }
        
        return null;
    }
    
    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $language): bool
    {
        return isset($this->supportedLanguages[$language]) && 
               $this->supportedLanguages[$language]['enabled'] === true;
    }
    
    /**
     * Get default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->config['default'] ?? 'en';
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage ?? $this->getDefaultLanguage();
    }
    
    /**
     * Get language configuration
     */
    public function getLanguageConfig(string $language): ?array
    {
        return $this->supportedLanguages[$language] ?? null;
    }
    
    /**
     * Get all supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
    
    /**
     * Set current language
     */
    public function setCurrentLanguage(string $language): bool
    {
        if (!$this->isLanguageSupported($language)) {
            return false;
        }
        
        $this->currentLanguage = $language;
        
        // Save to session if enabled
        if ($this->config['detection']['persist'] ?? false) {
            if ($this->container->has('session')) {
                $session = $this->container->get('session');
                if (method_exists($session, 'set')) {
                    $session->set('language', $language);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get localized URL for current language
     */
    public function getLocalizedUrl(string $path, ?string $language = null): string
    {
        $lang = $language ?? $this->getCurrentLanguage();
        $path = ltrim($path, '/');
        
        return "/{$lang}/{$path}";
    }
    
    /**
     * Get fallback language for a given language
     */
    public function getFallbackLanguage(string $language): ?string
    {
        $config = $this->getLanguageConfig($language);
        return $config['fallback'] ?? null;
    }
    
    /**
     * Check if root redirect is required
     */
    public function isRootRedirectRequired(): bool
    {
        return $this->config['url_structure']['root_redirect'] ?? false;
    }
    
    /**
     * Check if language prefix is required
     */
    public function isLanguagePrefixRequired(): bool
    {
        return $this->config['url_structure']['prefix_required'] ?? false;
    }
} 