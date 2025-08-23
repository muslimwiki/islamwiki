<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Processors;

/**
 * Enhanced Markdown Processor with Wiki Extensions
 * 
 * This processor combines standard Markdown processing with custom wiki extensions
 * to provide a powerful content creation system for IslamWiki.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class EnhancedMarkdownProcessor
{
    private MarkdownProcessor $baseProcessor;
    private WikiExtensionProcessor $wikiProcessor;
    private IslamicExtensionProcessor $islamicProcessor;
    
    public function __construct()
    {
        $this->baseProcessor = new MarkdownProcessor();
        $this->wikiProcessor = new WikiExtensionProcessor();
        $this->islamicProcessor = new IslamicExtensionProcessor();
    }
    
    /**
     * Process Enhanced Markdown content with wiki extensions
     * 
     * @param string $markdown The markdown content to process
     * @return string Processed HTML content
     */
    public function process(string $markdown): string
    {
        // Step 1: Process standard Markdown
        $html = $this->baseProcessor->process($markdown);
        
        // Step 2: Process wiki extensions
        $html = $this->wikiProcessor->process($html);
        
        // Step 3: Process Islamic content extensions
        $html = $this->islamicProcessor->process($html);
        
        return $html;
    }
    
    /**
     * Get the base Markdown processor
     */
    public function getBaseProcessor(): MarkdownProcessor
    {
        return $this->baseProcessor;
    }
    
    /**
     * Get the wiki extension processor
     */
    public function getWikiProcessor(): WikiExtensionProcessor
    {
        return $this->wikiProcessor;
    }
    
    /**
     * Get the Islamic extension processor
     */
    public function getIslamicProcessor(): IslamicExtensionProcessor
    {
        return $this->islamicProcessor;
    }
} 