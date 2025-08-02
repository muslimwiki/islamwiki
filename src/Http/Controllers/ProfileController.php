<?php
declare(strict_types=1);

/**
 * Profile Controller
 * 
 * Handles user profile display and management.
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

class ProfileController extends Controller
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
     * Display the user's profile page
     */
    public function show(): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to view your profile.');
        }

        $userId = $this->session->getUserId();
        
        // Get user data
        $user = null;
        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
        } catch (\Exception $e) {
            // User not found, continue with null user
        }
        
        // Get user settings
        $userSettings = $this->getUserSettings($userId);
        
        // Get user statistics
        $userStats = $this->getUserStatistics($userId);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($userId);
        
        // Load LocalSettings.php to get active skin
        $localSettingsPath = __DIR__ . '/../../../LocalSettings.php';
        if (file_exists($localSettingsPath)) {
            require_once $localSettingsPath;
        }
        
        global $wgActiveSkin;
        $activeSkinName = $wgActiveSkin ?? 'Bismillah';
        
        return $this->view('profile/index', [
            'title' => 'Profile - IslamWiki',
            'user' => $user,
            'userSettings' => $userSettings,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'activeSkin' => $activeSkinName
        ], 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Update user profile
     */
    public function update(): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(401, 'Authentication Required', 'You need to be logged in to update your profile.');
        }

        $userId = $this->session->getUserId();
        
        // Get request data
        $requestData = $this->getRequestData();
        
        try {
            // Update user profile
            $success = $this->updateUserProfile($userId, $requestData);
            
            if ($success) {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]));
            } else {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ]));
            }
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'An error occurred while updating profile'
            ]));
        }
    }

    /**
     * Get user settings from database
     */
    private function getUserSettings(int $userId): array
    {
        try {
            $settings = $this->db->select(
                'SELECT * FROM user_settings WHERE user_id = ?',
                [$userId]
            );
            
            if (!empty($settings)) {
                return $settings[0];
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet
        }
        
        // Return default settings
        return [
            'skin' => 'Bismillah',
            'theme' => 'light',
            'language' => 'en',
            'timezone' => 'UTC',
            'notifications' => 'daily',
            'privacy_level' => 'public'
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(int $userId): array
    {
        try {
            // Get page contributions
            $pageContributions = $this->db->select(
                'SELECT COUNT(*) as count FROM pages WHERE created_by = ?',
                [$userId]
            );
            
            // Get recent edits
            $recentEdits = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                [$userId]
            );
            
            // Get watchlist count
            $watchlistCount = $this->db->select(
                'SELECT COUNT(*) as count FROM user_watchlist WHERE user_id = ?',
                [$userId]
            );
            
            return [
                'total_pages' => $pageContributions[0]['count'] ?? 0,
                'recent_edits' => $recentEdits[0]['count'] ?? 0,
                'watchlist_items' => $watchlistCount[0]['count'] ?? 0,
                'member_since' => date('Y-m-d', strtotime('-6 months')), // Mock data
                'last_active' => date('Y-m-d H:i:s', strtotime('-2 hours')) // Mock data
            ];
        } catch (\Exception $e) {
            return [
                'total_pages' => 0,
                'recent_edits' => 0,
                'watchlist_items' => 0,
                'member_since' => date('Y-m-d'),
                'last_active' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(int $userId): array
    {
        try {
            // Get recent page edits
            $recentEdits = $this->db->select(
                'SELECT ph.*, p.title, p.slug 
                 FROM page_history ph 
                 JOIN pages p ON ph.page_id = p.id 
                 WHERE ph.user_id = ? 
                 ORDER BY ph.created_at DESC 
                 LIMIT 10',
                [$userId]
            );
            
            return $recentEdits;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Update user profile
     */
    private function updateUserProfile(int $userId, array $data): bool
    {
        try {
            // Update user table
            $this->db->update(
                'users',
                [
                    'display_name' => $data['display_name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'bio' => $data['bio'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                ['id' => $userId]
            );
            
            // Update user settings
            $this->updateUserSettings($userId, $data);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update user settings
     */
    private function updateUserSettings(int $userId, array $data): void
    {
        try {
            // Check if settings exist
            $existingSettings = $this->db->select(
                'SELECT * FROM user_settings WHERE user_id = ?',
                [$userId]
            );
            
            $settingsData = [
                'user_id' => $userId,
                'skin' => $data['skin'] ?? 'Bismillah',
                'theme' => $data['theme'] ?? 'light',
                'language' => $data['language'] ?? 'en',
                'timezone' => $data['timezone'] ?? 'UTC',
                'notifications' => $data['notifications'] ?? 'daily',
                'privacy_level' => $data['privacy_level'] ?? 'public',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (!empty($existingSettings)) {
                // Update existing settings
                $this->db->update(
                    'user_settings',
                    $settingsData,
                    ['user_id' => $userId]
                );
            } else {
                // Insert new settings
                $settingsData['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('user_settings', $settingsData);
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet, ignore
        }
    }

    /**
     * Get request data
     */
    private function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        return $data;
    }

    /**
     * Render error page
     */
    private function renderErrorPage(int $statusCode, string $title, string $message): Response
    {
        return new Response(
            $statusCode,
            ['Content-Type' => 'text/html'],
            $this->view('errors/401', [
                'title' => $title,
                'message' => $message
            ])->getBody()
        );
    }
}
