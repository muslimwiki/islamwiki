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

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\UserSkin;

class SkinManager
{
    /**
     * @var NizamApplication The application instance
     */
    private NizamApplication $app;
    
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
    public function __construct(NizamApplication $app)
    {
        $this->app = $app;
        
        // Force reload LocalSettings.php to get updated configuration
        $this->forceReloadLocalSettings();
        
        // Load all skins first
        $this->loadSkins();
        
        // Then initialize the active skin from LocalSettings
        $this->initializeFromLocalSettings();
    }
    
    /**
     * Force reload LocalSettings.php and clear all caching
     */
    private function forceReloadLocalSettings(): void
    {
        $localSettingsPath = $this->app->basePath('LocalSettings.php');
        if (file_exists($localSettingsPath)) {
            // Clear all potential caching layers
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($localSettingsPath, true);
            }
            if (function_exists('apc_delete_file')) {
                apc_delete_file($localSettingsPath);
            }
            
            // Force reload the file without clearing globals
            require_once $localSettingsPath;
            
            // Ensure globals are available after reload
            global $wgValidSkins, $wgActiveSkin;
            if (!isset($wgValidSkins)) {
                error_log("SkinManager: Warning - \$wgValidSkins not set after reload");
            } else {
                error_log("SkinManager: Force reloaded LocalSettings.php - Valid skins: " . implode(', ', array_keys($wgValidSkins)));
            }
        }
    }
    
    /**
     * Check if LocalSettings has been modified since last load
     */
    private function hasLocalSettingsChanged(): bool
    {
        static $lastModified = null;
        $localSettingsPath = $this->app->basePath('LocalSettings.php');
        
        if (!file_exists($localSettingsPath)) {
            return false;
        }
        
        $currentModified = filemtime($localSettingsPath);
        
        if ($lastModified === null) {
            $lastModified = $currentModified;
            return false;
        }
        
        if ($currentModified > $lastModified) {
            $lastModified = $currentModified;
            return true;
        }
        
        return false;
    }
    
    /**
     * Load all available skins
     */
    private function loadSkins(): void
    {
        // Get valid skins from LocalSettings - this is the single source of truth
        global $wgValidSkins;
        $validSkins = $wgValidSkins ?? [];
        
        if (empty($validSkins)) {
            error_log("SkinManager: No valid skins defined in LocalSettings - no skins will be loaded");
            return;
        }
        
        // Use public/skins as the base directory for skins
        $skinsPath = $this->app->basePath('skins');
        
        if (!is_dir($skinsPath)) {
            error_log("SkinManager: Skins directory not found: $skinsPath");
            return;
        }
        
        error_log("SkinManager: Loading skins defined in LocalSettings: " . implode(', ', array_keys($validSkins)));
        
        // Only load skins that are explicitly defined in LocalSettings
        foreach ($validSkins as $skinName => $skinDisplayName) {
            $skinDir = $skinsPath . '/' . $skinName;
            $skinConfigFile = $skinDir . '/skin.json';
            
            error_log("SkinManager: Processing skin: $skinName");
            error_log("SkinManager: Skin directory: $skinDir");
            error_log("SkinManager: Config file: $skinConfigFile");
            
            if (!is_dir($skinDir)) {
                error_log("SkinManager: Warning - Skin directory not found for '$skinName': $skinDir");
                continue;
            }
            
            if (!file_exists($skinConfigFile)) {
                error_log("SkinManager: Warning - Config file not found for '$skinName': $skinConfigFile");
                continue;
            }
            
            try {
                $config = json_decode(file_get_contents($skinConfigFile), true);
                
                if ($config && isset($config['name'])) {
                    error_log("SkinManager: Valid config for $skinName, creating UserSkin");
                    // Create a generic skin instance for user skins
                    $skin = new UserSkin($config, $skinDir);
                    
                    if ($skin->validate()) {
                        // Store skin with original case for proper matching
                        $this->skins[$skinName] = $skin;
                        // Also store with lowercase key for case-insensitive access
                        $this->skins[strtolower($skinName)] = $skin;
                        error_log("SkinManager: Successfully loaded skin $skinName");
                    } else {
                        error_log("SkinManager: Skin $skinName failed validation");
                    }
                } else {
                    error_log("SkinManager: Invalid config for $skinName - missing name");
                }
            } catch (\Exception $e) {
                // Log error but continue loading other skins
                error_log("SkinManager: Failed to load skin {$skinName}: " . $e->getMessage());
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
     * Debug method to see what skins are loaded
     */
    public function debugSkins(): array
    {
        return [
            'loaded_skins' => array_keys($this->skins),
            'valid_skins_from_localsettings' => $GLOBALS['wgValidSkins'] ?? [],
            'active_skin' => $this->activeSkin
        ];
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
        
        // Then try case-insensitive match
        $lowerName = strtolower($name);
        return $this->skins[$lowerName] ?? null;
    }
    
    /**
     * Get the active skin name (standardized method)
     * This is the preferred way to get the active skin name
     */
    public function getActiveSkinName(): string
    {
        return $this->activeSkin;
    }
    
    /**
     * Set the active skin (standardized method)
     * This is the preferred way to change the active skin
     */
    public function setActiveSkin(string $name): bool
    {
        if (!$this->hasSkin($name)) {
            error_log("SkinManager: Cannot set active skin to '$name' - skin not found");
            return false;
        }
        
        $this->activeSkin = $name;
        
        // Reset the current skin so it will be reloaded on next access
        $this->currentSkin = null;
        
        // Update the container's cached instances
        $container = $this->app->getContainer();
        if ($container->has('skin.manager')) {
            $container->instance('skin.manager', $this);
        }
        if ($container->has('skin.active')) {
            $container->instance('skin.active', $this->getActiveSkin());
        }
        if ($container->has('skin.data')) {
            $activeSkin = $this->getActiveSkin();
            $skinData = [
                'css_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/css/' . strtolower($activeSkin->getName()) . '.css' : '',
                'js_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/js/' . strtolower($activeSkin->getName()) . '.js' : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.28',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            $container->instance('skin.data', $skinData);
        }
        
        error_log("SkinManager: Active skin set to '$name'");
        return true;
    }
    
    /**
     * Get the active skin name with fallback to LocalSettings
     * This provides backward compatibility with the old $wgActiveSkin approach
     */
    public function getActiveSkinNameWithFallback(): string
    {
        // First try to get from SkinManager
        $activeSkin = $this->getActiveSkinName();
        if (!empty($activeSkin)) {
            return $activeSkin;
        }
        
        // Fallback to LocalSettings
        global $wgActiveSkin;
        return $wgActiveSkin ?? 'Muslim';
    }
    
    /**
     * Get the currently active skin object
     */
    public function getActiveSkin(): ?Skin
    {
        // Check if LocalSettings has changed and reload if necessary
        if ($this->hasLocalSettingsChanged()) {
            error_log("SkinManager: LocalSettings changed, forcing reload");
            $this->forceReloadLocalSettings();
            $this->reloadAllSkins();
        }
        
        if ($this->currentSkin === null) {
            error_log("SkinManager::getActiveSkin - Looking for skin: " . $this->activeSkin);
            $this->currentSkin = $this->getSkin($this->activeSkin);
            error_log("SkinManager::getActiveSkin - Found skin: " . ($this->currentSkin ? $this->currentSkin->getName() : 'null'));
        }
        
        return $this->currentSkin;
    }
    
    /**
     * Initialize the active skin from LocalSettings
     * This should be called after LocalSettings is loaded
     */
    public function initializeFromLocalSettings(): void
    {
        global $wgActiveSkin;
        
        if (isset($wgActiveSkin) && !empty($wgActiveSkin)) {
            // Only set if the skin exists
            if ($this->hasSkin($wgActiveSkin)) {
                $this->activeSkin = $wgActiveSkin;
                error_log("SkinManager: Initialized active skin from LocalSettings: $wgActiveSkin");
            } else {
                error_log("SkinManager: Warning - LocalSettings specifies skin '$wgActiveSkin' but it's not available");
                $this->activeSkin = 'Bismillah'; // fallback
            }
        } else {
            $this->activeSkin = 'Bismillah'; // fallback
        }
    }
    
    /**
     * Static helper to get the active skin name
     * This provides a consistent way to get the active skin across the application
     */
    public static function getActiveSkinNameStatic(NizamApplication $app): string
    {
        try {
            $container = $app->getContainer();
            $skinManager = $container->get('skin.manager');
            return $skinManager->getActiveSkinName();
        } catch (Exception $e) {
            // Fallback to LocalSettings if SkinManager is not available
            global $wgActiveSkin;
            return $wgActiveSkin ?? 'Bismillah';
        }
    }
    
    /**
     * Static helper to set the active skin
     * This provides a consistent way to change the active skin across the application
     */
    public static function setActiveSkinStatic(NizamApplication $app, string $skinName): bool
    {
        try {
            $container = $app->getContainer();
            $skinManager = $container->get('skin.manager');
            return $skinManager->setActiveSkin($skinName);
        } catch (Exception $e) {
            error_log("SkinManager::setActiveSkinStatic - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the active skin for a specific user
     */
    public function getActiveSkinForUser(int $userId): ?Skin
    {
        try {
            // Get user settings from database
            $stmt = $this->app->getContainer()->get('db')->prepare("
                SELECT settings FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                $settings = json_decode($result['settings'], true) ?? [];
                $userSkin = $settings['skin'] ?? null;
                
                if ($userSkin) {
                    return $this->getSkin($userSkin);
                }
            }
            
            // Fallback to global default
            return $this->getActiveSkin();
        } catch (\Throwable $e) {
            error_log("SkinManager::getActiveSkinForUser - Error: " . $e->getMessage());
            return $this->getActiveSkin();
        }
    }
    
    /**
     * Get the active skin name for a specific user
     */
    public function getActiveSkinNameForUser(int $userId): string
    {
        try {
            // Get user settings from database
            $db = $this->app->getContainer()->get('db');
            
            $stmt = $db->prepare("
                SELECT settings FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                $settings = json_decode($result['settings'], true) ?? [];
                $userSkin = $settings['skin'] ?? null;
                
                if ($userSkin) {
                    // Return the proper display name, not the lowercase key
                    $skin = $this->getSkin($userSkin);
                    return $skin ? $skin->getName() : strtolower($userSkin);
                }
            }
            
            // Fallback to global default
            return $this->getActiveSkinName();
        } catch (\Throwable $e) {
            error_log("SkinManager::getActiveSkinNameForUser - Error: " . $e->getMessage());
            return $this->getActiveSkinName();
        }
    }
    
    /**
     * Reload the active skin from LocalSettings.php
     */
    public function reloadActiveSkin(): void
    {
        // Parse LocalSettings.php content directly
        $localSettingsPath = $this->app->basePath('LocalSettings.php');
        if (file_exists($localSettingsPath)) {
            $content = file_get_contents($localSettingsPath);
            
            // Extract the active skin from the file content
            if (preg_match('/\$wgActiveSkin\s*=\s*env\(\'ACTIVE_SKIN\',\s*\'([^\']+)\'\);/', $content, $matches)) {
                $this->activeSkin = $matches[1];
            } else {
                $this->activeSkin = 'Bismillah'; // fallback
            }
        }
        
        // Reset the current skin so it will be reloaded on next access
        $this->currentSkin = null;
        
        // Update the container's cached instances
        $container = $this->app->getContainer();
        if ($container->has('skin.manager')) {
            $container->instance('skin.manager', $this);
        }
        if ($container->has('skin.active')) {
            $container->instance('skin.active', $this->getActiveSkin());
        }
        if ($container->has('skin.data')) {
            $activeSkin = $this->getActiveSkin();
            $skinData = [
                'css_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/css/' . strtolower($activeSkin->getName()) . '.css' : '',
                'js_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/js/' . strtolower($activeSkin->getName()) . '.js' : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.28',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            $container->instance('skin.data', $skinData);
        }
    }
    
    /**
     * Reload all skins from LocalSettings.php
     */
    public function reloadAllSkins(): void
    {
        // Clear existing skins
        $this->skins = [];
        
        // Force reload LocalSettings
        $this->forceReloadLocalSettings();
        
        // Reload all skins
        $this->loadSkins();
        
        // Reset the current skin so it will be reloaded on next access
        $this->currentSkin = null;
        
        // Update the container's cached instances (disable caching for skin data)
        $container = $this->app->getContainer();
        if ($container->has('skin.manager')) {
            $container->instance('skin.manager', $this);
        }
        if ($container->has('skin.active')) {
            $container->instance('skin.active', $this->getActiveSkin());
        }
        if ($container->has('skin.data')) {
            $activeSkin = $this->getActiveSkin();
            $skinData = [
                'css_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/css/' . strtolower($activeSkin->getName()) . '.css' : '',
                'js_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/js/' . strtolower($activeSkin->getName()) . '.js' : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.28',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            $container->instance('skin.data', $skinData);
        }
        
        error_log("SkinManager: All skins reloaded from LocalSettings");
    }
    
    /**
     * Check if a skin exists
     */
    public function hasSkin(string $name): bool
    {
        // First try exact match
        if (isset($this->skins[$name])) {
            return true;
        }
        
        // Then try case-insensitive match
        return isset($this->skins[strtolower($name)]);
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
        $this->skins[strtolower($name)] = $skin;
    }
    
    /**
     * Unregister a skin
     */
    public function unregisterSkin(string $name): bool
    {
        $lowerName = strtolower($name);
        $removed = false;
        
        // Remove both exact and lowercase versions
        if (isset($this->skins[$name])) {
            unset($this->skins[$name]);
            $removed = true;
        }
        
        if (isset($this->skins[$lowerName])) {
            unset($this->skins[$lowerName]);
            $removed = true;
        }
        
        // If we're unregistering the active skin, switch to default
        if ($this->activeSkin === $name || $this->activeSkin === $lowerName) {
            $this->setActiveSkin('Bismillah');
        }
        
        return $removed;
    }
    
    /**
     * Disable caching for skin operations
     */
    public function disableCaching(): void
    {
        // Clear any cached data
        $this->currentSkin = null;
        
        // Clear container cache for skin services
        $container = $this->app->getContainer();
        if ($container->has('skin.data')) {
            $container->forget('skin.data');
        }
        if ($container->has('skin.active')) {
            $container->forget('skin.active');
        }
        
        error_log("SkinManager: Caching disabled for skin operations");
    }
    
    /**
     * Force immediate reload of skin configuration
     */
    public function forceReload(): void
    {
        $this->disableCaching();
        $this->forceReloadLocalSettings();
        $this->reloadAllSkins();
        error_log("SkinManager: Forced immediate reload of skin configuration");
    }
} 