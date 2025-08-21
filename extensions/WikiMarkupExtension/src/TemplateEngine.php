<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\Template;

/**
 * Template Engine
 *
 * Handles advanced template processing, parameter substitution,
 * and template caching for the MediaWiki-style template system.
 */
class TemplateEngine
{
    /**
     * @var Connection|null Database connection
     */
    protected ?Connection $connection;

    /**
     * @var array Template cache
     */
    protected array $templateCache = [];

    /**
     * @var array Parameter cache
     */
    protected array $parameterCache = [];

    /**
     * @var array Configuration options
     */
    protected array $config;

    /**
     * Create a new template engine instance
     */
    public function __construct(?Connection $connection, array $config = [])
    {
        $this->connection = $connection;
        $this->config = array_merge([
            'enable_caching' => true,
            'cache_ttl' => 3600,
            'max_cache_size' => 1000,
            'enable_parameter_validation' => true,
            'enable_recursive_templates' => true,
            'max_recursion_depth' => 10
        ], $config);
    }

    /**
     * Process template calls in content
     */
    public function processTemplates(string $content, array $context = []): string
    {
        if (!$this->config['enable_caching'] || !isset($this->templateCache[$content])) {
            $processed = $this->parseAndRenderTemplates($content, $context);
            
            if ($this->config['enable_caching']) {
                $this->cacheTemplate($content, $processed);
            }
            
            return $processed;
        }

        return $this->templateCache[$content];
    }

    /**
     * Parse and render all templates in content
     */
    protected function parseAndRenderTemplates(string $content, array $context = [], int $depth = 0): string
    {
        if ($depth >= $this->config['max_recursion_depth']) {
            error_log("TemplateEngine: Maximum recursion depth reached");
            return $content;
        }

        // Find all template calls: {{TemplateName|param1|param2=value}}
        $pattern = '/\{\{([^|}]+)(?:\|([^}]+))?\}\}/';
        
        return preg_replace_callback($pattern, function ($matches) use ($context, $depth) {
            return $this->renderTemplate($matches[1], $matches[2] ?? '', $context, $depth);
        }, $content);
    }

    /**
     * Render a single template
     */
    protected function renderTemplate(string $templateName, string $paramString, array $context, int $depth): string
    {
        try {
            // Parse template parameters
            $parameters = $this->parseTemplateParameters($paramString);
            
            // Merge with context parameters
            $parameters = array_merge($context, $parameters);
            
            // Check if we have a database connection
            if (!$this->connection) {
                return $this->renderTemplateNotFound($templateName, $parameters);
            }
            
            // Find template in database
            $template = Template::findByName($this->connection, $templateName);
            
            if (!$template) {
                return $this->renderTemplateNotFound($templateName, $parameters);
            }
            
            // Render template with parameters
            $rendered = $template->render($parameters);
            
            // Process nested templates if enabled
            if ($this->config['enable_recursive_templates']) {
                $rendered = $this->parseAndRenderTemplates($rendered, $context, $depth + 1);
            }
            
            return $rendered;
            
        } catch (\Exception $e) {
            error_log("TemplateEngine: Error rendering template '{$templateName}': " . $e->getMessage());
            return $this->renderTemplateError($templateName, $e->getMessage());
        }
    }

    /**
     * Parse template parameter string
     */
    protected function parseTemplateParameters(string $paramString): array
    {
        if (empty($paramString)) {
            return [];
        }

        $parameters = [];
        $parts = explode('|', $paramString);

        foreach ($parts as $part) {
            $part = trim($part);
            
            if (strpos($part, '=') !== false) {
                // Named parameter: param=value
                list($key, $value) = explode('=', $part, 2);
                $parameters[trim($key)] = trim($value);
            } else {
                // Positional parameter
                $parameters[] = $part;
            }
        }

        return $parameters;
    }

    /**
     * Render template not found message
     */
    protected function renderTemplateNotFound(string $templateName, array $parameters): string
    {
        $html = '<div class="template-error template-not-found">';
        $html .= '<div class="error-header">Template Not Found: ' . htmlspecialchars($templateName) . '</div>';
        
        if (!empty($parameters)) {
            $html .= '<div class="error-parameters">';
            $html .= '<strong>Parameters:</strong><ul>';
            foreach ($parameters as $key => $value) {
                if (is_numeric($key)) {
                    $html .= '<li>' . htmlspecialchars($value) . '</li>';
                } else {
                    $html .= '<li><strong>' . htmlspecialchars($key) . '</strong>: ' . htmlspecialchars($value) . '</li>';
                }
            }
            $html .= '</ul></div>';
        }
        
        $html .= '<div class="error-help">';
        $html .= 'This template does not exist. Please check the template name or create it.';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Render template error message
     */
    protected function renderTemplateError(string $templateName, string $error): string
    {
        $html = '<div class="template-error template-render-error">';
        $html .= '<div class="error-header">Template Error: ' . htmlspecialchars($templateName) . '</div>';
        $html .= '<div class="error-message">' . htmlspecialchars($error) . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Cache a processed template
     */
    protected function cacheTemplate(string $original, string $processed): void
    {
        if (count($this->templateCache) >= $this->config['max_cache_size']) {
            // Remove oldest entries
            $this->templateCache = array_slice($this->templateCache, -$this->config['max_cache_size'] + 1, null, true);
        }

        $this->templateCache[$original] = $processed;
    }

    /**
     * Clear the template cache
     */
    public function clearCache(): void
    {
        $this->templateCache = [];
        $this->parameterCache = [];
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'template_cache_size' => count($this->templateCache),
            'parameter_cache_size' => count($this->parameterCache),
            'max_cache_size' => $this->config['max_cache_size'],
            'cache_ttl' => $this->config['cache_ttl'],
            'enable_caching' => $this->config['enable_caching']
        ];
    }

    /**
     * Validate template parameters
     */
    public function validateTemplateParameters(string $templateName, array $parameters): array
    {
        if (!$this->connection) {
            return ['valid' => false, 'error' => 'No database connection available'];
        }
        
        $template = Template::findByName($this->connection, $templateName);
        
        if (!$template) {
            return ['valid' => false, 'error' => 'Template not found'];
        }

        $templateParams = $template->getParameters();
        $errors = [];
        $warnings = [];

        foreach ($templateParams as $name => $config) {
            if (isset($config['required']) && $config['required'] && !isset($parameters[$name])) {
                $errors[] = "Required parameter '{$name}' is missing";
            }

            if (isset($parameters[$name]) && isset($config['type'])) {
                $type = $config['type'];
                $value = $parameters[$name];
                
                if (!$this->validateParameterType($value, $type)) {
                    $errors[] = "Parameter '{$name}' must be of type '{$type}'";
                }
            }
        }

        // Check for unknown parameters
        foreach ($parameters as $name => $value) {
            if (!isset($templateParams[$name])) {
                $warnings[] = "Unknown parameter '{$name}' will be ignored";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'template' => $templateName
        ];
    }

    /**
     * Validate parameter type
     */
    protected function validateParameterType(mixed $value, string $type): bool
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_numeric($value) && (string)(int)$value === (string)$value;
            case 'boolean':
                return is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no']);
            case 'array':
                return is_array($value);
            default:
                return true; // Unknown types are considered valid
        }
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): array
    {
        if (!$this->connection) {
            return [];
        }
        return Template::getAllActive($this->connection);
    }

    /**
     * Get templates by category
     */
    public function getTemplatesByCategory(string $category): array
    {
        if (!$this->connection) {
            return [];
        }
        return Template::getByCategory($this->connection, $category);
    }

    /**
     * Get system templates
     */
    public function getSystemTemplates(): array
    {
        if (!$this->connection) {
            return [];
        }
        return Template::getSystemTemplates($this->connection);
    }

    /**
     * Create a new template
     */
    public function createTemplate(array $data): ?Template
    {
        if (!$this->connection) {
            return null;
        }
        
        try {
            $template = new Template($this->connection, $data);
            
            if ($template->save()) {
                // Clear cache when new template is created
                $this->clearCache();
                return $template;
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("TemplateEngine: Error creating template: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing template
     */
    public function updateTemplate(int $id, array $data): bool
    {
        if (!$this->connection) {
            return false;
        }
        
        try {
            $template = new Template($this->connection, array_merge(['id' => $id], $data));
            
            if ($template->save()) {
                // Clear cache when template is updated
                $this->clearCache();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("TemplateEngine: Error updating template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(int $id): bool
    {
        if (!$this->connection) {
            return false;
        }
        
        try {
            $result = $this->connection->table('templates')
                ->where('id', '=', $id)
                ->delete();
            
            if ($result) {
                // Clear cache when template is deleted
                $this->clearCache();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("TemplateEngine: Error deleting template: " . $e->getMessage());
            return false;
        }
    }
} 