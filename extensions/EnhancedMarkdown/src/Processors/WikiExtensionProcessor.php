<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Processors;

use IslamWiki\Extensions\EnhancedMarkdown\Engines\TemplateEngine;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\CategoryManager;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\ReferenceManager;

/**
 * Wiki Extension Processor
 * 
 * Handles wiki-specific extensions like internal links, templates, categories, and references.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class WikiExtensionProcessor
{
    private TemplateEngine $templateEngine;
    private CategoryManager $categoryManager;
    private ReferenceManager $referenceManager;
    private bool $enabled = true;
    
    public function __construct($templateManager = null, $categoryManager = null, $referenceManager = null)
    {
        // Use provided managers or create default ones for backward compatibility
        if ($templateManager instanceof \IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager) {
            $this->templateEngine = new TemplateEngine($templateManager);
        } else {
            // Fallback for testing
            $mockConnection = new \IslamWiki\Extensions\EnhancedMarkdown\MockConnection();
            $templateManager = new \IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager($mockConnection);
            $this->templateEngine = new TemplateEngine($templateManager);
        }
        
        $this->categoryManager = $categoryManager ?: new \IslamWiki\Extensions\EnhancedMarkdown\Managers\CategoryManager(new \IslamWiki\Extensions\EnhancedMarkdown\MockConnection());
        $this->referenceManager = $referenceManager ?: new \IslamWiki\Extensions\EnhancedMarkdown\Managers\ReferenceManager(new \IslamWiki\Extensions\EnhancedMarkdown\MockConnection());
    }
    
    /**
     * Process wiki extensions in HTML content
     * 
     * @param string $html The HTML content to process
     * @return string Processed HTML content
     */
    public function process(string $html): string
    {
        if (!$this->enabled) {
            return $html;
        }
        
        // Process file links [[File:filename.jpg|alt=...|thumb|...]] FIRST
        $html = $this->processFileLinks($html);
        
        // Process templates {{Template|params}}
        $html = $this->processTemplates($html);
        
        // Process internal links [[Page Name]] AFTER templates
        $html = $this->processInternalLinks($html);
        
        // Process categories [Category:Name]
        $html = $this->processCategories($html);
        
        // Process references <ref>content</ref>
        $html = $this->processReferences($html);
        
        // Process special characters and formatting
        $html = $this->processSpecialFormatting($html);
        
        return $html;
    }
    
    /**
     * Process internal links [[Page Name]] and [[Page|Display]]
     */
    private function processInternalLinks(string $html): string
    {
        // Simple internal links [[Page Name]]
        $html = preg_replace_callback(
            '/\[\[([^\]]+)\]\]/',
            function ($matches) {
                $pageName = trim($matches[1]);
                $url = $this->generateWikiUrl($pageName);
                return '<a href="' . $url . '" class="wiki-link">' . htmlspecialchars($pageName) . '</a>';
            },
            $html
        );
        
        // Internal links with display text [[Page|Display]]
        $html = preg_replace_callback(
            '/\[\[([^|]+)\|([^\]]+)\]\]/',
            function ($matches) {
                $pageName = trim($matches[1]);
                $displayText = trim($matches[2]);
                $url = $this->generateWikiUrl($pageName);
                return '<a href="' . $url . '" class="wiki-link">' . htmlspecialchars($displayText) . '</a>';
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Generate wiki URL for a page name
     */
    private function generateWikiUrl(string $pageName): string
    {
        // Convert spaces to underscores and encode
        $encodedName = str_replace(' ', '_', $pageName);
        $encodedName = urlencode($encodedName);
        
        return '/wiki/' . $encodedName;
    }
    
    /**
     * Process templates {{Template|params}}
     */
    private function processTemplates(string $html): string
    {
        // Process templates recursively to handle nested templates
        $maxIterations = 10; // Prevent infinite loops
        $iteration = 0;
        
        while ($iteration < $maxIterations) {
            $previousHtml = $html;
            
            // Use a more sophisticated regex to handle nested templates
            $html = preg_replace_callback(
                '/\{\{([^{}]*(?:\{\{[^{}]*\}[^{}]*)*)\}\}/',
                function ($matches) {
                    $templateContent = trim($matches[1]);
                    return $this->templateEngine->render($templateContent);
                },
                $html
            );
            
            // If no changes were made, we're done
            if ($html === $previousHtml) {
                break;
            }
            
            $iteration++;
        }
        
        return $html;
    }
    
    /**
     * Process categories [Category:Name]
     */
    private function processCategories(string $html): string
    {
        $html = preg_replace_callback(
            '/\[Category:([^\]]+)\]/',
            function ($matches) {
                $categoryName = trim($matches[1]);
                return $this->categoryManager->renderCategory($categoryName);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process references <ref>content</ref>
     */
    private function processReferences(string $html): string
    {
        // Simple references <ref>content</ref> - handle content with internal links
        $html = preg_replace_callback(
            '/<ref>([^<]+(?:<[^>]+>[^<]*<\/[^>]+>[^<]*)*)<\/ref>/',
            function ($matches) {
                $content = trim($matches[1]);
                // Process internal links within references
                $content = $this->processInternalLinks($content);
                return $this->referenceManager->renderReference($content);
            },
            $html
        );
        
        // Named references <ref name="name">content</ref>
        $html = preg_replace_callback(
            '/<ref name="([^"]+)">([^<]+(?:<[^>]+>[^<]*<\/[^>]+>[^<]*)*)<\/ref>/',
            function ($matches) {
                $name = trim($matches[1]);
                $content = trim($matches[2]);
                // Process internal links within references
                $content = $this->processInternalLinks($content);
                return $this->referenceManager->renderNamedReference($name, $content);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process file links [[File:filename.jpg|alt=...|thumb|...]]
     */
    private function processFileLinks(string $html): string
    {
        // File links with parameters [[File:filename.jpg|alt=...|thumb|...]]
        $html = preg_replace_callback(
            '/\[\[File:([^|]+)(?:\|([^\]]+))?\]\]/i',
            function ($matches) {
                $filename = trim($matches[1]);
                $params = isset($matches[2]) ? $this->parseFileParams($matches[2]) : [];
                
                $alt = $params['alt'] ?? $filename;
                $thumb = isset($params['thumb']);
                $caption = $params['caption'] ?? '';
                
                $class = 'wiki-file';
                if ($thumb) {
                    $class .= ' thumb';
                }
                
                $html = '<div class="' . $class . '">';
                $html .= '<img src="/uploads/' . htmlspecialchars($filename) . '" alt="' . htmlspecialchars($alt) . '"';
                if ($thumb) {
                    $html .= ' class="thumb-image"';
                }
                $html .= '>';
                
                if ($caption) {
                    $html .= '<div class="file-caption">' . htmlspecialchars($caption) . '</div>';
                }
                $html .= '</div>';
                
                return $html;
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Parse file parameters (alt=text|thumb|caption=text)
     */
    private function parseFileParams(string $paramString): array
    {
        $params = [];
        $pairs = explode('|', $paramString);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $params[trim($key)] = trim($value);
            } else {
                // Handle parameters without values (like 'thumb')
                $params[trim($pair)] = true;
            }
        }
        
        return $params;
    }
    
    /**
     * Process special formatting and characters
     */
    private function processSpecialFormatting(string $html): string
    {
        // Process abbreviations <abbr>text</abbr>
        $html = preg_replace_callback(
            '/<abbr>([^<]+)<\/abbr>/',
            function ($matches) {
                return '<abbr title="' . htmlspecialchars($matches[1]) . '">' . htmlspecialchars($matches[1]) . '</abbr>';
            },
            $html
        );
        
        // Process nowiki <nowiki>text</nowiki>
        $html = preg_replace_callback(
            '/<nowiki>([^<]+)<\/nowiki>/',
            function ($matches) {
                return '<code class="nowiki">' . htmlspecialchars($matches[1]) . '</code>';
            },
            $html
        );
        
        // Process blockquotes <blockquote>text</blockquote>
        $html = preg_replace_callback(
            '/<blockquote>([^<]+)<\/blockquote>/',
            function ($matches) {
                return '<blockquote class="wiki-blockquote">' . htmlspecialchars($matches[1]) . '</blockquote>';
            },
            $html
        );
        
        // Process Arabic text and special characters
        $html = $this->processArabicText($html);
        
        return $html;
    }
    
    /**
     * Process Arabic text and special characters
     */
    private function processArabicText(string $html): string
    {
        // Add RTL support for Arabic text
        $html = preg_replace_callback(
            '/([ء-ي]+)/u',
            function ($matches) {
                return '<span dir="rtl" lang="ar" class="arabic-text">' . $matches[1] . '</span>';
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Enable or disable the processor
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
    
    /**
     * Check if processor is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * Disable the processor
     */
    public function disable(): void
    {
        $this->enabled = false;
    }
    
    /**
     * Enable the processor
     */
    public function enable(): void
    {
        $this->enabled = true;
    }
    
    /**
     * Get the template engine
     */
    public function getTemplateEngine(): TemplateEngine
    {
        return $this->templateEngine;
    }
    
    /**
     * Get the category manager
     */
    public function getCategoryManager(): CategoryManager
    {
        return $this->categoryManager;
    }
    
    /**
     * Get the reference manager
     */
    public function getReferenceManager(): ReferenceManager
    {
        return $this->referenceManager;
    }
} 