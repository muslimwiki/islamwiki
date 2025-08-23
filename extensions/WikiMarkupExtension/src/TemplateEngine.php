<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Database\Connection;

/**
 * Enhanced Template Engine
 *
 * Provides comprehensive MediaWiki template functionality including:
 * - Parameter handling and substitution
 * - Nested templates and recursion protection
 * - Islamic content templates (Quran, Hadith, etc.)
 * - Template caching and performance optimization
 * 
 * @package IslamWiki\Extensions\WikiMarkupExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class TemplateEngine
{
    /**
     * @var Connection|null Database connection
     */
    private ?Connection $database;

    /**
     * @var array Configuration options
     */
    private array $config;

    /**
     * @var array Template cache
     */
    private array $templateCache = [];

    /**
     * @var array Recursion protection
     */
    private array $recursionStack = [];

    /**
     * @var array Built-in templates
     */
    private array $builtinTemplates = [];

    /**
     * Create a new template engine instance
     */
    public function __construct(?Connection $database = null, array $config = [])
    {
        $this->database = $database;
        $this->config = array_merge([
            'enable_templates' => true,
            'max_recursion_depth' => 10,
            'enable_caching' => true,
            'cache_ttl' => 3600,
            'enable_islamic_templates' => true,
            'enable_math_templates' => true,
            'enable_media_templates' => true
        ], $config);

        $this->initializeBuiltinTemplates();
    }

    /**
     * Initialize built-in templates
     */
    private function initializeBuiltinTemplates(): void
    {
        $this->builtinTemplates = [
            // Islamic content templates
            'quran' => [
                'description' => 'Quran verse reference',
                'parameters' => ['surah', 'ayah', 'translation', 'tafsir'],
                'handler' => 'handleQuranTemplate'
            ],
            'hadith' => [
                'description' => 'Hadith citation',
                'parameters' => ['collection', 'book', 'number', 'narrator', 'grade'],
                'handler' => 'handleHadithTemplate'
            ],
            'scholar' => [
                'description' => 'Islamic scholar reference',
                'parameters' => ['name', 'era', 'school', 'works'],
                'handler' => 'handleScholarTemplate'
            ],
            'hijri' => [
                'description' => 'Hijri date formatting',
                'parameters' => ['date', 'format', 'locale'],
                'handler' => 'handleHijriTemplate'
            ],
            'prayer' => [
                'description' => 'Prayer time information',
                'parameters' => ['location', 'city', 'date', 'timezone'],
                'handler' => 'handlePrayerTemplate'
            ],
            'fatwa' => [
                'description' => 'Islamic ruling reference',
                'parameters' => ['scholar', 'topic', 'date', 'source'],
                'handler' => 'handleFatwaTemplate'
            ],

            // Media templates
            'image' => [
                'description' => 'Image with caption and options',
                'parameters' => ['file', 'caption', 'alt', 'size', 'align', 'link'],
                'handler' => 'handleImageTemplate'
            ],
            'gallery' => [
                'description' => 'Image gallery',
                'parameters' => ['images', 'caption', 'style', 'perrow'],
                'handler' => 'handleGalleryTemplate'
            ],
            'video' => [
                'description' => 'Video player',
                'parameters' => ['file', 'caption', 'width', 'height', 'autoplay'],
                'handler' => 'handleVideoTemplate'
            ],

            // Layout templates
            'infobox' => [
                'description' => 'Information box',
                'parameters' => ['title', 'content', 'style', 'width'],
                'handler' => 'handleInfoboxTemplate'
            ],
            'quote' => [
                'description' => 'Quotation block',
                'parameters' => ['text', 'author', 'source', 'date', 'style'],
                'handler' => 'handleQuoteTemplate'
            ],
            'warning' => [
                'description' => 'Warning or notice box',
                'parameters' => ['text', 'type', 'icon', 'dismissible'],
                'handler' => 'handleWarningTemplate'
            ],
            'success' => [
                'description' => 'Success message box',
                'parameters' => ['text', 'icon', 'dismissible'],
                'handler' => 'handleSuccessTemplate'
            ],
            'error' => [
                'description' => 'Error message box',
                'parameters' => ['text', 'icon', 'dismissible'],
                'handler' => 'handleErrorTemplate'
            ],

            // Math templates
            'math' => [
                'description' => 'Mathematical formula',
                'parameters' => ['formula', 'display', 'size', 'color'],
                'handler' => 'handleMathTemplate'
            ],
            'equation' => [
                'description' => 'Numbered equation',
                'parameters' => ['formula', 'label', 'reference'],
                'handler' => 'handleEquationTemplate'
            ],

            // Navigation templates
            'navbox' => [
                'description' => 'Navigation box',
                'parameters' => ['title', 'content', 'style', 'collapsible'],
                'handler' => 'handleNavboxTemplate'
            ],
            'sidebar' => [
                'description' => 'Sidebar content',
                'parameters' => ['title', 'content', 'position', 'width'],
                'handler' => 'handleSidebarTemplate'
            ],

            // Utility templates
            'main' => [
                'description' => 'Main article template',
                'parameters' => ['title', 'content', 'category', 'references'],
                'handler' => 'handleMainTemplate'
            ],
            'stub' => [
                'description' => 'Article stub indicator',
                'parameters' => ['type', 'category'],
                'handler' => 'handleStubTemplate'
            ],
            'cleanup' => [
                'description' => 'Cleanup needed indicator',
                'parameters' => ['reason', 'date', 'user'],
                'handler' => 'handleCleanupTemplate'
            ]
        ];
    }

    /**
     * Process templates in content
     */
    public function processTemplates(string $content, array $context = []): string
    {
        if (!$this->config['enable_templates']) {
            return $content;
        }

        // Reset recursion stack for new content
        $this->recursionStack = [];

        // Process templates recursively
        $processedContent = $this->processTemplateRecursive($content, $context, 0);

        return $processedContent;
    }

    /**
     * Process templates recursively with depth protection
     */
    private function processTemplateRecursive(string $content, array $context, int $depth): string
    {
        if ($depth >= $this->config['max_recursion_depth']) {
            return $this->addRecursionWarning($content);
        }

        // Find and process all templates
        $processedContent = preg_replace_callback(
            '/\{\{([^{}]+(?:\{[^{}]*\}[^{}]*)*)\}\}/',
            function ($matches) use ($context, $depth) {
                return $this->processSingleTemplate($matches[1], $context, $depth);
            },
            $content
        );

        // Check if we need to process nested templates
        if (strpos($processedContent, '{{') !== false && $processedContent !== $content) {
            return $this->processTemplateRecursive($processedContent, $context, $depth + 1);
        }

        return $processedContent;
    }

    /**
     * Process a single template
     */
    private function processSingleTemplate(string $templateText, array $context, int $depth): string
    {
        // Parse template name and parameters
        $parts = explode('|', $templateText);
        $templateName = trim($parts[0]);
        $parameters = array_slice($parts, 1);

        // Check recursion
        $templateKey = $templateName . ':' . md5(serialize($parameters));
        if (in_array($templateKey, $this->recursionStack)) {
            return $this->addRecursionWarning("Template: {$templateName}");
        }

        // Add to recursion stack
        $this->recursionStack[] = $templateKey;

        try {
            // Process the template
            $result = $this->executeTemplate($templateName, $parameters, $context);
            
            // Remove from recursion stack
            array_pop($this->recursionStack);
            
            return $result;
        } catch (\Exception $e) {
            // Remove from recursion stack
            array_pop($this->recursionStack);
            
            // Return error message
            return $this->addTemplateError($templateName, $e->getMessage());
        }
    }

    /**
     * Execute a template
     */
    private function executeTemplate(string $templateName, array $parameters, array $context): string
    {
        // Check built-in templates first
        if (isset($this->builtinTemplates[$templateName])) {
            return $this->executeBuiltinTemplate($templateName, $parameters, $context);
        }

        // Check database for custom templates
        if ($this->database) {
            return $this->executeDatabaseTemplate($templateName, $parameters, $context);
        }

        // Return template as-is if not found
        return $this->renderTemplateNotFound($templateName, $parameters);
    }

    /**
     * Execute built-in template
     */
    private function executeBuiltinTemplate(string $templateName, array $parameters, array $context): string
    {
        $template = $this->builtinTemplates[$templateName];
        $handler = $template['handler'];

        if (method_exists($this, $handler)) {
            return $this->$handler($parameters, $context);
        }

        return $this->renderTemplateNotFound($templateName, $parameters);
    }

    /**
     * Execute database template
     */
    private function executeDatabaseTemplate(string $templateName, array $parameters, array $context): string
    {
        // Check cache first
        $cacheKey = "template_{$templateName}_" . md5(serialize($parameters));
        if ($this->config['enable_caching'] && isset($this->templateCache[$cacheKey])) {
            return $this->templateCache[$cacheKey];
        }

        try {
            // Query database for template
            $template = $this->getTemplateFromDatabase($templateName);
            
            if ($template) {
                $result = $this->renderDatabaseTemplate($template, $parameters, $context);
                
                // Cache the result
                if ($this->config['enable_caching']) {
                    $this->templateCache[$cacheKey] = $result;
                }
                
                return $result;
            }
        } catch (\Exception $e) {
            // Log error and continue
            error_log("Template error for {$templateName}: " . $e->getMessage());
        }

        return $this->renderTemplateNotFound($templateName, $parameters);
    }

    /**
     * Get template from database
     */
    private function getTemplateFromDatabase(string $templateName): ?array
    {
        // This would query the database for custom templates
        // For now, return null to indicate no custom template found
        return null;
    }

    /**
     * Render database template
     */
    private function renderDatabaseTemplate(array $template, array $parameters, array $context): string
    {
        $content = $template['content'];
        
        // Replace parameters in template
        foreach ($parameters as $index => $value) {
            $content = str_replace("{{" . ($index + 1) . "}}", $value, $content);
        }
        
        // Replace named parameters
        foreach ($parameters as $param) {
            if (strpos($param, '=') !== false) {
                [$key, $value] = explode('=', $param, 2);
                $content = str_replace("{{" . trim($key) . "}}", trim($value), $content);
            }
        }
        
        return $content;
    }

    // Built-in template handlers

    /**
     * Handle Quran template
     */
    private function handleQuranTemplate(array $parameters, array $context): string
    {
        $surah = $parameters[0] ?? '';
        $ayah = $parameters[1] ?? '';
        $translation = $parameters[2] ?? 'English';
        $tafsir = $parameters[3] ?? '';

        if (!$surah || !$ayah) {
            return '<div class="template-error">Quran template requires surah and ayah parameters</div>';
        }

        return sprintf('
            <div class="quran-template">
                <div class="quran-reference">
                    <span class="surah">Surah %s</span>
                    <span class="ayah">Ayah %s</span>
                </div>
                <div class="quran-content">
                    <div class="arabic-text">[Quran %s:%s Arabic text]</div>
                    <div class="translation">[%s translation]</div>
                    %s
                </div>
            </div>
        ',
            htmlspecialchars($surah, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($ayah, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($surah, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($ayah, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($translation, ENT_QUOTES, 'UTF-8'),
            $tafsir ? '<div class="tafsir">[Tafsir: ' . htmlspecialchars($tafsir, ENT_QUOTES, 'UTF-8') . ']</div>' : ''
        );
    }

    /**
     * Handle Hadith template
     */
    private function handleHadithTemplate(array $parameters, array $context): string
    {
        $collection = $parameters[0] ?? '';
        $book = $parameters[1] ?? '';
        $number = $parameters[2] ?? '';
        $narrator = $parameters[3] ?? '';
        $grade = $parameters[4] ?? '';

        if (!$collection || !$number) {
            return '<div class="template-error">Hadith template requires collection and number parameters</div>';
        }

        return sprintf('
            <div class="hadith-template">
                <div class="hadith-reference">
                    <span class="collection">%s</span>
                    %s
                    <span class="number">#%s</span>
                </div>
                <div class="hadith-content">
                    <div class="arabic-text">[Hadith Arabic text]</div>
                    <div class="translation">[Hadith translation]</div>
                    %s
                    %s
                </div>
            </div>
        ',
            htmlspecialchars($collection, ENT_QUOTES, 'UTF-8'),
            $book ? '<span class="book">Book ' . htmlspecialchars($book, ENT_QUOTES, 'UTF-8') . '</span>' : '',
            htmlspecialchars($number, ENT_QUOTES, 'UTF-8'),
            $narrator ? '<div class="narrator">Narrated by: ' . htmlspecialchars($narrator, ENT_QUOTES, 'UTF-8') . '</div>' : '',
            $grade ? '<div class="grade">Grade: ' . htmlspecialchars($grade, ENT_QUOTES, 'UTF-8') . '</div>' : ''
        );
    }

    /**
     * Handle Image template
     */
    private function handleImageTemplate(array $parameters, array $context): string
    {
        $file = $parameters[0] ?? '';
        $caption = $parameters[1] ?? '';
        $alt = $parameters[2] ?? $caption;
        $size = $parameters[3] ?? 'medium';
        $align = $parameters[4] ?? 'center';
        $link = $parameters[5] ?? '';

        if (!$file) {
            return '<div class="template-error">Image template requires file parameter</div>';
        }

        $imageHtml = sprintf(
            '<img src="/media/%s" alt="%s" class="wiki-image image-%s image-align-%s">',
            htmlspecialchars($file, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($size, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($align, ENT_QUOTES, 'UTF-8')
        );

        if ($link) {
            $imageHtml = sprintf('<a href="%s">%s</a>', htmlspecialchars($link, ENT_QUOTES, 'UTF-8'), $imageHtml);
        }

        return sprintf('
            <figure class="image-template image-%s image-align-%s">
                %s
                %s
            </figure>
        ',
            htmlspecialchars($size, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($align, ENT_QUOTES, 'UTF-8'),
            $imageHtml,
            $caption ? '<figcaption>' . htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') . '</figcaption>' : ''
        );
    }

    /**
     * Handle Infobox template
     */
    private function handleInfoboxTemplate(array $parameters, array $context): string
    {
        $title = $parameters[0] ?? 'Information';
        $content = $parameters[1] ?? '';
        $style = $parameters[2] ?? 'default';
        $width = $parameters[3] ?? '300px';

        return sprintf('
            <div class="infobox infobox-%s" style="width: %s;">
                <div class="infobox-title">%s</div>
                <div class="infobox-content">%s</div>
            </div>
        ',
            htmlspecialchars($style, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($width, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            $content
        );
    }

    /**
     * Handle Math template
     */
    private function handleMathTemplate(array $parameters, array $context): string
    {
        $formula = $parameters[0] ?? '';
        $display = $parameters[1] ?? 'inline';
        $size = $parameters[2] ?? 'normal';
        $color = $parameters[3] ?? '';

        if (!$formula) {
            return '<div class="template-error">Math template requires formula parameter</div>';
        }

        $style = '';
        if ($color) {
            $style = sprintf(' style="color: %s;"', htmlspecialchars($color, ENT_QUOTES, 'UTF-8'));
        }

        $tag = $display === 'block' ? 'div' : 'span';
        $class = "math-{$display} math-size-{$size}";

        return sprintf(
            '<%s class="%s" data-formula="%s"%s>%s</%s>',
            $tag,
            $class,
            htmlspecialchars($formula, ENT_QUOTES, 'UTF-8'),
            $style,
            htmlspecialchars($formula, ENT_QUOTES, 'UTF-8'),
            $tag
        );
    }

    // Utility methods

    /**
     * Add recursion warning
     */
    private function addRecursionWarning(string $content): string
    {
        return sprintf('
            <div class="template-warning">
                <strong>Template Recursion Warning:</strong> Maximum recursion depth exceeded.
                <div class="template-content">%s</div>
            </div>
        ', htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Add template error
     */
    private function addTemplateError(string $templateName, string $error): string
    {
        return sprintf('
            <div class="template-error">
                <strong>Template Error:</strong> %s
                <div class="template-name">Template: %s</div>
            </div>
        ',
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Render template not found
     */
    private function renderTemplateNotFound(string $templateName, array $parameters): string
    {
        $paramList = implode(' | ', array_map('htmlspecialchars', $parameters));
        
        return sprintf('
            <div class="template-not-found">
                <strong>Template not found:</strong> %s
                <div class="template-params">Parameters: %s</div>
                <div class="template-help">
                    <a href="/wiki/Template:%s" class="create-template">Create this template</a>
                </div>
            </div>
        ',
            htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8'),
            $paramList,
            htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        return array_keys($this->builtinTemplates);
    }

    /**
     * Get template information
     */
    public function getTemplateInfo(string $templateName): ?array
    {
        return $this->builtinTemplates[$templateName] ?? null;
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $this->templateCache = [];
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_size' => count($this->templateCache),
            'cache_memory' => memory_get_usage(true),
            'recursion_stack_size' => count($this->recursionStack)
        ];
    }
} 