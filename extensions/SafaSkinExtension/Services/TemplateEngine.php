<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension\Services;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;

/**
 * Template Engine Service
 * 
 * Handles skin-aware template rendering and template path resolution.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Services
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class TemplateEngine
{
    private AsasContainer $container;
    private SkinManager $skinManager;
    private array $templateCache = [];
    private array $templatePaths = [];

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->skinManager = $container->get('skin.manager');
        $this->initializeTemplatePaths();
    }

    /**
     * Initialize template paths for the active skin
     */
    private function initializeTemplatePaths(): void
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return;
        }

        $skinPath = $activeSkin['path'];
        
        // Define template directories in priority order
        $this->templatePaths = [
            'layouts' => $skinPath . '/layouts',
            'components' => $skinPath . '/components',
            'pages' => $skinPath . '/pages',
            'fallback' => 'resources/views' // Fallback to old system
        ];
    }

    /**
     * Resolve template path to use skin system
     */
    public function resolveTemplatePath(string $template): string
    {
        // Check if template is already resolved
        if (isset($this->templateCache[$template])) {
            return $this->templateCache[$template];
        }

        $resolvedPath = $this->findTemplateInSkin($template);
        
        // Cache the result
        $this->templateCache[$template] = $resolvedPath;
        
        return $resolvedPath;
    }

    /**
     * Find template in the active skin
     */
    private function findTemplateInSkin(string $template): string
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return $template; // Return original if no skin
        }

        $skinPath = $activeSkin['path'];
        
        // Try to find template in skin directories
        $skinTemplate = $this->findTemplateInDirectory($template, $skinPath);
        if ($skinTemplate) {
            return $skinTemplate;
        }

        // Fallback to old system
        return $template;
    }

    /**
     * Find template in a specific directory
     */
    private function findTemplateInDirectory(string $template, string $directory): ?string
    {
        // Check if template exists in skin directory
        $skinTemplate = $directory . '/' . $template;
        if (file_exists($skinTemplate)) {
            return $skinTemplate;
        }

        // Check if template exists in subdirectories
        $subdirectories = ['layouts', 'components', 'pages'];
        foreach ($subdirectories as $subdir) {
            $subdirPath = $directory . '/' . $subdir;
            if (is_dir($subdirPath)) {
                $subdirTemplate = $subdirPath . '/' . basename($template);
                if (file_exists($subdirTemplate)) {
                    return $subdirTemplate;
                }
            }
        }

        return null;
    }

    /**
     * Process template through skin system
     */
    public function processTemplate(string $template, array $data = []): void
    {
        // Add skin information to template data
        $skinData = $this->getSkinTemplateData();
        $data = array_merge($data, $skinData);

        // Process template with enhanced data
        $this->enhanceTemplateData($template, $data);
    }

    /**
     * Get skin-specific template data
     */
    private function getSkinTemplateData(): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return [];
        }

        return [
            'skin' => [
                'name' => $activeSkin['name'],
                'display_name' => $activeSkin['display_name'] ?? $activeSkin['name'],
                'version' => $activeSkin['version'],
                'path' => $activeSkin['path'],
                'assets' => $this->skinManager->getActiveSkinAssets()
            ],
            'template_paths' => $this->templatePaths
        ];
    }

    /**
     * Enhance template data with skin information
     */
    private function enhanceTemplateData(string $template, array &$data): void
    {
        // Add skin-specific variables
        $data['current_skin'] = $this->skinManager->getActiveSkinName();
        $data['available_skins'] = $this->skinManager->getAvailableSkins();
        
        // Add skin configuration
        $activeSkin = $this->skinManager->getActiveSkin();
        if ($activeSkin) {
            $data['skin_config'] = $this->skinManager->getSkinConfiguration($activeSkin['name']);
        }
    }

    /**
     * Check if template exists in skin
     */
    public function templateExists(string $template): bool
    {
        $resolvedPath = $this->resolveTemplatePath($template);
        return file_exists($resolvedPath);
    }

    /**
     * Get template content
     */
    public function getTemplateContent(string $template): ?string
    {
        $resolvedPath = $this->resolveTemplatePath($template);
        if (!file_exists($resolvedPath)) {
            return null;
        }

        return file_get_contents($resolvedPath);
    }

    /**
     * Get all available templates in skin
     */
    public function getAvailableTemplates(): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return [];
        }

        $templates = [];
        $skinPath = $activeSkin['path'];

        // Scan for templates in skin directories
        $directories = ['layouts', 'components', 'pages'];
        foreach ($directories as $dir) {
            $dirPath = $skinPath . '/' . $dir;
            if (is_dir($dirPath)) {
                $files = glob($dirPath . '/*.twig');
                foreach ($files as $file) {
                    $templates[] = [
                        'path' => $file,
                        'name' => basename($file, '.twig'),
                        'type' => $dir,
                        'relative_path' => $dir . '/' . basename($file)
                    ];
                }
            }
        }

        return $templates;
    }

    /**
     * Get template metadata
     */
    public function getTemplateMetadata(string $template): array
    {
        $resolvedPath = $this->resolveTemplatePath($template);
        if (!file_exists($resolvedPath)) {
            return [];
        }

        $content = file_get_contents($resolvedPath);
        
        return [
            'path' => $resolvedPath,
            'size' => strlen($content),
            'modified' => filemtime($resolvedPath),
            'exists' => true
        ];
    }

    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $this->templateCache = [];
    }

    /**
     * Get template cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cached_templates' => count($this->templateCache),
            'template_paths' => count($this->templatePaths),
            'cache_size' => memory_get_usage(true)
        ];
    }

    /**
     * Validate template structure
     */
    public function validateTemplateStructure(): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return ['valid' => false, 'errors' => ['No active skin']];
        }

        $errors = [];
        $skinPath = $activeSkin['path'];

        // Check required directories
        $requiredDirs = ['layouts', 'components', 'pages'];
        foreach ($requiredDirs as $dir) {
            $dirPath = $skinPath . '/' . $dir;
            if (!is_dir($dirPath)) {
                $errors[] = "Missing required directory: {$dir}";
            }
        }

        // Check for at least one layout template
        $layoutsPath = $skinPath . '/layouts';
        if (is_dir($layoutsPath)) {
            $layoutFiles = glob($layoutsPath . '/*.twig');
            if (empty($layoutFiles)) {
                $errors[] = 'No layout templates found';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'skin_path' => $skinPath
        ];
    }
} 