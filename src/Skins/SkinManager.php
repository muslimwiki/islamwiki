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
        
        // Force reload LocalSettings.php to get updated configuration
        $localSettingsPath = $this->app->basePath('LocalSettings.php');
        if (file_exists($localSettingsPath)) {
            // Clear any potential caching
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($localSettingsPath, true);
            }
            require_once $localSettingsPath;
        }
        
        // Ensure $wgValidSkins is set with both skins
        global $wgValidSkins;
        if (!isset($wgValidSkins) || !isset($wgValidSkins['Muslim'])) {
            $wgValidSkins = [
                'Bismillah' => 'Bismillah',
                'Muslim' => 'Muslim',
            ];
        }
        
        // Load all skins first
        $this->loadSkins();
        
        // Then initialize the active skin from LocalSettings
        $this->initializeFromLocalSettings();
    }
    
    /**
     * Load all available skins
     */
    private function loadSkins(): void
    {
        // Use public/skins as the base directory for skins
        $skinsPath = $this->app->basePath('public/skins');
        
        if (!is_dir($skinsPath)) {
            return;
        }
        
        // Get valid skins from LocalSettings
        global $wgValidSkins;
        $validSkins = $wgValidSkins ?? [];
        
        // Always load all skins from the directory for dynamic discovery
        // LocalSettings can be used to restrict which skins are available for selection
        $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
        error_log("SkinManager: Loading all skins from directory: " . implode(', ', array_map('basename', $skinDirs)));
        
        // Log the valid skins from LocalSettings for reference
        if (!empty($validSkins)) {
            error_log("SkinManager: Valid skins from LocalSettings: " . implode(', ', array_keys($validSkins)));
        } else {
            error_log("SkinManager: No valid skins defined in LocalSettings");
        }
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            error_log("SkinManager: Processing skin directory: $skinDir");
            error_log("SkinManager: Skin name: $skinName");
            error_log("SkinManager: Config file: $skinConfigFile");
            
            if (file_exists($skinConfigFile)) {
                error_log("SkinManager: Config file exists for $skinName");
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
                    error_log("Failed to load skin {$skinName}: " . $e->getMessage());
                }
            } else {
                error_log("SkinManager: Config file not found for $skinName");
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
                'css' => $activeSkin ? $activeSkin->getCssContent() : '',
                'js' => $activeSkin ? $activeSkin->getJsContent() : '',
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
        return $wgActiveSkin ?? 'Bismillah';
    }
    
    /**
     * Get the currently active skin object
     */
    public function getActiveSkin(): ?Skin
    {
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
    public static function getActiveSkinNameStatic(Application $app): string
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
    public static function setActiveSkinStatic(Application $app, string $skinName): bool
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
                'css' => $activeSkin ? $activeSkin->getCssContent() : '',
                'js' => $activeSkin ? $activeSkin->getJsContent() : '',
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
        
        // Reload LocalSettings.php
        $localSettingsPath = $this->app->basePath('LocalSettings.php');
        if (file_exists($localSettingsPath)) {
            // Clear any existing globals to ensure fresh loading
            unset($GLOBALS['wgValidSkins']);
            unset($GLOBALS['wgActiveSkin']);
            
            // Reload LocalSettings
            require_once $localSettingsPath;
            
            // Get updated values
            global $wgValidSkins, $wgActiveSkin;
            $this->activeSkin = $wgActiveSkin ?? 'Bismillah';
        }
        
        // Reload all skins
        $this->loadSkins();
        
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
                'css' => $activeSkin ? $activeSkin->getCssContent() : '',
                'js' => $activeSkin ? $activeSkin->getJsContent() : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.28',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            $container->instance('skin.data', $skinData);
        }
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
} 