<?php

declare(strict_types=1);

namespace IslamWiki\Core\I18n;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Internationalization Middleware
 * 
 * Intercepts requests to handle language detection and routing
 * for the IslamWiki platform.
 */
class I18nMiddleware
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
     * @var I18nService
     */
    protected I18nService $i18nService;
    
    /**
     * Constructor
     */
    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->i18nService = new I18nService($container, $logger);
    }
    
    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        $uri = $request->getUri()->getPath();
        $serverParams = $request->getServerParams();
        
        $this->logger->info('I18nMiddleware: Processing request', ['uri' => $uri]);
        
        // Handle root redirect if required
        if ($uri === '/' && $this->i18nService->isRootRedirectRequired()) {
            $defaultLang = $this->i18nService->getDefaultLanguage();
            $this->logger->info('I18nMiddleware: Redirecting root to default language', ['language' => $defaultLang]);
            
            return new Response(302, ['Location' => "/{$defaultLang}"], '');
        }
        
        // Skip language prefix check for static files and PHP files
        if ($this->isStaticFile($uri) || $this->isPhpFile($uri)) {
            $this->logger->info('I18nMiddleware: Skipping language prefix check for static/PHP file', ['uri' => $uri]);
            return $next($request);
        }
        
        // Detect language from request
        $detectedLanguage = $this->i18nService->detectLanguage($uri, $serverParams);
        $this->logger->info('I18nMiddleware: Language detected', ['language' => $detectedLanguage, 'uri' => $uri]);
        
        // Check if language prefix is required but missing
        if ($this->i18nService->isLanguagePrefixRequired() && !$this->hasLanguagePrefix($uri)) {
            $this->logger->info('I18nMiddleware: Language prefix missing, redirecting', ['uri' => $uri, 'detected' => $detectedLanguage]);
            
            // Redirect to language-prefixed URL
            $newUri = "/{$detectedLanguage}{$uri}";
            return new Response(302, ['Location' => $newUri], '');
        }
        
        // Set current language in service
        $this->i18nService->setCurrentLanguage($detectedLanguage);
        
        // Continue to next middleware/route handler
        return $next($request);
    }
    
    /**
     * Check if URI has language prefix
     */
    protected function hasLanguagePrefix(string $uri): bool
    {
        $segments = explode('/', trim($uri, '/'));
        if (empty($segments)) {
            return false;
        }
        
        $firstSegment = $segments[0];
        return $this->i18nService->isLanguageSupported($firstSegment);
    }
    
    /**
     * Check if URI is a static file
     */
    protected function isStaticFile(string $uri): bool
    {
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $staticExtensions);
    }
    
    /**
     * Check if URI is a PHP file
     */
    protected function isPhpFile(string $uri): bool
    {
        return pathinfo($uri, PATHINFO_EXTENSION) === 'php';
    }
    
    /**
     * Get i18n service instance
     */
    public function getI18nService(): I18nService
    {
        return $this->i18nService;
    }
} 