<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Language\LanguageService;

/**
 * Settings Controller
 * 
 * Handles user settings including language preferences
 */
class SettingsController extends Controller
{
    /**
     * @var Connection
     */
    private Connection $database;

    /**
     * @var LanguageService
     */
    private LanguageService $languageService;

    /**
     * Constructor
     */
    public function __construct(Connection $database, \IslamWiki\Core\Container\Container $container, LanguageService $languageService)
    {
        parent::__construct($database, $container);
        $this->languageService = $languageService;
    }

    /**
     * Show settings page
     */
    public function index(Request $request): Response
    {
        error_log("SettingsController::index - Starting settings page load");
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                error_log("SettingsController::index - User not logged in, redirecting to login");
                return new Response(302, ['Location' => '/login'], '');
            }

            $userId = $_SESSION['user_id'];
            error_log("SettingsController::index - User ID: $userId");
            
            $currentLanguage = $this->getUserLanguage($userId);
            error_log("SettingsController::index - Current language: $currentLanguage");

            // Debug: Check if LanguageService is working
            try {
                $supportedLanguages = $this->languageService->getSupportedLanguages();
                error_log("SettingsController::index - Supported languages: " . print_r($supportedLanguages, true));
                
                // Transform the supported languages data to match template expectations
                $transformedLanguages = [];
                foreach ($supportedLanguages as $code => $info) {
                    $transformedLanguages[] = [
                        'code' => $code,
                        'name' => $info['name'],
                        'native_name' => $info['native'],
                        'flag' => $info['flag'],
                        'direction' => $info['direction'],
                        'isRTL' => $info['isRTL'],
                        'locale' => $info['locale']
                    ];
                }
                error_log("SettingsController::index - Transformed languages: " . print_r($transformedLanguages, true));
            } catch (\Exception $e) {
                error_log("SettingsController::index - Error getting supported languages: " . $e->getMessage());
                error_log("SettingsController::index - LanguageService class: " . get_class($this->languageService));
                throw $e;
            }

            try {
                $currentLanguageDirection = $this->languageService->getCurrentLanguageDirection();
                error_log("SettingsController::index - Current language direction: $currentLanguageDirection");
            } catch (\Exception $e) {
                error_log("SettingsController::index - Error getting language direction: " . $e->getMessage());
                throw $e;
            }

            error_log("SettingsController::index - About to render template");
            
            return $this->view('settings/index.twig', [
                'user_id' => $userId,
                'current_language' => $currentLanguage,
                'current_language_direction' => $currentLanguageDirection,
                'supported_languages' => $transformedLanguages,
                'page_title' => 'Settings - Language Preference'
            ]);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log("SettingsController::index - CRITICAL ERROR: " . $e->getMessage());
            error_log("SettingsController::index - Error file: " . $e->getFile());
            error_log("SettingsController::index - Error line: " . $e->getLine());
            error_log("SettingsController::index - Stack trace: " . $e->getTraceAsString());
            
            // Return a detailed error response for debugging
            return new Response(500, ['Content-Type' => 'text/html'], '
                <h1>Error Loading Settings</h1>
                <p>Sorry, there was an error loading the settings page.</p>
                <h2>Error Details:</h2>
                <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>
                <p><strong>Line:</strong> ' . htmlspecialchars($e->getLine()) . '</p>
                <h2>Stack Trace:</h2>
                <pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                <p><a href="/">Return to Homepage</a></p>
            ');
        }
    }

    /**
     * Update user language preference
     */
    public function updateLanguage(Request $request, string $locale = 'en'): Response
    {
        error_log("SettingsController::updateLanguage - Starting language update");
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                error_log("SettingsController::updateLanguage - User not logged in, redirecting to login");
                return new Response(302, ['Location' => '/login'], '');
            }

            $userId = $_SESSION['user_id'];
            $language = $_POST['language'] ?? 'en';
            
            error_log("SettingsController::updateLanguage - User ID: $userId, Language: $language");

            // Validate language
            if (!$this->languageService->isLanguageSupported($language)) {
                error_log("SettingsController::updateLanguage - Invalid language: $language");
                $translationService = $this->languageService->getTranslationService();
                $errorMessage = $translationService ? $translationService->translate('messages.error.invalid_language') : 'Invalid language selected';
                
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]));
            }

            error_log("SettingsController::updateLanguage - Language validated, updating user preference");

            // Update user language preference
            $this->updateUserLanguage($userId, $language);

            error_log("SettingsController::updateLanguage - User language updated in database");

            // Set session language using LanguageService
            $this->languageService->setCurrentLanguage($language);

            // Set language cookie for future requests
            setcookie('islamwiki_language', $language, time() + (365 * 24 * 60 * 60), '/', '', false, true);

            error_log("SettingsController::updateLanguage - Session language updated and cookie set");

            // Update TwigRenderer translation language
            try {
                $twigRenderer = $this->container->get(\IslamWiki\Core\View\TwigRenderer::class);
                $twigRenderer->updateTranslationLanguage($language);
                error_log("SettingsController::updateLanguage - TwigRenderer translation language updated");
            } catch (\Exception $e) {
                error_log("SettingsController::updateLanguage - Error updating TwigRenderer: " . $e->getMessage());
            }

            // Get success message from translation service
            $translationService = $this->languageService->getTranslationService();
            $successMessage = $translationService ? $translationService->translate('messages.success.language_updated') : 'Language updated successfully';

            // Return success response with redirect to locale-aware settings page
            error_log("SettingsController::updateLanguage - Language update successful, redirecting to locale-aware settings");
            
            // Build redirect URL based on new language
            $redirectUrl = $language === 'en' ? '/settings' : "/{$language}/settings";
            
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => $successMessage,
                'language' => $language,
                'redirect_url' => $redirectUrl
            ]));
        } catch (\Exception $e) {
            error_log("SettingsController::updateLanguage - ERROR: " . $e->getMessage());
            error_log("SettingsController::updateLanguage - Stack trace: " . $e->getTraceAsString());
            
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'An error occurred while updating language: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * API endpoint for switching language
     */
    public function switchLanguage(Request $request): Response
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $userId = $_SESSION['user_id'];
        $language = $_POST['language'] ?? 'en';

        // Validate language
        if (!$this->languageService->isLanguageSupported($language)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Invalid language selected'
            ]));
        }

        // Update user language preference
        $this->updateUserLanguage($userId, $language);

        // Set session language using LanguageService
        $this->languageService->setCurrentLanguage($language);

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'message' => 'Language updated successfully',
            'language' => $language
        ]));
    }

    /**
     * Get user's preferred language
     */
    private function getUserLanguage(int $userId): string
    {
        try {
            $stmt = $this->db->prepare("
                SELECT settings 
                FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();

            if ($result && $result['settings']) {
                $settings = json_decode($result['settings'], true);
                if ($settings && isset($settings['language_preference'])) {
                    return $settings['language_preference'];
                }
            }
        } catch (\Exception $e) {
            // Log error but continue with default
            error_log("Error getting user language: " . $e->getMessage());
        }

        // Default to English
        return 'en';
    }

    /**
     * Update user's language preference
     */
    private function updateUserLanguage(int $userId, string $language): void
    {
        try {
            // First, try to get existing settings
            $stmt = $this->db->prepare("
                SELECT settings 
                FROM user_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();

            if ($result) {
                // Update existing record
                $settings = json_decode($result['settings'], true) ?: [];
                $settings['language_preference'] = $language;
                $settings['updated_at'] = date('Y-m-d H:i:s');
                
                $stmt = $this->db->prepare("
                    UPDATE user_settings 
                    SET settings = ?, updated_at = NOW() 
                    WHERE user_id = ?
                ");
                $stmt->execute([json_encode($settings), $userId]);
            } else {
                // Insert new record
                $settings = [
                    'language_preference' => $language,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$userId, json_encode($settings)]);
            }
            
            error_log("SettingsController::updateUserLanguage - Language preference updated successfully for user $userId to $language");
        } catch (\Exception $e) {
            error_log("Error updating user language: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get current language from session or user preference
     */
    public function getCurrentLanguage(): string
    {
        return $this->languageService->getCurrentLanguage();
    }

    /**
     * Check if current language is RTL
     */
    public function isCurrentLanguageRTL(): bool
    {
        return $this->languageService->isCurrentLanguageRTL();
    }

    /**
     * Get current language direction
     */
    public function getCurrentLanguageDirection(): string
    {
        return $this->languageService->getCurrentLanguageDirection();
    }

    /**
     * Update user skin preference
     */
    public function updateSkin(Request $request): Response
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return new Response(302, ['Location' => '/login'], '');
        }

        $userId = $_SESSION['user_id'];
        $skin = $_POST['skin'] ?? 'Bismillah';

        // Update user skin preference
        $this->updateUserSkin($userId, $skin);

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'message' => 'Skin updated successfully',
            'skin' => $skin
        ]));
    }

    /**
     * Get available skins
     */
    public function getAvailableSkins(Request $request): Response
    {
        $skins = [
            'Bismillah' => [
                'name' => 'Bismillah',
                'description' => 'Beautiful Islamic-themed skin with enhanced styling',
                'version' => '1.0.0',
                'author' => 'IslamWiki Team'
            ],
            'Muslim' => [
                'name' => 'Muslim',
                'description' => 'Clean and modern skin with Islamic elements',
                'version' => '1.0.0',
                'author' => 'IslamWiki Team'
            ]
        ];

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'skins' => $skins
        ]));
    }

    /**
     * Get skin information
     */
    public function getSkinInfo(Request $request, string $skinName): Response
    {
        $skins = [
            'Bismillah' => [
                'name' => 'Bismillah',
                'description' => 'Beautiful Islamic-themed skin with enhanced styling',
                'version' => '1.0.0',
                'author' => 'IslamWiki Team',
                'features' => ['RTL Support', 'Islamic Colors', 'Responsive Design']
            ],
            'Muslim' => [
                'name' => 'Muslim',
                'description' => 'Clean and modern skin with Islamic elements',
                'version' => '1.0.0',
                'author' => 'IslamWiki Team',
                'features' => ['Modern Design', 'Fast Loading', 'Mobile Optimized']
            ]
        ];

        if (!isset($skins[$skinName])) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'message' => 'Skin not found'
            ]));
        }

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => true,
            'skin' => $skins[$skinName]
        ]));
    }

    /**
     * Update user skin preference
     */
    private function updateUserSkin(int $userId, string $skin): void
    {
        try {
            // Try to update existing record
            $stmt = $this->db->prepare("
                UPDATE user_settings 
                SET skin_preference = ?, updated_at = NOW() 
                WHERE user_id = ?
            ");
            $stmt->execute([$skin, $userId]);

            // If no rows were affected, insert new record
            if ($stmt->rowCount() === 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO user_settings (user_id, skin_preference, created_at, updated_at) 
                    VALUES (?, ?, NOW(), NOW())
                ");
                $stmt->execute([$userId, $skin]);
            }
        } catch (\Exception $e) {
            error_log("Error updating user skin: " . $e->getMessage());
            throw $e;
        }
    }
}
