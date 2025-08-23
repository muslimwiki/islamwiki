<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Engines;

use IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager;

/**
 * Template Engine
 * 
 * Handles rendering of wiki templates for IslamWiki following MediaWiki's approach.
 * Templates are stored as namespace pages at /wiki/Template:TemplateName.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class TemplateEngine
{
    private TemplateManager $templateManager;
    private array $templateCache = [];
    
    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }
    
    /**
     * Render a template with parameters
     * 
     * @param string $templateContent The template content to render
     * @return string Rendered HTML content
     */
    public function render(string $templateContent): string
    {
        // Parse template name and parameters
        $parts = explode('|', $templateContent, 2);
        $templateName = trim($parts[0]);
        $params = isset($parts[1]) ? $this->parseTemplateParams($parts[1]) : [];
        
        // Capitalize first letter to match MediaWiki convention
        $templateName = ucfirst($templateName);
        
        // Try to load template from database first
        $templateContent = $this->templateManager->loadTemplate($templateName);
        
        if ($templateContent !== null) {
            // Template exists in database, render it with parameters
            return $this->renderTemplateFromDatabase($templateContent, $params);
        }
        
        // Fallback to built-in template rendering for common templates
        return $this->renderBuiltInTemplate($templateName, $params);
    }
    
    /**
     * Parse template parameters (param1=value1|param2=value2)
     * Handles complex parameters that may contain | characters
     */
    private function parseTemplateParams(string $paramString): array
    {
        $params = [];
        
        // For templates with specific parameter structures, handle them specially
        if (empty($paramString)) {
            return $params;
        }
        
        // Handle templates with positional parameters that may contain |
        // This is a simplified approach - in a full implementation, we'd need more sophisticated parsing
        
        // For now, split by | and treat as positional parameters
        $parts = explode('|', $paramString);
        
        foreach ($parts as $index => $part) {
            $trimmedPart = trim($part);
            if (strpos($trimmedPart, '=') !== false) {
                // Key-value parameter
                list($key, $value) = explode('=', $trimmedPart, 2);
                $params[trim($key)] = trim($value);
            } else {
                // Positional parameter
                $params[$index] = $trimmedPart;
            }
        }
        
        return $params;
    }
    
    /**
     * Load template content from database
     */
    private function loadTemplateFromDatabase(string $templateName): ?string
    {
        // This method is now handled by TemplateManager
        // Keeping for backward compatibility but it should not be called
        return null;
    }
    
    /**
     * Render template loaded from database
     */
    private function renderTemplateFromDatabase(string $templateContent, array $params): string
    {
        // Replace parameters in template content
        // MediaWiki uses {{{1}}}, {{{2}}}, {{{param}}} syntax
        foreach ($params as $key => $value) {
            if (is_numeric($key)) {
                // Positional parameter: {{{1}}}, {{{2}}}, etc.
                $templateContent = str_replace('{{{' . ($key + 1) . '}}}', $value, $templateContent);
            } else {
                // Named parameter: {{{param}}}
                $templateContent = str_replace('{{{' . $key . '}}}', $value, $templateContent);
            }
        }
        
        // Handle MediaWiki-style conditionals: {{#if:condition|then|else}}
        $templateContent = $this->processConditionals($templateContent, $params);
        
        // Handle default values: {{{param|default}}}
        $templateContent = $this->processDefaults($templateContent);
        
        // Process any remaining wiki syntax in the template
        // This would recursively process the template content
        return $templateContent;
    }
    
    /**
     * Process MediaWiki-style conditionals
     */
    private function processConditionals(string $content, array $params): string
    {
        // Simple {{#if:condition|then|else}} processing
        $pattern = '/\{\{#if:([^|]+)\|([^|]*)\|([^}]*)\}\}/';
        return preg_replace_callback($pattern, function($matches) use ($params) {
            $condition = trim($matches[1]);
            $then = $matches[2];
            $else = $matches[3];
            
            // Check if condition is truthy (not empty, not just whitespace)
            if (!empty(trim($condition))) {
                return $then;
            } else {
                return $else;
            }
        }, $content);
    }
    
    /**
     * Process default values in parameters
     */
    private function processDefaults(string $content): string
    {
        // Handle {{{param|default}}} syntax
        $pattern = '/\{\{\{([^|]+)\|([^}]+)\}\}\}/';
        return preg_replace_callback($pattern, function($matches) {
            $param = trim($matches[1]);
            $default = trim($matches[2]);
            
            // For now, just return the default value
            // In a full implementation, this would check if the parameter was provided
            return $default;
        }, $content);
    }
    
    /**
     * Render built-in templates (fallback for common templates)
     */
    private function renderBuiltInTemplate(string $templateName, array $params): string
    {
        // Render based on template type
        switch (strtolower($templateName)) {
            // Basic templates
            case 'infobox':
                return $this->renderInfobox($params);
            case 'template':
                return $this->renderGenericTemplate($params);
            case 'reference':
                return $this->renderReference($params);
            case 'warning':
                return $this->renderWarning($params);
            case 'note':
                return $this->renderNote($params);
            case 'success':
                return $this->renderSuccess($params);
            case 'error':
                return $this->renderError($params);
                
            // Page information templates
            case 'about':
                return $this->renderAbout($params);
            case 'pp-semi-indef':
            case 'pp-move':
                return $this->renderPageProtection($templateName);
            case 'good article':
                return $this->renderArticleQuality($templateName);
            case 'use dmy dates':
                return $this->renderDateFormatting($params);
            case 'use oxford spelling':
                return $this->renderSpellingPreference($params);
                
            // Navigation templates
            case 'sidebar':
                return $this->renderSidebar($params);
            case 'main':
                return $this->renderMainArticle($params);
            case 'further information':
                return $this->renderFurtherInformation($params);
            case 'see also':
                return $this->renderSeeAlso($params);
                
            // Quote templates
            case 'cquote':
                return $this->renderCquote($params);
                
            // Reference and portal templates
            case 'reflist':
                return $this->renderReflist($params);
            case 'islam portal':
            case 'portal':
                return $this->renderPortal($params);
                
            // Default case for unknown templates
            default:
                return $this->renderUnknownTemplate($templateName, $params);
        }
    }
    
    /**
     * Render unknown template (template not found in database or built-in)
     */
    private function renderUnknownTemplate(string $templateName, array $params): string
    {
        $paramString = '';
        if (!empty($params)) {
            $paramStrings = [];
            foreach ($params as $key => $value) {
                if (is_numeric($key)) {
                    $paramStrings[] = htmlspecialchars($value);
                } else {
                    $paramStrings[] = htmlspecialchars($key) . '=' . htmlspecialchars($value);
                }
            }
            $paramString = '|' . implode('|', $paramStrings);
        }
        
        // Show template name with link to create/edit it
        return '<div class="template template-' . strtolower(str_replace(' ', '-', $templateName)) . '">' .
               '<strong>Template: <a href="/wiki/Template:' . urlencode($templateName) . '">' . htmlspecialchars($templateName) . '</a></strong>' .
               $paramString . 
               ' <small>(<a href="/wiki/Template:' . urlencode($templateName) . '?action=edit">edit</a>)</small></div>';
    }
    
    /**
     * Render Infobox template
     */
    private function renderInfobox(array $params): string
    {
        $title = $params['title'] ?? 'Information';
        $content = $params['content'] ?? '';
        $class = $params['class'] ?? 'infobox';
        $style = $params['style'] ?? '';
        
        $html = '<div class="' . htmlspecialchars($class) . '"';
        if ($style) {
            $html .= ' style="' . htmlspecialchars($style) . '"';
        }
        $html .= '>';
        
        if ($title) {
            $html .= '<div class="infobox-title">' . htmlspecialchars($title) . '</div>';
        }
        
        if ($content) {
            $html .= '<div class="infobox-content">' . htmlspecialchars($content) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render generic template
     */
    private function renderGenericTemplate(array $params): string
    {
        $name = $params['name'] ?? 'Template';
        $content = $params['content'] ?? '';
        $class = $params['class'] ?? 'template';
        
        $html = '<div class="' . htmlspecialchars($class) . '">';
        $html .= '<div class="template-name">' . htmlspecialchars($name) . '</div>';
        
        if ($content) {
            $html .= '<div class="template-content">' . htmlspecialchars($content) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Reference template
     */
    private function renderReference(array $params): string
    {
        $source = $params['source'] ?? 'Source';
        $author = $params['author'] ?? '';
        $date = $params['date'] ?? '';
        $url = $params['url'] ?? '';
        
        $html = '<div class="reference">';
        $html .= '<div class="reference-source">' . htmlspecialchars($source) . '</div>';
        
        if ($author) {
            $html .= '<div class="reference-author">By: ' . htmlspecialchars($author) . '</div>';
        }
        
        if ($date) {
            $html .= '<div class="reference-date">Date: ' . htmlspecialchars($date) . '</div>';
        }
        
        if ($url) {
            $html .= '<div class="reference-url"><a href="' . htmlspecialchars($url) . '">View Source</a></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Warning template
     */
    private function renderWarning(array $params): string
    {
        $message = $params['message'] ?? 'Warning';
        $title = $params['title'] ?? 'Warning';
        
        return '<div class="template-warning">
            <div class="warning-icon">⚠️</div>
            <div class="warning-content">
                <div class="warning-title">' . htmlspecialchars($title) . '</div>
                <div class="warning-message">' . htmlspecialchars($message) . '</div>
            </div>
        </div>';
    }
    
    /**
     * Render Note template
     */
    private function renderNote(array $params): string
    {
        $message = $params['message'] ?? 'Note';
        $title = $params['title'] ?? 'Note';
        
        return '<div class="template-note">
            <div class="note-icon">ℹ️</div>
            <div class="note-content">
                <div class="note-title">' . htmlspecialchars($title) . '</div>
                <div class="note-message">' . htmlspecialchars($message) . '</div>
            </div>
        </div>';
    }
    
    /**
     * Render Success template
     */
    private function renderSuccess(array $params): string
    {
        $message = $params['message'] ?? 'Success';
        $title = $params['title'] ?? 'Success';
        
        return '<div class="template-success">
            <div class="success-icon">✅</div>
            <div class="success-content">
                <div class="success-title">' . htmlspecialchars($title) . '</div>
                <div class="success-message">' . htmlspecialchars($message) . '</div>
            </div>
        </div>';
    }
    
    /**
     * Render Error template
     */
    private function renderError(array $params): string
    {
        $message = $params['message'] ?? 'Error';
        $title = $params['title'] ?? 'Error';
        
        return '<div class="template-error">
            <div class="error-icon">❌</div>
            <div class="error-content">
                <div class="error-title">' . htmlspecialchars($title) . '</div>
                <div class="error-message">' . htmlspecialchars($message) . '</div>
            </div>
        </div>';
    }
    
    /**
     * Render custom template with name and parameters
     */
    private function renderCustomTemplate(string $templateName, array $params): string
    {
        $paramString = '';
        if (!empty($params)) {
            $paramStrings = [];
            foreach ($params as $key => $value) {
                if (is_numeric($key)) {
                    $paramStrings[] = htmlspecialchars($value);
                } else {
                    $paramStrings[] = htmlspecialchars($key) . '=' . htmlspecialchars($value);
                }
            }
            $paramString = '|' . implode('|', $paramStrings);
        }
        
        return '<div class="template template-' . strtolower(str_replace(' ', '-', $templateName)) . '">' .
               '<strong>Template: ' . htmlspecialchars($templateName) . '</strong>' .
               $paramString . '</div>';
    }
    
    /**
     * Render About template {{About|topic||disambiguation}}
     */
    private function renderAbout(array $params): string
    {
        $topic = $params[0] ?? '';
        $disambiguation = $params[2] ?? '';
        
        $html = '<div class="template about-template">';
        if ($topic) {
            $html .= '<p>This article is about <strong>' . htmlspecialchars($topic) . '</strong>';
            if ($disambiguation) {
                $html .= '. For other uses, see <a href="/wiki/' . urlencode($disambiguation) . '">' . htmlspecialchars($disambiguation) . '</a>';
            }
            $html .= '.</p>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Page Protection template
     */
    private function renderPageProtection(string $templateName): string
    {
        $protectionType = str_replace('pp-', '', $templateName);
        $protectionText = match($protectionType) {
            'semi-indef' => 'This page is semi-protected indefinitely',
            'move' => 'This page is protected from being moved',
            default => 'This page is protected'
        };
        
        return '<div class="template page-protection ' . $protectionType . '">' .
               '<i class="fas fa-shield-alt"></i> ' . $protectionText . '</div>';
    }
    
    /**
     * Render Article Quality template
     */
    private function renderArticleQuality(string $templateName): string
    {
        $qualityText = match($templateName) {
            'good article' => 'This is a good article',
            default => 'This article has been reviewed'
        };
        
        return '<div class="template article-quality">' .
               '<i class="fas fa-star"></i> ' . $qualityText . '</div>';
    }
    
    /**
     * Render Date Formatting template
     */
    private function renderDateFormatting(array $params): string
    {
        $date = $params['date'] ?? '';
        $format = str_contains(strtolower($params[0] ?? ''), 'dmy') ? 'DD/MM/YYYY' : 'MM/DD/YYYY';
        
        return '<div class="template date-formatting">' .
               '<i class="fas fa-calendar"></i> Date format: ' . $format .
               ($date ? ' (since ' . htmlspecialchars($date) . ')' : '') . '</div>';
    }
    
    /**
     * Render Spelling Preference template
     */
    private function renderSpellingPreference(array $params): string
    {
        $date = $params['date'] ?? '';
        $spelling = str_contains(strtolower($params[0] ?? ''), 'oxford') ? 'Oxford' : 'Standard';
        
        return '<div class="template spelling-preference">' .
               '<i class="fas fa-language"></i> Spelling: ' . $spelling . ' English' .
               ($date ? ' (since ' . htmlspecialchars($date) . ')' : '') . '</div>';
    }
    
    /**
     * Render Sidebar template
     */
    private function renderSidebar(array $params): string
    {
        $sidebarName = $params[0] ?? 'default';
        
        return '<div class="template sidebar" data-sidebar="' . htmlspecialchars($sidebarName) . '">' .
               '<i class="fas fa-bars"></i> Sidebar: ' . htmlspecialchars($sidebarName) . '</div>';
    }
    
    /**
     * Render Main Article template
     */
    private function renderMainArticle(array $params): string
    {
        $articleName = $params[0] ?? '';
        
        return '<div class="template main-article">' .
               '<i class="fas fa-external-link-alt"></i> Main article: ' .
               '<a href="/wiki/' . urlencode($articleName) . '">' . htmlspecialchars($articleName) . '</a></div>';
    }
    
    /**
     * Render Further Information template
     */
    private function renderFurtherInformation(array $params): string
    {
        $links = [];
        foreach ($params as $param) {
            if (preg_match('/\[\[([^\]]+)\]\]/', $param, $matches)) {
                $pageName = $matches[1];
                $links[] = '<a href="/wiki/' . urlencode($pageName) . '">' . htmlspecialchars($pageName) . '</a>';
            }
        }
        
        if (empty($links)) {
            return '<div class="template further-info">Further information available</div>';
        }
        
        return '<div class="template further-info">' .
               '<i class="fas fa-info-circle"></i> Further information: ' . implode(', ', $links) . '</div>';
    }
    
    /**
     * Render See Also template
     */
    private function renderSeeAlso(array $params): string
    {
        $links = [];
        foreach ($params as $param) {
            if (preg_match('/\[\[([^\]]+)\]\]/', $param, $matches)) {
                $pageName = $matches[1];
                $links[] = '<a href="/wiki/' . urlencode($pageName) . '">' . htmlspecialchars($pageName) . '</a>';
            }
        }
        
        if (empty($links)) {
            return '<div class="template see-also">See also section</div>';
        }
        
        return '<div class="template see-also">' .
               '<i class="fas fa-link"></i> See also: ' . implode(', ', $links) . '</div>';
    }
    
    /**
     * Render Cquote template {{Cquote|text|source}}
     */
    private function renderCquote(array $params): string
    {
        $text = $params[0] ?? '';
        $source = $params[1] ?? '';
        
        $html = '<div class="template cquote">';
        if ($text) {
            $html .= '<blockquote class="quote-text">' . htmlspecialchars($text) . '</blockquote>';
        }
        if ($source) {
            $html .= '<div class="quote-source">— ' . htmlspecialchars($source) . '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Reflist template {{reflist|30em}}
     */
    private function renderReflist(array $params): string
    {
        $columns = $params[0] ?? '1';
        $style = '';
        
        if (preg_match('/(\d+)em/', $columns, $matches)) {
            $style = ' style="column-width: ' . $matches[1] . 'em;"';
        }
        
        return '<div class="template reflist"' . $style . '>' .
               '<h3>References</h3>' .
               '<div class="references-list" id="reflist"></div></div>';
    }
    
    /**
     * Render Portal template
     */
    private function renderPortal(array $params): string
    {
        $portalName = $params[0] ?? 'Portal';
        
        return '<div class="template portal">' .
               '<i class="fas fa-door-open"></i> ' .
               '<a href="/portal/' . urlencode($portalName) . '">' . htmlspecialchars($portalName) . ' Portal</a></div>';
    }
    
    /**
     * Render cached template
     */
    private function renderCachedTemplate(string $templateName, array $params): string
    {
        $template = $this->templateCache[$templateName];
        
        // Simple template replacement
        $html = $template;
        foreach ($params as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
        }
        
        return $html;
    }
    
    /**
     * Register a custom template
     */
    public function registerTemplate(string $name, string $template): void
    {
        $this->templateCache[$name] = $template;
    }
    
    /**
     * Get registered template names
     */
    public function getRegisteredTemplates(): array
    {
        return array_keys($this->templateCache);
    }
    
    /**
     * Check if template is registered
     */
    public function isTemplateRegistered(string $name): bool
    {
        return isset($this->templateCache[$name]);
    }
} 