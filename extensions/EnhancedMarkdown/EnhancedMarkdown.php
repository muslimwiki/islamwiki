<?php

namespace IslamWiki\Extensions\EnhancedMarkdown;

use IslamWiki\Extensions\EnhancedMarkdown\Processors\EnhancedMarkdownProcessor;
use IslamWiki\Extensions\EnhancedMarkdown\Processors\MarkdownProcessor;
use IslamWiki\Extensions\EnhancedMarkdown\Processors\WikiExtensionProcessor;
use IslamWiki\Extensions\EnhancedMarkdown\Processors\IslamicExtensionProcessor;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\CategoryManager;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\ReferenceManager;

/**
 * Enhanced Markdown Extension
 *
 * Main class for the Enhanced Markdown extension that provides
 * Markdown with wiki extensions and Islamic content features.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class EnhancedMarkdown
{
    private EnhancedMarkdownProcessor $processor;
    private TemplateManager $templateManager;
    private CategoryManager $categoryManager;
    private ReferenceManager $referenceManager;
    
    public function __construct($connection = null, $templateManager = null, $categoryManager = null, $referenceManager = null)
    {
        // Create managers with connection if provided, or use provided managers
        if ($templateManager) {
            $this->templateManager = $templateManager;
        } elseif ($connection) {
            $this->templateManager = new TemplateManager($connection);
        } else {
            // Create mock manager for testing
            $this->templateManager = new TemplateManager(new MockConnection());
        }
        
        if ($categoryManager) {
            $this->categoryManager = $categoryManager;
        } elseif ($connection) {
            $this->categoryManager = new CategoryManager($connection);
        } else {
            // Create mock manager for testing
            $this->categoryManager = new CategoryManager(new MockConnection());
        }
        
        if ($referenceManager) {
            $this->referenceManager = $referenceManager;
        } elseif ($connection) {
            $this->referenceManager = new ReferenceManager($connection);
        } else {
            // Create mock manager for testing
            $this->referenceManager = new ReferenceManager(new MockConnection());
        }
        
        // Create processors
        $markdownProcessor = new MarkdownProcessor();
        $wikiProcessor = new WikiExtensionProcessor($this->templateManager, $this->categoryManager, $this->referenceManager);
        $islamicProcessor = new IslamicExtensionProcessor();
        
        // Create main processor
        $this->processor = new EnhancedMarkdownProcessor(
            $markdownProcessor,
            $wikiProcessor,
            $islamicProcessor
        );
    }

    /**
     * Process Enhanced Markdown content
     * 
     * @param string $content The content to process
     * @return string Processed HTML content
     */
    public function process(string $content): string
    {
        return $this->processor->process($content);
    }
    
    /**
     * Get template manager
     */
    public function getTemplateManager(): TemplateManager
    {
        return $this->templateManager;
    }
    
    /**
     * Get category manager
     */
    public function getCategoryManager(): CategoryManager
    {
        return $this->categoryManager;
    }
    
    /**
     * Get reference manager
     */
    public function getReferenceManager(): ReferenceManager
    {
        return $this->referenceManager;
    }
}

/**
 * Mock connection for testing when no real database is available
 */
class MockConnection {
    private array $templates = [
        'Template:Good article' => [
            'content' => '<div class="template article-quality"><i class="fas fa-star"></i> This is a good article</div>',
            'namespace' => 'Template',
            'is_active' => 1
        ],
        'Template:About' => [
            'content' => '<div class="template about-template"><p>This article is about <strong>{{{1}}}</strong>{{#if:{{{3}}}|. For other uses, see <a href="/wiki/{{{3}}}">{{{3}}}</a>|}}.</p></div>',
            'namespace' => 'Template',
            'is_active' => 1
        ],
        'Template:Infobox' => [
            'content' => '<div class="template infobox"><h3>{{{title|Article Title}}}</h3><div class="infobox-content">{{{1}}}</div></div>',
            'namespace' => 'Template',
            'is_active' => 1
        ]
    ];
    
    public function prepare($query) {
        return new MockStatement($this->templates, $query);
    }
}

class MockStatement {
    private array $templates;
    private string $query;
    private array $params = [];
    
    public function __construct(array $templates, string $query) {
        $this->templates = $templates;
        $this->query = $query;
    }
    
    public function execute($params = []) {
        $this->params = $params;
    }
    
    public function fetch() {
        // Handle different types of queries
        if (strpos($this->query, 'SELECT title FROM pages WHERE namespace = \'Template\'') !== false) {
            // This is the listTemplates query
            static $templateIndex = 0;
            $templateNames = array_keys($this->templates);
            if ($templateIndex < count($templateNames)) {
                $templateName = $templateNames[$templateIndex];
                $templateIndex++;
                return ['title' => $templateName];
            }
            return false;
        } elseif (strpos($this->query, 'SELECT content FROM pages WHERE title = ? AND namespace = \'Template\'') !== false) {
            // This is the loadTemplate query
            $templateName = $this->params[0];
            if (isset($this->templates[$templateName])) {
                return $this->templates[$templateName];
            }
        }
        return false;
    }
}
