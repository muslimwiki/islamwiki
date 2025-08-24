<?php

declare(strict_types=1);

namespace IslamWiki\Core\Auth;

use IslamWiki\Core\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use AuthService;\Security
use SecurityUserService;\Security
use SecurityRoleService;\Security
use SessionService;\Session

/**
 * Security Authentication Controller
 * 
 * Handles user authentication, login, logout, and session management.
 * 
 * @package IslamWiki\Core\Auth
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AuthenticationController extends Controller
{
    private AuthService $authService;
    private SecurityUserService $userService;
    private SecurityRoleService $roleService;
    private SessionService $sessionService;

    public function __construct(
        AuthService $authService,
        SecurityUserService $userService,
        SecurityRoleService $roleService,
        SessionService $sessionService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->sessionService = $sessionService;
    }

    /**
     * Display login page
     */
    public function showLogin(): Response
    {
        // Redirect if already logged in
        if ($this->authService->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.login', [
            'page_title' => 'Login - IslamWiki',
            'error' => null
        ]);
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request): Response
    {
        $credentials = $request->only(['username', 'password']);
        
        // Validate input
        $validation = $this->validateLoginCredentials($credentials);
        if (!$validation['valid']) {
            return $this->view('auth.login', [
                'page_title' => 'Login - IslamWiki',
                'error' => $validation['message'],
                'username' => $credentials['username'] ?? ''
            ]);
        }

        // Attempt authentication
        $authResult = $this->authService->authenticate(
            $credentials['username'],
            $credentials['password']
        );

        if ($authResult['success']) {
            // Create session
            $this->sessionService->createSession($authResult['user']);
            
            // Redirect to intended page or dashboard
            $redirectTo = $request->get('redirect') ?: '/dashboard';
            return $this->redirect($redirectTo);
        } else {
            return $this->view('auth.login', [
                'page_title' => 'Login - IslamWiki',
                'error' => $authResult['message'],
                'username' => $credentials['username'] ?? ''
            ]);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(): Response
    {
        $this->sessionService->destroySession();
        $this->authService->logout();
        
        return $this->redirect('/login')->with('message', 'You have been successfully logged out.');
    }

    /**
     * Display user registration page
     */
    public function showRegister(): Response
    {
        // Redirect if already logged in
        if ($this->authService->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.register', [
            'page_title' => 'Register - IslamWiki',
            'error' => null
        ]);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request): Response
    {
        $userData = $request->only(['username', 'email', 'password', 'password_confirmation', 'first_name', 'last_name']);
        
        // Validate registration data
        $validation = $this->validateRegistrationData($userData);
        if (!$validation['valid']) {
            return $this->view('auth.register', [
                'page_title' => 'Register - IslamWiki',
                'error' => $validation['message'],
                'user_data' => $userData
            ]);
        }

        // Check if user already exists
        if ($this->userService->userExists($userData['username'], $userData['email'])) {
            return $this->view('auth.register', [
                'page_title' => 'Register - IslamWiki',
                'error' => 'Username or email already exists.',
                'user_data' => $userData
            ]);
        }

        // Create user
        $user = $this->userService->createUser($userData);
        
        if ($user) {
            // Auto-login after registration
            $this->authService->login($user);
            $this->sessionService->createSession($user);
            
            return $this->redirect('/dashboard')->with('message', 'Registration successful! Welcome to IslamWiki.');
        } else {
            return $this->view('auth.register', [
                'page_title' => 'Register - IslamWiki',
                'error' => 'Failed to create user account. Please try again.',
                'user_data' => $userData
            ]);
        }
    }

    /**
     * Display user profile page
     */
    public function showProfile(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        
        return $this->view('auth.profile', [
            'page_title' => 'Profile - IslamWiki',
            'user' => $user,
            'roles' => $this->roleService->getUserRoles($user->id)
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $profileData = $request->only(['first_name', 'last_name', 'email', 'bio']);
        
        // Validate profile data
        $validation = $this->validateProfileData($profileData);
        if (!$validation['valid']) {
            return $this->view('auth.profile', [
                'page_title' => 'Profile - IslamWiki',
                'user' => $user,
                'error' => $validation['message'],
                'roles' => $this->roleService->getUserRoles($user->id)
            ]);
        }

        // Update profile
        $updated = $this->userService->updateProfile($user->id, $profileData);
        
        if ($updated) {
            return $this->redirect('/profile')->with('message', 'Profile updated successfully.');
        } else {
            return $this->view('auth.profile', [
                'page_title' => 'Profile - IslamWiki',
                'user' => $user,
                'error' => 'Failed to update profile. Please try again.',
                'roles' => $this->roleService->getUserRoles($user->id)
            ]);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();
        $passwordData = $request->only(['current_password', 'new_password', 'new_password_confirmation']);
        
        // Validate password data
        $validation = $this->validatePasswordData($passwordData);
        if (!$validation['valid']) {
            return $this->view('auth.profile', [
                'page_title' => 'Profile - IslamWiki',
                'user' => $user,
                'error' => $validation['message'],
                'roles' => $this->roleService->getUserRoles($user->id)
            ]);
        }

        // Verify current password
        if (!$this->authService->verifyPassword($user->username, $passwordData['current_password'])) {
            return $this->view('auth.profile', [
                'page_title' => 'Profile - IslamWiki',
                'user' => $user,
                'error' => 'Current password is incorrect.',
                'roles' => $this->roleService->getUserRoles($user->id)
            ]);
        }

        // Change password
        $changed = $this->userService->changePassword($user->id, $passwordData['new_password']);
        
        if ($changed) {
            return $this->redirect('/profile')->with('message', 'Password changed successfully.');
        } else {
            return $this->view('auth.profile', [
                'page_title' => 'Profile - IslamWiki',
                'user' => $user,
                'error' => 'Failed to change password. Please try again.',
                'roles' => $this->roleService->getUserRoles($user->id)
            ]);
        }
    }

    /**
     * Validate login credentials
     */
    private function validateLoginCredentials(array $credentials): array
    {
        if (empty($credentials['username'])) {
            return ['valid' => false, 'message' => 'Username is required.'];
        }
        
        if (empty($credentials['password'])) {
            return ['valid' => false, 'message' => 'Password is required.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $userData): array
    {
        if (empty($userData['username']) || strlen($userData['username']) < 3) {
            return ['valid' => false, 'message' => 'Username must be at least 3 characters long.'];
        }
        
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address.'];
        }
        
        if (empty($userData['password']) || strlen($userData['password']) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
        }
        
        if ($userData['password'] !== $userData['password_confirmation']) {
            return ['valid' => false, 'message' => 'Password confirmation does not match.'];
        }
        
        if (empty($userData['first_name'])) {
            return ['valid' => false, 'message' => 'First name is required.'];
        }
        
        if (empty($userData['last_name'])) {
            return ['valid' => false, 'message' => 'Last name is required.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate profile data
     */
    private function validateProfileData(array $profileData): array
    {
        if (empty($profileData['first_name'])) {
            return ['valid' => false, 'message' => 'First name is required.'];
        }
        
        if (empty($profileData['last_name'])) {
            return ['valid' => false, 'message' => 'Last name is required.'];
        }
        
        if (empty($profileData['email']) || !filter_var($profileData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate password data
     */
    private function validatePasswordData(array $passwordData): array
    {
        if (empty($passwordData['current_password'])) {
            return ['valid' => false, 'message' => 'Current password is required.'];
        }
        
        if (empty($passwordData['new_password']) || strlen($passwordData['new_password']) < 8) {
            return ['valid' => false, 'message' => 'New password must be at least 8 characters long.'];
        }
        
        if ($passwordData['new_password'] !== $passwordData['new_password_confirmation']) {
            return ['valid' => false, 'message' => 'Password confirmation does not match.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }
} 