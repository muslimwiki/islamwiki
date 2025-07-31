<?php
declare(strict_types=1);

/**
 * Skin Manager
 * 
 * Manages skin loading, registration, and configuration.
 * 
 * @package IslamWiki\Skins
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Skins;

use IslamWiki\Core\Application;
use IslamWiki\Skins\UserSkin;

class SkinManager
{
    /**
     * @var Application The application instance
     */
    private Application $app;
    
    /**
     * @var array Registered skins
     */
    private array $skins = [];
    
    /**
     * @var string The currently active skin
     */
    private string $activeSkin;
    
    /**
     * @var Skin|null The current skin instance
     */
    private ?Skin $currentSkin = null;
    
    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        
        // Get active skin from LocalSettings
        global $wgActiveSkin;
        $this->activeSkin = strtolower($wgActiveSkin ?? 'bismillah');
        
        $this->loadSkins();
    }
    
    /**
     * Load all available skins
     */
    private function loadSkins(): void
    {
        $skinsPath = $this->app->basePath('skins');
        
        if (!is_dir($skinsPath)) {
            return;
        }
        
        $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    
                    if ($config && isset($config['name'])) {
                        // Create a generic skin instance for user skins
                        $skin = new UserSkin($config, $skinDir);
                        
                        if ($skin->validate()) {
                            $this->skins[$skinName] = $skin;
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but continue loading other skins
                    error_log("Failed to load skin {$skinName}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get all registered skins
     */
    public function getSkins(): array
    {
        return $this->skins;
    }
    
    /**
     * Get a specific skin by name (case-insensitive)
     */
    public function getSkin(string $name): ?Skin
    {
        // First try exact match
        if (isset($this->skins[$name])) {
            return $this->skins[$name];
        }
        
        // Try case-insensitive match
        $lowerName = strtolower($name);
        foreach ($this->skins as $skinName => $skin) {
            if (strtolower($skinName) === $lowerName) {
                return $skin;
            }
        }
        
        return null;
    }
    
    /**
     * Set the active skin (case-insensitive)
     */
    public function setActiveSkin(string $name): bool
    {
        $skin = $this->getSkin($name);
        if ($skin !== null) {
            // Use the actual skin name (preserve case)
            $this->activeSkin = array_search($skin, $this->skins);
            $this->currentSkin = $skin;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the currently active skin
     */
    public function getActiveSkin(): ?Skin
    {
        if ($this->currentSkin === null) {
            $this->currentSkin = $this->getSkin($this->activeSkin);
        }
        
        return $this->currentSkin;
    }
    
    /**
     * Get the name of the currently active skin
     */
    public function getActiveSkinName(): string
    {
        return $this->activeSkin;
    }
    
    /**
     * Check if a skin exists
     */
    public function hasSkin(string $name): bool
    {
        return isset($this->skins[$name]);
    }
    
    /**
     * Get the CSS content for the active skin
     */
    public function getActiveSkinCss(): string
    {
        $skin = $this->getActiveSkin();
        
        if ($skin === null) {
            return '';
        }
        
        return $skin->getCssContent();
    }
    
    /**
     * Get the JavaScript content for the active skin
     */
    public function getActiveSkinJs(): string
    {
        $skin = $this->getActiveSkin();
        
        if ($skin === null) {
            return '';
        }
        
        return $skin->getJsContent();
    }
    
    /**
     * Get the layout template path for the active skin
     */
    public function getActiveSkinLayoutPath(): string
    {
        $skin = $this->getActiveSkin();
        
        if ($skin === null) {
            return '';
        }
        
        return $skin->getLayoutPath();
    }
    
    /**
     * Check if the active skin has a custom layout
     */
    public function hasActiveSkinCustomLayout(): bool
    {
        $skin = $this->getActiveSkin();
        
        if ($skin === null) {
            return false;
        }
        
        return $skin->hasCustomLayout();
    }
    
    /**
     * Get all available skin names
     */
    public function getAvailableSkinNames(): array
    {
        return array_keys($this->skins);
    }
    
    /**
     * Get skin metadata for all skins
     */
    public function getAllSkinMetadata(): array
    {
        $metadata = [];
        
        foreach ($this->skins as $name => $skin) {
            $metadata[$name] = $skin->getMetadata();
        }
        
        return $metadata;
    }
    
    /**
     * Register a skin manually
     */
    public function registerSkin(string $name, Skin $skin): void
    {
        $this->skins[$name] = $skin;
    }
    
    /**
     * Unregister a skin
     */
    public function unregisterSkin(string $name): bool
    {
        if (isset($this->skins[$name])) {
            unset($this->skins[$name]);
            
            // If we're unregistering the active skin, switch to default
            if ($this->activeSkin === $name) {
                $this->setActiveSkin('bismillah');
            }
            
            return true;
        }
        
        return false;
    }
} 