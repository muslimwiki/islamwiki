<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Language\LanguageService;

/**
 * Core Language Middleware
 * 
 * This is part of the core framework and handles:
 * - Language detection from URL paths
 * - Setting language context for the request
 * - Adding language headers to responses
 */
class LanguageMiddleware
{
    /**
     * @var LanguageService
     */
    private LanguageService $languageService;

    /**
     * Constructor
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Process the request
     */
    public function process(Request $request, callable $next): ?Response
    {
        // Simple test to see if middleware is being called
        error_log("LanguageMiddleware: PROCESS CALLED for URI: " . $request->getUri()->getPath());
        
        try {
            // Extract language from URI path
            $uri = $request->getUri()->getPath();
            error_log("LanguageMiddleware: Processing URI: " . $uri);
            
            $language = $this->languageService->extractLanguageFromPath($uri);
            error_log("LanguageMiddleware: Extracted language: " . $language);
            
            // Set the current language
            $this->languageService->setCurrentLanguage($language);
            error_log("LanguageMiddleware: Set current language: " . $language);
            
            // Add language information to request attributes
            $request = $request->withAttribute('language', $language);
            $request = $request->withAttribute('is_rtl', $this->languageService->isCurrentLanguageRTL());
            $request = $request->withAttribute('language_direction', $this->languageService->getCurrentLanguageDirection());
            $request = $request->withAttribute('locale', $this->languageService->getCurrentLanguageLocale());
            
            // Since the router's middleware system is broken, we'll just set the language context
            // and let the route handler process the request normally
            error_log("LanguageMiddleware: Language context set, continuing to route handler");
            
            // Process the request through the middleware chain
            $response = $next($request);
            
            // If next() returned null, it means we should continue to the route handler
            if ($response === null) {
                error_log("LanguageMiddleware: next() returned null, continuing to route handler");
                return null;
            }
            
            // Add language headers to response
            $response = $response->withHeader('Content-Language', $language);
            $response = $response->withHeader('X-Language', $language);
            $response = $response->withHeader('X-Language-Direction', $this->languageService->getCurrentLanguageDirection());
            
            error_log("LanguageMiddleware: Successfully processed request for language: " . $language);
            return $response;
            
        } catch (\Exception $e) {
            error_log("LanguageMiddleware: Error processing request: " . $e->getMessage());
            error_log("LanguageMiddleware: Stack trace: " . $e->getTraceAsString());
            
            // If there's an error, continue with default language
            try {
                $this->languageService->setCurrentLanguage('en');
                $request = $request->withAttribute('language', 'en');
                $request = $request->withAttribute('is_rtl', false);
                $request = $request->withAttribute('language_direction', 'ltr');
                $request = $request->withAttribute('locale', 'en_US');
                
                error_log("LanguageMiddleware: Using fallback language: en");
                return new Response(200, [
                    'Content-Type' => 'text/html',
                    'Content-Language' => 'en',
                    'X-Language' => 'en',
                    'X-Language-Direction' => 'ltr'
                ], '');
            } catch (\Exception $fallbackError) {
                error_log("LanguageMiddleware: Fallback also failed: " . $fallbackError->getMessage());
                throw $e; // Re-throw original error
            }
        }
    }

    /**
     * Handle the request (alias for process method to match router expectations)
     */
    public function handle(Request $request, callable $next): ?Response
    {
        return $this->process($request, $next);
    }

    /**
     * Make the middleware callable (required by SabilRouting)
     */
    public function __invoke(Request $request, callable $next): ?Response
    {
        return $this->process($request, $next);
    }
} 