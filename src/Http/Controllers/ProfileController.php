<?php

/**
 * Profile Controller
 *
 * Handles user profile display and management.
 * Supports both private profiles (for logged-in users) and public profiles (viewable by anyone).
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Profile Controller - Handles User Profile Functionality
 */
class ProfileController extends Controller
{
    /**
     * Display the current user's profile page (private)
     */
    public function show(Request $request): Response
    {
        try {
            // Check if user is logged in
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return new Response(401, [], 'Authentication Required');
            }

            $userId = $session->getUserId();
            return $this->showUserProfile($userId, true);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display the current user's profile page (alias for show)
     */
    public function index(Request $request): Response
    {
        return $this->show($request);
    }

    /**
     * Display a public user profile page
     */
    public function showPublic(Request $request, string $username): Response
    {
        try {
            // Find user by username
            $user = $this->getUserByUsername($username);

            if (!$user) {
                return new Response(404, [], 'User Not Found');
            }

            return $this->showUserProfile($user['id'], false, $user);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Edit user profile
     */
    public function edit(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return new Response(401, [], 'Authentication Required');
            }

            $userId = $session->getUserId();
            $userData = $this->getUserById($userId);

            if (!$userData) {
                return new Response(404, [], 'User Not Found');
            }

            return $this->view('profile/edit', [
                'user' => $userData,
                'title' => 'Edit Profile - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return new Response(401, [], 'Authentication Required');
            }

            $userId = $session->getUserId();
            $data = $request->getParsedBody();

            $success = $this->updateUserProfile($userId, $data);

            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                return new Response(500, [], 'Failed to update profile');
            }
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display user profile (private or public)
     */
    private function showUserProfile(int $userId, bool $isOwnProfile = false, ?array $userData = null): Response
    {
        try {
            // Get user data if not provided
            if ($userData === null) {
                $userData = $this->getUserById($userId);
            }

            if (!$userData) {
                return new Response(404, [], 'User Not Found');
            }

            // Get user settings
            $userSettings = $this->getUserSettings($userId);
            
            // Get user statistics
            $userStats = $this->getUserStatistics($userId);

            $viewData = [
                'user' => $userData,
                'settings' => $userSettings,
                'stats' => $userStats,
                'isOwnProfile' => $isOwnProfile,
                'title' => "Profile - {$userData['username']} - IslamWiki"
            ];

            return $this->view('profile/show', $viewData, 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get user by ID
     */
    private function getUserById(int $userId): ?array
    {
        try {
            $sql = "SELECT id, username, display_name, email, bio, created_at, last_login_at, is_active, is_admin 
                    FROM users WHERE id = ? AND is_active = 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user by username
     */
    private function getUserByUsername(string $username): ?array
    {
        try {
            $sql = "SELECT id, username, display_name, email, bio, created_at, last_login_at, is_active, is_admin 
                    FROM users WHERE username = ? AND is_active = 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$username]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user settings
     */
    private function getUserSettings(int $userId): array
    {
        try {
            $sql = "SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
            
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(int $userId): array
    {
        try {
            $sql = "SELECT COUNT(*) as total_pages FROM pages WHERE created_by = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            $totalPages = (int)($result['total_pages'] ?? 0);
            
            return [
                'total_pages' => $totalPages,
                'total_edits' => 0,
                'member_since' => date('Y-m-d'),
                'last_activity' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'total_pages' => 0,
                'total_edits' => 0,
                'member_since' => date('Y-m-d'),
                'last_activity' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Update user profile
     */
    private function updateUserProfile(int $userId, array $data): bool
    {
        try {
            $allowedFields = ['display_name', 'bio', 'email'];
            $updateFields = [];
            $updateValues = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $updateValues[] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $updateValues[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($updateValues);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
