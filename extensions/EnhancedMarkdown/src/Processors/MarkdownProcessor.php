<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Processors;

/**
 * Base Markdown Processor
 * 
 * Handles standard Markdown syntax processing for IslamWiki.
 * This processor converts standard Markdown to HTML.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class MarkdownProcessor
{
    /**
     * Process standard Markdown content
     * 
     * @param string $markdown The markdown content to process
     * @return string Processed HTML content
     */
    public function process(string $markdown): string
    {
        $html = $markdown;
        
        // Process headings
        $html = $this->processHeadings($html);
        
        // Process text formatting
        $html = $this->processTextFormatting($html);
        
        // Process lists
        $html = $this->processLists($html);
        
        // Process links and images
        $html = $this->processLinksAndImages($html);
        
        // Process code blocks
        $html = $this->processCodeBlocks($html);
        
        // Process blockquotes
        $html = $this->processBlockquotes($html);
        
        // Process tables
        $html = $this->processTables($html);
        
        // Process horizontal rules
        $html = $this->processHorizontalRules($html);
        
        return $html;
    }
    
    /**
     * Process headings (# ## ###)
     */
    private function processHeadings(string $html): string
    {
        // H1
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        
        // H2
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        
        // H3
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        
        // H4
        $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);
        
        // H5
        $html = preg_replace('/^##### (.+)$/m', '<h5>$1</h5>', $html);
        
        // H6
        $html = preg_replace('/^###### (.+)$/m', '<h6>$1</h6>', $html);
        
        return $html;
    }
    
    /**
     * Process text formatting (**bold**, *italic*, etc.)
     */
    private function processTextFormatting(string $html): string
    {
        // Bold
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        
        // Italic
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*\*(.+?)\*\*\*/s', '<strong><em>$1</em></strong>', $html);
        
        // Strikethrough
        $html = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $html);
        
        // Inline code
        $html = preg_replace('/`(.+?)`/s', '<code>$1</code>', $html);
        
        return $html;
    }
    
    /**
     * Process lists (- item, 1. item)
     */
    private function processLists(string $html): string
    {
        // Unordered lists
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = $this->wrapListItems($html, '<li>', '</li>', '<ul>', '</ul>');
        
        // Ordered lists
        $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
        $html = $this->wrapListItems($html, '<li>', '</li>', '<ol>', '</ol>');
        
        return $html;
    }
    
    /**
     * Wrap list items in appropriate list tags
     */
    private function wrapListItems(string $html, string $itemStart, string $itemEnd, string $listStart, string $listEnd): string
    {
        $pattern = '/' . preg_quote($itemStart, '/') . '(.+?)' . preg_quote($itemEnd, '/') . '/s';
        
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[0] as $match) {
                $replacement = $listStart . $match . $listEnd;
                $html = str_replace($match, $replacement, $html);
            }
        }
        
        return $html;
    }
    
    /**
     * Process links and images
     */
    private function processLinksAndImages(string $html): string
    {
        // Links [text](url)
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/s', '<a href="$2">$1</a>', $html);
        
        // Images ![alt](url)
        $html = preg_replace('/!\[(.+?)\]\((.+?)\)/s', '<img src="$2" alt="$1">', $html);
        
        return $html;
    }
    
    /**
     * Process code blocks
     */
    private function processCodeBlocks(string $html): string
    {
        // Fenced code blocks ```language
        $html = preg_replace('/```(\w+)?\n(.+?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $html);
        
        // Inline code is already processed in processTextFormatting
        
        return $html;
    }
    
    /**
     * Process blockquotes
     */
    private function processBlockquotes(string $html): string
    {
        $html = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $html);
        
        return $html;
    }
    
    /**
     * Process tables
     */
    private function processTables(string $html): string
    {
        // Simple table processing
        $lines = explode("\n", $html);
        $inTable = false;
        $tableHtml = '';
        
        foreach ($lines as $line) {
            if (preg_match('/^\|(.+)\|$/', $line)) {
                if (!$inTable) {
                    $tableHtml = '<table><tbody>';
                    $inTable = true;
                }
                
                $cells = explode('|', trim($line, '|'));
                $tableHtml .= '<tr>';
                foreach ($cells as $cell) {
                    $tableHtml .= '<td>' . trim($cell) . '</td>';
                }
                $tableHtml .= '</tr>';
            } elseif ($inTable) {
                $tableHtml .= '</tbody></table>';
                $inTable = false;
            }
        }
        
        if ($inTable) {
            $tableHtml .= '</tbody></table>';
        }
        
        return $html;
    }
    
    /**
     * Process horizontal rules
     */
    private function processHorizontalRules(string $html): string
    {
        $html = preg_replace('/^---$/m', '<hr>', $html);
        $html = preg_replace('/^\*\*\*$/m', '<hr>', $html);
        
        return $html;
    }
} 