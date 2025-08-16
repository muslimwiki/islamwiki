<?php

/**
 * Profile Controller
 *
 * Handles user profile display and management.
 * Supports both private profiles (for logged-in users) and public profiles (viewable by anyone).
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.34
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Skins\SkinManager;

class ProfileController extends Controller
{
    private WisalSession $session;

    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->session = $container->get('session');
    }

    /**
     * Display the current user's profile page (private)
     */
    public function show(): Response
    {
        // Check if user is logged in
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(
                401,
                'Authentication Required',
                'You need to be logged in to view your profile.'
            );
        }

        $userId = $this->session->getUserId();
        return $this->showUserProfile($userId, true);
    }

    /**
     * Display the current user's profile page (alias for show)
     */
    public function index(): Response
    {
        return $this->show();
    }

    /**
     * Display a public user profile page
     */
    public function showPublic(\IslamWiki\Core\Http\Request $request, string $username): Response
    {
        try {
            // Find user by username
            $user = $this->db->select(
                'SELECT * FROM users WHERE username = ? AND is_active = 1',
                [$username]
            );

            if (empty($user)) {
                return $this->renderErrorPage(404, 'User Not Found', 'The requested user profile could not be found.');
            }

            $userData = $user[0];
            return $this->showUserProfile($userData['id'], false, $userData);
        } catch (\Exception $e) {
            return $this->renderErrorPage(500, 'Server Error', 'An error occurred while loading the profile.');
        }
    }

    /**
     * Display user profile (private or public)
     */
    private function showUserProfile(int $userId, bool $isOwnProfile = false, ?array $userData = null): Response
    {
        // Get user data if not provided
        if ($userData === null) {
            try {
                $user = \IslamWiki\Models\User::find($userId, $this->db);
                $userData = $user ? $user->toArray() : null;
            } catch (\Exception $e) {
                $userData = null;
            }
        }

        if (!$userData) {
            return $this->renderErrorPage(404, 'User Not Found', 'The requested user profile could not be found.');
        }

        // Get user settings
        $userSettings = $this->getUserSettings($userId);

        // Get user statistics
        $userStats = $this->getUserStatistics($userId);

        // Get recent activity (only for own profile or if public)
        $recentActivity = [];
        if ($isOwnProfile || $userSettings['privacy_level'] === 'public') {
            $recentActivity = $this->getRecentActivity($userId);
        }

        // Get active skin using standardized skin manager
        // For now, use a fallback since we don't have the 'app' binding
        $activeSkinName = 'Bismillah'; // Default skin name
        
        // TODO: Once the full application system is implemented, this can be updated to:
        // $app = $this->container->get('app');
        // $activeSkinName = SkinManager::getActiveSkinNameStatic($app);

        // Determine if current user can edit this profile
        $canEdit = $isOwnProfile;
        $currentUserId = $this->session->isLoggedIn() ? $this->session->getUserId() : null;

        return $this->view('profile/index', [
            'title' => ($isOwnProfile ? 'My Profile' : $userData['display_name'] . "'s Profile") . ' - IslamWiki',
            'user' => $userData,
            'userSettings' => $userSettings,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'activeSkin' => $activeSkinName,
            'isOwnProfile' => $isOwnProfile,
            'canEdit' => $canEdit,
            'currentUserId' => $currentUserId
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
            return $this->renderErrorPage(
                401,
                'Authentication Required',
                'You need to be logged in to update your profile.'
            );
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
                'SELECT COUNT(*) as count 
                 FROM page_history 
                 WHERE user_id = ? 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
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
     * Update privacy settings
     */
    public function updatePrivacySettings(\IslamWiki\Core\Http\Request $request): Response
    {
        try {
            $userId = $this->session->getUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'message' => 'Authentication required'
                ]));
            }

            $data = json_decode($request->getBody()->getContents(), true);

            // Update privacy settings in user_settings table
            foreach ($data as $setting => $value) {
                $this->db->update(
                    'user_settings',
                    ['value' => $value],
                    ['user_id' => $userId, 'setting_key' => $setting]
                );
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Privacy settings updated successfully'
            ]));
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Failed to update privacy settings'
            ]));
        }
    }

    /**
     * Update customization settings
     */
    public function updateCustomizationSettings(\IslamWiki\Core\Http\Request $request): Response
    {
        try {
            $userId = $this->session->getUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'message' => 'Authentication required'
                ]));
            }

            $data = $request->getParsedBody();

            // Update user profile information
            $userUpdates = [];
            if (isset($data['custom_display_name'])) {
                $userUpdates['display_name'] = $data['custom_display_name'];
            }
            if (isset($data['custom_bio'])) {
                $userUpdates['bio'] = $data['custom_bio'];
            }
            if (isset($data['custom_location'])) {
                $userUpdates['location'] = $data['custom_location'];
            }
            if (isset($data['custom_website'])) {
                $userUpdates['website'] = $data['custom_website'];
            }

            if (!empty($userUpdates)) {
                $this->db->update('users', $userUpdates, ['id' => $userId]);
            }

            // Update customization settings
            $customizationSettings = [
                'custom_theme', 'custom_layout', 'custom_featured_content',
                'custom_profile_message'
            ];

            foreach ($customizationSettings as $setting) {
                if (isset($data[$setting])) {
                    $this->db->update(
                        'user_settings',
                        ['value' => $data[$setting]],
                        ['user_id' => $userId, 'setting_key' => $setting]
                    );
                }
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Customization settings updated successfully'
            ]));
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Failed to update customization settings'
            ]));
        }
    }

    /**
     * Render error page
     */
    private function renderErrorPage(int $statusCode, string $title, string $message): Response
    {
        $html = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title} - IslamWiki</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 2rem; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; 
                           border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-icon { font-size: 3rem; text-align: center; margin-bottom: 1rem; }
                h1 { color: #dc2626; margin-bottom: 1rem; }
                p { color: #6b7280; line-height: 1.6; }
                .back-link { margin-top: 2rem; }
                .back-link a { color: #667eea; text-decoration: none; }
                .back-link a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='error-icon'>⚠️</div>
                <h1>{$title}</h1>
                <p>{$message}</p>
                <div class='back-link'>
                    <a href='/'>← Back to Home</a>
                </div>
            </div>
        </body>
        </html>";

        return new Response(
            $statusCode,
            ['Content-Type' => 'text/html'],
            $html
        );
    }

    /**
     * API: Get current user profile
     */
    public function apiIndex(): Response
    {
        if (!$this->session->isLoggedIn()) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Authentication required'
            ]));
        }

        $userId = $this->session->getUserId();
        $userSettings = $this->getUserSettings($userId);
        $userStats = $this->getUserStatistics($userId);

        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
            $userData = $user ? $user->toArray() : null;
        } catch (\Exception $e) {
            $userData = null;
        }

        if (!$userData) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'User not found'
            ]));
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'user' => $userData,
            'settings' => $userSettings,
            'stats' => $userStats
        ]));
    }

    /**
     * API: Update user profile
     */
    public function apiUpdate(): Response
    {
        if (!$this->session->isLoggedIn()) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Authentication required'
            ]));
        }

        $userId = $this->session->getUserId();
        $requestData = $this->getRequestData();

        try {
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
     * API: Update user password
     */
    public function apiUpdatePassword(): Response
    {
        if (!$this->session->isLoggedIn()) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Authentication required'
            ]));
        }

        $userId = $this->session->getUserId();
        $requestData = $this->getRequestData();

        $currentPassword = $requestData['current_password'] ?? '';
        $newPassword = $requestData['new_password'] ?? '';
        $confirmPassword = $requestData['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'All password fields are required'
            ]));
        }

        if ($newPassword !== $confirmPassword) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'New passwords do not match'
            ]));
        }

        try {
            // Verify current password
            $user = \IslamWiki\Models\User::find($userId, $this->db);
            if (!$user || !password_verify($currentPassword, $user->password)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]));
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update(
                'users',
                ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')],
                ['id' => $userId]
            );

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Password updated successfully'
            ]));
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'An error occurred while updating password'
            ]));
        }
    }

    /**
     * Update user password (non-API version)
     */
    public function updatePassword(): Response
    {
        return $this->apiUpdatePassword();
    }

    /**
     * Edit profile page
     */
    public function edit(): Response
    {
        if (!$this->session->isLoggedIn()) {
            return $this->renderErrorPage(
                401,
                'Authentication Required',
                'You need to be logged in to edit your profile.'
            );
        }

        $userId = $this->session->getUserId();
        $userSettings = $this->getUserSettings($userId);

        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
            $userData = $user ? $user->toArray() : null;
        } catch (\Exception $e) {
            $userData = null;
        }

        if (!$userData) {
            return $this->renderErrorPage(404, 'User Not Found', 'The requested user profile could not be found.');
        }

        return $this->view('profile/edit', [
            'title' => 'Edit Profile - IslamWiki',
            'user' => $userData,
            'userSettings' => $userSettings
        ]);
    }
}
