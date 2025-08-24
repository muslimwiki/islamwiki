<?php

/**
 * Core Formatter
 *
 * Centralized formatting system for IslamWiki.
 * Handles text, content, and data formatting operations.
 *
 * @package IslamWiki\Core\Formatter
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Formatter;

use IslamWiki\Core\Logging\Logger;

/**
 * Core Formatter - Centralized Formatting System
 *
 * This class provides comprehensive formatting capabilities for
 * text, content, and data throughout the application.
 */
class Formatter
{
    /**
     * The logging system instance.
     */
    protected Logger $logger;

    /**
     * Formatter configuration.
     */
    private array $config;

    /**
     * Create a new formatter instance.
     *
     * @param Logger $logger The logging system
     * @param array $config Formatter configuration
     */
    public function __construct(Logger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'default_format' => 'text',
            'enable_markdown' => true,
            'enable_wiki_syntax' => true,
            'max_length' => 10000
        ], $config);

        $this->logger->info('Formatter system initialized');
    }

    /**
     * Format text content.
     *
     * @param string $content The content to format
     * @param string $format The format to apply
     * @param array $options Formatting options
     * @return string Formatted content
     */
    public function format(string $content, string $format = null, array $options = []): string
    {
        $format = $format ?: $this->config['default_format'];
        
        try {
            switch ($format) {
                case 'markdown':
                    return $this->formatMarkdown($content, $options);
                case 'wiki':
                    return $this->formatWiki($content, $options);
                case 'html':
                    return $this->formatHtml($content, $options);
                case 'text':
                default:
                    return $this->formatText($content, $options);
            }
        } catch (\Exception $e) {
            $this->logger->error('Formatting failed', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            return $content; // Return original content on error
        }
    }

    /**
     * Format content as Markdown.
     */
    private function formatMarkdown(string $content, array $options): string
    {
        if (!$this->config['enable_markdown']) {
            return $content;
        }

        // Basic Markdown formatting
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/`(.*?)`/s', '<code>$1</code>', $content);
        $content = preg_replace('/\n\s*#\s+(.*?)\n/s', "\n<h1>$1</h1>\n", $content);
        $content = preg_replace('/\n\s*##\s+(.*?)\n/s', "\n<h2>$1</h2>\n", $content);
        $content = preg_replace('/\n\s*###\s+(.*?)\n/s', "\n<h3>$1</h3>\n", $content);

        return $content;
    }

    /**
     * Format content as Wiki syntax.
     */
    private function formatWiki(string $content, array $options): string
    {
        if (!$this->config['enable_wiki_syntax']) {
            return $content;
        }

        // Basic Wiki syntax formatting
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/\[\[(.*?)\]\]/s', '<a href="/wiki/$1">$1</a>', $content);
        $content = preg_replace('/\n\s*=\s+(.*?)\s+=\s*\n/s', "\n<h1>$1</h1>\n", $content);
        $content = preg_replace('/\n\s*==\s+(.*?)\s+==\s*\n/s', "\n<h2>$1</h2>\n", $content);

        return $content;
    }

    /**
     * Format content as HTML.
     */
    private function formatHtml(string $content, array $options): string
    {
        // Basic HTML sanitization
        $allowedTags = $options['allowed_tags'] ?? ['p', 'br', 'strong', 'em', 'code', 'h1', 'h2', 'h3', 'a'];
        $content = strip_tags($content, $allowedTags);
        
        return $content;
    }

    /**
     * Format content as plain text.
     */
    private function formatText(string $content, array $options): string
    {
        // Convert to plain text
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        
        return $content;
    }

    /**
     * Get formatter configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update formatter configuration.
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->logger->info('Formatter configuration updated', $config);
    }
} 