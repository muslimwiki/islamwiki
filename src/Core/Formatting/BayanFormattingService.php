<?php

declare(strict_types=1);

namespace IslamWiki\Core\Formatting;

/**
 * Bayan Formatting Service (بيان - Explanation/Clarification)
 * 
 * Content formatting, text processing, and Islamic content presentation.
 * Part of the User Interface Layer in the Islamic core architecture.
 * 
 * @package IslamWiki\Core\Formatting
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class BayanFormattingService
{
    private array $config;
    private array $formatters = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_formatter' => 'html',
            'allowed_tags' => [
                'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'ul', 'ol', 'li', 'blockquote', 'code', 'pre', 'a', 'img', 'table',
                'tr', 'td', 'th', 'thead', 'tbody'
            ],
            'islamic_formatting' => true,
            'arabic_support' => true,
            'rtl_support' => true,
        ], $config);

        $this->initializeFormatters();
    }

    /**
     * Initialize formatters
     */
    private function initializeFormatters(): void
    {
        $this->formatters['html'] = new BayanHtmlFormatter($this->config);
        $this->formatters['markdown'] = new BayanMarkdownFormatter($this->config);
        $this->formatters['text'] = new BayanTextFormatter($this->config);
        $this->formatters['islamic'] = new BayanIslamicFormatter($this->config);
    }

    /**
     * Format content
     */
    public function format(string $content, string $format = null, array $options = []): string
    {
        $format = $format ?? $this->config['default_formatter'];
        
        if (!isset($this->formatters[$format])) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        $formatter = $this->formatters[$format];
        return $formatter->format($content, $options);
    }

    /**
     * Format as HTML
     */
    public function toHtml(string $content, array $options = []): string
    {
        return $this->format($content, 'html', $options);
    }

    /**
     * Format as Markdown
     */
    public function toMarkdown(string $content, array $options = []): string
    {
        return $this->format($content, 'markdown', $options);
    }

    /**
     * Format as plain text
     */
    public function toText(string $content, array $options = []): string
    {
        return $this->format($content, 'text', $options);
    }

    /**
     * Format with Islamic formatting
     */
    public function toIslamic(string $content, array $options = []): string
    {
        return $this->format($content, 'islamic', $options);
    }

    /**
     * Sanitize HTML content
     */
    public function sanitizeHtml(string $content): string
    {
        $allowedTags = implode('', array_map(function ($tag) {
            return "<{$tag}>";
        }, $this->config['allowed_tags']));

        return strip_tags($content, $allowedTags);
    }

    /**
     * Convert Arabic numerals to English
     */
    public function convertArabicNumerals(string $content): string
    {
        if (!$this->config['arabic_support']) {
            return $content;
        }

        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabicNumerals, $englishNumerals, $content);
    }

    /**
     * Convert English numerals to Arabic
     */
    public function convertToArabicNumerals(string $content): string
    {
        if (!$this->config['arabic_support']) {
            return $content;
        }

        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($englishNumerals, $arabicNumerals, $content);
    }

    /**
     * Add RTL support for Arabic text
     */
    public function addRtlSupport(string $content): string
    {
        if (!$this->config['rtl_support']) {
            return $content;
        }

        // Add RTL attributes to Arabic text
        $content = preg_replace_callback('/[\p{Arabic}]+/u', function ($matches) {
            return '<span dir="rtl" lang="ar">' . $matches[0] . '</span>';
        }, $content);

        return $content;
    }

    /**
     * Format Islamic terms
     */
    public function formatIslamicTerms(string $content): string
    {
        if (!$this->config['islamic_formatting']) {
            return $content;
        }

        $islamicTerms = [
            'allah' => 'Allah ﷻ',
            'muhammad' => 'Muhammad ﷺ',
            'pbuh' => 'ﷺ',
            'saw' => 'ﷺ',
            'swt' => 'ﷻ',
            'ra' => 'رضي الله عنه',
            'rha' => 'رضي الله عنها',
            'quran' => 'Quran',
            'hadith' => 'Hadith',
            'salah' => 'Salah',
            'dua' => 'Dua',
            'adhan' => 'Adhan',
            'qibla' => 'Qibla',
            'hijri' => 'Hijri',
            'ramadan' => 'Ramadan',
            'eid' => 'Eid',
            'hajj' => 'Hajj',
            'umrah' => 'Umrah',
            'zakat' => 'Zakat',
            'sadaqah' => 'Sadaqah',
        ];

        foreach ($islamicTerms as $term => $formatted) {
            $content = preg_replace('/\b' . preg_quote($term, '/') . '\b/i', $formatted, $content);
        }

        return $content;
    }

    /**
     * Get available formatters
     */
    public function getAvailableFormatters(): array
    {
        return array_keys($this->formatters);
    }

    /**
     * Get formatter instance
     */
    public function getFormatter(string $name): ?BayanFormatterInterface
    {
        return $this->formatters[$name] ?? null;
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

/**
 * Formatter Interface
 */
interface BayanFormatterInterface
{
    public function format(string $content, array $options = []): string;
}

/**
 * HTML Formatter
 */
class BayanHtmlFormatter implements BayanFormatterInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function format(string $content, array $options = []): string
    {
        // Sanitize HTML
        $content = $this->sanitizeHtml($content);
        
        // Add Islamic formatting
        if ($this->config['islamic_formatting']) {
            $content = $this->formatIslamicContent($content);
        }
        
        // Add RTL support
        if ($this->config['rtl_support']) {
            $content = $this->addRtlSupport($content);
        }

        return $content;
    }

    private function sanitizeHtml(string $content): string
    {
        $allowedTags = implode('', array_map(function ($tag) {
            return "<{$tag}>";
        }, $this->config['allowed_tags']));

        return strip_tags($content, $allowedTags);
    }

    private function formatIslamicContent(string $content): string
    {
        // Format Islamic terms
        $islamicTerms = [
            'allah' => 'Allah ﷻ',
            'muhammad' => 'Muhammad ﷺ',
            'pbuh' => 'ﷺ',
            'saw' => 'ﷺ',
            'swt' => 'ﷻ',
        ];

        foreach ($islamicTerms as $term => $formatted) {
            $content = preg_replace('/\b' . preg_quote($term, '/') . '\b/i', $formatted, $content);
        }

        return $content;
    }

    private function addRtlSupport(string $content): string
    {
        // Add RTL attributes to Arabic text
        $content = preg_replace_callback('/[\p{Arabic}]+/u', function ($matches) {
            return '<span dir="rtl" lang="ar">' . $matches[0] . '</span>';
        }, $content);

        return $content;
    }
}

/**
 * Markdown Formatter
 */
class BayanMarkdownFormatter implements BayanFormatterInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function format(string $content, array $options = []): string
    {
        // Convert Markdown to HTML
        $html = $this->markdownToHtml($content);
        
        // Apply HTML formatting
        $formatter = new BayanHtmlFormatter($this->config);
        return $formatter->format($html, $options);
    }

    private function markdownToHtml(string $markdown): string
    {
        // Basic Markdown to HTML conversion
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Lists
        $html = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Paragraphs
        $html = preg_replace('/^(?!<[h|u|o]|<li>)(.*$)/m', '<p>$1</p>', $html);
        
        // Clean up empty paragraphs
        $html = str_replace('<p></p>', '', $html);
        
        return $html;
    }
}

/**
 * Text Formatter
 */
class BayanTextFormatter implements BayanFormatterInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function format(string $content, array $options = []): string
    {
        // Remove HTML tags
        $text = strip_tags($content);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
}

/**
 * Islamic Formatter
 */
class BayanIslamicFormatter implements BayanFormatterInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function format(string $content, array $options = []): string
    {
        // Apply Islamic formatting
        $content = $this->formatIslamicTerms($content);
        
        // Add Arabic numeral support
        if ($this->config['arabic_support']) {
            $content = $this->convertArabicNumerals($content);
        }
        
        // Add RTL support
        if ($this->config['rtl_support']) {
            $content = $this->addRtlSupport($content);
        }
        
        // Convert to HTML for final formatting
        $htmlFormatter = new BayanHtmlFormatter($this->config);
        return $htmlFormatter->format($content, $options);
    }

    private function formatIslamicTerms(string $content): string
    {
        $islamicTerms = [
            'allah' => 'Allah ﷻ',
            'muhammad' => 'Muhammad ﷺ',
            'pbuh' => 'ﷺ',
            'saw' => 'ﷺ',
            'swt' => 'ﷻ',
            'ra' => 'رضي الله عنه',
            'rha' => 'رضي الله عنها',
            'quran' => 'Quran',
            'hadith' => 'Hadith',
            'salah' => 'Salah',
            'dua' => 'Dua',
            'adhan' => 'Adhan',
            'qibla' => 'Qibla',
            'hijri' => 'Hijri',
            'ramadan' => 'Ramadan',
            'eid' => 'Eid',
            'hajj' => 'Hajj',
            'umrah' => 'Umrah',
            'zakat' => 'Zakat',
            'sadaqah' => 'Sadaqah',
        ];

        foreach ($islamicTerms as $term => $formatted) {
            $content = preg_replace('/\b' . preg_quote($term, '/') . '\b/i', $formatted, $content);
        }

        return $content;
    }

    private function convertArabicNumerals(string $content): string
    {
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabicNumerals, $englishNumerals, $content);
    }

    private function addRtlSupport(string $content): string
    {
        // Add RTL attributes to Arabic text
        $content = preg_replace_callback('/[\p{Arabic}]+/u', function ($matches) {
            return '<span dir="rtl" lang="ar">' . $matches[0] . '</span>';
        }, $content);

        return $content;
    }
} 