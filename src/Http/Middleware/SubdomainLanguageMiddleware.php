<?php

declare(strict_types=1);

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Psr\Log\LoggerInterface;
use IslamWiki\Services\TranslationService;

/**
 * Subdomain Language Middleware
 * 
 * Handles subdomain-based language switching and routing.
 * Supports patterns like:
 * - en.local.islam.wiki (default English)
 * - ar.local.islam.wiki (Arabic)
 * - ur.local.islam.wiki (Urdu)
 * - etc.
 */
class SubdomainLanguageMiddleware
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var TranslationService
     */
    private TranslationService $translationService;

    /**
     * @var array Supported language subdomains
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
     * @var string Default language
     */
    private string $defaultLanguage = 'en';

    /**
     * @var string Base domain (without language subdomain)
     */
    private string $baseDomain;

    /**
     * Constructor
     */
    public function __construct(LoggerInterface $logger, TranslationService $translationService)
    {
        $this->logger = $logger;
        $this->translationService = $translationService;
        $this->baseDomain = $this->getBaseDomain();
    }

    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        $host = $request->getHeaderLine('Host');
        $language = $this->extractLanguageFromHost($host);
        
        // Set language in request attributes
        $request = $request->withAttribute('language', $language);
        $request = $request->withAttribute('is_rtl', $this->translationService->isRTL($language));
        $request = $request->withAttribute('language_direction', $this->translationService->getLanguageDirection($language));
        
        // Set language in session
        $this->setLanguageInSession($language);
        
        // Log language detection
        $this->logger->debug('Language detected from subdomain', [
            'host' => $host,
            'language' => $language,
            'is_rtl' => $this->translationService->isRTL($language)
        ]);

        // Process the request
        $response = $next($request);

        // Add language headers to response
        $response = $response->withHeader('Content-Language', $language);
        $response = $response->withHeader('X-Language', $language);
        $response = $response->withHeader('X-Language-Direction', $this->translationService->getLanguageDirection($language));

        return $response;
    }

    /**
     * Handle the request (alias for process method to match router expectations)
     */
    public function handle(Request $request, callable $next): Response
    {
        return $this->process($request, $next);
    }

    /**
     * Extract language from host
     */
    private function extractLanguageFromHost(string $host): string
    {
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);
        
        // Check if host contains language subdomain
        foreach ($this->supportedLanguages as $code => $name) {
            if (strpos($host, $code . '.') === 0) {
                return $code;
            }
        }
        
        // Check for www prefix
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
            foreach ($this->supportedLanguages as $code => $name) {
                if (strpos($host, $code . '.') === 0) {
                    return $code;
                }
            }
        }
        
        // Default to English if no language subdomain found
        return $this->defaultLanguage;
    }

    /**
     * Get base domain without language subdomain
     */
    public function getBaseDomain(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'local.islam.wiki';
        
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);
        
        // Remove language subdomain if present
        foreach ($this->supportedLanguages as $code => $name) {
            if (strpos($host, $code . '.') === 0) {
                $host = substr($host, strlen($code) + 1);
                break;
            }
        }
        
        // Remove www prefix if present
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        
        return $host;
    }

    /**
     * Set language in session
     */
    private function setLanguageInSession(string $language): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['language'] = $language;
            $_SESSION['language_direction'] = $this->translationService->getLanguageDirection($language);
            $_SESSION['is_rtl'] = $this->translationService->isRTL($language);
        }
    }

    /**
     * Get current language from session or request
     */
    public function getCurrentLanguage(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return $this->extractLanguageFromHost($host);
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRTL(): bool
    {
        $language = $this->getCurrentLanguage();
        return $this->translationService->isRTL($language);
    }

    /**
     * Get current language direction
     */
    public function getCurrentLanguageDirection(): string
    {
        $language = $this->getCurrentLanguage();
        return $this->translationService->getLanguageDirection($language);
    }

    /**
     * Generate URL for specific language
     */
    public function generateLanguageUrl(string $language, string $path = '/'): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        
        if ($language === $this->defaultLanguage) {
            // For default language, use base domain
            return $protocol . '://' . $this->baseDomain . $path;
        }
        
        // For other languages, use language subdomain
        return $protocol . '://' . $language . '.' . $this->baseDomain . $path;
    }

    /**
     * Get all language URLs for current page
     */
    public function getAllLanguageUrls(string $currentPath = '/'): array
    {
        $urls = [];
        
        foreach ($this->supportedLanguages as $code => $name) {
            $urls[$code] = [
                'code' => $code,
                'name' => $name,
                'url' => $this->generateLanguageUrl($code, $currentPath),
                'is_current' => $code === $this->getCurrentLanguage(),
                'is_rtl' => $this->translationService->isRTL($code),
                'direction' => $this->translationService->getLanguageDirection($code)
            ];
        }
        
        return $urls;
    }

    /**
     * Redirect to language-specific subdomain if needed
     */
    public function shouldRedirectToLanguageSubdomain(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $currentLanguage = $this->getCurrentLanguage();
        
        // If we're on the base domain but have a language preference, redirect
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $preferredLanguage = $_SESSION['language'];
            if ($preferredLanguage !== $this->defaultLanguage) {
                // Check if we're not already on the language subdomain
                if (strpos($host, $preferredLanguage . '.') !== 0) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get redirect URL for language subdomain
     */
    public function getLanguageRedirectUrl(): string
    {
        $language = $_SESSION['language'] ?? $this->defaultLanguage;
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $this->generateLanguageUrl($language, $currentPath);
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
        return isset($this->supportedLanguages[$language]);
    }


} 