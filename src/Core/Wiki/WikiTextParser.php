<?php

declare(strict_types=1);

namespace IslamWiki\Core\Wiki;

/**
 * WikiText Parser
 * 
 * Converts MediaWiki-style markup to HTML, providing compatibility
 * with MediaWiki syntax while maintaining Islamic content support.
 * 
 * Features:
 * - Headings and sections
 * - Links (internal, external, interwiki)
 * - Lists (ordered, unordered, definition)
 * - Tables
 * - Templates
 * - Categories
 * - Islamic-specific markup
 * - Arabic text support
 */
class WikiTextParser
{
    private array $templates = [];
    private array $categories = [];
    private array $links = [];
    private array $images = [];
    private string $currentNamespace = 'Main';
    
    /**
     * Parse WikiText content to HTML
     */
    public function parse(string $wikitext, array $options = []): string
    {
        $this->reset();
        
        // Pre-process: extract templates and categories
        $wikitext = $this->extractTemplates($wikitext);
        $wikitext = $this->extractCategories($wikitext);
        
        // Parse main content
        $html = $this->parseContent($wikitext);
        
        // Post-process: add extracted metadata
        $html = $this->addMetadata($html, $options);
        
        return $html;
    }
    
    /**
     * Parse the main content
     */
    private function parseContent(string $wikitext): string
    {
        // Convert line breaks
        $wikitext = str_replace("\r\n", "\n", $wikitext);
        
        // Parse headings
        $wikitext = $this->parseHeadings($wikitext);
        
        // Parse lists
        $wikitext = $this->parseLists($wikitext);
        
        // Parse tables
        $wikitext = $this->parseTables($wikitext);
        
        // Parse links
        $wikitext = $this->parseLinks($wikitext);
        
        // Parse formatting
        $wikitext = $this->parseFormatting($wikitext);
        
        // Parse Islamic markup
        $wikitext = $this->parseIslamicMarkup($wikitext);
        
        // Convert remaining newlines to paragraphs
        $wikitext = $this->parseParagraphs($wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse headings (== Heading ==)
     */
    private function parseHeadings(string $wikitext): string
    {
        // Level 2 headings
        $wikitext = preg_replace('/^==\s*(.+?)\s*==\s*$/m', '<h2>$1</h2>', $wikitext);
        
        // Level 3 headings
        $wikitext = preg_replace('/^===\s*(.+?)\s*===\s*$/m', '<h3>$1</h3>', $wikitext);
        
        // Level 4 headings
        $wikitext = preg_replace('/^====\s*(.+?)\s*====\s*$/m', '<h4>$1</h4>', $wikitext);
        
        // Level 5 headings
        $wikitext = preg_replace('/^=====\s*(.+?)\s*=====\s*$/m', '<h5>$1</h5>', $wikitext);
        
        // Level 6 headings
        $wikitext = preg_replace('/^======\s*(.+?)\s*======\s*$/m', '<h6>$1</h6>', $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse lists
     */
    private function parseLists(string $wikitext): string
    {
        // Unordered lists
        $wikitext = $this->parseUnorderedLists($wikitext);
        
        // Ordered lists
        $wikitext = $this->parseOrderedLists($wikitext);
        
        // Definition lists
        $wikitext = $this->parseDefinitionLists($wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse unordered lists (* item)
     */
    private function parseUnorderedLists(string $wikitext): string
    {
        $lines = explode("\n", $wikitext);
        $inList = false;
        $result = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(\*+)\s*(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $content = trim($matches[2]);
                
                if (!$inList) {
                    $result[] = '<ul>';
                    $inList = true;
                }
                
                $indent = str_repeat('  ', $level - 1);
                $result[] = $indent . '<li>' . $this->parseInlineFormatting($content) . '</li>';
            } else {
                if ($inList && trim($line) !== '') {
                    $result[] = '</ul>';
                    $inList = false;
                }
                $result[] = $line;
            }
        }
        
        if ($inList) {
            $result[] = '</ul>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Parse ordered lists (# item)
     */
    private function parseOrderedLists(string $wikitext): string
    {
        $lines = explode("\n", $wikitext);
        $inList = false;
        $result = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(#+)\s*(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $content = trim($matches[2]);
                
                if (!$inList) {
                    $result[] = '<ol>';
                    $inList = true;
                }
                
                $indent = str_repeat('  ', $level - 1);
                $result[] = $indent . '<li>' . $this->parseInlineFormatting($content) . '</li>';
            } else {
                if ($inList && trim($line) !== '') {
                    $result[] = '</ol>';
                    $inList = false;
                }
                $result[] = $line;
            }
        }
        
        if ($inList) {
            $result[] = '</ol>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Parse definition lists (; term : definition)
     */
    private function parseDefinitionLists(string $wikitext): string
    {
        $lines = explode("\n", $wikitext);
        $inList = false;
        $result = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^;\s*(.+?)\s*:\s*(.+)$/', $line, $matches)) {
                $term = trim($matches[1]);
                $definition = trim($matches[2]);
                
                if (!$inList) {
                    $result[] = '<dl>';
                    $inList = true;
                }
                
                $result[] = '<dt>' . $this->parseInlineFormatting($term) . '</dt>';
                $result[] = '<dd>' . $this->parseInlineFormatting($definition) . '</dd>';
            } else {
                if ($inList && trim($line) !== '') {
                    $result[] = '</dl>';
                    $inList = false;
                }
                $result[] = $line;
            }
        }
        
        if ($inList) {
            $result[] = '</dl>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Parse tables
     */
    private function parseTables(string $wikitext): string
    {
        $lines = explode("\n", $wikitext);
        $inTable = false;
        $result = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^\|(.+)$/', $line)) {
                if (!$inTable) {
                    $result[] = '<table class="wikitable">';
                    $inTable = true;
                }
                
                $cells = explode('|', $line);
                array_shift($cells); // Remove first empty element
                
                $row = '<tr>';
                foreach ($cells as $cell) {
                    $cell = trim($cell);
                    if (preg_match('/^!/, $cell)) {
                        // Header cell
                        $cell = preg_replace('/^!/', '', $cell);
                        $row .= '<th>' . $this->parseInlineFormatting(trim($cell)) . '</th>';
                    } else {
                        // Regular cell
                        $row .= '<td>' . $this->parseInlineFormatting(trim($cell)) . '</td>';
                    }
                }
                $row .= '</tr>';
                $result[] = $row;
            } else {
                if ($inTable && trim($line) !== '') {
                    $result[] = '</table>';
                    $inTable = false;
                }
                $result[] = $line;
            }
        }
        
        if ($inTable) {
            $result[] = '</table>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Parse links
     */
    private function parseLinks(string $wikitext): string
    {
        // Internal links [[Page|Display text]]
        $wikitext = preg_replace_callback('/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/', function($matches) {
            $page = $matches[1];
            $display = $matches[2] ?? $page;
            
            // Handle special namespaces
            if (strpos($page, ':') !== false) {
                [$namespace, $title] = explode(':', $page, 2);
                $this->links[] = ['type' => 'internal', 'namespace' => $namespace, 'title' => $title];
                return '<a href="/' . strtolower($namespace) . '/' . urlencode($title) . '">' . htmlspecialchars($display) . '</a>';
            }
            
            $this->links[] = ['type' => 'internal', 'namespace' => 'Main', 'title' => $page];
            return '<a href="/wiki/' . urlencode($page) . '">' . htmlspecialchars($display) . '</a>';
        }, $wikitext);
        
        // External links [http://example.com Display text]
        $wikitext = preg_replace_callback('/\[(https?:\/\/[^\s\]]+)(?:\s+([^\]]+))?\]/', function($matches) {
            $url = $matches[1];
            $display = $matches[2] ?? $url;
            
            $this->links[] = ['type' => 'external', 'url' => $url];
            return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">' . htmlspecialchars($display) . '</a>';
        }, $wikitext);
        
        // Bare URLs
        $wikitext = preg_replace_callback('/(https?:\/\/[^\s]+)/', function($matches) {
            $url = $matches[1];
            $this->links[] = ['type' => 'external', 'url' => $url];
            return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">' . htmlspecialchars($url) . '</a>';
        }, $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse formatting
     */
    private function parseFormatting(string $wikitext): string
    {
        // Bold '''text'''
        $wikitext = preg_replace('/\'\'\'(.+?)\'\'\'/', '<strong>$1</strong>', $wikitext);
        
        // Italic ''text''
        $wikitext = preg_replace('/\'\'(.+?)\'\'/', '<em>$1</em>', $wikitext);
        
        // Underline __text__
        $wikitext = preg_replace('/__(.+?)__/', '<u>$1</u>', $wikitext);
        
        // Strikethrough ~~text~~
        $wikitext = preg_replace('/~~(.+?)~~/', '<del>$1</del>', $wikitext);
        
        // Monospace `text`
        $wikitext = preg_replace('/`(.+?)`/', '<code>$1</code>', $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse Islamic-specific markup
     */
    private function parseIslamicMarkup(string $wikitext): string
    {
        // Quran references {{Quran|1|1|5}}
        $wikitext = preg_replace_callback('/\{\{Quran\|(\d+)\|(\d+)(?:\|(\d+))?\}\}/', function($matches) {
            $surah = $matches[1];
            $ayah = $matches[2];
            $endAyah = $matches[3] ?? $ayah;
            
            if ($endAyah == $ayah) {
                return '<quran-verse surah="' . $surah . '" ayah="' . $ayah . '"></quran-verse>';
            } else {
                return '<quran-verse surah="' . $surah . '" ayah="' . $ayah . '" end-ayah="' . $endAyah . '"></quran-verse>';
            }
        }, $wikitext);
        
        // Hadith references {{Hadith|Bukhari|1|1}}
        $wikitext = preg_replace_callback('/\{\{Hadith\|([^|]+)\|(\d+)\|(\d+)\}\}/', function($matches) {
            $collection = $matches[1];
            $book = $matches[2];
            $number = $matches[3];
            
            return '<hadith-reference collection="' . htmlspecialchars($collection) . '" book="' . $book . '" number="' . $number . '"></hadith-reference>';
        }, $wikitext);
        
        // Arabic text with diacritics
        $wikitext = preg_replace('/\[\[ar:([^\]]+)\]\]/', '<span lang="ar" class="arabic-text">$1</span>', $wikitext);
        
        // Islamic dates
        $wikitext = preg_replace('/\{\{HijriDate\|(\d{4})\|(\d{1,2})\|(\d{1,2})\}\}/', '<islamic-date year="$1" month="$2" day="$3"></islamic-date>', $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Parse inline formatting within other elements
     */
    private function parseInlineFormatting(string $text): string
    {
        $text = $this->parseFormatting($text);
        $text = $this->parseLinks($text);
        return $text;
    }
    
    /**
     * Parse paragraphs
     */
    private function parseParagraphs(string $wikitext): string
    {
        $lines = explode("\n", $wikitext);
        $result = [];
        $currentParagraph = '';
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if ($trimmed === '') {
                if ($currentParagraph !== '') {
                    $result[] = '<p>' . $currentParagraph . '</p>';
                    $currentParagraph = '';
                }
            } elseif (preg_match('/^<[a-z][1-6]?>/', $trimmed) || 
                     preg_match('/^<(ul|ol|dl|table|div|section)/', $trimmed) ||
                     preg_match('/^<\/(ul|ol|dl|table|div|section)>/', $trimmed)) {
                // HTML elements - don't wrap in paragraphs
                if ($currentParagraph !== '') {
                    $result[] = '<p>' . $currentParagraph . '</p>';
                    $currentParagraph = '';
                }
                $result[] = $trimmed;
            } else {
                if ($currentParagraph !== '') {
                    $currentParagraph .= ' ' . $trimmed;
                } else {
                    $currentParagraph = $trimmed;
                }
            }
        }
        
        if ($currentParagraph !== '') {
            $result[] = '<p>' . $currentParagraph . '</p>';
        }
        
        return implode("\n", $result);
    }
    
    /**
     * Extract templates
     */
    private function extractTemplates(string $wikitext): string
    {
        $wikitext = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) {
            $template = $matches[1];
            $this->templates[] = $template;
            return '<!-- TEMPLATE: ' . htmlspecialchars($template) . ' -->';
        }, $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Extract categories
     */
    private function extractCategories(string $wikitext): string
    {
        $wikitext = preg_replace_callback('/\[\[Category:([^\]]+)\]\]/', function($matches) {
            $category = $matches[1];
            $this->categories[] = $category;
            return '<!-- CATEGORY: ' . htmlspecialchars($category) . ' -->';
        }, $wikitext);
        
        return $wikitext;
    }
    
    /**
     * Add metadata to HTML
     */
    private function addMetadata(string $html, array $options): string
    {
        $metadata = '';
        
        // Add categories
        if (!empty($this->categories)) {
            $metadata .= '<div class="categories">';
            $metadata .= '<strong>Categories:</strong> ';
            foreach ($this->categories as $category) {
                $metadata .= '<a href="/Category:' . urlencode($category) . '">' . htmlspecialchars($category) . '</a> ';
            }
            $metadata .= '</div>';
        }
        
        // Add templates info
        if (!empty($this->templates) && isset($options['show_templates']) && $options['show_templates']) {
            $metadata .= '<div class="templates">';
            $metadata .= '<strong>Templates used:</strong> ';
            foreach ($this->templates as $template) {
                $metadata .= '<a href="/Template:' . urlencode($template) . '">' . htmlspecialchars($template) . '</a> ';
            }
            $metadata .= '</div>';
        }
        
        if ($metadata !== '') {
            $html .= "\n" . $metadata;
        }
        
        return $html;
    }
    
    /**
     * Reset parser state
     */
    private function reset(): void
    {
        $this->templates = [];
        $this->categories = [];
        $this->links = [];
        $this->images = [];
        $this->currentNamespace = 'Main';
    }
    
    /**
     * Get extracted templates
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
    
    /**
     * Get extracted categories
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
    
    /**
     * Get extracted links
     */
    public function getLinks(): array
    {
        return $this->links;
    }
} 