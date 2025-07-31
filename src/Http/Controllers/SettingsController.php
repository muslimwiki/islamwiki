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
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to access settings.');
        }

        $userId = $this->session->getUserId();
        $userSettings = $this->getUserSettings($userId);
        
        // Get current user from session
        $user = null;
        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
        } catch (\Exception $e) {
            error_log('SettingsController: Error getting user: ' . $e->getMessage());
        }
        
        $skinManager = $this->container->get('skin.manager');
        $availableSkins = $skinManager->getSkins();
        $userActiveSkin = $userSettings['skin'] ?? 'Bismillah'; // Default to Bismillah
        
        $skinOptions = [];
        foreach ($availableSkins as $name => $skin) {
            $skinOptions[$name] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => strtolower($name) === strtolower($userActiveSkin)
            ];
        }

        return $this->view('settings/index', [
            'title' => 'Settings - IslamWiki',
            'user' => $user,
            'skinOptions' => $skinOptions,
            'activeSkin' => $userActiveSkin,
            'availableSkins' => $availableSkins,
            'userSettings' => $userSettings
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
            error_log("SettingsController::updateSkin - Raw body: " . $body);
            
            // Check if this is a JSON request
            $contentType = $request->getHeaderLine('Content-Type');
            $isJson = strpos($contentType, 'application/json') !== false;
            
            $skinName = null;
            
            if ($isJson && !empty($body)) {
                // Parse JSON body
                $parsedBody = json_decode($body, true);
                $skinName = $parsedBody['skin'] ?? null;
                error_log("SettingsController::updateSkin - JSON parsed, skin: " . ($skinName ?? 'null'));
            } else {
                // Try regular POST data
                $parsedBody = $request->getParsedBody();
                $skinName = $parsedBody['skin'] ?? null;
                error_log("SettingsController::updateSkin - POST data, skin: " . ($skinName ?? 'null'));
            }
            
            // Fallback to $_POST
            if (!$skinName && isset($_POST['skin'])) {
                $skinName = $_POST['skin'];
                error_log("SettingsController::updateSkin - Got skin from $_POST: " . $skinName);
            }

            if (!$skinName) {
                return $this->json(['error' => 'Skin name is required'], 400);
            }

            $availableSkins = $this->skinManager->getSkins();
            
            if (!isset($availableSkins[$skinName])) {
                return $this->json(['error' => 'Invalid skin selected'], 400);
            }
            
            // Update user's skin preference in database
            $this->updateUserSkin($userId, $skinName);
            
            return $this->json([
                'success' => true,
                'message' => "Skin updated to $skinName successfully",
                'activeSkin' => $skinName,
                'userId' => $userId
            ]);
            
        } catch (\Throwable $e) {
            error_log("SettingsController::updateSkin - Exception: " . $e->getMessage());
            error_log("SettingsController::updateSkin - Stack trace: " . $e->getTraceAsString());
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
        $userActiveSkin = $userSettings['skin'] ?? 'Bismillah';
        
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
        $availableSkins = $skinManager->getSkins();
        
        if (!isset($availableSkins[$skinName])) {
            return $this->json(['error' => 'Skin not found'], 404);
        }

        $skin = $availableSkins[$skinName];
        
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
            $stmt = $this->db->prepare("
                SELECT settings FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            if ($result) {
                return json_decode($result['settings'], true) ?? [];
            }
            
            return [];
        } catch (\Throwable $e) {
            error_log("SettingsController::getUserSettings - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user's skin preference in database
     */
    private function updateUserSkin(int $userId, string $skinName): bool
    {
        try {
            // Get current settings
            $currentSettings = $this->getUserSettings($userId);
            
            // Update skin setting
            $currentSettings['skin'] = $skinName;
            $currentSettings['updated_at'] = date('Y-m-d H:i:s');
            
            // Insert or update user settings
            $stmt = $this->db->prepare("
                INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                settings = VALUES(settings), 
                updated_at = VALUES(updated_at)
            ");
            
            $settingsJson = json_encode($currentSettings);
            $result = $stmt->execute([$userId, $settingsJson]);
            
            error_log("SettingsController::updateUserSkin - Updated skin for user $userId to $skinName");
            
            return $result;
        } catch (\Throwable $e) {
            error_log("SettingsController::updateUserSkin - Error: " . $e->getMessage());
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