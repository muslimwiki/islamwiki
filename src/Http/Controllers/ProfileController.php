<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function index(Request $request): Response
    {
        $user = $this->getUserOrFail($request);
        
        return $this->view('profile.show', [
            'title' => 'My Profile - IslamWiki',
            'user' => $user,
            'activeTab' => 'profile',
        ]);
    }

    /**
     * Show the edit profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $this->getUserOrFail($request);
        
        return $this->view('profile/edit', [
            'title' => 'Edit Profile - IslamWiki',
            'user' => $user,
            'activeTab' => 'profile',
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): Response
    {
        $user = $this->getUserOrFail($request);
        $data = $request->getParsedBody();
        
        // Validate input
        $errors = $this->validateProfileUpdate($user, $data);
        
        if (!empty($errors)) {
            $request->getAttribute('session')->setFlash('errors', $errors);
            return $this->redirect(route('profile.show'));
        }
        
        // Update profile
        $user->update([
            'display_name' => $data['display_name'] ?? $user->display_name,
            'email' => $data['email'] ?? $user->email,
            'bio' => $data['bio'] ?? $user->bio,
        ]);
        
        $request->getAttribute('session')->setFlash('success', 'Profile updated successfully!');
        return $this->redirect(route('profile.show'));
    }
    
    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): Response
    {
        $user = $this->getUserOrFail($request);
        $data = $request->getParsedBody();
        
        // Validate input
        $errors = $this->validatePasswordUpdate($user, $data);
        
        if (!empty($errors)) {
            $request->getAttribute('session')->setFlash('password_errors', $errors);
            return $this->redirect(route('profile.show') . '#password');
        }
        
        // Update password
        $user->update([
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
        ]);
        
        // Invalidate all other sessions
        $this->invalidateOtherSessions($request, $user);
        
        $request->getAttribute('session')->setFlash('success', 'Password updated successfully!');
        return $this->redirect(route('profile.show') . '#password');
    }
    
    /**
     * Validate the profile update data.
     */
    protected function validateProfileUpdate(User $user, array $data): array
    {
        $errors = [];
        
        // Display name validation
        if (empty($data['display_name'])) {
            $errors['display_name'] = 'Display name is required.';
        } elseif (strlen($data['display_name']) < 3) {
            $errors['display_name'] = 'Display name must be at least 3 characters.';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($data['email'] !== $user->email) {
            // Check if email is already taken by another user
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser && $existingUser->id !== $user->id) {
                $errors['email'] = 'This email is already in use by another account.';
            }
        }
        
        // Bio validation (optional)
        if (isset($data['bio']) && strlen($data['bio']) > 500) {
            $errors['bio'] = 'Bio must not exceed 500 characters.';
        }
        
        return $errors;
    }
    
    /**
     * Validate the password update data.
     */
    protected function validatePasswordUpdate(User $user, array $data): array
    {
        $errors = [];
        
        // Current password validation
        if (empty($data['current_password'])) {
            $errors['current_password'] = 'Current password is required.';
        } elseif (!password_verify($data['current_password'], $user->password)) {
            $errors['current_password'] = 'Current password is incorrect.';
        }
        
        // New password validation
        if (empty($data['new_password'])) {
            $errors['new_password'] = 'New password is required.';
        } elseif (strlen($data['new_password']) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif ($data['new_password'] === $data['current_password']) {
            $errors['new_password'] = 'New password must be different from current password.';
        }
        
        // Password confirmation
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            $errors['new_password_confirmation'] = 'Passwords do not match.';
        }
        
        return $errors;
    }
    
    /**
     * Invalidate all other sessions for the user.
     */
    protected function invalidateOtherSessions(Request $request, User $user): void
    {
        // Get the current session ID
        $currentSessionId = $request->getAttribute('session')->getId();
        
        // TODO: Implement session invalidation logic
        // This would typically involve:
        // 1. Getting all active sessions for the user from your session store
        // 2. Deleting all sessions except the current one
        // 3. Updating the user's remember token to invalidate "remember me" cookies
        
        // For now, we'll just update the user's remember token
        $user->update([
            'remember_token' => null,
        ]);
    }
    
    /**
     * Get the authenticated user or fail.
     */
    protected function getUserOrFail(Request $request): User
    {
        $user = $this->user($request);
        if (!$user) {
            throw new HttpForbiddenException($request, 'You must be logged in to view this page.');
        }
        
        $userModel = User::find($user['id']);
        if (!$userModel) {
            throw new HttpNotFoundException($request, 'User not found.');
        }
        
        return $userModel;
    }
}
