<?php
declare(strict_types=1);

/**
 * Enhanced Authentication Controller
 * 
 * Handles user authentication including login, register, and logout.
 * 
 * @package IslamWiki\Http\Controllers\Auth
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Core\Auth\Aman;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Asas;

class AuthController
{
    private Aman $auth;
    private Connection $db;
    private Asas $container;
    
    /**
     * Create a new authentication controller instance.
     */
    public function __construct(Connection $db, Asas $container)
    {
        $this->db = $db;
        $this->container = $container;
        $this->auth = new Aman(
            $container->get('session'),
            $db
        );
    }
    
    /**
     * Show the login form.
     */
    public function showLogin(Request $request): Response
    {
        // If user is already logged in, redirect to dashboard
        if ($this->auth->check()) {
            return $this->redirect('/dashboard');
        }
        
        $error = $request->getQueryParam('error');
        $redirect = $request->getQueryParam('redirect', '/dashboard');
        
        // Use the proper Twig renderer
        $view = $this->container->get('view');
        $html = $view->render('auth/login.twig', [
            'title' => 'Login - IslamWiki',
            'error' => $error,
            'redirect' => $redirect,
            'auth' => $this->auth,
            'csrf_token' => $this->container->get('session')->get('csrf_token', ''),
            'user' => null
        ]);
        
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }
    
    /**
     * Handle user login.
     */
    public function login(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $username = trim($data['username'] ?? '');
            $password = $data['password'] ?? '';
            $redirect = $data['redirect'] ?? '/dashboard';
            
            // Validate input
            if (empty($username) || empty($password)) {
                return $this->redirect('/login?error=Please provide both username and password');
            }
            
            // Attempt authentication
            if ($this->auth->attempt($username, $password)) {
                // Success - redirect to intended page
                return $this->redirect($redirect);
            } else {
                // Failed login
                return $this->redirect('/login?error=Invalid username or password');
            }
            
        } catch (\Exception $e) {
            error_log('AuthController::login error: ' . $e->getMessage());
            return $this->redirect('/login?error=An error occurred during login');
        }
    }
    
    /**
     * Show the registration form.
     */
    public function showRegister(Request $request): Response
    {
        // If user is already logged in, redirect to dashboard
        if ($this->auth->check()) {
            return $this->redirect('/dashboard');
        }
        
        $error = $request->getQueryParam('error');
        
        // Use the proper Twig renderer
        $view = $this->container->get('view');
        $html = $view->render('auth/register.twig', [
            'title' => 'Register - IslamWiki',
            'error' => $error,
            'auth' => $this->auth,
            'csrf_token' => $this->container->get('session')->get('csrf_token', ''),
            'user' => null
        ]);
        
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }
    
    /**
     * Handle user registration.
     */
    public function register(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validate required fields
            $required = ['username', 'email', 'password', 'password_confirmation', 'display_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->redirect('/register?error=Please fill in all required fields');
                }
            }
            
            // Validate password confirmation
            if ($data['password'] !== $data['password_confirmation']) {
                return $this->redirect('/register?error=Passwords do not match');
            }
            
            // Validate password strength
            if (strlen($data['password']) < 8) {
                return $this->redirect('/register?error=Password must be at least 8 characters long');
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->redirect('/register?error=Please enter a valid email address');
            }
            
            // Validate username format
            if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $data['username'])) {
                return $this->redirect('/register?error=Username must be 3-20 characters and contain only letters, numbers, underscores, and hyphens');
            }
            
            // Prepare user data
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'display_name' => $data['display_name'],
                'bio' => $data['bio'] ?? '',
            ];
            
            // Register user
            $userId = $this->auth->register($userData);
            
            if ($userId) {
                // Success - redirect to dashboard
                return $this->redirect('/dashboard?welcome=1');
            } else {
                return $this->redirect('/register?error=Registration failed. Please try again.');
            }
            
        } catch (\InvalidArgumentException $e) {
            return $this->redirect('/register?error=' . urlencode($e->getMessage()));
        } catch (\Exception $e) {
            error_log('AuthController::register error: ' . $e->getMessage());
            return $this->redirect('/register?error=An error occurred during registration');
        }
    }
    
    /**
     * Handle user logout.
     */
    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return $this->redirect('/?message=You have been logged out successfully');
    }
    
    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(Request $request): Response
    {
        $error = $request->getQueryParam('error');
        $success = $request->getQueryParam('success');
        
        return $this->view('auth/forgot-password', [
            'title' => 'Forgot Password - IslamWiki',
            'error' => $error,
            'success' => $success,
            'auth' => $this->auth
        ]);
    }
    
    /**
     * Handle forgot password request.
     */
    public function forgotPassword(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $email = trim($data['email'] ?? '');
            
            if (empty($email)) {
                return $this->redirect('/forgot-password?error=Please enter your email address');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->redirect('/forgot-password?error=Please enter a valid email address');
            }
            
            // Generate reset token
            $token = $this->auth->generatePasswordResetToken($email);
            
            if ($token) {
                // In a real application, you would send an email here
                // For now, we'll just show a success message
                return $this->redirect('/forgot-password?success=If an account with that email exists, a password reset link has been sent');
            } else {
                // Don't reveal if email exists or not
                return $this->redirect('/forgot-password?success=If an account with that email exists, a password reset link has been sent');
            }
            
        } catch (\Exception $e) {
            error_log('AuthController::forgotPassword error: ' . $e->getMessage());
            return $this->redirect('/forgot-password?error=An error occurred. Please try again.');
        }
    }
    
    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request): Response
    {
        $token = $request->getQueryParam('token');
        $error = $request->getQueryParam('error');
        
        if (empty($token)) {
            return $this->redirect('/forgot-password?error=Invalid reset link');
        }
        
        return $this->view('auth/reset-password', [
            'title' => 'Reset Password - IslamWiki',
            'token' => $token,
            'error' => $error,
            'auth' => $this->auth
        ]);
    }
    
    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $token = $data['token'] ?? '';
            $password = $data['password'] ?? '';
            $password_confirmation = $data['password_confirmation'] ?? '';
            
            if (empty($token) || empty($password) || empty($password_confirmation)) {
                return $this->redirect('/reset-password?token=' . urlencode($token) . '&error=Please fill in all fields');
            }
            
            if ($password !== $password_confirmation) {
                return $this->redirect('/reset-password?token=' . urlencode($token) . '&error=Passwords do not match');
            }
            
            if (strlen($password) < 8) {
                return $this->redirect('/reset-password?token=' . urlencode($token) . '&error=Password must be at least 8 characters long');
            }
            
            // Reset password
            if ($this->auth->resetPassword($token, $password)) {
                return $this->redirect('/login?success=Your password has been reset successfully');
            } else {
                return $this->redirect('/reset-password?token=' . urlencode($token) . '&error=Invalid or expired reset link');
            }
            
        } catch (\Exception $e) {
            error_log('AuthController::resetPassword error: ' . $e->getMessage());
            return $this->redirect('/reset-password?token=' . urlencode($token) . '&error=An error occurred. Please try again.');
        }
    }
    
    /**
     * Show user profile.
     */
    public function profile(Request $request): Response
    {
        if (!$this->auth->check()) {
            return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath()));
        }
        
        $user = $this->auth->user();
        
        return $this->view('auth/profile', [
            'title' => 'Profile - IslamWiki',
            'user' => $user,
            'auth' => $this->auth
        ]);
    }
    
    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): Response
    {
        if (!$this->auth->check()) {
            return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath()));
        }
        
        try {
            $data = $request->getParsedBody();
            $userId = $this->auth->id();
            
            // Validate email format
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->redirect('/profile?error=Please enter a valid email address');
            }
            
            // Update profile
            if ($this->auth->updateProfile($userId, $data)) {
                return $this->redirect('/profile?success=Profile updated successfully');
            } else {
                return $this->redirect('/profile?error=Failed to update profile');
            }
            
        } catch (\Exception $e) {
            error_log('AuthController::updateProfile error: ' . $e->getMessage());
            return $this->redirect('/profile?error=An error occurred while updating profile');
        }
    }
    
    /**
     * Change password.
     */
    public function changePassword(Request $request): Response
    {
        if (!$this->auth->check()) {
            return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath()));
        }
        
        try {
            $data = $request->getParsedBody();
            $userId = $this->auth->id();
            
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $newPasswordConfirmation = $data['new_password_confirmation'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($newPasswordConfirmation)) {
                return $this->redirect('/profile?error=Please fill in all password fields');
            }
            
            if ($newPassword !== $newPasswordConfirmation) {
                return $this->redirect('/profile?error=New passwords do not match');
            }
            
            if (strlen($newPassword) < 8) {
                return $this->redirect('/profile?error=New password must be at least 8 characters long');
            }
            
            // Change password
            if ($this->auth->changePassword($userId, $currentPassword, $newPassword)) {
                return $this->redirect('/profile?success=Password changed successfully');
            } else {
                return $this->redirect('/profile?error=Current password is incorrect');
            }
            
        } catch (\Exception $e) {
            error_log('AuthController::changePassword error: ' . $e->getMessage());
            return $this->redirect('/profile?error=An error occurred while changing password');
        }
    }
    
    /**
     * Helper method to render a view.
     */
    private function view(string $template, array $data = []): Response
    {
        // This would normally use a view renderer
        // For now, we'll return a simple response
        $html = $this->renderView($template, $data);
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }
    
    /**
     * Helper method to redirect.
     */
    private function redirect(string $url): Response
    {
        return new Response(302, ['Location' => $url], '');
    }
    
    /**
     * Simple view renderer for now.
     */
    private function renderView(string $template, array $data): string
    {
        // This is a simplified view renderer
        // In a real application, you would use Twig or similar
        $templatePath = __DIR__ . '/../../../resources/views/' . $template . '.twig';
        
        if (file_exists($templatePath)) {
            $content = file_get_contents($templatePath);
            
            // Simple variable replacement for testing
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $content = str_replace('{{ ' . $key . ' }}', $value, $content);
                }
            }
            
            return $content;
        }
        
        // Fallback HTML
        return $this->renderFallbackView($template, $data);
    }
    
    /**
     * Render a fallback view when template doesn't exist.
     */
    private function renderFallbackView(string $template, array $data): string
    {
        $title = $data['title'] ?? 'IslamWiki';
        $error = $data['error'] ?? '';
        $success = $data['success'] ?? '';
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, sans-serif; margin: 0; padding: 20px; background: #f8fafc; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .title { text-align: center; margin-bottom: 2rem; color: #1f2937; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
        .form-input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; }
        .form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn { width: 100%; padding: 0.75rem; background: #4f46e5; color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; }
        .btn:hover { background: #4338ca; }
        .error { color: #dc2626; margin-bottom: 1rem; padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; }
        .success { color: #059669; margin-bottom: 1rem; padding: 0.75rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; }
        .links { text-align: center; margin-top: 1rem; }
        .links a { color: #4f46e5; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">' . htmlspecialchars($title) . '</h1>';
        
        if ($error) {
            $html .= '<div class="error">' . htmlspecialchars($error) . '</div>';
        }
        
        if ($success) {
            $html .= '<div class="success">' . htmlspecialchars($success) . '</div>';
        }
        
        if ($template === 'auth/login') {
            $html .= $this->renderLoginForm($data);
        } elseif ($template === 'auth/register') {
            $html .= $this->renderRegisterForm($data);
        } elseif ($template === 'auth/forgot-password') {
            $html .= $this->renderForgotPasswordForm($data);
        } elseif ($template === 'auth/reset-password') {
            $html .= $this->renderResetPasswordForm($data);
        } elseif ($template === 'auth/profile') {
            $html .= $this->renderProfileForm($data);
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Render login form.
     */
    private function renderLoginForm(array $data): string
    {
        $redirect = $data['redirect'] ?? '/dashboard';
        
        return '
        <form method="POST" action="/login">
            <input type="hidden" name="redirect" value="' . htmlspecialchars($redirect) . '">
            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <div class="links">
            <a href="/register">Create Account</a> | 
            <a href="/forgot-password">Forgot Password?</a>
        </div>';
    }
    
    /**
     * Render register form.
     */
    private function renderRegisterForm(array $data): string
    {
        return '
        <form method="POST" action="/register">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Display Name</label>
                <input type="text" name="display_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
            <button type="submit" class="btn">Create Account</button>
        </form>
        <div class="links">
            <a href="/login">Already have an account? Sign In</a>
        </div>';
    }
    
    /**
     * Render forgot password form.
     */
    private function renderForgotPasswordForm(array $data): string
    {
        return '
        <form method="POST" action="/forgot-password">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        <div class="links">
            <a href="/login">Back to Login</a>
        </div>';
    }
    
    /**
     * Render reset password form.
     */
    private function renderResetPasswordForm(array $data): string
    {
        $token = $data['token'] ?? '';
        
        return '
        <form method="POST" action="/reset-password">
            <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
        <div class="links">
            <a href="/login">Back to Login</a>
        </div>';
    }
    
    /**
     * Render profile form.
     */
    private function renderProfileForm(array $data): string
    {
        $user = $data['user'] ?? [];
        
        return '
        <form method="POST" action="/profile">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" value="' . htmlspecialchars($user['username'] ?? '') . '" class="form-input" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="' . htmlspecialchars($user['email'] ?? '') . '" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Display Name</label>
                <input type="text" name="display_name" value="' . htmlspecialchars($user['display_name'] ?? '') . '" class="form-input">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
        <div class="links">
            <a href="/dashboard">Back to Dashboard</a>
        </div>';
    }
} 