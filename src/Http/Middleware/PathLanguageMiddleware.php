<?php

declare(strict_types=1);

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Psr\Log\LoggerInterface;
use IslamWiki\Services\TranslationService;

/**
 * Path Language Middleware
 * 
 * Handles path-based language switching and routing.
 * Supports patterns like:
 * - local.islam.wiki/ (default English)
 * - local.islam.wiki/ar (Arabic)
 * - local.islam.wiki/ur (Urdu)
 * - etc.
 * 
 * This approach is much easier for users as it doesn't require
 * DNS configuration for each language.
 */
class PathLanguageMiddleware
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
     * @var array Supported language paths
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
     * Constructor
     */
    public function __construct(LoggerInterface $logger, TranslationService $translationService)
    {
        $this->logger = $logger;
        $this->translationService = $translationService;
    }

    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        $uri = $request->getUri()->getPath();
        $language = $this->extractLanguageFromPath($uri);
        
        // Set language in request attributes
        $request = $request->withAttribute('language', $language);
        $request = $request->withAttribute('is_rtl', $this->translationService->isRTL($language));
        $request = $request->withAttribute('language_direction', $this->translationService->getLanguageDirection($language));
        
        // Set language in session
        $this->setLanguageInSession($language);
        
        // Log language detection
        $this->logger->debug('Language detected from path', [
            'uri' => $uri,
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
     * Extract language from URI path
     */
    private function extractLanguageFromPath(string $uri): string
    {
        // Remove leading slash
        $uri = ltrim($uri, '/');
        
        // Split path into segments
        $segments = explode('/', $uri);
        
        // Check if first segment is a language code
        if (!empty($segments[0]) && array_key_exists($segments[0], $this->supportedLanguages)) {
            return $segments[0];
        }
        
        // Default to English if no language path found
        return $this->defaultLanguage;
    }

    /**
     * Get current language from session or request
     */
    public function getCurrentLanguage(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $this->extractLanguageFromPath($uri);
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
        $host = $_SERVER['HTTP_HOST'] ?? 'local.islam.wiki';
        
        if ($language === $this->defaultLanguage) {
            // For default language, use base domain
            return $protocol . '://' . $host . $path;
        }
        
        // For other languages, use language path
        return $protocol . '://' . $host . '/' . $language . $path;
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
     * Get language name by code
     */
    public function getLanguageName(string $language): string
    {
        return $this->supportedLanguages[$language] ?? 'Unknown';
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
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
     * Remove language prefix from path for internal routing
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
     * Add language prefix to path for external links
     */
    public function addLanguagePrefix(string $path, string $language): string
    {
        if ($language === $this->defaultLanguage) {
            return $path;
        }
        
        return '/' . $language . $path;
    }
} 