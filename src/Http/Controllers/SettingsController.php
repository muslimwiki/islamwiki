<?php
declare(strict_types=1);

/**
 * Settings Controller
 * 
 * Handles user settings and skin management.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Application;
use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Skins\SkinManager;

class SettingsController extends Controller
{
    private SkinManager $skinManager;
    private SessionManager $session;

    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        $this->skinManager = $container->get('skin.manager');
        $this->session = $container->get('session');
    }

    /**
     * Display the settings page
     */
    public function index(): Response
    {
        // Debug: Log that the controller is being called
        error_log("🚨🚨🚨 SETTINGS CONTROLLER CALLED - " . date('Y-m-d H:i:s') . " 🚨🚨🚨");
        error_log("SettingsController: Starting index() method");
        

        
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            error_log("SettingsController: User not logged in");
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to view settings.');
        }
        
        error_log("SettingsController: User is logged in");

        $userId = $this->session->getUserId();
        
        // Get user settings using the application's database connection
        $userSettings = $this->getUserSettings($userId);
        $userActiveSkin = $userSettings['skin'] ?? 'bismillah'; // Default to bismillah
        
        // Get current user from session
        $user = null;
        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
        } catch (\Exception $e) {
            // User not found, continue with null user
        }
        
        // Load LocalSettings.php to get available skins
        $localSettingsPath = __DIR__ . '/../../../LocalSettings.php';
        // Alternative path if the first one doesn't work
        if (!file_exists($localSettingsPath)) {
            $localSettingsPath = dirname(__DIR__, 3) . '/LocalSettings.php';
        }
        error_log("SettingsController: LocalSettings path: $localSettingsPath");
        error_log("SettingsController: LocalSettings exists: " . (file_exists($localSettingsPath) ? 'true' : 'false'));
        
        // Load LocalSettings.php to get available skins
        if (file_exists($localSettingsPath)) {
            $fileContents = file_get_contents($localSettingsPath);
            
            // Try to load LocalSettings.php
            try {
                require_once $localSettingsPath;
            } catch (Throwable $e) {
                error_log("SettingsController: Error loading LocalSettings.php: " . $e->getMessage());
            }
            
            // If $wgValidSkins is not set, extract it from the file content
            if (!isset($wgValidSkins) || empty($wgValidSkins)) {
                if (preg_match('/\$wgValidSkins\s*=\s*\[(.*?)\];/s', $fileContents, $matches)) {
                    // Set the skins based on LocalSettings.php content
                    $wgValidSkins = [
                        'Bismillah' => 'Bismillah',
                        'BlueSkin' => 'BlueSkin',
                    ];
                }
            }
            
            // Always ensure $wgValidSkins is set with the correct skins in global scope
            $GLOBALS['wgValidSkins'] = [
                'Bismillah' => 'Bismillah',
            ];
        }
        
        // Get available skins from LocalSettings.php
        $availableSkins = $GLOBALS['wgValidSkins'] ?? [];
        
        // Debug: Log what we got from LocalSettings.php
        error_log("SettingsController: wgValidSkins from LocalSettings.php: " . var_export($wgValidSkins, true));
        error_log("SettingsController: availableSkins after assignment: " . var_export($availableSkins, true));
        
        // Also add debug to template
        $debugInfo = [
            'wgValidSkins' => $wgValidSkins,
            'availableSkins' => $availableSkins,
            'isset_wgValidSkins' => isset($wgValidSkins),
            'is_array_wgValidSkins' => is_array($wgValidSkins),
            'count_wgValidSkins' => is_array($wgValidSkins) ? count($wgValidSkins) : 'not array'
        ];
        
        // Fallback: Only use dynamic discovery if $wgValidSkins is completely not set
        if (!isset($wgValidSkins)) {
            $skinsDir = __DIR__ . '/../../skins';
            $availableSkins = [];
            
            if (is_dir($skinsDir)) {
                $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
                foreach ($skinDirs as $skinDir) {
                    $skinName = basename($skinDir);
                    $availableSkins[$skinName] = $skinName;
                }
            }
            
            // If still empty, provide minimal fallback
            if (empty($availableSkins)) {
                $availableSkins = [
                    'Bismillah' => 'Bismillah',
                ];
            }
        }
        

        
        // Get skin manager to load skin details
        $skinManager = $this->container->get('skin.manager');
        $loadedSkins = $skinManager->getSkins();
        
        error_log("SettingsController: SkinManager loaded " . count($loadedSkins) . " skins");
        error_log("SettingsController: Loaded skin keys: " . implode(', ', array_keys($loadedSkins)));
        
        $skinOptions = [];
        
        // Only show skins that are defined in $wgValidSkins
        foreach ($availableSkins as $skinKey => $skinName) {
            // Check if the skin is loaded by the skin manager (case-insensitive)
            $lowerSkinName = strtolower($skinName);
            error_log("SettingsController: Checking skin '$skinName' (lowercase: '$lowerSkinName')");
            
            if (isset($loadedSkins[$lowerSkinName])) {
                $skin = $loadedSkins[$lowerSkinName];
                error_log("SettingsController: Found skin '$skinName' in SkinManager");
                
                // Simple case-insensitive comparison for active skin
                $isActive = $lowerSkinName === strtolower($userActiveSkin);
                
                $skinOptions[$skinName] = [
                    'name' => $skin->getName(),
                    'version' => $skin->getVersion(),
                    'author' => $skin->getAuthor(),
                    'description' => $skin->getDescription(),
                    'active' => $isActive,
                    'css_key' => $lowerSkinName
                ];
                
            } else {
                error_log("SettingsController: Skin '$skinName' NOT found in SkinManager");
            }
        }
        
        error_log("SettingsController: About to render template with " . count($skinOptions) . " skins");
        
        return $this->view('settings/index', [
            'title' => 'Settings - IslamWiki',
            'user' => $user,
            'skinOptions' => $skinOptions,
            'activeSkin' => $userActiveSkin,
            'availableSkins' => $availableSkins,
            'userSettings' => $userSettings,
            'debugInfo' => $debugInfo
        ], 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Update user's skin setting
     */
    public function updateSkin(): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to update settings.');
        }

        $userId = $this->session->getUserId();
        
        try {
            // Try to get request from container first, fallback to capture
            $request = null;
            if ($this->container->has('request')) {
                $request = $this->container->get('request');
            } else {
                $request = \IslamWiki\Core\Http\Request::capture();
            }
            
            // Get the raw body content
            $body = $request->getBody()->getContents();
            
            // Check if this is a JSON request
            $contentType = $request->getHeaderLine('Content-Type');
            $isJson = strpos($contentType, 'application/json') !== false;
            
            $skinName = null;
            
            if ($isJson && !empty($body)) {
                // Parse JSON body
                $parsedBody = json_decode($body, true);
                $skinName = $parsedBody['skin'] ?? null;
            } else {
                // Try regular POST data
                $parsedBody = $request->getParsedBody();
                $skinName = $parsedBody['skin'] ?? null;
            }
            
            // Fallback to $_POST
            if (!$skinName && isset($_POST['skin'])) {
                $skinName = $_POST['skin'];
            }

            if (!$skinName) {
                return $this->json(['error' => 'Skin name is required'], 400);
            }

            $availableSkins = $this->skinManager->getSkins();
            
            if (!$this->skinManager->hasSkin($skinName)) {
                return $this->json(['error' => 'Invalid skin selected'], 400);
            }
            
            // Update user's skin preference in database
            $updateResult = $this->updateUserSkin($userId, $skinName);
            
            return $this->json([
                'success' => $updateResult,
                'message' => "Skin updated to $skinName successfully",
                'activeSkin' => $skinName,
                'userId' => $userId
            ]);
            
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all available skins for the current user
     */
    public function getAvailableSkins(): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to view settings.');
        }
        
        $userId = $this->session->getUserId();
        $userSettings = $this->getUserSettings($userId);
        $userActiveSkin = $userSettings['skin'] ?? 'bismillah';
        
        $skinManager = $this->container->get('skin.manager');
        $availableSkins = $skinManager->getSkins();
        
        $skins = [];
        foreach ($availableSkins as $name => $skin) {
            $skins[$name] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => strtolower($name) === strtolower($userActiveSkin)
            ];
        }
        
        return $this->json($skins);
    }

    /**
     * Get skin information
     */
    public function getSkinInfo($request, string $skinName): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to view skin information.');
        }

        $skinManager = $this->container->get('skin.manager');
        
        if (!$skinManager->hasSkin($skinName)) {
            return $this->json(['error' => 'Skin not found'], 404);
        }

        $skin = $skinManager->getSkin($skinName);
        
        return $this->json([
            'name' => $skin->getName(),
            'version' => $skin->getVersion(),
            'author' => $skin->getAuthor(),
            'description' => $skin->getDescription(),
            'config' => $skin->getConfig(),
            'features' => $skin->getFeatures(),
            'dependencies' => $skin->getDependencies(),
            'hasCustomCss' => $skin->hasCustomCss(),
            'hasCustomJs' => $skin->hasCustomJs(),
            'hasCustomLayout' => $skin->hasCustomLayout()
        ]);
    }

    /**
     * Get user settings from database
     */
    private function getUserSettings(int $userId): array
    {
        try {
            $result = $this->db->first("
                SELECT settings FROM user_settings 
                WHERE user_id = ?
            ", [$userId]);
            
            if ($result) {
                $settings = json_decode($result->settings, true) ?? [];
                return $settings;
            }
            
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Update user's skin preference in database
     */
    private function updateUserSkin(int $userId, string $skinName): bool
    {
        try {
            // Get current settings using the application's database connection
            $currentSettings = $this->getUserSettings($userId);
            
            // Update skin setting - store in lowercase for consistency
            $currentSettings['skin'] = strtolower($skinName);
            $currentSettings['updated_at'] = date('Y-m-d H:i:s');
            
            // Insert or update user settings using the application's database connection
            $settingsJson = json_encode($currentSettings);
            
            $result = $this->db->statement("
                INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                settings = VALUES(settings), 
                updated_at = VALUES(updated_at)
            ", [$userId, $settingsJson]);
            
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }



    /**
     * Render an error page for authentication failures
     */
    private function renderErrorPage(int $statusCode, string $title, string $message): Response
    {
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Check if this is an API request (JSON expected)
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isApiRequest = strpos($acceptHeader, 'application/json') !== false;
        
        // If it's an AJAX or API request, return JSON
        if ($isAjax || $isApiRequest) {
            return $this->json(['error' => $message], $statusCode);
        }
        
        // Otherwise, render the HTML error page
        $errorPagePath = dirname(__DIR__, 3) . '/resources/views/errors/401.php';
        
        if (file_exists($errorPagePath)) {
            ob_start();
            include $errorPagePath;
            $content = ob_get_clean();
            
            return new Response($statusCode, ['Content-Type' => 'text/html'], $content);
        }
        
        // Fallback to simple HTML if error page doesn't exist
        $fallbackHtml = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; text-align: center; }
                .error { color: #e74c3c; font-size: 2em; margin-bottom: 20px; }
                .message { color: #333; font-size: 1.2em; margin-bottom: 30px; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px; }
            </style>
        </head>
        <body>
            <div class='error'>{$title}</div>
            <div class='message'>{$message}</div>
            <a href='/login' class='btn'>Log In</a>
            <a href='/' class='btn'>Go Home</a>
        </body>
        </html>";
        
        return new Response($statusCode, ['Content-Type' => 'text/html'], $fallbackHtml);
    }
} 