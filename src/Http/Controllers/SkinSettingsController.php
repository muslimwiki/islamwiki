<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinRegistry;
use IslamWiki\Extensions\SafaSkinExtension\Services\AssetManager;

/**
 * Enhanced Skin Settings Controller
 * 
 * Provides comprehensive skin management functionality including:
 * - Skin selection and preview
 * - Theme customization
 * - Layout options
 * - Component management
 * 
 * @package IslamWiki\Http\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SkinSettingsController
{
    private AsasContainer $container;
    private SkinManager $skinManager;
    private SkinRegistry $skinRegistry;
    private AssetManager $assetManager;
    private ShahidLogger $logger;
    private HookManager $hookManager;

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->skinManager = $container->get('skin.manager');
        $this->skinRegistry = $container->get('skin.registry');
        $this->assetManager = $container->get('skin.asset_manager');
        $this->logger = $container->get('logger');
        $this->hookManager = $container->get('IslamWiki\Core\Extensions\Hooks\HookManager');
    }

    /**
     * Display the main skin settings page
     */
    public function index(): array
    {
        try {
            $currentSkin = $this->skinManager->getActiveSkin();
            $availableSkins = $this->getSampleSkins(); // Use sample data for now
            $userPreferences = $this->getUserPreferences();

            return [
                'current_skin' => $currentSkin ?: ['name' => 'Bismillah', 'version' => '1.0.0'],
                'available_skins' => $availableSkins,
                'user_preferences' => $userPreferences,
                'customization_options' => $this->getCustomizationOptions(),
                'layout_options' => $this->getLayoutOptions(),
                'component_options' => $this->getComponentOptions()
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error loading skin settings: ' . $e->getMessage());
            return [
                'error' => 'Failed to load skin settings',
                'current_skin' => ['name' => 'Bismillah', 'version' => '1.0.0'],
                'available_skins' => $this->getSampleSkins(),
                'user_preferences' => $this->getDefaultPreferences(),
                'customization_options' => $this->getCustomizationOptions(),
                'layout_options' => $this->getLayoutOptions(),
                'component_options' => $this->getComponentOptions()
            ];
        }
    }

    /**
     * Switch to a different skin
     */
    public function switchSkin(string $skinName): array
    {
        try {
            // For demonstration, allow switching to any sample skin
            $sampleSkins = ['Bismillah', 'Muslim', 'Al-Andalus', 'Modern Islamic'];
            
            if (in_array($skinName, $sampleSkins)) {
                $this->saveUserPreference('active_skin', $skinName);
                $this->hookManager->run('skin.changed', ['skin' => $skinName]);
                
                return [
                    'success' => true,
                    'message' => "Successfully switched to {$skinName} skin",
                    'new_skin' => $skinName,
                    'skin_info' => $this->getSkinInfo($skinName)
                ];
            } else {
                // Try the actual skin manager
                if ($this->skinManager->setActiveSkin($skinName)) {
                    $this->saveUserPreference('active_skin', $skinName);
                    $this->hookManager->run('skin.changed', ['skin' => $skinName]);
                    
                    return [
                        'success' => true,
                        'message' => "Successfully switched to {$skinName} skin",
                        'new_skin' => $skinName
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => "Failed to switch to {$skinName} skin"
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error switching skin: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while switching skins'
            ];
        }
    }

    /**
     * Update theme customization
     */
    public function updateTheme(array $themeData): array
    {
        try {
            $validatedData = $this->validateThemeData($themeData);
            $this->saveUserPreference('theme_customization', $validatedData);
            
            // Trigger theme update hook
            $this->hookManager->run('theme.updated', ['theme' => $validatedData]);
            
            return [
                'success' => true,
                'message' => 'Theme customization updated successfully',
                'theme_data' => $validatedData
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error updating theme: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update theme customization'
            ];
        }
    }

    /**
     * Update layout preferences
     */
    public function updateLayout(array $layoutData): array
    {
        try {
            $validatedData = $this->validateLayoutData($layoutData);
            $this->saveUserPreference('layout_preferences', $validatedData);
            
            // Trigger layout update hook
            $this->hookManager->run('layout.updated', ['layout' => $validatedData]);
            
            return [
                'success' => true,
                'message' => 'Layout preferences updated successfully',
                'layout_data' => $validatedData
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error updating layout: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update layout preferences'
            ];
        }
    }

    /**
     * Toggle component visibility
     */
    public function toggleComponent(string $componentName, bool $visible): array
    {
        try {
            $preferences = $this->getUserPreferences();
            $preferences['component_visibility'][$componentName] = $visible;
            
            $this->saveUserPreference('component_visibility', $preferences['component_visibility']);
            
            // Trigger component toggle hook
            $this->hookManager->run('component.toggled', [
                'component' => $componentName,
                'visible' => $visible
            ]);
            
            return [
                'success' => true,
                'message' => "Component '{$componentName}' " . ($visible ? 'enabled' : 'disabled'),
                'component' => $componentName,
                'visible' => $visible
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error toggling component: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to toggle component'
            ];
        }
    }

    /**
     * Get live preview data for a skin
     */
    public function getLivePreview(string $skinName, array $customization = []): array
    {
        try {
            // Generate live preview data
            $previewData = [
                'skin_name' => $skinName,
                'customization' => $customization,
                'preview_url' => "/skins/{$skinName}/preview.html",
                'status' => 'preview_ready',
                'preview_html' => $this->generatePreviewHTML($skinName, $customization),
                'css_variables' => $this->generateCSSVariables($customization),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            return [
                'success' => true,
                'preview_data' => $previewData,
                'skin_name' => $skinName
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error generating preview: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate preview'
            ];
        }
    }

    /**
     * Reset user preferences to default
     */
    public function resetPreferences(): array
    {
        try {
            $defaultPreferences = $this->getDefaultPreferences();
            $this->saveUserPreference('all', $defaultPreferences);
            
            // Trigger reset hook
            $this->hookManager->run('preferences.reset', ['preferences' => $defaultPreferences]);
            
            return [
                'success' => true,
                'message' => 'Preferences reset to default successfully',
                'default_preferences' => $defaultPreferences
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error resetting preferences: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reset preferences'
            ];
        }
    }

    /**
     * Export user preferences
     */
    public function exportPreferences(): array
    {
        try {
            $preferences = $this->getUserPreferences();
            $exportData = [
                'export_date' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'preferences' => $preferences
            ];
            
            return [
                'success' => true,
                'export_data' => $exportData,
                'filename' => 'islamwiki_preferences_' . date('Y-m-d_H-i-s') . '.json'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error exporting preferences: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to export preferences'
            ];
        }
    }

    /**
     * Import user preferences
     */
    public function importPreferences(array $importData): array
    {
        try {
            $validatedData = $this->validateImportData($importData);
            $this->saveUserPreference('all', $validatedData['preferences']);
            
            // Trigger import hook
            $this->hookManager->run('preferences.imported', ['preferences' => $validatedData['preferences']]);
            
            return [
                'success' => true,
                'message' => 'Preferences imported successfully',
                'imported_count' => count($validatedData['preferences'])
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error importing preferences: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to import preferences'
            ];
        }
    }

    /**
     * Get available customization options
     */
    private function getCustomizationOptions(): array
    {
        return [
            'color_schemes' => [
                'traditional' => [
                    'name' => 'Traditional Islamic',
                    'primary' => '#2d5016',
                    'secondary' => '#8bc34a',
                    'accent' => '#ffc107',
                    'description' => 'Classic Islamic green theme with warm gold accents'
                ],
                'modern' => [
                    'name' => 'Modern Professional',
                    'primary' => '#1976d2',
                    'secondary' => '#42a5f5',
                    'accent' => '#ff7043',
                    'description' => 'Contemporary blue theme with orange highlights'
                ],
                'elegant' => [
                    'name' => 'Elegant Dark',
                    'primary' => '#424242',
                    'secondary' => '#757575',
                    'accent' => '#ffd54f',
                    'description' => 'Sophisticated dark theme with gold accents'
                ],
                'warm' => [
                    'name' => 'Warm Comfort',
                    'primary' => '#8d6e63',
                    'secondary' => '#a1887f',
                    'accent' => '#ffab91',
                    'description' => 'Warm brown theme for comfortable reading'
                ]
            ],
            'typography' => [
                'font_families' => [
                    'Amiri' => 'Traditional Arabic serif font',
                    'Noto Naskh Arabic' => 'Modern Arabic font with excellent readability',
                    'Open Sans' => 'Clean, modern sans-serif font',
                    'Roboto' => 'Google\'s modern system font',
                    'Lora' => 'Elegant serif font for body text'
                ],
                'font_sizes' => [
                    'small' => 'Compact text for dense layouts',
                    'medium' => 'Standard readable text size',
                    'large' => 'Enhanced readability for longer content',
                    'x-large' => 'Large text for accessibility'
                ],
                'line_heights' => [
                    'tight' => 'Compact line spacing',
                    'normal' => 'Standard line spacing',
                    'relaxed' => 'Comfortable line spacing for reading'
                ]
            ],
            'spacing' => [
                'compact' => 'Compact spacing for dense layouts and mobile devices',
                'normal' => 'Standard spacing for balanced layouts and general use',
                'relaxed' => 'Relaxed spacing for comfortable reading and accessibility'
            ]
        ];
    }

    /**
     * Get available layout options
     */
    private function getLayoutOptions(): array
    {
        return [
            'sidebar_position' => ['left', 'right', 'hidden'],
            'content_width' => ['narrow', 'standard', 'wide', 'full'],
            'header_style' => ['minimal', 'standard', 'extended'],
            'footer_style' => ['minimal', 'standard', 'extended'],
            'navigation_style' => ['dropdown', 'sidebar', 'horizontal']
        ];
    }

    /**
     * Get available component options
     */
    private function getComponentOptions(): array
    {
        return [
            'search_bar' => [
                'name' => 'Search Bar',
                'description' => 'Search functionality'
            ],
            'user_menu' => [
                'name' => 'User Menu',
                'description' => 'User account menu'
            ],
            'notifications' => [
                'name' => 'Notifications',
                'description' => 'Notification system'
            ],
            'breadcrumbs' => [
                'name' => 'Breadcrumbs',
                'description' => 'Navigation breadcrumbs'
            ],
            'social_links' => [
                'name' => 'Social Links',
                'description' => 'Social media links'
            ],
            'related_content' => [
                'name' => 'Related Content',
                'description' => 'Related content suggestions'
            ],
            'quick_actions' => [
                'name' => 'Quick Actions',
                'description' => 'Quick action buttons'
            ],
            'progress_indicator' => [
                'name' => 'Progress Indicator',
                'description' => 'Progress indicators'
            ]
        ];
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(): array
    {
        // This would typically come from user session or database
        // For now, return default preferences
        return $this->getDefaultPreferences();
    }

    /**
     * Generate preview HTML for live preview
     */
    private function generatePreviewHTML(string $skinName, array $customization): string
    {
        $colorScheme = $customization['color_scheme'] ?? 'traditional';
        $fontFamily = $customization['font_family'] ?? 'Amiri';
        
        return "
        <div class='preview-container' style='font-family: {$fontFamily}, serif;'>
            <div class='preview-header' style='background: var(--primary-color); color: white; padding: 1rem; text-align: center;'>
                <h2>Live Preview - {$skinName} Skin</h2>
                <p>Real-time customization preview</p>
            </div>
            <div class='preview-content' style='padding: 2rem;'>
                <h3>Sample Content</h3>
                <p>This is a live preview of how your content will look with the selected customization options.</p>
                <div class='preview-features'>
                    <h4>Features:</h4>
                    <ul>
                        <li>Color Scheme: {$colorScheme}</li>
                        <li>Font Family: {$fontFamily}</li>
                        <li>Real-time Updates</li>
                        <li>Professional Appearance</li>
                    </ul>
                </div>
            </div>
        </div>";
    }

    /**
     * Generate CSS variables for live preview
     */
    private function generateCSSVariables(array $customization): array
    {
        $colorSchemes = [
            'traditional' => ['#2d5016', '#8bc34a', '#ffc107'],
            'modern' => ['#1976d2', '#42a5f5', '#ff7043'],
            'elegant' => ['#424242', '#757575', '#ffd54f'],
            'sunset' => ['#d84315', '#ff8f00', '#ffeb3b']
        ];
        
        $colorScheme = $customization['color_scheme'] ?? 'traditional';
        $colors = $colorSchemes[$colorScheme] ?? $colorSchemes['traditional'];
        
        return [
            '--primary-color' => $colors[0],
            '--secondary-color' => $colors[1],
            '--accent-color' => $colors[2],
            '--font-family' => $customization['font_family'] ?? 'Amiri',
            '--font-size' => $customization['font_size'] ?? 'medium',
            '--line-height' => $customization['line_height'] ?? 'normal'
        ];
    }

    /**
     * Get information about a specific skin
     */
    private function getSkinInfo(string $skinName): array
    {
        $sampleSkins = $this->getSampleSkins();
        
        foreach ($sampleSkins as $skin) {
            if ($skin['name'] === $skinName) {
                return $skin;
            }
        }
        
        return [
            'name' => $skinName,
            'version' => '1.0.0',
            'description' => 'Custom skin',
            'author' => 'User',
            'features' => ['Custom', 'Personalized'],
            'preview_url' => "/skins/{$skinName}/preview.png"
        ];
    }

    /**
     * Get sample available skins for demonstration
     */
    private function getSampleSkins(): array
    {
        return [
            [
                'name' => 'Bismillah',
                'version' => '1.0.0',
                'description' => 'Beautiful Islamic-themed skin with modern design principles',
                'author' => 'IslamWiki Team',
                'features' => ['Responsive', 'Islamic Theme', 'Modern UI', 'Accessibility'],
                'preview_url' => '/skins/Bismillah/preview.png'
            ],
            [
                'name' => 'Muslim',
                'version' => '0.9.5',
                'description' => 'Clean and minimalist skin focused on content readability',
                'author' => 'IslamWiki Team',
                'features' => ['Minimalist', 'High Contrast', 'Fast Loading', 'Mobile First'],
                'preview_url' => '/skins/Muslim/preview.png'
            ],
            [
                'name' => 'Al-Andalus',
                'version' => '0.8.2',
                'description' => 'Elegant skin inspired by Islamic architecture and calligraphy',
                'author' => 'Community Contributor',
                'features' => ['Calligraphy', 'Architecture', 'Cultural', 'Elegant'],
                'preview_url' => '/skins/Al-Andalus/preview.png'
            ],
            [
                'name' => 'Modern Islamic',
                'version' => '1.1.0',
                'description' => 'Contemporary design with Islamic elements and modern UX',
                'author' => 'IslamWiki Team',
                'features' => ['Modern', 'Islamic Elements', 'UX Focused', 'Responsive'],
                'preview_url' => '/skins/Modern-Islamic/preview.png'
            ]
        ];
    }

    /**
     * Get default preferences
     */
    private function getDefaultPreferences(): array
    {
        return [
            'active_skin' => 'Bismillah',
            'theme_customization' => [
                'color_scheme' => 'traditional',
                'font_family' => 'Amiri',
                'font_size' => 'medium',
                'line_height' => 'normal',
                'spacing' => 'normal'
            ],
            'layout_preferences' => [
                'sidebar_position' => 'left',
                'content_width' => 'standard',
                'header_style' => 'standard',
                'footer_style' => 'standard',
                'navigation_style' => 'sidebar'
            ],
            'component_visibility' => [
                'search_bar' => true,
                'user_menu' => true,
                'notifications' => true,
                'breadcrumbs' => true,
                'social_links' => false,
                'related_content' => true,
                'quick_actions' => true,
                'progress_indicator' => false
            ]
        ];
    }

    /**
     * Save user preference
     */
    private function saveUserPreference(string $key, $value): void
    {
        // This would typically save to user session or database
        // For now, just log the action
        $this->logger->info("User preference saved: {$key}", ['value' => $value]);
    }

    /**
     * Validate theme data
     */
    private function validateThemeData(array $data): array
    {
        $validated = [];
        
        if (isset($data['color_scheme'])) {
            $validated['color_scheme'] = in_array($data['color_scheme'], ['traditional', 'modern', 'elegant']) 
                ? $data['color_scheme'] : 'traditional';
        }
        
        if (isset($data['font_family'])) {
            $validated['font_family'] = in_array($data['font_family'], ['Amiri', 'Noto Naskh Arabic', 'Open Sans', 'Roboto']) 
                ? $data['font_family'] : 'Amiri';
        }
        
        if (isset($data['font_size'])) {
            $validated['font_size'] = in_array($data['font_size'], ['small', 'medium', 'large', 'x-large']) 
                ? $data['font_size'] : 'medium';
        }
        
        if (isset($data['line_height'])) {
            $validated['line_height'] = in_array($data['line_height'], ['tight', 'normal', 'relaxed']) 
                ? $data['line_height'] : 'normal';
        }
        
        if (isset($data['spacing'])) {
            $validated['spacing'] = in_array($data['spacing'], ['compact', 'normal', 'relaxed']) 
                ? $data['spacing'] : 'normal';
        }
        
        return $validated;
    }

    /**
     * Validate layout data
     */
    private function validateLayoutData(array $data): array
    {
        $validated = [];
        
        if (isset($data['sidebar_position'])) {
            $validated['sidebar_position'] = in_array($data['sidebar_position'], ['left', 'right', 'hidden']) 
                ? $data['sidebar_position'] : 'left';
        }
        
        if (isset($data['content_width'])) {
            $validated['content_width'] = in_array($data['content_width'], ['narrow', 'standard', 'wide', 'full']) 
                ? $data['content_width'] : 'standard';
        }
        
        if (isset($data['header_style'])) {
            $validated['header_style'] = in_array($data['header_style'], ['minimal', 'standard', 'extended']) 
                ? $data['header_style'] : 'standard';
        }
        
        if (isset($data['footer_style'])) {
            $validated['footer_style'] = in_array($data['footer_style'], ['minimal', 'standard', 'extended']) 
                ? $data['footer_style'] : 'standard';
        }
        
        if (isset($data['navigation_style'])) {
            $validated['navigation_style'] = in_array($data['navigation_style'], ['dropdown', 'sidebar', 'horizontal']) 
                ? $data['navigation_style'] : 'sidebar';
        }
        
        return $validated;
    }

    /**
     * Validate import data
     */
    private function validateImportData(array $data): array
    {
        if (!isset($data['preferences']) || !is_array($data['preferences'])) {
            throw new \InvalidArgumentException('Invalid import data format');
        }
        
        return $data;
    }
} 