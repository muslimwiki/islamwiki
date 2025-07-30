<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Models\User;
use IslamWiki\Core\Session\SessionManager;

/**
 * Authentication Controller
 * 
 * Handles user authentication including login, register, and logout.
 */
class AuthController extends Controller
{
    /**
     * @var SessionManager Session manager instance
     */
    private SessionManager $session;
    
    /**
     * Create a new authentication controller instance.
     */
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        $this->session = $container->get('session');
    }

    /**
     * Show the login form.
     */
    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login', [
            'title' => 'Login - IslamWiki',
            'error' => $request->getQueryParam('error'),
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Handle user login.
     */
    public function login(Request $request): Response
    {
        $username = $request->getPostParam('username');
        $password = $request->getPostParam('password');
        $remember = $request->getPostParam('remember') === 'on';

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->redirect('/login?error=Please provide both username and password');
        }

        // Find user by username or email
        $user = User::findByUsername($username, $this->db);
        if (!$user) {
            $user = User::findByEmail($username, $this->db);
        }

        if (!$user || !$user->verifyPassword($password)) {
            return $this->redirect('/login?error=Invalid username or password');
        }

        if (!$user->isActive()) {
            return $this->redirect('/login?error=Account is deactivated');
        }

        // Log in the user using session manager
        $this->session->login(
            $user->getAttribute('id'),
            $user->getAttribute('username'),
            $user->isAdmin()
        );

        // Record login
        $user->recordLogin();

        // Set remember token if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $user->setAttribute('remember_token', $token);
            $user->save();
            $this->session->setRememberToken($token);
            
            // Set remember token cookie
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        }

        return $this->redirect('/dashboard');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register', [
            'title' => 'Register - IslamWiki',
            'error' => $request->getQueryParam('error'),
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request): Response
    {
        $username = $request->getPostParam('username');
        $email = $request->getPostParam('email');
        $password = $request->getPostParam('password');
        $passwordConfirm = $request->getPostParam('password_confirm');
        $displayName = $request->getPostParam('display_name');

        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return $this->redirect('/register?error=All fields are required');
        }

        if ($password !== $passwordConfirm) {
            return $this->redirect('/register?error=Passwords do not match');
        }

        if (strlen($password) < 8) {
            return $this->redirect('/register?error=Password must be at least 8 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirect('/register?error=Invalid email address');
        }

        // Check if username or email already exists
        if (User::findByUsername($username, $this->db)) {
            return $this->redirect('/register?error=Username already exists');
        }

        if (User::findByEmail($email, $this->db)) {
            return $this->redirect('/register?error=Email already exists');
        }

        // Create new user
        $user = new User($this->db, [
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'display_name' => $displayName ?: $username,
            'is_active' => true,
            'is_admin' => false,
        ]);

        if (!$user->save()) {
            return $this->redirect('/register?error=Failed to create account');
        }

        // Auto-login after registration
        $this->session->login(
            $user->getAttribute('id'),
            $user->getAttribute('username'),
            $user->isAdmin()
        );

        return $this->redirect('/dashboard');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): Response
    {
        // Log out the user using session manager
        $this->session->logout();

        // Clear remember token cookie
        setcookie('remember_token', '', time() - 3600, '/');

        return $this->redirect('/');
    }



    /**
     * Get the currently authenticated user.
     */
    public function getCurrentUser(): ?User
    {
        if ($this->session->isLoggedIn()) {
            return User::find($this->session->getUserId(), $this->db);
        }

        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user = $this->findUserByRememberToken($token);
            
            if ($user) {
                $this->session->login(
                    $user->getAttribute('id'),
                    $user->getAttribute('username'),
                    $user->isAdmin()
                );
                return $user;
            }
        }

        return null;
    }

    /**
     * Find user by remember token.
     */
    protected function findUserByRememberToken(string $token): ?User
    {
        $data = $this->db->table('users')
            ->where('remember_token', '=', $token)
            ->where('is_active', '=', true)
            ->first();

        if (!$data) {
            return null;
        }

        return new User($this->db, (array) $data);
    }
} 