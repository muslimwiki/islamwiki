<?php

/**
 * Skin Settings Controller
 *
 * Provides comprehensive skin management functionality including:
 * - Skin selection and preview
 * - Theme customization
 * - Layout options
 * - Component management
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
 * Skin Settings Controller - Handles Skin Management Functionality
 */
class SkinSettingsController extends Controller
{
    /**
     * Display the main skin settings page
     */
    public function index(Request $request): Response
    {
        try {
            $currentSkin = $this->getCurrentSkin();
            $availableSkins = $this->getAvailableSkins();
            $userPreferences = $this->getUserPreferences();

            return $this->view('skin-settings/index', [
                'current_skin' => $currentSkin,
                'available_skins' => $availableSkins,
                'user_preferences' => $userPreferences,
                'customization_options' => $this->getCustomizationOptions(),
                'layout_options' => $this->getLayoutOptions(),
                'component_options' => $this->getComponentOptions(),
                'title' => 'Skin Settings - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Switch to a different skin
     */
    public function switchSkin(Request $request, string $skinName): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return new Response(401, [], 'Authentication Required');
            }

            $availableSkins = $this->getAvailableSkins();
            $skinNames = array_column($availableSkins, 'name');
            
            if (in_array($skinName, $skinNames)) {
                $success = $this->saveUserPreference('active_skin', $skinName);
                
                if ($success) {
                    return $this->json([
                        'success' => true,
                        'message' => "Successfully switched to {$skinName} skin",
                        'new_skin' => $skinName
                    ]);
                } else {
                    return new Response(500, [], 'Failed to switch skin');
                }
            } else {
                return new Response(400, [], 'Invalid skin name');
            }
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Update skin customization
     */
    public function updateCustomization(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return new Response(401, [], 'Authentication Required');
            }

            $data = $request->getParsedBody();
            $success = $this->updateSkinCustomization($data);

            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Skin customization updated successfully'
                ]);
            } else {
                return new Response(500, [], 'Failed to update skin customization');
            }
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Preview skin with custom settings
     */
    public function preview(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $skinName = $data['skin'] ?? 'Bismillah';
            $customization = $data['customization'] ?? [];

            $previewData = $this->generateSkinPreview($skinName, $customization);

            return $this->view('skin-settings/preview', [
                'skin_name' => $skinName,
                'preview_data' => $previewData,
                'title' => "Preview {$skinName} Skin - IslamWiki"
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get current skin
     */
    private function getCurrentSkin(): array
    {
        try {
            $session = $this->container->get('session');
            if ($session->isLoggedIn()) {
                $preference = $this->getUserPreference('active_skin');
                if ($preference) {
                    return $this->getSkinInfo($preference);
                }
            }
            
            return $this->getSkinInfo('Bismillah');
        } catch (\Exception $e) {
            return $this->getSkinInfo('Bismillah');
        }
    }

    /**
     * Get available skins
     */
    private function getAvailableSkins(): array
    {
        return [
            [
                'name' => 'Bismillah',
                'version' => '1.0.0',
                'description' => 'Modern Islamic design with clean aesthetics',
                'author' => 'IslamWiki Team',
                'preview_image' => '/skins/Bismillah/preview.png'
            ],
            [
                'name' => 'Muslim',
                'version' => '1.0.0',
                'description' => 'Traditional Islamic design with rich colors',
                'author' => 'IslamWiki Team',
                'preview_image' => '/skins/Muslim/preview.png'
            ]
        ];
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(): array
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return $this->getDefaultPreferences();
            }

            $userId = $session->getUserId();
            $sql = "SELECT preference_key, preference_value FROM user_preferences WHERE user_id = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
            
            $preferences = [];
            while ($row = $stmt->fetch()) {
                $preferences[$row['preference_key']] = $row['preference_value'];
            }
            
            return array_merge($this->getDefaultPreferences(), $preferences);
        } catch (\Exception $e) {
            return $this->getDefaultPreferences();
        }
    }

    /**
     * Get default preferences
     */
    private function getDefaultPreferences(): array
    {
        return [
            'active_skin' => 'Bismillah',
            'theme_color' => 'default',
            'font_size' => 'medium',
            'layout_width' => 'fluid'
        ];
    }

    /**
     * Get customization options
     */
    private function getCustomizationOptions(): array
    {
        return [
            'theme_colors' => [
                'default' => 'Default Islamic',
                'blue' => 'Blue Theme',
                'green' => 'Green Theme',
                'gold' => 'Gold Theme'
            ],
            'font_sizes' => [
                'small' => 'Small',
                'medium' => 'Medium',
                'large' => 'Large'
            ]
        ];
    }

    /**
     * Get layout options
     */
    private function getLayoutOptions(): array
    {
        return [
            'sidebar_position' => ['left', 'right', 'none'],
            'header_style' => ['minimal', 'traditional', 'modern'],
            'footer_style' => ['minimal', 'detailed', 'social']
        ];
    }

    /**
     * Get component options
     */
    private function getComponentOptions(): array
    {
        return [
            'navigation' => ['top', 'sidebar', 'both'],
            'search' => ['header', 'sidebar', 'both'],
            'breadcrumbs' => ['show', 'hide']
        ];
    }

    /**
     * Get skin info
     */
    private function getSkinInfo(string $skinName): array
    {
        $skins = $this->getAvailableSkins();
        foreach ($skins as $skin) {
            if ($skin['name'] === $skinName) {
                return $skin;
            }
        }
        
        return $skins[0]; // Default to first skin
    }

    /**
     * Save user preference
     */
    private function saveUserPreference(string $key, string $value): bool
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return false;
            }

            $userId = $session->getUserId();
            $sql = "INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE preference_value = ?, updated_at = NOW()";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId, $key, $value, $value]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user preference
     */
    private function getUserPreference(string $key): ?string
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return null;
            }

            $userId = $session->getUserId();
            $sql = "SELECT preference_value FROM user_preferences WHERE user_id = ? AND preference_key = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId, $key]);
            
            $result = $stmt->fetch();
            return $result ? $result['preference_value'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update skin customization
     */
    private function updateSkinCustomization(array $data): bool
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                return false;
            }

            $allowedKeys = ['theme_color', 'font_size', 'layout_width'];
            $success = true;

            foreach ($allowedKeys as $key) {
                if (isset($data[$key])) {
                    if (!$this->saveUserPreference($key, $data[$key])) {
                        $success = false;
                    }
                }
            }

            return $success;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate skin preview
     */
    private function generateSkinPreview(string $skinName, array $customization): array
    {
        return [
            'skin_name' => $skinName,
            'customization' => $customization,
            'preview_url' => "/skins/{$skinName}/preview",
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
} 