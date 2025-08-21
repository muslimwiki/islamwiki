<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

/**
 * Wiki Markup Parser
 *
 * Parses MediaWiki-style syntax and converts it to HTML
 * 
 * @package IslamWiki\Extensions\WikiMarkupExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiMarkupParser
{
    /**
     * @var array Configuration options
     */
    private array $config;

    /**
     * @var array Cache for parsed content
     */
    private array $cache = [];

    /**
     * Create a new parser instance
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'parse_internal_links' => true,
            'parse_templates' => true,
            'parse_headers' => true,
            'parse_lists' => true,
            'enable_caching' => true,
            'cache_ttl' => 3600
        ], $config);
    }

    /**
     * Parse wiki markup content
     */
    public function parse(string $content): string
    {
        // Check cache first
        $cacheKey = md5($content);
        if ($this->config['enable_caching'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $parsedContent = $content;

        // Parse in order of complexity (simple to complex)
        if ($this->config['parse_headers']) {
            $parsedContent = $this->parseHeaders($parsedContent);
        }

        if ($this->config['parse_lists']) {
            $parsedContent = $this->parseLists($parsedContent);
        }

        if ($this->config['parse_internal_links']) {
            $parsedContent = $this->parseInternalLinks($parsedContent);
        }

        if ($this->config['parse_templates']) {
            $parsedContent = $this->parseTemplates($parsedContent);
        }

        // Cache the result
        if ($this->config['enable_caching']) {
            $this->cache[$cacheKey] = $parsedContent;
        }

        return $parsedContent;
    }

    /**
     * Parse MediaWiki-style headers === Header ===
     */
    private function parseHeaders(string $content): string
    {
        // Pattern: === Header === (h3)
        $content = preg_replace('/^=== (.+) ===$/m', '<h3>$1</h3>', $content);
        
        // Pattern: == Header == (h2)
        $content = preg_replace('/^== (.+) ==$/m', '<h2>$1</h2>', $content);
        
        // Pattern: = Header = (h1)
        $content = preg_replace('/^= (.+) =$/m', '<h1>$1</h1>', $content);
        
        return $content;
    }

    /**
     * Parse list formatting
     */
    private function parseLists(string $content): string
    {
        // Convert * to unordered lists
        $content = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ul>$0</ul>', $content);
        
        // Convert # to ordered lists
        $content = preg_replace('/^# (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ol>$0</ol>', $content);
        
        // Convert ; to definition lists
        $content = preg_replace('/^; (.+)$/m', '<dt>$1</dt>', $content);
        $content = preg_replace('/^: (.+)$/m', '<dd>$1</dd>', $content);
        
        // Group definition list items
        $content = preg_replace('/(<dt>.+<\/dt>\n<dd>.+<\/dd>\n)+/s', '<dl>$0</dl>', $content);
        
        return $content;
    }

    /**
     * Parse internal links [[Page]] and [[Page|Display Text]]
     */
    private function parseInternalLinks(string $content): string
    {
        // Pattern: [[Page]] or [[Page|Display Text]]
        $pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $pageName = trim($matches[1]);
            $displayText = isset($matches[2]) ? trim($matches[2]) : $pageName;
            
            $url = $this->generatePageUrl($pageName);
            $cssClass = $this->getLinkCssClass($pageName);
            
            return "<a href=\"{$url}\" class=\"{$cssClass}\" title=\"{$pageName}\">{$displayText}</a>";
        }, $content);
    }

        /**
     * Parse templates {{Template}} and {{Template|param1|param2}}
     */
    private function parseTemplates(string $content): string
    {
        // For now, return placeholder - templates will be processed by TemplateEngine
        // in the post-processing phase
        return $content;
    }

    /**
     * Generate page URL for internal links
     */
    private function generatePageUrl(string $pageName): string
    {
        // Convert page name to URL-friendly format
        $slug = strtolower(str_replace(' ', '-', $pageName));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return "/wiki/{$slug}";
    }

    /**
     * Get CSS class for links
     */
    private function getLinkCssClass(string $pageName): string
    {
        $classes = ['wiki-link', 'internal-link'];
        
        // Add special classes for different types of pages
        if (strpos(strtolower($pageName), 'quran') !== false) {
            $classes[] = 'quran-link';
        } elseif (strpos(strtolower($pageName), 'hadith') !== false) {
            $classes[] = 'hadith-link';
        } elseif (strpos(strtolower($pageName), 'scholar') !== false) {
            $classes[] = 'scholar-link';
        }
        
        return implode(' ', $classes);
    }

        // Template processing is now handled by TemplateEngine in the post-processing phase

    /**
     * Post-process HTML output
     */
    public function postProcess(string $html): string
    {
        // Clean up any remaining wiki markup
        // Add CSS classes for styling
        $html = str_replace('class="wiki-link"', 'class="wiki-link internal-link"', $html);
        
        // Add data attributes for JavaScript enhancement
        $html = preg_replace('/<a([^>]*class="[^"]*wiki-link[^"]*"[^>]*)>/', '<a$1 data-wiki-link="true">', $html);
        
        return $html;
    }

    /**
     * Clear the parser cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_size' => count($this->cache),
            'cache_memory' => memory_get_usage(true),
            'config' => $this->config
        ];
    }
} 