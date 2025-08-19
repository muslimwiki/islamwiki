<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * LocaleMiddleware - Handles language preferences and routing
 * 
 * Implements MediaWiki-style language handling:
 * - /wiki routes: Keep clean URLs, detect language from user preferences
 * - Other routes: Use locale prefixes (/en/, /ar/, etc.)
 */
class LocaleMiddleware
{
    /**
     * Default locale
     */
    private const DEFAULT_LOCALE = 'en';

    /**
     * Supported locales
     */
    private const SUPPORTED_LOCALES = ['en', 'ar'];

    /**
     * Routes that should use MediaWiki-style language handling (no locale prefix)
     * Note: Logic is now handled directly in isMediaWikiRoute() method
     */

    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        $path = $request->getUri()->getPath();
        
        error_log("LocaleMiddleware: Processing request for path: $path");
        
        // Check if this is a MediaWiki-style route (wiki routes or homepage)
        if ($this->isMediaWikiRoute($path)) {
            return $this->handleMediaWikiRoute($request, $next);
        }

        // For non-MediaWiki routes, use traditional locale prefix handling
        return $this->handleTraditionalRoute($request, $next);
    }

    /**
     * Make the middleware callable for the router
     */
    public function __invoke(Request $request, callable $next): Response
    {
        try {
            error_log("LocaleMiddleware: __invoke called with path: " . $request->getUri()->getPath());
            return $this->process($request, $next);
        } catch (\Exception $e) {
            error_log("LocaleMiddleware: Exception caught: " . $e->getMessage());
            error_log("LocaleMiddleware: Exception trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Check if the path is a MediaWiki-style route (no locale prefix needed)
     */
    private function isMediaWikiRoute(string $path): bool
    {
        // Exact match for root path
        if ($path === '/') {
            return true;
        }
        
        // Check if path starts with /wiki (but not /wiki/something-else that might be a different route)
        if (strpos($path, '/wiki') === 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle MediaWiki-style routes with clean URLs and language detection
     * - Keep clean URLs (/wiki/PageName, /)
     * - Detect language from user preferences
     * - Set language context for the request
     */
    private function handleMediaWikiRoute(Request $request, callable $next): Response
    {
        $path = $request->getUri()->getPath();
        
        // Get user's preferred locale from various sources
        $preferredLocale = $this->getPreferredLocale($request);
        
        // Set the locale in the request attributes for controllers to use
        $request = $request->withAttribute('locale', $preferredLocale);
        $request = $request->withAttribute('isWikiRoute', true);
        
        // Log the wiki route handling
        error_log("LocaleMiddleware: Processing wiki route: $path with locale: $preferredLocale");
        
        // Process the request with the detected locale
        $debugLog = BASE_PATH . '/storage/logs/debug.log';
        file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: About to call next middleware with request path: " . $request->getUri()->getPath(), FILE_APPEND);
        file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Next middleware type: " . gettype($next), FILE_APPEND);
        file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Next middleware is callable: " . (is_callable($next) ? 'yes' : 'no'), FILE_APPEND);
        
        try {
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Calling next middleware...", FILE_APPEND);
            $response = $next($request);
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Next middleware returned response with status: " . $response->getStatusCode(), FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Exception in next middleware: " . $e->getMessage(), FILE_APPEND);
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Exception trace: " . $e->getTraceAsString(), FILE_APPEND);
            throw $e;
        } catch (\Error $e) {
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Error in next middleware: " . $e->getMessage(), FILE_APPEND);
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Error trace: " . $e->getTraceAsString(), FILE_APPEND);
            throw $e;
        } catch (\Throwable $e) {
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Exception in next middleware: " . $e->getMessage(), FILE_APPEND);
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] LocaleMiddleware: Exception trace: " . $e->getTraceAsString(), FILE_APPEND);
            throw $e;
        }
        
        // Set language headers for the response
        $response = $response->withHeader('Content-Language', $preferredLocale);
        
        return $response;
    }

    /**
     * Handle traditional routes with locale prefixes
     * - Redirect /route to /{locale}/route if no locale prefix
     * - Process /{locale}/route normally
     */
    private function handleTraditionalRoute(Request $request, callable $next): Response
    {
        $path = $request->getUri()->getPath();
        
        // Check if path already has a locale prefix
        if ($this->hasLocalePrefix($path)) {
            // Extract locale and set it in request attributes
            $locale = $this->extractLocale($path);
            $request = $request->withAttribute('locale', $locale);
            $request = $request->withAttribute('isWikiRoute', false);
            
            error_log("LocaleMiddleware: Processing traditional route: $path with locale: $locale");
            return $next($request);
        }

        // No locale prefix - redirect to preferred locale
        $preferredLocale = $this->getPreferredLocale($request);
        $redirectPath = "/{$preferredLocale}{$path}";
        
        error_log("LocaleMiddleware: Redirecting traditional route: $path to $redirectPath");
        
        return new Response(302, ['Location' => $redirectPath], '');
    }

    /**
     * Get user's preferred locale from various sources
     */
    private function getPreferredLocale(Request $request): string
    {
        // 1. Check URL parameter first (for explicit language switching)
        $urlLocale = $request->getQueryParam('lang');
        if ($urlLocale && in_array($urlLocale, self::SUPPORTED_LOCALES)) {
            return $urlLocale;
        }

        // 2. Check Accept-Language header
        $acceptLanguage = $request->getHeader('Accept-Language');
        if ($acceptLanguage && is_array($acceptLanguage) && !empty($acceptLanguage)) {
            $locale = $this->parseAcceptLanguage($acceptLanguage[0]);
            if ($locale) {
                return $locale;
            }
        }

        // 3. Check session/cookie for saved preference
        $sessionLocale = $this->getSessionLocale();
        if ($sessionLocale) {
            return $sessionLocale;
        }

        // 4. Default to English
        return self::DEFAULT_LOCALE;
    }

    /**
     * Check if path has a locale prefix
     */
    private function hasLocalePrefix(string $path): bool
    {
        $segments = explode('/', trim($path, '/'));
        return count($segments) > 0 && in_array($segments[0], self::SUPPORTED_LOCALES);
    }

    /**
     * Extract locale from path
     */
    private function extractLocale(string $path): string
    {
        $segments = explode('/', trim($path, '/'));
        return $segments[0] ?? self::DEFAULT_LOCALE;
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        $languages = explode(',', $acceptLanguage);
        
        foreach ($languages as $language) {
            $lang = trim(explode(';', $language)[0]);
            $lang = strtolower(substr($lang, 0, 2)); // Get first 2 chars
            
            if (in_array($lang, self::SUPPORTED_LOCALES)) {
                return $lang;
            }
        }
        
        return null;
    }

    /**
     * Get locale from session
     */
    private function getSessionLocale(): ?string
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION['locale'] ?? null;
        }
        return null;
    }
} 