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

        // Post-render hook for wrapping markdown HTML in nicer containers
        $hookManager->register('ContentPostRender', [$this, 'onContentPostRender'], 10);

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

        // Convert ProgressBar shorthand to placeholders understood by the Docs renderer
        // Syntax inspired by PyMdown ProgressBar: [=65% "Title"]{: .class1 .class2}
        // Ref: https://facelessuser.github.io/pymdown-extensions/extensions/progressbar/
        $content = preg_replace_callback(
            '/^\s*\[(=+)\s*([^\]"\']+?)\s*(?:\"([^\"]*)\"|\'([^\']*)\')?\]\s*(\{\s*:[^}]+\}\s*)?$/m',
            function ($m) {
                $rawVal = trim($m[2]);
                $title = isset($m[3]) && $m[3] !== '' ? $m[3] : (isset($m[4]) ? $m[4] : '');
                $attrs = isset($m[5]) ? trim($m[5]) : '';
                // Extract classes from {: .class .class2}
                $classes = [];
                if ($attrs) {
                    if (preg_match_all('/\.([A-Za-z0-9_-]+)/', $attrs, $mm)) {
                        $classes = $mm[1];
                    }
                }
                // Normalize percent
                $percent = 0.0;
                if (preg_match('/^([0-9]+(?:\.[0-9]+)?)%$/', $rawVal, $pm)) {
                    $percent = (float)$pm[1];
                } elseif (preg_match('/^(\d+)\/(\d+)$/', $rawVal, $fm)) {
                    $den = (int)$fm[2];
                    $num = (int)$fm[1];
                    $percent = $den > 0 ? ($num / $den) * 100.0 : 0.0;
                }
                $percent = max(0.0, min(100.0, $percent));
                $label = $title !== '' ? $title : (sprintf('%.0f%%', $percent));
                // Placeholder token the docs renderer will convert to HTML
                $token = sprintf(
                    '[[[PROGRESS;percent=%s;label=%s;classes=%s]]]',
                    number_format($percent, 2, '.', ''),
                    str_replace([';',']',"\n"], ['\;', '', ' '], $label),
                    implode(',', array_map(function ($c) {
                        return str_replace([';',','], '', $c);
                    }, $classes))
                );
                return $token;
            },
            $content
        );

        // Parse Islamic syntax
        $content = $this->parseIslamicSyntax($content);

        // Parse Arabic text
        $content = $this->parseArabicText($content);

        return $content;
    }

    /**
     * Post-render enhancement: add classes and containers for prettier docs
     */
    public function onContentPostRender(string $html, string $context = ''): string
    {
        // Sitewide: render PROGRESS placeholders into HTML
        $html = preg_replace_callback(
            '/\[\[\[PROGRESS;percent=([0-9]+(?:\.[0-9]+)?);label=([^;\]]*);classes=([^\]]*)\]\]\]/',
            function ($m) {
                $percent = (float)$m[1];
                $label = htmlspecialchars($m[2] ?? '', ENT_QUOTES, 'UTF-8');
                $classes = trim($m[3] ?? '');
                $cls = 'progress ';
                if ($classes !== '') {
                    foreach (explode(',', $classes) as $c) {
                        $c = trim($c);
                        if ($c !== '') {
                            $cls .= $c . ' ';
                        }
                    }
                }
                $bucket = ((int) floor($percent / 20)) * 20; // 0,20,40,60,80,100
                $bucket = max(0, min(100, $bucket));
                $cls .= 'progress-' . $bucket . 'plus';
                $bar = '<div class="progress-bar" style="width:' . number_format($percent, 2) . '%">'
                     . '<p class="progress-label">' . $label . '</p>'
                     . '</div>';
                return '<div class="' . trim($cls) . '">' . $bar . '</div>';
            },
            $html
        );

        // Docs-specific enhancements (styling wrappers, TOC)
        if ($context === 'docs') {
            // Add class wrappers for tables, images, blockquotes, code
            $html = preg_replace('/<table(\s|>)/', '<table class="md-table bismillah-table"$1', $html);
            $html = preg_replace('/<img /', '<img class="md-image" ', $html);
            $html = preg_replace('/<blockquote>/', '<blockquote class="md-quote">', $html);
            $html = preg_replace('/<pre><code/', '<pre class="md-code"><code', $html);

            // Auto-generate a simple TOC if headings exist
            if (preg_match_all('/<h([1-6])>(.*?)<\/h\1>/', $html, $m, PREG_SET_ORDER)) {
                $toc = [];
                $idx = 0;
                $html = preg_replace_callback(
                    '/<h([1-6])>(.*?)<\/h\1>/',
                    function ($mm) use (&$toc, &$idx) {
                        $level = (int) $mm[1];
                        $text = strip_tags($mm[2]);
                        $id = 'h-' . (++$idx) . '-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text));
                        $toc[] = ['level' => $level, 'text' => $text, 'id' => $id];
                        return '<h' . $level . ' id="' . $id . '">' . $mm[2] . '</h' . $level . '>';
                    },
                    $html
                );

                if (!empty($toc)) {
                    $tocHtml = '<nav class="md-toc"><strong>Contents</strong><ul>';
                    foreach ($toc as $entry) {
                        $indent = str_repeat('&nbsp;&nbsp;', max(0, $entry['level'] - 1));
                        $tocHtml .= '<li>' . $indent . '<a href="#' . htmlspecialchars($entry['id']) . '">' . htmlspecialchars($entry['text']) . '</a></li>';
                    }
                    $tocHtml .= '</ul></nav>';
                    $html = $tocHtml . $html;
                }
            }
        }

        return $html;
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
            function ($matches) {
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
            function ($matches) {
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
