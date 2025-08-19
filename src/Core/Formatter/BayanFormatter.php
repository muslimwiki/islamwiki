<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\Formatter
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Formatter;

use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * BayanFormatter (بيان) - Content Formatting and Islamic Content Presentation System
 *
 * Bayan means "Explanation" or "Clarification" in Arabic. This class provides
 * comprehensive content formatting, Islamic content presentation, multi-format
 * output support, and content validation for the IslamWiki application.
 *
 * This system is part of the User Interface Layer and ensures all Islamic
 * content is presented with proper formatting, respect, and clarity.
 *
 * @category  Core
 * @package   IslamWiki\Core\Formatter
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class BayanFormatter
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Formatter configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Formatting templates.
     *
     * @var array<string, array>
     */
    protected array $templates = [];

    /**
     * Islamic formatting rules.
     *
     * @var array<string, array>
     */
    protected array $islamicRules = [];

    /**
     * Output formats.
     *
     * @var array<string, array>
     */
    protected array $outputFormats = [];

    /**
     * Formatter statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Constructor.
     *
     * @param ShahidLogger $logger The logging system
     * @param array        $config Formatter configuration
     */
    public function __construct(ShahidLogger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeFormatter();
    }

    /**
     * Initialize formatter system.
     *
     * @return self
     */
    protected function initializeFormatter(): self
    {
        $this->initializeStatistics();
        $this->initializeTemplates();
        $this->initializeIslamicRules();
        $this->initializeOutputFormats();
        $this->logger->info('BayanFormatter system initialized');

        return $this;
    }

    /**
     * Initialize formatter statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'formatting' => [
                'total_formatted' => 0,
                'successful_formats' => 0,
                'failed_formats' => 0,
                'format_errors' => 0
            ],
            'content_types' => [
                'quran' => 0,
                'hadith' => 0,
                'islamic_text' => 0,
                'general_content' => 0
            ],
            'output_formats' => [
                'html' => 0,
                'markdown' => 0,
                'plain_text' => 0,
                'json' => 0
            ],
            'performance' => [
                'average_formatting_time' => 0.0,
                'total_formatting_time' => 0.0,
                'fastest_format' => PHP_FLOAT_MAX,
                'slowest_format' => 0.0
            ]
        ];

        return $this;
    }

    /**
     * Initialize formatting templates.
     *
     * @return self
     */
    protected function initializeTemplates(): self
    {
        $this->templates = [
            'quran_verse' => [
                'html' => '<div class="quran-verse" data-surah="{surah}" data-ayah="{ayah}">
                    <div class="arabic-text">{arabic_text}</div>
                    <div class="translation">{translation}</div>
                    <div class="tafsir">{tafsir}</div>
                    <div class="verse-info">
                        <span class="surah-name">{surah_name}</span>
                        <span class="ayah-number">{ayah_number}</span>
                    </div>
                </div>',
                'markdown' => '## {surah_name} {ayah_number}\n\n**{arabic_text}**\n\n{translation}\n\n*{tafsir}*',
                'plain_text' => '{surah_name} {ayah_number}\n\n{arabic_text}\n\n{translation}\n\n{tafsir}'
            ],
            'hadith' => [
                'html' => '<div class="hadith" data-authenticity="{authenticity}">
                    <div class="arabic-text">{arabic_text}</div>
                    <div class="translation">{translation}</div>
                    <div class="narrator">Narrated by: {narrator}</div>
                    <div class="authenticity">Authenticity: {authenticity}</div>
                    <div class="source">Source: {source}</div>
                </div>',
                'markdown' => '### Hadith\n\n**{arabic_text}**\n\n{translation}\n\n*Narrated by: {narrator}*\n*Authenticity: {authenticity}*\n*Source: {source}*',
                'plain_text' => 'Hadith\n\n{arabic_text}\n\n{translation}\n\nNarrated by: {narrator}\nAuthenticity: {authenticity}\nSource: {source}'
            ],
            'islamic_article' => [
                'html' => '<article class="islamic-article">
                    <header>
                        <h1>{title}</h1>
                        <div class="meta">
                            <span class="author">{author}</span>
                            <span class="date">{date}</span>
                            <span class="category">{category}</span>
                        </div>
                    </header>
                    <div class="content">{content}</div>
                    <footer>
                        <div class="tags">{tags}</div>
                        <div class="references">{references}</div>
                    </footer>
                </article>',
                'markdown' => '# {title}\n\n*By {author} on {date}*\n*Category: {category}*\n\n{content}\n\n---\n\n**Tags:** {tags}\n\n**References:** {references}',
                'plain_text' => '{title}\n\nBy {author} on {date}\nCategory: {category}\n\n{content}\n\nTags: {tags}\nReferences: {references}'
            ],
            'scholar_profile' => [
                'html' => '<div class="scholar-profile">
                    <div class="name">{name}</div>
                    <div class="arabic-name">{arabic_name}</div>
                    <div class="period">{period}</div>
                    <div class="specialization">{specialization}</div>
                    <div class="biography">{biography}</div>
                    <div class="works">{works}</div>
                </div>',
                'markdown' => '## {name} ({arabic_name})\n\n*{period}*\n*Specialization: {specialization}*\n\n{biography}\n\n**Notable Works:**\n{works}',
                'plain_text' => '{name} ({arabic_name})\n\n{period}\nSpecialization: {specialization}\n\n{biography}\n\nNotable Works:\n{works}'
            ]
        ];

        return $this;
    }

    /**
     * Initialize Islamic formatting rules.
     *
     * @return self
     */
    protected function initializeIslamicRules(): self
    {
        $this->islamicRules = [
            'respectful_terms' => [
                'Allah' => [
                    'arabic' => 'الله',
                    'honorifics' => ['(SWT)', 'سبحانه وتعالى', 'عز وجل'],
                    'usage' => 'Always use with proper honorifics when possible'
                ],
                'Muhammad' => [
                    'arabic' => 'محمد',
                    'honorifics' => ['(PBUH)', 'صلى الله عليه وسلم', 'صلى الله عليه وآله وسلم'],
                    'usage' => 'Always use with proper honorifics when possible'
                ],
                'Quran' => [
                    'arabic' => 'القرآن',
                    'honorifics' => ['The Holy', 'القرآن الكريم', 'The Noble'],
                    'usage' => 'Use respectful descriptors when possible'
                ],
                'Hadith' => [
                    'arabic' => 'الحديث',
                    'honorifics' => ['The Noble', 'الحديث الشريف'],
                    'usage' => 'Use respectful descriptors when possible'
                ]
            ],
            'formatting_guidelines' => [
                'arabic_text' => [
                    'direction' => 'rtl',
                    'font_family' => 'Traditional Arabic, Amiri, Scheherazade',
                    'font_size' => '1.2em',
                    'line_height' => '1.8'
                ],
                'islamic_terms' => [
                    'highlight' => true,
                    'style' => 'font-weight: bold; color: #2E5C8A;'
                ],
                'verses_and_hadith' => [
                    'indent' => true,
                    'border' => '1px solid #E8E8E8',
                    'background' => '#FAFAFA',
                    'padding' => '15px'
                ]
            ],
            'content_validation' => [
                'require_respectful_language' => true,
                'validate_islamic_terms' => true,
                'check_source_attribution' => true,
                'ensure_proper_formatting' => true
            ]
        ];

        return $this;
    }

    /**
     * Initialize output formats.
     *
     * @return self
     */
    protected function initializeOutputFormats(): self
    {
        $this->outputFormats = [
            'html' => [
                'name' => 'HTML',
                'description' => 'HyperText Markup Language for web display',
                'mime_type' => 'text/html',
                'extension' => '.html',
                'supports_styling' => true,
                'supports_scripting' => true
            ],
            'markdown' => [
                'name' => 'Markdown',
                'description' => 'Lightweight markup language for documentation',
                'mime_type' => 'text/markdown',
                'extension' => '.md',
                'supports_styling' => false,
                'supports_scripting' => false
            ],
            'plain_text' => [
                'name' => 'Plain Text',
                'description' => 'Simple text format without formatting',
                'mime_type' => 'text/plain',
                'extension' => '.txt',
                'supports_styling' => false,
                'supports_scripting' => false
            ],
            'json' => [
                'name' => 'JSON',
                'description' => 'JavaScript Object Notation for data exchange',
                'mime_type' => 'application/json',
                'extension' => '.json',
                'supports_styling' => false,
                'supports_scripting' => false
            ]
        ];

        return $this;
    }

    /**
     * Format content using specified template and output format.
     *
     * @param string $template    Template name
     * @param array  $data        Content data
     * @param string $outputFormat Output format
     * @param array  $options     Formatting options
     * @return array<string, mixed>
     */
    public function formatContent(string $template, array $data, string $outputFormat, array $options = []): array
    {
        $startTime = microtime(true);
        $this->statistics['formatting']['total_formatted']++;

        try {
            // Validate template
            if (!isset($this->templates[$template])) {
                throw new Exception("Template '{$template}' not found");
            }

            // Validate output format
            if (!isset($this->outputFormats[$outputFormat])) {
                throw new Exception("Output format '{$outputFormat}' not supported");
            }

            // Validate and sanitize data
            $validatedData = $this->validateAndSanitizeData($data, $template);

            // Apply Islamic formatting rules
            $formattedData = $this->applyIslamicFormatting($validatedData, $template);

            // Get template for output format
            $templateContent = $this->templates[$template][$outputFormat];

            // Replace placeholders with data
            $formattedContent = $this->replacePlaceholders($templateContent, $formattedData);

            // Apply final formatting based on output format
            $finalContent = $this->applyOutputFormatting($formattedContent, $outputFormat, $options);

            // Update statistics
            $formattingTime = microtime(true) - $startTime;
            $this->updateFormattingStatistics($formattingTime, $template, $outputFormat);

            $this->logger->info("Content formatted successfully: {$template} to {$outputFormat}");

            return [
                'success' => true,
                'content' => $finalContent,
                'template' => $template,
                'output_format' => $outputFormat,
                'formatting_time' => $formattingTime,
                'data_used' => $formattedData
            ];

        } catch (Exception $e) {
            $this->statistics['formatting']['failed_formats']++;
            $this->statistics['formatting']['format_errors']++;
            $this->logger->error("Content formatting failed: {$template} to {$outputFormat} - " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'template' => $template,
                'output_format' => $outputFormat,
                'formatting_time' => microtime(true) - $startTime
            ];
        }
    }

    /**
     * Validate and sanitize content data.
     *
     * @param array  $data     Content data
     * @param string $template Template name
     * @return array<string, mixed>
     */
    protected function validateAndSanitizeData(array $data, string $template): array
    {
        $validatedData = [];

        // Get required fields for template
        $requiredFields = $this->getRequiredFields($template);

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $validatedData[$field] = '';
            } else {
                $validatedData[$field] = $this->sanitizeField($data[$field], $field);
            }
        }

        // Add optional fields
        foreach ($data as $field => $value) {
            if (!in_array($field, $requiredFields)) {
                $validatedData[$field] = $this->sanitizeField($value, $field);
            }
        }

        return $validatedData;
    }

    /**
     * Get required fields for a template.
     *
     * @param string $template Template name
     * @return array<string>
     */
    protected function getRequiredFields(string $template): array
    {
        $requiredFields = [
            'quran_verse' => ['surah', 'ayah', 'arabic_text', 'translation'],
            'hadith' => ['arabic_text', 'translation', 'narrator', 'authenticity', 'source'],
            'islamic_article' => ['title', 'author', 'date', 'category', 'content'],
            'scholar_profile' => ['name', 'arabic_name', 'period', 'specialization', 'biography']
        ];

        return $requiredFields[$template] ?? [];
    }

    /**
     * Sanitize a field value.
     *
     * @param mixed  $value Field value
     * @param string $field Field name
     * @return string
     */
    protected function sanitizeField(mixed $value, string $field): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        // Remove potentially dangerous HTML
        $value = strip_tags($value);

        // Escape HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Special handling for specific fields
        switch ($field) {
            case 'arabic_text':
                // Preserve Arabic text formatting
                $value = $this->preserveArabicFormatting($value);
                break;
            case 'content':
                // Allow basic formatting for content
                $value = $this->allowBasicFormatting($value);
                break;
            case 'date':
                // Validate date format
                $value = $this->validateDateFormat($value);
                break;
        }

        return $value;
    }

    /**
     * Preserve Arabic text formatting.
     *
     * @param string $text Arabic text
     * @return string
     */
    protected function preserveArabicFormatting(string $text): string
    {
        // Preserve Arabic diacritics and formatting
        $text = preg_replace('/[^\p{Arabic}\p{N}\s\.,!?;:()\[\]{}"\'\-\+]/u', '', $text);
        return trim($text);
    }

    /**
     * Allow basic formatting in content.
     *
     * @param string $content Content text
     * @return string
     */
    protected function allowBasicFormatting(string $content): string
    {
        // Allow basic HTML formatting
        $allowedTags = ['<p>', '</p>', '<br>', '<strong>', '</strong>', '<em>', '</em>', '<ul>', '</ul>', '<li>', '</li>'];
        $content = strip_tags($content, implode('', $allowedTags));
        return $content;
    }

    /**
     * Validate date format.
     *
     * @param string $date Date string
     * @return string
     */
    protected function validateDateFormat(string $date): string
    {
        // Basic date validation
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return date('Y-m-d'); // Return current date if invalid
    }

    /**
     * Apply Islamic formatting rules.
     *
     * @param array  $data     Content data
     * @param string $template Template name
     * @return array<string, mixed>
     */
    protected function applyIslamicFormatting(array $data, string $template): array
    {
        $formattedData = $data;

        // Apply respectful language formatting
        foreach ($this->islamicRules['respectful_terms'] as $term => $info) {
            if (isset($formattedData['title']) && stripos($formattedData['title'], $term) !== false) {
                $formattedData['title'] = $this->applyRespectfulFormatting($formattedData['title'], $term, $info);
            }
            if (isset($formattedData['content']) && stripos($formattedData['content'], $term) !== false) {
                $formattedData['content'] = $this->applyRespectfulFormatting($formattedData['content'], $term, $info);
            }
        }

        // Apply template-specific formatting
        switch ($template) {
            case 'quran_verse':
                $formattedData = $this->formatQuranVerse($formattedData);
                break;
            case 'hadith':
                $formattedData = $this->formatHadith($formattedData);
                break;
            case 'islamic_article':
                $formattedData = $this->formatIslamicArticle($formattedData);
                break;
            case 'scholar_profile':
                $formattedData = $this->formatScholarProfile($formattedData);
                break;
        }

        return $formattedData;
    }

    /**
     * Apply respectful formatting to Islamic terms.
     *
     * @param string $text Text content
     * @param string $term Islamic term
     * @param array  $info Term information
     * @return string
     */
    protected function applyRespectfulFormatting(string $text, string $term, array $info): string
    {
        // Add honorifics if not present
        $hasHonorific = false;
        foreach ($info['honorifics'] as $honorific) {
            if (stripos($text, $honorific) !== false) {
                $hasHonorific = true;
                break;
            }
        }

        if (!$hasHonorific) {
            // Add first honorific
            $text = str_ireplace($term, $term . ' ' . $info['honorifics'][0], $text);
        }

        return $text;
    }

    /**
     * Format Quran verse data.
     *
     * @param array $data Verse data
     * @return array<string, mixed>
     */
    protected function formatQuranVerse(array $data): array
    {
        // Ensure proper Arabic text formatting
        if (isset($data['arabic_text'])) {
            $data['arabic_text'] = $this->preserveArabicFormatting($data['arabic_text']);
        }

        // Add surah name if not present
        if (isset($data['surah']) && !isset($data['surah_name'])) {
            $data['surah_name'] = $this->getSurahName($data['surah']);
        }

        return $data;
    }

    /**
     * Format Hadith data.
     *
     * @param array $data Hadith data
     * @return array<string, mixed>
     */
    protected function formatHadith(array $data): array
    {
        // Ensure proper Arabic text formatting
        if (isset($data['arabic_text'])) {
            $data['arabic_text'] = $this->preserveArabicFormatting($data['arabic_text']);
        }

        // Format authenticity
        if (isset($data['authenticity'])) {
            $data['authenticity'] = ucfirst($data['authenticity']);
        }

        return $data;
    }

    /**
     * Format Islamic article data.
     *
     * @param array $data Article data
     * @return array<string, mixed>
     */
    protected function formatIslamicArticle(array $data): array
    {
        // Format date
        if (isset($data['date'])) {
            $data['date'] = $this->formatDate($data['date']);
        }

        // Format tags
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = implode(', ', $data['tags']);
        }

        return $data;
    }

    /**
     * Format scholar profile data.
     *
     * @param array $data Scholar data
     * @return array<string, mixed>
     */
    protected function formatScholarProfile(array $data): array
    {
        // Ensure proper Arabic name formatting
        if (isset($data['arabic_name'])) {
            $data['arabic_name'] = $this->preserveArabicFormatting($data['arabic_name']);
        }

        // Format works
        if (isset($data['works']) && is_array($data['works'])) {
            $data['works'] = implode("\n", $data['works']);
        }

        return $data;
    }

    /**
     * Get surah name by number.
     *
     * @param int $surahNumber Surah number
     * @return string
     */
    protected function getSurahName(int $surahNumber): string
    {
        $surahNames = [
            1 => 'Al-Fatiha',
            2 => 'Al-Baqarah',
            3 => 'Aal-Imran',
            4 => 'An-Nisa',
            5 => 'Al-Ma\'idah'
        ];

        return $surahNames[$surahNumber] ?? "Surah {$surahNumber}";
    }

    /**
     * Format date string.
     *
     * @param string $date Date string
     * @return string
     */
    protected function formatDate(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        return date('F j, Y', $timestamp);
    }

    /**
     * Replace placeholders in template.
     *
     * @param string $template Template content
     * @param array  $data     Data to replace placeholders
     * @return string
     */
    protected function replacePlaceholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $template = str_replace($placeholder, $value, $template);
        }

        return $template;
    }

    /**
     * Apply output format specific formatting.
     *
     * @param string $content      Formatted content
     * @param string $outputFormat Output format
     * @param array  $options      Formatting options
     * @return string
     */
    protected function applyOutputFormatting(string $content, string $outputFormat, array $options): string
    {
        switch ($outputFormat) {
            case 'html':
                return $this->formatAsHtml($content, $options);
            case 'markdown':
                return $this->formatAsMarkdown($content, $options);
            case 'json':
                return $this->formatAsJson($content, $options);
            case 'plain_text':
            default:
                return $this->formatAsPlainText($content, $options);
        }
    }

    /**
     * Format content as HTML.
     *
     * @param string $content HTML content
     * @param array  $options Formatting options
     * @return string
     */
    protected function formatAsHtml(string $content, array $options): string
    {
        // Add CSS classes if specified
        if (isset($options['css_classes'])) {
            $content = $this->addCssClasses($content, $options['css_classes']);
        }

        // Add inline styles if specified
        if (isset($options['inline_styles'])) {
            $content = $this->addInlineStyles($content, $options['inline_styles']);
        }

        return $content;
    }

    /**
     * Format content as Markdown.
     *
     * @param string $content Markdown content
     * @param array  $options Formatting options
     * @return string
     */
    protected function formatAsMarkdown(string $content, array $options): string
    {
        // Clean up any HTML that might have been included
        $content = strip_tags($content);
        
        // Ensure proper markdown formatting
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }

    /**
     * Format content as JSON.
     *
     * @param string $content JSON content
     * @param array  $options Formatting options
     * @return string
     */
    protected function formatAsJson(string $content, array $options): string
    {
        // Parse content and return as JSON
        $data = [
            'content' => $content,
            'format' => 'json',
            'timestamp' => date('c'),
            'options' => $options
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Format content as plain text.
     *
     * @param string $content Plain text content
     * @param array  $options Formatting options
     * @return string
     */
    protected function formatAsPlainText(string $content, array $options): string
    {
        // Remove all HTML tags
        $content = strip_tags($content);
        
        // Convert HTML entities
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        
        // Clean up whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }

    /**
     * Add CSS classes to HTML content.
     *
     * @param string $content    HTML content
     * @param array  $cssClasses CSS classes
     * @return string
     */
    protected function addCssClasses(string $content, array $cssClasses): string
    {
        foreach ($cssClasses as $selector => $classes) {
            $pattern = '/<' . $selector . '([^>]*)>/i';
            $replacement = '<' . $selector . '$1 class="' . $classes . '">';
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /**
     * Add inline styles to HTML content.
     *
     * @param string $content      HTML content
     * @param array  $inlineStyles Inline styles
     * @return string
     */
    protected function addInlineStyles(string $content, array $inlineStyles): string
    {
        foreach ($inlineStyles as $selector => $styles) {
            $pattern = '/<' . $selector . '([^>]*)>/i';
            $replacement = '<' . $selector . '$1 style="' . $styles . '">';
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /**
     * Update formatting statistics.
     *
     * @param float  $formattingTime Formatting time
     * @param string $template       Template name
     * @param string $outputFormat   Output format
     * @return self
     */
    protected function updateFormattingStatistics(float $formattingTime, string $template, string $outputFormat): self
    {
        $this->statistics['formatting']['successful_formats']++;
        $this->statistics['performance']['total_formatting_time'] += $formattingTime;

        // Update content type statistics
        if (isset($this->statistics['content_types'][$template])) {
            $this->statistics['content_types'][$template]++;
        }

        // Update output format statistics
        if (isset($this->statistics['output_formats'][$outputFormat])) {
            $this->statistics['output_formats'][$outputFormat]++;
        }

        // Update performance statistics
        $totalFormats = $this->statistics['formatting']['successful_formats'];
        $this->statistics['performance']['average_formatting_time'] = 
            $this->statistics['performance']['total_formatting_time'] / $totalFormats;

        if ($formattingTime < $this->statistics['performance']['fastest_format']) {
            $this->statistics['performance']['fastest_format'] = $formattingTime;
        }

        if ($formattingTime > $this->statistics['performance']['slowest_format']) {
            $this->statistics['performance']['slowest_format'] = $formattingTime;
        }

        return $this;
    }

    /**
     * Get formatter statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get available templates.
     *
     * @return array<string, array>
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Get Islamic formatting rules.
     *
     * @return array<string, array>
     */
    public function getIslamicRules(): array
    {
        return $this->islamicRules;
    }

    /**
     * Get available output formats.
     *
     * @return array<string, array>
     */
    public function getOutputFormats(): array
    {
        return $this->outputFormats;
    }

    /**
     * Get formatter configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set formatter configuration.
     *
     * @param array<string, mixed> $config Formatter configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
