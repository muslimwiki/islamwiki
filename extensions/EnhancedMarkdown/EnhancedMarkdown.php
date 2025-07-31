<?php
declare(strict_types=1);

namespace IslamWiki\Extensions\EnhancedMarkdown;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Enhanced Markdown Extension
 * 
 * Provides enhanced Markdown support with Islamic content syntax,
 * Arabic text handling, and pre-built templates for Islamic content.
 */
class EnhancedMarkdown extends Extension
{
    /**
     * @var array Islamic syntax patterns
     */
    private array $islamicSyntax = [];

    /**
     * @var array Available templates
     */
    private array $templates = [];

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadIslamicSyntax();
        $this->loadTemplates();
        $this->registerHooks();
    }

    /**
     * Load Islamic syntax patterns from configuration
     */
    private function loadIslamicSyntax(): void
    {
        $config = $this->getConfig();
        $this->islamicSyntax = $config['islamicSyntax'] ?? [];
    }

    /**
     * Load available templates
     */
    private function loadTemplates(): void
    {
        $config = $this->getConfig();
        $this->templates = $config['templates'] ?? [];
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hook
        $hookManager->register('ContentParse', [$this, 'onContentParse'], 10);

        // Editor initialization hook
        $hookManager->register('EditorInit', [$this, 'onEditorInit'], 10);

        // Article save hook
        $hookManager->register('ArticleSave', [$this, 'onArticleSave'], 10);

        // Template loading hook
        $hookManager->register('TemplateLoad', [$this, 'onTemplateLoad'], 10);
    }

    /**
     * Enhanced content parsing hook
     *
     * @param string $content The content to parse
     * @param string $format The content format
     * @return string The parsed content
     */
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        if ($format !== 'markdown') {
            return $content;
        }

        // Parse Islamic syntax
        $content = $this->parseIslamicSyntax($content);

        // Parse Arabic text
        $content = $this->parseArabicText($content);

        return $content;
    }

    /**
     * Parse Islamic syntax patterns
     *
     * @param string $content The content to parse
     * @return string The parsed content
     */
    private function parseIslamicSyntax(string $content): string
    {
        // Parse Quran verse references
        $content = preg_replace_callback(
            '/{{quran:(\d+):(\d+)}}/',
            [$this, 'parseQuranVerse'],
            $content
        );

        // Parse Hadith citations
        $content = preg_replace_callback(
            '/{{hadith:([^:]+):(\d+):(\d+)}}/',
            [$this, 'parseHadithCitation'],
            $content
        );

        // Parse Islamic dates
        $content = preg_replace_callback(
            '/{{hijri:(\d{4}-\d{2}-\d{2})}}/',
            [$this, 'parseIslamicDate'],
            $content
        );

        // Parse prayer times
        $content = preg_replace_callback(
            '/{{prayer-times:location:([^}]+)}}/',
            [$this, 'parsePrayerTimes'],
            $content
        );

        // Parse scholar references
        $content = preg_replace_callback(
            '/{{scholar:([^}]+)}}/',
            [$this, 'parseScholarReference'],
            $content
        );

        return $content;
    }

    /**
     * Parse Quran verse reference
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    private function parseQuranVerse(array $matches): string
    {
        $surah = (int) $matches[1];
        $ayah = (int) $matches[2];

        return sprintf(
            '<div class="quran-verse" data-surah="%d" data-ayah="%d">
                <div class="verse-header">
                    <span class="verse-number">%d:%d</span>
                    <span class="verse-title">Surah %d, Ayah %d</span>
                </div>
                <div class="verse-content">
                    <div class="arabic-text" dir="rtl">[Quran verse content]</div>
                    <div class="translation">[Translation content]</div>
                </div>
            </div>',
            $surah,
            $ayah,
            $surah,
            $ayah,
            $surah,
            $ayah
        );
    }

    /**
     * Parse Hadith citation
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    private function parseHadithCitation(array $matches): string
    {
        $collection = $matches[1];
        $book = (int) $matches[2];
        $number = (int) $matches[3];

        return sprintf(
            '<div class="hadith-citation" data-collection="%s" data-book="%d" data-number="%d">
                <div class="hadith-header">
                    <span class="collection-name">%s</span>
                    <span class="hadith-number">Book %d, Hadith %d</span>
                </div>
                <div class="hadith-content">
                    <div class="arabic-text" dir="rtl">[Hadith Arabic text]</div>
                    <div class="translation">[Hadith translation]</div>
                </div>
            </div>',
            $collection,
            $book,
            $number,
            ucfirst($collection),
            $book,
            $number
        );
    }

    /**
     * Parse Islamic date
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    private function parseIslamicDate(array $matches): string
    {
        $hijriDate = $matches[1];

        return sprintf(
            '<span class="islamic-date" data-hijri="%s">
                <span class="hijri-date">%s</span>
                <span class="gregorian-date">[Gregorian equivalent]</span>
            </span>',
            $hijriDate,
            $hijriDate
        );
    }

    /**
     * Parse prayer times
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    private function parsePrayerTimes(array $matches): string
    {
        $location = $matches[1];

        return sprintf(
            '<div class="prayer-times-widget" data-location="%s">
                <div class="prayer-times-header">
                    <h4>Prayer Times - %s</h4>
                </div>
                <div class="prayer-times-content">
                    <div class="prayer-time">
                        <span class="prayer-name">Fajr</span>
                        <span class="prayer-time">[Time]</span>
                    </div>
                    <div class="prayer-time">
                        <span class="prayer-name">Dhuhr</span>
                        <span class="prayer-time">[Time]</span>
                    </div>
                    <div class="prayer-time">
                        <span class="prayer-name">Asr</span>
                        <span class="prayer-time">[Time]</span>
                    </div>
                    <div class="prayer-time">
                        <span class="prayer-name">Maghrib</span>
                        <span class="prayer-time">[Time]</span>
                    </div>
                    <div class="prayer-time">
                        <span class="prayer-name">Isha</span>
                        <span class="prayer-time">[Time]</span>
                    </div>
                </div>
            </div>',
            $location,
            ucfirst($location)
        );
    }

    /**
     * Parse scholar reference
     *
     * @param array $matches Regex matches
     * @return string HTML output
     */
    private function parseScholarReference(array $matches): string
    {
        $scholarName = $matches[1];

        return sprintf(
            '<span class="scholar-reference" data-scholar="%s">
                <a href="/scholars/%s" class="scholar-link">%s</a>
            </span>',
            $scholarName,
            $scholarName,
            ucwords(str_replace('-', ' ', $scholarName))
        );
    }

    /**
     * Parse Arabic text with proper RTL handling
     *
     * @param string $content The content to parse
     * @return string The parsed content
     */
    private function parseArabicText(string $content): string
    {
        // Detect Arabic text blocks and wrap them in RTL containers
        $content = preg_replace_callback(
            '/([\u0600-\u06FF\s]+)/u',
            function($matches) {
                $arabicText = trim($matches[1]);
                if (empty($arabicText)) {
                    return $matches[0];
                }
                
                return sprintf(
                    '<span class="arabic-text" dir="rtl" lang="ar">%s</span>',
                    htmlspecialchars($arabicText, ENT_QUOTES, 'UTF-8')
                );
            },
            $content
        );

        return $content;
    }

    /**
     * Editor initialization hook
     *
     * @param array $editorConfig Editor configuration
     * @return array Modified editor configuration
     */
    public function onEditorInit(array $editorConfig): array
    {
        // Add Islamic syntax shortcuts
        $editorConfig['shortcuts'] = array_merge(
            $editorConfig['shortcuts'] ?? [],
            [
                'quran' => '{{quran:1:1}}',
                'hadith' => '{{hadith:bukhari:1:1}}',
                'hijri' => '{{hijri:1445-01-01}}',
                'prayer' => '{{prayer-times:location:mecca}}',
                'scholar' => '{{scholar:ibn-taymiyyah}}',
            ]
        );

        // Add Arabic keyboard support
        $editorConfig['arabicSupport'] = true;
        $editorConfig['rtlSupport'] = true;

        // Add template support
        $editorConfig['templates'] = $this->templates;

        return $editorConfig;
    }

    /**
     * Article save hook
     *
     * @param array $articleData Article data
     * @param array $userData User data
     * @return array Modified article data
     */
    public function onArticleSave(array $articleData, array $userData): array
    {
        // Validate Islamic syntax
        $articleData['content'] = $this->validateIslamicSyntax($articleData['content']);

        // Add metadata about Islamic content
        if ($this->containsIslamicContent($articleData['content'])) {
            $articleData['is_islamic'] = true;
            $articleData['islamic_tags'] = $this->extractIslamicTags($articleData['content']);
        }

        return $articleData;
    }

    /**
     * Template loading hook
     *
     * @param string $templateName Template name
     * @return string|null Template content or null if not found
     */
    public function onTemplateLoad(string $templateName): ?string
    {
        if (!isset($this->templates[$templateName])) {
            return null;
        }

        $templatePath = $this->getExtensionPath() . '/templates/' . $this->templates[$templateName];
        
        if (!file_exists($templatePath)) {
            return null;
        }

        return file_get_contents($templatePath);
    }

    /**
     * Validate Islamic syntax in content
     *
     * @param string $content The content to validate
     * @return string The validated content
     */
    private function validateIslamicSyntax(string $content): string
    {
        // Validate Quran verse references
        $content = preg_replace_callback(
            '/{{quran:(\d+):(\d+)}}/',
            function($matches) {
                $surah = (int) $matches[1];
                $ayah = (int) $matches[2];
                
                // Validate surah number (1-114)
                if ($surah < 1 || $surah > 114) {
                    return sprintf('<!-- Invalid surah number: %d -->', $surah);
                }
                
                // Validate ayah number (basic check)
                if ($ayah < 1) {
                    return sprintf('<!-- Invalid ayah number: %d -->', $ayah);
                }
                
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Check if content contains Islamic syntax
     *
     * @param string $content The content to check
     * @return bool True if content contains Islamic syntax
     */
    private function containsIslamicContent(string $content): bool
    {
        $patterns = [
            '/{{quran:\d+:\d+}}/',
            '/{{hadith:[^}]+}}/',
            '/{{hijri:\d{4}-\d{2}-\d{2}}}/',
            '/{{prayer-times:[^}]+}}/',
            '/{{scholar:[^}]+}}/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract Islamic tags from content
     *
     * @param string $content The content to analyze
     * @return array Array of Islamic tags
     */
    private function extractIslamicTags(string $content): array
    {
        $tags = [];

        // Extract Quran references
        if (preg_match_all('/{{quran:(\d+):(\d+)}}/', $content, $matches)) {
            $tags[] = 'quran';
        }

        // Extract Hadith references
        if (preg_match_all('/{{hadith:([^:]+):(\d+):(\d+)}}/', $content, $matches)) {
            $tags[] = 'hadith';
        }

        // Extract Islamic dates
        if (preg_match_all('/{{hijri:\d{4}-\d{2}-\d{2}}}/', $content, $matches)) {
            $tags[] = 'islamic-calendar';
        }

        // Extract prayer times
        if (preg_match_all('/{{prayer-times:[^}]+}}/', $content, $matches)) {
            $tags[] = 'prayer-times';
        }

        // Extract scholar references
        if (preg_match_all('/{{scholar:[^}]+}}/', $content, $matches)) {
            $tags[] = 'scholars';
        }

        return array_unique($tags);
    }

    /**
     * Get Islamic syntax patterns
     *
     * @return array Array of Islamic syntax patterns
     */
    public function getIslamicSyntax(): array
    {
        return $this->islamicSyntax;
    }

    /**
     * Get available templates
     *
     * @return array Array of available templates
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
} 