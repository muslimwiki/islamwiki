<?php
declare(strict_types=1);

/**
 * Base Skin Class
 * 
 * Provides the foundation for all skins in IslamWiki.
 * 
 * @package IslamWiki\Skins
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Skins;

abstract class Skin
{
    /**
     * @var string The name of the skin
     */
    protected string $name;
    
    /**
     * @var string The version of the skin
     */
    protected string $version;
    
    /**
     * @var string The author of the skin
     */
    protected string $author;
    
    /**
     * @var string The description of the skin
     */
    protected string $description;
    
    /**
     * @var array The skin configuration
     */
    protected array $config;
    
    /**
     * @var string|null The skin directory path
     */
    protected ?string $skinPath = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeSkin();
    }
    
    /**
     * Initialize the skin with default values
     */
    abstract protected function initializeSkin(): void;
    
    /**
     * Get the skin name
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Get the skin version
     */
    public function getVersion(): string
    {
        return $this->version;
    }
    
    /**
     * Get the skin author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }
    
    /**
     * Get the skin description
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Get the skin configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Get the skin path
     */
    public function getSkinPath(): string
    {
        return $this->skinPath;
    }
    
    /**
     * Set the skin path
     */
    public function setSkinPath(string $path): void
    {
        $this->skinPath = $path;
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
        return $this->skinPath . '/css/' . $skinName . '.css';
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
        return $this->skinPath . '/js/' . $skinName . '.js';
    }
    
    /**
     * Get the layout template path for this skin
     */
    public function getLayoutPath(): string
    {
        if ($this->skinPath === null) {
            return '';
        }
        return $this->skinPath . '/templates/layout.twig';
    }
    
    /**
     * Check if the skin has a custom CSS file
     */
    public function hasCustomCss(): bool
    {
        $cssPath = $this->getCssPath();
        return !empty($cssPath) && file_exists($cssPath);
    }
    
    /**
     * Check if the skin has a custom JavaScript file
     */
    public function hasCustomJs(): bool
    {
        $jsPath = $this->getJsPath();
        return !empty($jsPath) && file_exists($jsPath);
    }
    
    /**
     * Check if the skin has a custom layout template
     */
    public function hasCustomLayout(): bool
    {
        $layoutPath = $this->getLayoutPath();
        return !empty($layoutPath) && file_exists($layoutPath);
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
     * Get the default CSS for this skin
     */
    abstract protected function getDefaultCss(): string;
    
    /**
     * Get the default JavaScript for this skin
     */
    abstract protected function getDefaultJs(): string;
    
    /**
     * Get the skin metadata as an array
     */
    public function getMetadata(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'author' => $this->author,
            'description' => $this->description,
            'config' => $this->config,
        ];
    }
    
    /**
     * Validate the skin configuration
     */
    public function validate(): bool
    {
        return !empty($this->name) && !empty($this->version);
    }
} 