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

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Skins\SkinManager;

class SettingsController extends Controller
{
    private SkinManager $skinManager;
    private Wisal $session;

    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
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
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to view settings.');
        }

        $userId = $this->session->getUserId();
        
        // Get user settings using the application's database connection
        $userSettings = $this->getUserSettings($userId);
        $userActiveSkin = $userSettings['skin'] ?? 'Bismillah'; // Default to Bismillah
        
        // Get current user from session
        $user = null;
        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
        } catch (\Exception $e) {
            // User not found, continue with null user
        }
        
        // Dynamically discover available skins from the skins directory
        $availableSkins = $this->discoverAvailableSkins();
        
        // Get skin manager to load skin details
        $skinManager = $this->container->get('skin.manager');
        $loadedSkins = $skinManager->getSkins();
        
        $skinOptions = [];
        
        // Process discovered skins
        foreach ($availableSkins as $skinKey => $skinData) {
            // Check if the skin is loaded by the skin manager (case-insensitive)
            $lowerSkinName = strtolower($skinData['name']);
            
            if (isset($loadedSkins[$lowerSkinName])) {
                $skin = $loadedSkins[$lowerSkinName];
                
                // Simple case-insensitive comparison for active skin
                $isActive = $lowerSkinName === strtolower($userActiveSkin);
                
                $skinOptions[$skinData['name']] = [
                    'name' => $skin->getName(),
                    'version' => $skin->getVersion(),
                    'author' => $skin->getAuthor(),
                    'description' => $skin->getDescription(),
                    'active' => $isActive,
                    'css_key' => $lowerSkinName,
                    'directory' => $skinData['directory'],
                    'features' => $skinData['features'] ?? [],
                    'config' => $skinData['config'] ?? []
                ];
            }
        }
        
        return $this->view('settings/index.twig', [
            'title' => 'Settings - IslamWiki',
            'user' => $user,
            'skinOptions' => $skinOptions,
            'activeSkin' => $userActiveSkin,
            'availableSkins' => $availableSkins,
            'userSettings' => $userSettings
        ], 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Dynamically discover available skins from the skins directory
     */
    private function discoverAvailableSkins(): array
    {
        // Use a more reliable path calculation
        $skinsDir = dirname(__DIR__, 3) . '/skins';
        $availableSkins = [];
        
        if (!is_dir($skinsDir)) {
            return $availableSkins;
        }
        
        $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    
                    if ($config && isset($config['name'])) {
                        $availableSkins[strtolower($skinName)] = [
                            'name' => $config['name'],
                            'version' => $config['version'] ?? '0.0.1',
                            'author' => $config['author'] ?? 'Unknown',
                            'description' => $config['description'] ?? '',
                            'directory' => $skinName,
                            'features' => $config['features'] ?? [],
                            'config' => $config['config'] ?? [],
                            'dependencies' => $config['dependencies'] ?? []
                        ];
                    }
                } catch (\Exception $e) {
                    // Log error but continue loading other skins
                    error_log("Failed to load skin config for {$skinName}: " . $e->getMessage());
                }
            }
        }
        
        return $availableSkins;
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

            // Validate that the skin exists in our discovered skins
            $availableSkins = $this->discoverAvailableSkins();
            $skinExists = false;
            
            foreach ($availableSkins as $skinData) {
                if (strtolower($skinData['name']) === strtolower($skinName)) {
                    $skinExists = true;
                    break;
                }
            }
            
            if (!$skinExists) {
                return $this->json(['error' => 'Invalid skin selected'], 400);
            }
            
            // Set the active skin using standardized approach
            $app = $this->container->get('app');
            $skinSetResult = SkinManager::setActiveSkinStatic($app, $skinName);
            
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
                    $userActiveSkin = $userSettings['skin'] ?? 'muslim';
        
        $availableSkins = $this->discoverAvailableSkins();
        
        $skins = [];
        foreach ($availableSkins as $key => $skinData) {
            $skins[$skinData['name']] = [
                'name' => $skinData['name'],
                'version' => $skinData['version'],
                'author' => $skinData['author'],
                'description' => $skinData['description'],
                'active' => strtolower($skinData['name']) === strtolower($userActiveSkin),
                'features' => $skinData['features'],
                'config' => $skinData['config']
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

        $availableSkins = $this->discoverAvailableSkins();
        $skinData = null;
        
        // Find the skin data
        foreach ($availableSkins as $skinInfo) {
            if (strtolower($skinInfo['name']) === strtolower($skinName)) {
                $skinData = $skinInfo;
                break;
            }
        }
        
        if (!$skinData) {
            return $this->json(['error' => 'Skin not found'], 404);
        }
        
        return $this->json([
            'name' => $skinData['name'],
            'version' => $skinData['version'],
            'author' => $skinData['author'],
            'description' => $skinData['description'],
            'config' => $skinData['config'],
            'features' => $skinData['features'],
            'dependencies' => $skinData['dependencies'],
            'hasCustomCss' => true, // All skins have CSS
            'hasCustomJs' => true,  // All skins have JS
            'hasCustomLayout' => true // All skins have layout
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