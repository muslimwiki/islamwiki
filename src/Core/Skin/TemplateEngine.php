<?php

/**
 * Core Skin Template Engine
 *
 * Handles skin template rendering and customization.
 *
 * @package IslamWiki\Core\Skin
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Skin;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Core Skin Template Engine - Template Rendering System
 */
class TemplateEngine
{
    private Container $container;
    private Logger $logger;
    private SkinManager $skinManager;
    private string $templatesPath;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->skinManager = $container->get('skin.manager');
        $this->templatesPath = $container->get('base_path') . '/skins';
    }

    /**
     * Render a skin template
     */
    public function renderTemplate(string $templateName, array $data = [], string $skinName = null): string
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            $this->logger->error('No skin available for template rendering', ['template' => $templateName]);
            return '';
        }

        $templatePath = $skin['path'] . '/templates/' . $templateName . '.twig';
        
        if (!file_exists($templatePath)) {
            $this->logger->warning('Skin template not found', ['template' => $templateName, 'skin' => $skin['name'], 'path' => $templatePath]);
            return $this->renderFallbackTemplate($templateName, $data);
        }

        try {
            // For now, return the template content as-is
            // In a full implementation, this would use Twig or similar
            $content = file_get_contents($templatePath);
            if ($content === false) {
                throw new \Exception('Failed to read template file');
            }

            // Simple variable replacement for now
            foreach ($data as $key => $value) {
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }

            return $content;
        } catch (\Exception $e) {
            $this->logger->error('Template rendering failed', ['template' => $templateName, 'error' => $e->getMessage()]);
            return $this->renderFallbackTemplate($templateName, $data);
        }
    }

    /**
     * Render fallback template
     */
    private function renderFallbackTemplate(string $templateName, array $data): string
    {
        $fallbackContent = "<div class='skin-template-fallback'>";
        $fallbackContent .= "<h3>Template: {$templateName}</h3>";
        $fallbackContent .= "<p>This template is not available in the current skin.</p>";
        
        if (!empty($data)) {
            $fallbackContent .= "<h4>Template Data:</h4>";
            $fallbackContent .= "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
        }
        
        $fallbackContent .= "</div>";
        
        return $fallbackContent;
    }

    /**
     * Get available templates for a skin
     */
    public function getAvailableTemplates(string $skinName = null): array
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return [];
        }

        $templatesPath = $skin['path'] . '/templates';
        if (!is_dir($templatesPath)) {
            return [];
        }

        $templates = [];
        $templateFiles = glob($templatesPath . '/*.twig');
        
        foreach ($templateFiles as $templateFile) {
            $templateName = basename($templateFile, '.twig');
            $templates[] = [
                'name' => $templateName,
                'path' => $templateFile,
                'size' => filesize($templateFile),
                'modified' => filemtime($templateFile)
            ];
        }

        return $templates;
    }

    /**
     * Check if template exists
     */
    public function templateExists(string $templateName, string $skinName = null): bool
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return false;
        }

        $templatePath = $skin['path'] . '/templates/' . $templateName . '.twig';
        return file_exists($templatePath);
    }

    /**
     * Get template content
     */
    public function getTemplateContent(string $templateName, string $skinName = null): ?string
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return null;
        }

        $templatePath = $skin['path'] . '/templates/' . $templateName . '.twig';
        
        if (!file_exists($templatePath)) {
            return null;
        }

        try {
            $content = file_get_contents($templatePath);
            return $content === false ? null : $content;
        } catch (\Exception $e) {
            $this->logger->error('Failed to read template content', ['template' => $templateName, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get template metadata
     */
    public function getTemplateMetadata(string $templateName, string $skinName = null): ?array
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return null;
        }

        $templatePath = $skin['path'] . '/templates/' . $templateName . '.twig';
        
        if (!file_exists($templatePath)) {
            return null;
        }

        $stat = stat($templatePath);
        if ($stat === false) {
            return null;
        }

        return [
            'name' => $templateName,
            'skin' => $skin['name'],
            'path' => $templatePath,
            'size' => $stat['size'],
            'modified' => $stat['mtime'],
            'permissions' => substr(sprintf('%o', fileperms($templatePath)), -4)
        ];
    }

    /**
     * Validate template syntax
     */
    public function validateTemplate(string $templateName, string $skinName = null): array
    {
        $result = [
            'valid' => false,
            'errors' => [],
            'warnings' => []
        ];

        $content = $this->getTemplateContent($templateName, $skinName);
        if ($content === null) {
            $result['errors'][] = 'Template not found';
            return $result;
        }

        // Basic validation checks
        $errors = [];
        $warnings = [];

        // Check for basic Twig syntax
        if (strpos($content, '{{') !== false && strpos($content, '}}') === false) {
            $errors[] = 'Unclosed Twig variable';
        }

        if (strpos($content, '{%') !== false && strpos($content, '%}') === false) {
            $errors[] = 'Unclosed Twig tag';
        }

        // Check for common issues
        if (strpos($content, '{{ ') !== false && strpos($content, ' }}') === false) {
            $warnings[] = 'Inconsistent Twig variable spacing';
        }

        // Check for potential XSS vulnerabilities
        if (strpos($content, '{{') !== false && strpos($content, '|escape') === false) {
            $warnings[] = 'Consider using |escape filter for user input';
        }

        $result['valid'] = empty($errors);
        $result['errors'] = $errors;
        $result['warnings'] = $warnings;

        return $result;
    }

    /**
     * Get template dependencies
     */
    public function getTemplateDependencies(string $templateName, string $skinName = null): array
    {
        $content = $this->getTemplateContent($templateName, $skinName);
        if ($content === null) {
            return [];
        }

        $dependencies = [];

        // Extract extends statements
        if (preg_match_all('/{%\s*extends\s+[\'"]([^\'"]+)[\'"]\s*%}/', $content, $matches)) {
            $dependencies['extends'] = $matches[1];
        }

        // Extract include statements
        if (preg_match_all('/{%\s*include\s+[\'"]([^\'"]+)[\'"]\s*%}/', $content, $matches)) {
            $dependencies['includes'] = $matches[1];
        }

        // Extract block definitions
        if (preg_match_all('/{%\s*block\s+([^\s%]+)/', $content, $matches)) {
            $dependencies['blocks'] = $matches[1];
        }

        // Extract macro definitions
        if (preg_match_all('/{%\s*macro\s+([^\s%]+)/', $content, $matches)) {
            $dependencies['macros'] = $matches[1];
        }

        return $dependencies;
    }

    /**
     * Get template statistics
     */
    public function getTemplateStatistics(string $skinName = null): array
    {
        $templates = $this->getAvailableTemplates($skinName);
        $totalTemplates = count($templates);
        $totalSize = 0;
        $validTemplates = 0;
        $invalidTemplates = 0;

        foreach ($templates as $template) {
            $totalSize += $template['size'];
            
            $validation = $this->validateTemplate($template['name'], $skinName);
            if ($validation['valid']) {
                $validTemplates++;
            } else {
                $invalidTemplates++;
            }
        }

        return [
            'total_templates' => $totalTemplates,
            'valid_templates' => $validTemplates,
            'invalid_templates' => $invalidTemplates,
            'total_size' => $totalSize,
            'average_size' => $totalTemplates > 0 ? $totalSize / $totalTemplates : 0
        ];
    }
} 