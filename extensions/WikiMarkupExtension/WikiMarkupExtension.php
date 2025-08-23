<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Database\Connection;

/**
 * Enhanced Wiki Markup Extension
 *
 * Provides comprehensive MediaWiki-style syntax support including:
 * - Internal links: [[Page]] and [[Page|Display Text]]
 * - Templates: {{Template}} and {{Template|param1|param2}}
 * - Headers: === Header ===
 * - Lists: *, #, ;, :
 * - Tables: {| |} |- | ||
 * - Media: [[Image:file.jpg|Caption]]
 * - Categories: [[Category:Name]]
 * - Math: <math>formula</math>
 * - Syntax highlighting: <source lang="php">code</source>
 * - And much more...
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
     * @var WikiMarkupEditor
     */
    private WikiMarkupEditor $editor;

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
        
        // Initialize editor
        $this->initializeEditor();
        
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
            'enable_markdown' => true,
            'default_format' => 'wikimarkup',
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
            'enable_edit_functionality' => true,
            'auto_save_interval' => 30000,
            'show_edit_button' => true,
            'show_source_button' => true,
            'enable_live_preview' => true
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
                $this->templateEngine = new TemplateEngine($database, $this->extensionConfig);
                error_log('WikiMarkupExtension: TemplateEngine initialized with database connection');
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
     * Initialize editor
     */
    private function initializeEditor(): void
    {
        try {
            $container = $this->getContainer();
            $database = null;
            
            if ($container && $container->has('mizan.database')) {
                $database = $container->get('mizan.database');
            }
            
            $this->editor = new WikiMarkupEditor($this->parser, $this->extensionConfig, $database);
            error_log('WikiMarkupExtension: WikiMarkupEditor initialized successfully');
        } catch (\Exception $e) {
            error_log('WikiMarkupExtension: Failed to initialize WikiMarkupEditor: ' . $e->getMessage());
            // Create editor without database connection
            $this->editor = new WikiMarkupEditor($this->parser, $this->extensionConfig);
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

        // Page edit hook - provide edit functionality
        $hookManager->register('PageEdit', [$this, 'onPageEdit'], 10);

        // Page save hook - handle content saving
        $hookManager->register('PageSave', [$this, 'onPageSave'], 10);

        // Editor initialization hook
        $hookManager->register('EditorInit', [$this, 'onEditorInit'], 10);

        error_log('WikiMarkupExtension hooks registered: ContentParse, ContentPostRender, PageEdit, PageSave, EditorInit');
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
            $format = $context['format'] ?? $this->extensionConfig['default_format'];
            
            $content = $this->parser->parse($content, $format);
            
            error_log('Wiki markup parsing completed: ' . strlen($originalContent) . ' -> ' . strlen($content) . ' chars (format: ' . $format . ')');
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
     * Page edit hook
     * 
     * @param array $context Edit context information
     */
    public function onPageEdit(array $context = []): void
    {
        if (!$this->extensionConfig['enable_edit_functionality']) {
            return;
        }

        try {
            $pageTitle = $context['title'] ?? '';
            $currentContent = $context['content'] ?? '';
            $format = $context['format'] ?? $this->extensionConfig['default_format'];
            
            // Generate edit form
            $editForm = $this->editor->generateEditForm($pageTitle, $currentContent, $format);
            
            // Add edit form to context
            $context['edit_form'] = $editForm;
            
            error_log('Wiki markup edit form generated for page: ' . $pageTitle);
        } catch (\Exception $e) {
            error_log('Wiki markup edit form generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Page save hook
     * 
     * @param array $context Save context information
     */
    public function onPageSave(array $context = []): void
    {
        if (!$this->extensionConfig['enable_edit_functionality']) {
            return;
        }

        try {
            $content = $context['content'] ?? '';
            $format = $context['format'] ?? $this->extensionConfig['default_format'];
            
            // Validate content format
            if (!$this->isValidFormat($format)) {
                throw new \InvalidArgumentException('Invalid content format: ' . $format);
            }
            
            // Parse content to validate markup
            $parsedContent = $this->parser->parse($content, $format);
            
            // Add parsed content to context
            $context['parsed_content'] = $parsedContent;
            
            error_log('Wiki markup content saved and parsed successfully (format: ' . $format . ')');
        } catch (\Exception $e) {
            error_log('Wiki markup content save failed: ' . $e->getMessage());
            // Re-throw to prevent save if parsing fails
            throw $e;
        }
    }

    /**
     * Editor initialization hook
     * 
     * @param array $context Editor context information
     */
    public function onEditorInit(array $context = []): void
    {
        if (!$this->extensionConfig['enable_edit_functionality']) {
            return;
        }

        try {
            // Initialize editor with context
            $editorConfig = array_merge($this->extensionConfig, $context);
            $this->editor->updateConfig($editorConfig);
            
            error_log('Wiki markup editor initialized with enhanced configuration');
        } catch (\Exception $e) {
            error_log('Wiki markup editor initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate content format
     */
    private function isValidFormat(string $format): bool
    {
        $validFormats = ['wikimarkup', 'markdown'];
        return in_array($format, $validFormats);
    }

    /**
     * Get edit form for a page
     */
    public function getEditForm(string $pageTitle, string $currentContent, string $format = 'wikimarkup'): string
    {
        if (!$this->extensionConfig['enable_edit_functionality']) {
            return $this->editor->generateReadOnlyView($currentContent, $format);
        }

        return $this->editor->generateEditForm($pageTitle, $currentContent, $format);
    }

    /**
     * Parse content with specified format
     */
    public function parseContent(string $content, string $format = 'wikimarkup'): string
    {
        return $this->parser->parse($content, $format);
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        if (isset($this->templateEngine)) {
            return $this->templateEngine->getAvailableTemplates();
        }
        return [];
    }

    /**
     * Get template information
     */
    public function getTemplateInfo(string $templateName): ?array
    {
        if (isset($this->templateEngine)) {
            return $this->templateEngine->getTemplateInfo($templateName);
        }
        return null;
    }

    /**
     * Get supported markup features
     */
    public function getSupportedFeatures(): array
    {
        return $this->parser->getSupportedFeatures();
    }

    /**
     * Get extension information
     */
    public function getInfo(): array
    {
        return [
            'name' => 'WikiMarkupExtension',
            'version' => '0.0.1.3',
            'description' => 'Comprehensive MediaWiki-style markup and Markdown support for IslamWiki with edit functionality',
            'author' => 'IslamWiki Development Team',
            'license' => 'AGPL-3.0',
            'features' => [
                'WikiMarkup Support' => $this->extensionConfig['enable_wiki_markup'],
                'Markdown Support' => $this->extensionConfig['enable_markdown'],
                'Template System' => $this->extensionConfig['parse_templates'],
                'Edit Functionality' => $this->extensionConfig['enable_edit_functionality'],
                'Live Preview' => $this->extensionConfig['enable_live_preview'],
                'Auto-save' => $this->extensionConfig['auto_save_interval'] > 0,
                'Syntax Help' => true,
                'Media Support' => $this->extensionConfig['parse_media'],
                'Math Support' => $this->extensionConfig['parse_math'],
                'Syntax Highlighting' => $this->extensionConfig['parse_syntax_highlighting']
            ],
            'supported_formats' => ['wikimarkup', 'markdown'],
            'default_format' => $this->extensionConfig['default_format'],
            'templates_available' => count($this->getAvailableTemplates()),
            'features_available' => count($this->getSupportedFeatures())
        ];
    }
} 