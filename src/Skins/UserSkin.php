<?php

/**
 * User Skin Class
 *
 * Handles user-defined skins from JSON configuration files.
 *
 * @package IslamWiki\Skins
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Skins;

class UserSkin extends Skin
{
    /**
     * @var array The skin configuration from JSON
     */
    private array $jsonConfig;

    /**
     * Constructor
     */
    public function __construct(array $config, string $skinPath)
    {
        $this->jsonConfig = $config;
        $this->setSkinPath($skinPath);
        $this->initializeSkin();
    }

    /**
     * Initialize the skin with values from JSON config
     */
    protected function initializeSkin(): void
    {
        $this->name = $this->jsonConfig['name'] ?? 'Unknown';
        $this->version = $this->jsonConfig['version'] ?? '1.0.0';
        $this->author = $this->jsonConfig['author'] ?? 'Unknown';
        $this->description = $this->jsonConfig['description'] ?? 'User-defined skin';
        $this->config = $this->jsonConfig['config'] ?? [];
    }

    /**
     * Get the CSS content for this skin
     */
    public function getCssContent(): string
    {
        if ($this->hasCustomCss()) {
            return file_get_contents($this->getCssPath());
        }

        return $this->getDefaultCss();
    }

    /**
     * Get the JavaScript content for this skin
     */
    public function getJsContent(): string
    {
        if ($this->hasCustomJs()) {
            return file_get_contents($this->getJsPath());
        }

        return $this->getDefaultJs();
    }

    /**
     * Get the CSS file path for this skin
     */
    public function getCssPath(): string
    {
        if ($this->skinPath === null) {
            return '';
        }

        $skinName = strtolower($this->getName());
        $cssFile = $this->jsonConfig['assets']['css'] ?? 'css/' . $skinName . '.css';
        return $this->skinPath . '/' . $cssFile;
    }

    /**
     * Get the JavaScript file path for this skin
     */
    public function getJsPath(): string
    {
        if ($this->skinPath === null) {
            return '';
        }

        $skinName = strtolower($this->getName());
        $jsFile = $this->jsonConfig['assets']['js'] ?? 'js/' . $skinName . '.js';
        return $this->skinPath . '/' . $jsFile;
    }

    /**
     * Get the layout template path for this skin
     */
    public function getLayoutPath(): string
    {
        if ($this->skinPath === null) {
            return '';
        }

        $layoutFile = $this->jsonConfig['assets']['layout'] ?? 'templates/layout.twig';
        return $this->skinPath . '/' . $layoutFile;
    }

    /**
     * Get the default CSS for this skin
     */
    protected function getDefaultCss(): string
    {
        return <<<'CSS'
/* Default CSS for user skins */
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --secondary-color: #6b7280;
    --secondary-hover: #4b5563;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --background-color: #f8fafc;
    --card-background: #ffffff;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    color: var(--text-primary);
    background-color: var(--background-color);
    font-size: 16px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
CSS;
    }

    /**
     * Get the default JavaScript for this skin
     */
    protected function getDefaultJs(): string
    {
        return <<<'JS'
// Default JavaScript for user skins
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Prism.js for syntax highlighting
    if (typeof Prism !== 'undefined') {
        Prism.highlightAll();
    }
});
JS;
    }

    /**
     * Get additional skin metadata from JSON config
     */
    public function getJsonConfig(): array
    {
        return $this->jsonConfig;
    }

    /**
     * Get skin features
     */
    public function getFeatures(): array
    {
        return $this->jsonConfig['features'] ?? [];
    }

    /**
     * Get skin dependencies
     */
    public function getDependencies(): array
    {
        return $this->jsonConfig['dependencies'] ?? [];
    }

    /**
     * Check if skin has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getFeatures());
    }
}
