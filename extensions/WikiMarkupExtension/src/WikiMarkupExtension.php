<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Database\Connection;

/**
 * Wiki Markup Extension
 *
 * Provides MediaWiki-style syntax support including:
 * - Internal links: [[Page]] and [[Page|Display Text]]
 * - Templates: {{Template}} and {{Template|param1|param2}}
 * - Headers: === Header ===
 * - Lists: *, #, ;, :
 * 
 * @package IslamWiki\Extensions\WikiMarkupExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiMarkupExtension extends Extension
{
    /**
     * @var WikiMarkupParser
     */
    private WikiMarkupParser $parser;

    /**
     * @var TemplateEngine
     */
    private TemplateEngine $templateEngine;

    /**
     * @var array Extension-specific configuration options
     */
    private array $extensionConfig;

        /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadConfiguration();
        $this->parser = new WikiMarkupParser($this->extensionConfig);
        
        // Initialize template engine if database connection is available
        $this->initializeTemplateEngine();
        
        $this->registerHooks();

        error_log('WikiMarkupExtension initialized successfully with config: ' . json_encode($this->extensionConfig));
    }

    /**
     * Load extension configuration
     */
    private function loadConfiguration(): void
    {
        $this->extensionConfig = $this->getConfig() ?? [
            'enable_wiki_markup' => true,
            'parse_internal_links' => true,
            'parse_templates' => true,
            'parse_headers' => true,
            'parse_lists' => true
        ];
    }

    /**
     * Initialize template engine
     */
    private function initializeTemplateEngine(): void
    {
        try {
            // Try to get database connection from container
            $container = $this->getContainer();
            if ($container && $container->has('mizan.database')) {
                $database = $container->get('mizan.database');
                // For now, we'll create a mock connection since we need to adapt to Database
                // This will be properly implemented when we integrate with Database's connection system
                $this->templateEngine = new TemplateEngine(null, $this->extensionConfig);
                error_log('WikiMarkupExtension: TemplateEngine initialized (mock mode)');
            } else {
                $this->templateEngine = new TemplateEngine(null, $this->extensionConfig);
                error_log('WikiMarkupExtension: TemplateEngine initialized (mock mode - no database)');
            }
        } catch (\Exception $e) {
            error_log('WikiMarkupExtension: Failed to initialize TemplateEngine: ' . $e->getMessage());
            // Create a mock template engine for now
            $this->templateEngine = new TemplateEngine(null, $this->extensionConfig);
        }
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hook - process wiki markup
        $hookManager->register('ContentParse', [$this, 'onContentParse'], 10);

        // Post-render hook - finalize HTML output
        $hookManager->register('ContentPostRender', [$this, 'onContentPostRender'], 10);

        error_log('WikiMarkupExtension hooks registered: ContentParse, ContentPostRender');
    }

    /**
     * Content parsing hook
     * 
     * @param string $content The content to parse
     * @param array $context Additional context information
     */
    public function onContentParse(string &$content, array $context = []): void
    {
        if (!$this->extensionConfig['enable_wiki_markup']) {
            return;
        }

        try {
            $originalContent = $content;
            $content = $this->parser->parse($content);
            
            error_log('Wiki markup parsing completed: ' . strlen($originalContent) . ' -> ' . strlen($content) . ' chars');
        } catch (\Exception $e) {
            error_log('Wiki markup parsing failed: ' . $e->getMessage());
            // Don't break content processing if parsing fails
        }
    }

    /**
     * Post-render hook
     * 
     * @param string $html The HTML output to process
     * @param array $context Additional context information
     */
    public function onContentPostRender(string &$html, array $context = []): void
    {
        if (!$this->extensionConfig['enable_wiki_markup']) {
            return;
        }

        try {
            $originalHtml = $html;
            
            // Process templates in the HTML
            if ($this->extensionConfig['parse_templates'] && isset($this->templateEngine)) {
                $html = $this->templateEngine->processTemplates($html, $context);
            }
            
            // Post-process the HTML
            $html = $this->parser->postProcess($html);
            
            error_log('Wiki markup post-processing completed: ' . strlen($originalHtml) . ' -> ' . strlen($html) . ' chars');
        } catch (\Exception $e) {
            error_log('Wiki markup post-processing failed: ' . $e->getMessage());
            // Don't break HTML output if post-processing fails
        }
    }

    /**
     * Get extension information
     */
    public function getInfo(): array
    {
        return [
            'name' => 'WikiMarkupExtension',
            'version' => '0.0.1.0',
            'description' => 'MediaWiki-style markup support for IslamWiki',
            'author' => 'IslamWiki Development Team',
            'license' => 'AGPL-3.0',
            'features' => [
                'Internal Links' => $this->extensionConfig['parse_internal_links'],
                'Templates' => $this->extensionConfig['parse_templates'],
                'Headers' => $this->extensionConfig['parse_headers'],
                'Lists' => $this->extensionConfig['parse_lists']
            ]
        ];
    }
} 