<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

/**
 * Comprehensive Wiki Markup Parser
 *
 * Parses MediaWiki-style syntax and converts it to HTML
 * Supports all standard MediaWiki markup including:
 * - Headers, Lists, Links, Templates
 * - Tables, Media, Categories, Signatures
 * - Math formulas, Syntax highlighting, Comments
 * - And much more...
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
     * @var array MediaWiki markup patterns
     */
    private array $patterns = [];

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
            'parse_tables' => true,
            'parse_media' => true,
            'parse_categories' => true,
            'parse_signatures' => true,
            'parse_comments' => true,
            'parse_math' => true,
            'parse_syntax_highlighting' => true,
            'enable_caching' => true,
            'cache_ttl' => 3600
        ], $config);

        $this->initializePatterns();
    }

    /**
     * Initialize MediaWiki markup patterns
     */
    private function initializePatterns(): void
    {
        $this->patterns = [
            'headers' => [
                'h1' => '/^= (.+) =$/m',
                'h2' => '/^== (.+) ==$/m',
                'h3' => '/^=== (.+) ===$/m',
                'h4' => '/^==== (.+) ====$/m',
                'h5' => '/^===== (.+) =====$/m',
                'h6' => '/^====== (.+) ======$/m'
            ],
            'emphasis' => [
                'bold' => '/\'\'\'(.+?)\'\'\'/s',
                'italic' => '/\'\'(.+?)\'\'/s',
                'bold_italic' => '/\'\'\'\'\'(.+?)\'\'\'\'\'/s',
                'strikethrough' => '/<del>(.+?)<\/del>/s',
                'underline' => '/<u>(.+?)<\/u>/s',
                'code' => '/<code>(.+?)<\/code>/s',
                'pre' => '/<pre>(.+?)<\/pre>/s'
            ],
            'lists' => [
                'unordered' => '/^\* (.+)$/m',
                'ordered' => '/^# (.+)$/m',
                'definition' => [
                    'term' => '/^; (.+)$/m',
                    'definition' => '/^: (.+)$/m'
                ],
                'indentation' => '/^(:+)(.+)$/m'
            ],
            'links' => [
                'internal' => '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/',
                'external' => '/\[([^\]]+)\s+([^\]]+)\]/',
                'external_simple' => '/(https?:\/\/[^\s]+)/',
                'interwiki' => '/\[\[([a-z]+):([^|\]]+)(?:\|([^\]]+))?\]\]/'
            ],
            'tables' => [
                'start' => '/^\{\|(.+)$/m',
                'end' => '/^\|\}$/m',
                'row_start' => '/^\|-/m',
                'header_cell' => '/^\|(.+)\|\|/',
                'data_cell' => '/^\|(.+)\|/',
                'cell_separator' => '/\|\|/',
                'cell_single' => '/\|/'
            ],
            'media' => [
                'image' => '/\[\[(Image|File):([^|\]]+)(?:\|([^\]]+))?\]\]/i',
                'gallery' => '/<gallery>(.+?)<\/gallery>/s',
                'video' => '/\[\[(Video|Media):([^|\]]+)(?:\|([^\]]+))?\]\]/i'
            ],
            'categories' => [
                'category' => '/\[\[(Category|تصنيف):([^|\]]+)(?:\|([^\]]+))?\]\]/i'
            ],
            'templates' => [
                'template' => '/\{\{([^|}]+)(?:\|([^}]+))?\}\}/',
                'nested_template' => '/\{\{([^{}]+(?:\{[^{}]*\}[^{}]*)*)\}\}/'
            ],
            'math' => [
                'inline' => '/<math>(.+?)<\/math>/s',
                'block' => '/<math\s+display="block">(.+?)<\/math>/s'
            ],
            'syntax_highlighting' => [
                'code_block' => '/<source\s+lang="([^"]+)">(.+?)<\/source>/s',
                'syntax_highlight' => '/<syntaxhighlight\s+lang="([^"]+)">(.+?)<\/syntaxhighlight>/s'
            ],
            'comments' => [
                'html_comment' => '/<!--(.+?)-->/s',
                'wiki_comment' => '/__HIDDENCAT__/'
            ],
            'signatures' => [
                'user_signature' => '/--\s*\[\[User:([^|\]]+)(?:\|([^\]]+))?\]\]/',
                'timestamp' => '/--\s*(\d{2}:\d{2},\s*\d{1,2}\s+\w+\s+\d{4})/'
            ],
            'special' => [
                'magic_words' => '/\{\{([A-Z_]+)\}\}/',
                'parser_functions' => '/\{\{#([^:}]+):([^}]+)\}\}/',
                'variables' => '/\{\{([A-Z_]+)\}\}/'
            ]
        ];
    }

    /**
     * Parse wiki markup content
     */
    public function parse(string $content, string $format = 'wikimarkup'): string
    {
        // Check cache first
        $cacheKey = md5($content . $format);
        if ($this->config['enable_caching'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $parsedContent = $content;

        // Parse based on format
        if ($format === 'markdown') {
            $parsedContent = $this->parseMarkdown($parsedContent);
        } else {
            $parsedContent = $this->parseWikiMarkup($parsedContent);
        }

        // Cache the result
        if ($this->config['enable_caching']) {
            $this->cache[$cacheKey] = $parsedContent;
        }

        return $parsedContent;
    }

    /**
     * Parse MediaWiki markup
     */
    private function parseWikiMarkup(string $content): string
    {
        // Parse in order of complexity (simple to complex)
        if ($this->config['parse_headers']) {
            $content = $this->parseHeaders($content);
        }

        if ($this->config['parse_emphasis']) {
            $content = $this->parseEmphasis($content);
        }

        if ($this->config['parse_lists']) {
            $content = $this->parseLists($content);
        }

        if ($this->config['parse_tables']) {
            $content = $this->parseTables($content);
        }

        if ($this->config['parse_media']) {
            $content = $this->parseMedia($content);
        }

        if ($this->config['parse_categories']) {
            $content = $this->parseCategories($content);
        }

        if ($this->config['parse_internal_links']) {
            $content = $this->parseInternalLinks($content);
        }

        if ($this->config['parse_templates']) {
            $content = $this->parseTemplates($content);
        }

        if ($this->config['parse_math']) {
            $content = $this->parseMath($content);
        }

        if ($this->config['parse_syntax_highlighting']) {
            $content = $this->parseSyntaxHighlighting($content);
        }

        if ($this->config['parse_signatures']) {
            $content = $this->parseSignatures($content);
        }

        if ($this->config['parse_comments']) {
            $content = $this->parseComments($content);
        }

        return $content;
    }

    /**
     * Parse Markdown content
     */
    private function parseMarkdown(string $content): string
    {
        // Headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);

        // Emphasis
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/~~(.*?)~~/s', '<del>$1</del>', $content);

        // Code
        $content = preg_replace('/`([^`]+)`/', '<code>$1</code>', $content);
        $content = preg_replace_callback(
            '/```(\w+)?\n(.*?)\n```/s',
            function ($matches) {
                $language = $matches[1] ?? 'text';
                $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
                return sprintf(
                    '<pre class="code-block language-%s"><code class="language-%s">%s</code></pre>',
                    $language,
                    $language,
                    $code
                );
            },
            $content
        );

        // Links
        $content = preg_replace(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>',
            $content
        );

        // Lists
        $content = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ul>$0</ul>', $content);
        $content = preg_replace('/^# (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ol>$0</ol>', $content);

        // Blockquotes
        $content = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $content);

        // Horizontal rules
        $content = preg_replace('/^---$/m', '<hr>', $content);

        return $content;
    }

    /**
     * Parse MediaWiki-style headers
     */
    private function parseHeaders(string $content): string
    {
        foreach ($this->patterns['headers'] as $level => $pattern) {
            $tag = $level;
            $content = preg_replace($pattern, "<{$tag}>$1</{$tag}>", $content);
        }
        return $content;
    }

    /**
     * Parse emphasis formatting
     */
    private function parseEmphasis(string $content): string
    {
        // Bold and italic
        $content = preg_replace($this->patterns['emphasis']['bold'], '<strong>$1</strong>', $content);
        $content = preg_replace($this->patterns['emphasis']['italic'], '<em>$1</em>', $content);
        $content = preg_replace($this->patterns['emphasis']['bold_italic'], '<strong><em>$1</em></strong>', $content);
        
        // Other emphasis
        $content = preg_replace($this->patterns['emphasis']['strikethrough'], '<del>$1</del>', $content);
        $content = preg_replace($this->patterns['emphasis']['underline'], '<u>$1</u>', $content);
        $content = preg_replace($this->patterns['emphasis']['code'], '<code>$1</code>', $content);
        $content = preg_replace($this->patterns['emphasis']['pre'], '<pre>$1</pre>', $content);
        
        return $content;
    }

    /**
     * Parse list formatting
     */
    private function parseLists(string $content): string
    {
        // Unordered lists
        $content = preg_replace($this->patterns['lists']['unordered'], '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ul>$0</ul>', $content);
        
        // Ordered lists
        $content = preg_replace($this->patterns['lists']['ordered'], '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ol>$0</ol>', $content);
        
        // Definition lists
        $content = preg_replace($this->patterns['lists']['definition']['term'], '<dt>$1</dt>', $content);
        $content = preg_replace($this->patterns['lists']['definition']['definition'], '<dd>$1</dd>', $content);
        $content = preg_replace('/(<dt>.+<\/dt>\n<dd>.+<\/dd>\n)+/s', '<dl>$0</dl>', $content);
        
        // Indentation
        $content = preg_replace_callback(
            $this->patterns['lists']['indentation'],
            function ($matches) {
                $indentLevel = strlen($matches[1]);
                $content = $matches[2];
                return str_repeat('<div class="indent">', $indentLevel) . $content . str_repeat('</div>', $indentLevel);
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse MediaWiki tables
     */
    private function parseTables(string $content): string
    {
        // Find table blocks
        $content = preg_replace_callback(
            '/\{\|(.+?)\|\}/s',
            function ($matches) {
                $tableContent = $matches[1];
                $rows = explode("\n", $tableContent);
                $html = '<table class="wikitable">';
                
                foreach ($rows as $row) {
                    $row = trim($row);
                    if (empty($row)) continue;
                    
                    if (preg_match('/^\|-/', $row)) {
                        // New row
                        $html .= '<tr>';
                    } elseif (preg_match('/^\|(.+)\|\|/', $row)) {
                        // Header cell
                        $cellContent = preg_replace('/^\|(.+)\|\|/', '$1', $row);
                        $html .= '<th>' . trim($cellContent) . '</th>';
                    } elseif (preg_match('/^\|(.+)\|/', $row)) {
                        // Data cell
                        $cellContent = preg_replace('/^\|(.+)\|/', '$1', $row);
                        $html .= '<td>' . trim($cellContent) . '</td>';
                    }
                }
                
                $html .= '</tr></table>';
                return $html;
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse media (images, videos, galleries)
     */
    private function parseMedia(string $content): string
    {
        // Images
        $content = preg_replace_callback(
            $this->patterns['media']['image'],
            function ($matches) {
                $filename = $matches[2];
                $caption = $matches[3] ?? '';
                $alt = $caption ?: $filename;
                
                return sprintf(
                    '<figure class="wiki-image"><img src="/media/%s" alt="%s" class="wiki-media-image">%s</figure>',
                    htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
                    $caption ? '<figcaption>' . htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') . '</figcaption>' : ''
                );
            },
            $content
        );
        
        // Galleries
        $content = preg_replace_callback(
            $this->patterns['media']['gallery'],
            function ($matches) {
                $galleryContent = $matches[1];
                $images = explode("\n", $galleryContent);
                $html = '<div class="wiki-gallery">';
                
                foreach ($images as $image) {
                    $image = trim($image);
                    if (empty($image)) continue;
                    
                    $parts = explode('|', $image);
                    $filename = $parts[0];
                    $caption = $parts[1] ?? '';
                    
                    $html .= sprintf(
                        '<div class="gallery-item"><img src="/media/%s" alt="%s">%s</div>',
                        htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($caption ?: $filename, ENT_QUOTES, 'UTF-8'),
                        $caption ? '<div class="gallery-caption">' . htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') . '</div>' : ''
                    );
                }
                
                $html .= '</div>';
                return $html;
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse categories
     */
    private function parseCategories(string $content): string
    {
        $content = preg_replace_callback(
            $this->patterns['categories']['category'],
            function ($matches) {
                $categoryName = $matches[2];
                $sortKey = $matches[3] ?? '';
                
                return sprintf(
                    '<div class="wiki-category" data-category="%s" data-sort="%s">%s</div>',
                    htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($sortKey, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse internal links [[Page]] and [[Page|Display Text]]
     */
    private function parseInternalLinks(string $content): string
    {
        $content = preg_replace_callback(
            $this->patterns['links']['internal'],
            function ($matches) {
                $pageName = trim($matches[1]);
                $displayText = isset($matches[2]) ? trim($matches[2]) : $pageName;
                
                $url = $this->generatePageUrl($pageName);
                $cssClass = $this->getLinkCssClass($pageName);
                
                return sprintf(
                    '<a href="%s" class="%s" title="%s" data-wiki-link="true">%s</a>',
                    htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($pageName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        return $content;
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
     * Parse math formulas
     */
    private function parseMath(string $content): string
    {
        // Inline math
        $content = preg_replace_callback(
            $this->patterns['math']['inline'],
            function ($matches) {
                $formula = $matches[1];
                return sprintf(
                    '<span class="math-inline" data-formula="%s">%s</span>',
                    htmlspecialchars($formula, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($formula, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        // Block math
        $content = preg_replace_callback(
            $this->patterns['math']['block'],
            function ($matches) {
                $formula = $matches[1];
                return sprintf(
                    '<div class="math-block" data-formula="%s">%s</div>',
                    htmlspecialchars($formula, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($formula, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse syntax highlighting
     */
    private function parseSyntaxHighlighting(string $content): string
    {
        // Source blocks
        $content = preg_replace_callback(
            $this->patterns['syntax_highlighting']['code_block'],
            function ($matches) {
                $language = $matches[1];
                $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
                
                return sprintf(
                    '<pre class="code-block language-%s"><code class="language-%s">%s</code></pre>',
                    htmlspecialchars($language, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($language, ENT_QUOTES, 'UTF-8'),
                    $code
                );
            },
            $content
        );
        
        // Syntax highlighting
        $content = preg_replace_callback(
            $this->patterns['syntax_highlighting']['syntax_highlight'],
            function ($matches) {
                $language = $matches[1];
                $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
                
                return sprintf(
                    '<pre class="syntax-highlight language-%s"><code class="language-%s">%s</code></pre>',
                    htmlspecialchars($language, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($language, ENT_QUOTES, 'UTF-8'),
                    $code
                );
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse signatures
     */
    private function parseSignatures(string $content): string
    {
        // User signatures
        $content = preg_replace_callback(
            $this->patterns['signatures']['user_signature'],
            function ($matches) {
                $username = $matches[1];
                $displayName = $matches[2] ?? $username;
                
                return sprintf(
                    '<span class="user-signature">-- <a href="/wiki/User:%s" class="user-link">%s</a></span>',
                    htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        // Timestamps
        $content = preg_replace_callback(
            $this->patterns['signatures']['timestamp'],
            function ($matches) {
                $timestamp = $matches[1];
                return sprintf(
                    '<span class="timestamp">-- %s</span>',
                    htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );
        
        return $content;
    }

    /**
     * Parse comments
     */
    private function parseComments(string $content): string
    {
        // HTML comments
        $content = preg_replace($this->patterns['comments']['html_comment'], '<!--$1-->', $content);
        
        // Wiki comments
        $content = preg_replace($this->patterns['comments']['wiki_comment'], '', $content);
        
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

    /**
     * Get supported markup features
     */
    public function getSupportedFeatures(): array
    {
        return [
            'headers' => 'MediaWiki-style headers (=== Header ===)',
            'emphasis' => 'Bold, italic, strikethrough, underline',
            'lists' => 'Unordered (*), ordered (#), definition (; :)',
            'tables' => 'MediaWiki table syntax ({| |} |-)',
            'media' => 'Images, videos, galleries',
            'categories' => 'Category links and organization',
            'links' => 'Internal [[Page]] and external links',
            'templates' => 'Template system {{Template}}',
            'math' => 'Mathematical formulas',
            'syntax_highlighting' => 'Code blocks with language support',
            'signatures' => 'User signatures and timestamps',
            'comments' => 'HTML and wiki comments'
        ];
    }
} 