<?php

declare(strict_types=1);

namespace IslamWiki\Core\Auth;

use IslamWiki\Core\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use AuthService;\Security
use SecurityUserService;\Security
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;

/**
 * User Settings Controller
 * 
 * Handles user personal settings, preferences, and skin selection.
 * 
 * @package IslamWiki\Core\Auth
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class UserSettingsController extends Controller
{
    private AuthService $authService;
    private SecurityUserService $userService;
    private SkinManager $skinManager;

    public function __construct(
        AuthService $authService,
        SecurityUserService $userService,
        SkinManager $skinManager
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->skinManager = $skinManager;
    }

    /**
     * Display user settings page
     */
    public function index(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $availableSkins = $this->skinManager->getAvailableSkins();
        $userPreferences = $this->userService->getUserPreferences($user->id);

        return $this->view('user.settings.index', [
            'page_title' => 'User Settings - IslamWiki',
            'user' => $user,
            'available_skins' => $availableSkins,
            'user_preferences' => $userPreferences,
            'active_tab' => 'general'
        ]);
    }

    /**
     * Display profile settings
     */
    public function profile(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();

        return $this->view('user.settings.profile', [
            'page_title' => 'Profile Settings - IslamWiki',
            'user' => $user,
            'active_tab' => 'profile'
        ]);
    }

    /**
     * Display appearance settings
     */
    public function appearance(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $availableSkins = $this->skinManager->getAvailableSkins();
        $userPreferences = $this->userService->getUserPreferences($user->id);

        return $this->view('user.settings.appearance', [
            'page_title' => 'Appearance Settings - IslamWiki',
            'user' => $user,
            'available_skins' => $availableSkins,
            'user_preferences' => $userPreferences,
            'active_tab' => 'appearance'
        ]);
    }

    /**
     * Display notification settings
     */
    public function notifications(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $notificationSettings = $this->userService->getNotificationSettings($user->id);

        return $this->view('user.settings.notifications', [
            'page_title' => 'Notification Settings - IslamWiki',
            'user' => $user,
            'notification_settings' => $notificationSettings,
            'active_tab' => 'notifications'
        ]);
    }

    /**
     * Display privacy settings
     */
    public function privacy(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $privacySettings = $this->userService->getPrivacySettings($user->id);

        return $this->view('user.settings.privacy', [
            'page_title' => 'Privacy Settings - IslamWiki',
            'user' => $user,
            'privacy_settings' => $privacySettings,
            'active_tab' => 'privacy'
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $preferences = $request->input('preferences');

        if (empty($preferences)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No preferences provided'], 400);
        }

        $updated = $this->userService->updateUserPreferences($user->id, $preferences);

        if ($updated) {
            return $this->jsonResponse(['success' => true, 'message' => 'Preferences updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update preferences'], 500);
        }
    }

    /**
     * Update user skin preference
     */
    public function updateSkin(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $skinName = $request->input('skin_name');

        if (empty($skinName)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Skin name is required'], 400);
        }

        // Check if skin is available
        $availableSkins = $this->skinManager->getAvailableSkins();
        $skinExists = false;
        foreach ($availableSkins as $skin) {
            if ($skin['name'] === $skinName) {
                $skinExists = true;
                break;
            }
        }

        if (!$skinExists) {
            return $this->jsonResponse(['success' => false, 'message' => 'Selected skin is not available'], 400);
        }

        // Update user skin preference
        $updated = $this->userService->updateUserSkin($user->id, $skinName);

        if ($updated) {
            return $this->jsonResponse(['success' => true, 'message' => 'Skin preference updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update skin preference'], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $notificationSettings = $request->input('notifications');

        if (empty($notificationSettings)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No notification settings provided'], 400);
        }

        $updated = $this->userService->updateNotificationSettings($user->id, $notificationSettings);

        if ($updated) {
            return $this->jsonResponse(['success' => true, 'message' => 'Notification settings updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update notification settings'], 500);
        }
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacy(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $privacySettings = $request->input('privacy');

        if (empty($privacySettings)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No privacy settings provided'], 400);
        }

        $updated = $this->userService->updatePrivacySettings($user->id, $privacySettings);

        if ($updated) {
            return $this->jsonResponse(['success' => true, 'message' => 'Privacy settings updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update privacy settings'], 500);
        }
    }

    /**
     * Export user data
     */
    public function exportData(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $userData = $this->userService->exportUserData($user->id);

        $filename = 'islamwiki_user_data_' . $user->username . '_' . date('Y-m-d') . '.json';
        
        return $this->jsonResponse($userData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->authService->getCurrentUser();
        $password = $request->input('password');

        if (empty($password)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Password is required to delete account'], 400);
        }

        // Verify password
        if (!$this->authService->verifyPassword($user->username, $password)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Incorrect password'], 400);
        }

        // Delete account
        $deleted = $this->userService->deleteUserAccount($user->id);

        if ($deleted) {
            // Logout user
            $this->authService->logout();
            
            return $this->jsonResponse(['success' => true, 'message' => 'Account deleted successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete account'], 500);
        }
    }
} 