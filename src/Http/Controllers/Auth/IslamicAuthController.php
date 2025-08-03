<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Models\IslamicUser;
use IslamWiki\Core\Session\Wisal;

/**
 * Islamic Authentication Controller
 * 
 * Enhanced authentication controller with Islamic community features:
 * - Scholar verification during registration
 * - Islamic role assignment
 * - Enhanced user profiles with Islamic data
 * - Two-factor authentication for scholars
 */
class IslamicAuthController extends AuthController
{
    /**
     * Create a new Islamic authentication controller instance.
     */
    public function __construct(Connection $db, Asas $container)
    {
        parent::__construct($db, $container);
    }

    /**
     * Show the enhanced Islamic registration form.
     */
    public function showIslamicRegister(Request $request): Response
    {
        return $this->view('auth/islamic_register', [
            'title' => 'Islamic Registration - IslamWiki',
            'error' => $request->getQueryParam('error'),
            'success' => $request->getQueryParam('success'),
            'csrf_token' => $this->session->getCsrfToken(),
            'madhab_options' => $this->getMadhabOptions(),
            'qualification_options' => $this->getQualificationOptions(),
            'specialization_options' => $this->getSpecializationOptions(),
        ]);
    }

    /**
     * Handle Islamic user registration with scholar verification.
     */
    public function islamicRegister(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-register?error=Invalid security token');
        }

        // Get registration data
        $username = $request->getPostParam('username');
        $email = $request->getPostParam('email');
        $password = $request->getPostParam('password');
        $confirmPassword = $request->getPostParam('confirm_password');
        $displayName = $request->getPostParam('display_name');
        $arabicName = $request->getPostParam('arabic_name');
        $kunyah = $request->getPostParam('kunyah');
        $laqab = $request->getPostParam('laqab');
        $nasab = $request->getPostParam('nasab');
        $madhab = $request->getPostParam('madhab');
        $qualificationLevel = $request->getPostParam('qualification_level');
        $specialization = $request->getPostParam('specialization');
        $islamicBio = $request->getPostParam('islamic_bio');
        $isScholar = $request->getPostParam('is_scholar') === 'on';
        $scholarVerification = $request->getPostParam('scholar_verification') === 'on';

        // Validate required fields
        if (empty($username) || empty($email) || empty($password)) {
            return $this->redirect('/islamic-register?error=Please fill in all required fields');
        }

        if ($password !== $confirmPassword) {
            return $this->redirect('/islamic-register?error=Passwords do not match');
        }

        // Check if username or email already exists
        $existingUser = IslamicUser::findByUsername($username, $this->db);
        if ($existingUser) {
            return $this->redirect('/islamic-register?error=Username already exists');
        }

        $existingUser = IslamicUser::findByEmail($email, $this->db);
        if ($existingUser) {
            return $this->redirect('/islamic-register?error=Email already exists');
        }

        // Create Islamic user
        $user = new IslamicUser($this->db, [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'display_name' => $displayName,
            'arabic_name' => $arabicName,
            'kunyah' => $kunyah,
            'laqab' => $laqab,
            'nasab' => $nasab,
            'madhab' => $madhab,
            'qualification_level' => $qualificationLevel,
            'specialization' => $specialization,
            'islamic_bio' => $islamicBio,
            'is_scholar' => $isScholar,
            'islamic_role' => $isScholar ? 'scholar' : 'user',
            'verification_status' => $scholarVerification ? 'pending' : 'none',
        ]);

        // Save user
        if (!$user->save()) {
            return $this->redirect('/islamic-register?error=Failed to create account');
        }

        // If scholar verification requested, create verification request
        if ($scholarVerification && $isScholar) {
            $verificationData = [
                'qualification_level' => $qualificationLevel,
                'madhab' => $madhab,
                'specialization' => $specialization,
                'islamic_bio' => $islamicBio,
                'credentials' => $request->getPostParam('credentials', []),
                'works' => $request->getPostParam('works', []),
            ];

            if ($user->requestScholarVerification($verificationData)) {
                return $this->redirect('/islamic-register?success=Account created successfully. Scholar verification request submitted.');
            }
        }

        // Log in the user
        $this->session->login(
            $user->getAttribute('id'),
            $user->getAttribute('username'),
            $user->isAdmin()
        );

        return $this->redirect('/dashboard?success=Welcome to IslamWiki!');
    }

    /**
     * Show the enhanced Islamic login form.
     */
    public function showIslamicLogin(Request $request): Response
    {
        return $this->view('auth/islamic_login', [
            'title' => 'Islamic Login - IslamWiki',
            'error' => $request->getQueryParam('error'),
            'success' => $request->getQueryParam('success'),
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Handle Islamic user login with enhanced features.
     */
    public function islamicLogin(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-login?error=Invalid security token');
        }

        $username = $request->getPostParam('username');
        $password = $request->getPostParam('password');
        $remember = $request->getPostParam('remember') === 'on';

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->redirect('/islamic-login?error=Please provide both username and password');
        }

        // Find user by username or email
        $user = IslamicUser::findByUsername($username, $this->db);
        if (!$user) {
            $user = IslamicUser::findByEmail($username, $this->db);
        }

        if (!$user || !$user->verifyPassword($password)) {
            return $this->redirect('/islamic-login?error=Invalid username or password');
        }

        if (!$user->isActive()) {
            return $this->redirect('/islamic-login?error=Account is deactivated');
        }

        // Check if user needs two-factor authentication (for scholars)
        if ($user->isScholar() && $this->requiresTwoFactor($user)) {
            $this->session->set('pending_2fa_user_id', $user->getAttribute('id'));
            return $this->redirect('/two-factor-auth');
        }

        // Log in the user
        $this->session->login(
            $user->getAttribute('id'),
            $user->getAttribute('username'),
            $user->isAdmin()
        );

        // Record login
        $user->recordLogin();

        return $this->redirect('/dashboard?success=Welcome back!');
    }

    /**
     * Show the Islamic user profile.
     */
    public function showIslamicProfile(Request $request): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user) {
            return $this->redirect('/islamic-login?error=Please log in to view your profile');
        }

        return $this->view('auth/islamic_profile', [
            'title' => 'Islamic Profile - IslamWiki',
            'user' => $user,
            'islamic_profile' => $user->getIslamicProfile(),
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Update Islamic user profile.
     */
    public function updateIslamicProfile(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-profile?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user) {
            return $this->redirect('/islamic-login?error=Please log in to update your profile');
        }

        // Update Islamic profile fields
        $user->setAttribute('arabic_name', $request->getPostParam('arabic_name'));
        $user->setAttribute('kunyah', $request->getPostParam('kunyah'));
        $user->setAttribute('laqab', $request->getPostParam('laqab'));
        $user->setAttribute('nasab', $request->getPostParam('nasab'));
        $user->setAttribute('madhab', $request->getPostParam('madhab'));
        $user->setAttribute('qualification_level', $request->getPostParam('qualification_level'));
        $user->setAttribute('specialization', $request->getPostParam('specialization'));
        $user->setAttribute('islamic_bio', $request->getPostParam('islamic_bio'));

        // Update Islamic credentials
        $credentials = $request->getPostParam('credentials', []);
        if (is_array($credentials)) {
            $user->setAttribute('islamic_credentials', $credentials);
        }

        // Update Islamic works
        $works = $request->getPostParam('works', []);
        if (is_array($works)) {
            $user->setAttribute('islamic_works', $works);
        }

        if ($user->save()) {
            return $this->redirect('/islamic-profile?success=Profile updated successfully');
        } else {
            return $this->redirect('/islamic-profile?error=Failed to update profile');
        }
    }

    /**
     * Show scholar verification requests (admin only).
     */
    public function showScholarVerificationRequests(Request $request): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('verify_scholars')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        // Get pending verification requests
        $requests = $this->getScholarVerificationRequests();

        return $this->view('auth/scholar_verification_requests', [
            'title' => 'Scholar Verification Requests - IslamWiki',
            'user' => $user,
            'requests' => $requests,
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Approve scholar verification request.
     */
    public function approveScholarVerification(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/scholar-verification?error=Invalid security token');
        }

        $admin = $this->getCurrentIslamicUser();
        if (!$admin || !$admin->hasIslamicPermission('verify_scholars')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $userId = (int) $request->getPostParam('user_id');
        $user = IslamicUser::find($userId, $this->db);

        if (!$user) {
            return $this->redirect('/scholar-verification?error=User not found');
        }

        if ($user->approveScholarVerification($admin->getAttribute('id'))) {
            return $this->redirect('/scholar-verification?success=Scholar verification approved');
        } else {
            return $this->redirect('/scholar-verification?error=Failed to approve verification');
        }
    }

    /**
     * Reject scholar verification request.
     */
    public function rejectScholarVerification(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/scholar-verification?error=Invalid security token');
        }

        $admin = $this->getCurrentIslamicUser();
        if (!$admin || !$admin->hasIslamicPermission('verify_scholars')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $userId = (int) $request->getPostParam('user_id');
        $reason = $request->getPostParam('reason');
        $user = IslamicUser::find($userId, $this->db);

        if (!$user) {
            return $this->redirect('/scholar-verification?error=User not found');
        }

        if (empty($reason)) {
            return $this->redirect('/scholar-verification?error=Please provide a reason for rejection');
        }

        if ($user->rejectScholarVerification($admin->getAttribute('id'), $reason)) {
            return $this->redirect('/scholar-verification?success=Scholar verification rejected');
        } else {
            return $this->redirect('/scholar-verification?error=Failed to reject verification');
        }
    }

    /**
     * Get current Islamic user.
     */
    protected function getCurrentIslamicUser(): ?IslamicUser
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }

        return IslamicUser::find($userId, $this->db);
    }

    /**
     * Check if user requires two-factor authentication.
     */
    protected function requiresTwoFactor(IslamicUser $user): bool
    {
        // Scholars and verified scholars require 2FA
        return $user->isScholar() || $user->isVerifiedScholar();
    }

    /**
     * Get Madhab options.
     */
    protected function getMadhabOptions(): array
    {
        return [
            'hanafi' => 'Hanafi',
            'maliki' => 'Maliki',
            'shafii' => 'Shafi\'i',
            'hanbali' => 'Hanbali',
            'jaafari' => 'Ja\'afari',
            'zaydi' => 'Zaydi',
            'ibadi' => 'Ibadi',
            'other' => 'Other',
        ];
    }

    /**
     * Get qualification level options.
     */
    protected function getQualificationOptions(): array
    {
        return [
            'none' => 'None',
            'student' => 'Student',
            'graduate' => 'Graduate',
            'scholar' => 'Scholar',
            'expert' => 'Expert',
        ];
    }

    /**
     * Get specialization options.
     */
    protected function getSpecializationOptions(): array
    {
        return [
            'fiqh' => 'Fiqh (Islamic Jurisprudence)',
            'hadith' => 'Hadith (Prophetic Traditions)',
            'tafsir' => 'Tafsir (Quranic Exegesis)',
            'aqeedah' => 'Aqeedah (Islamic Creed)',
            'seerah' => 'Seerah (Prophetic Biography)',
            'arabic' => 'Arabic Language',
            'islamic_history' => 'Islamic History',
            'islamic_philosophy' => 'Islamic Philosophy',
            'other' => 'Other',
        ];
    }

    /**
     * Get scholar verification requests.
     */
    protected function getScholarVerificationRequests(): array
    {
        try {
            $islamicDb = $this->container->get('islamic_database_manager');
            $scholarConnection = $islamicDb->getScholarConnection();
            
            return $scholarConnection->select(
                'SELECT * FROM scholar_verification_requests WHERE status = ? ORDER BY created_at DESC',
                ['pending']
            );
        } catch (\Exception $e) {
            error_log("Failed to get scholar verification requests: " . $e->getMessage());
            return [];
        }
    }
} 