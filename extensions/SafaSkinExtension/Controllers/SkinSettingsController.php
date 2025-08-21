<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension\Controllers;

use IslamWiki\Core\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;
use IslamWiki\Extensions\SafaSkinExtension\Services\TemplateEngine;
use IslamWiki\Extensions\SafaSkinExtension\Services\AssetManager;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinRegistry;

/**
 * Skin Settings Controller
 * 
 * Handles skin management, customization, and settings interface.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Controllers
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SkinSettingsController extends Controller
{
    private SkinManager $skinManager;
    private TemplateEngine $templateEngine;
    private AssetManager $assetManager;
    private SkinRegistry $skinRegistry;

    public function __construct(
        SkinManager $skinManager,
        TemplateEngine $templateEngine,
        AssetManager $assetManager,
        SkinRegistry $skinRegistry
    ) {
        $this->skinManager = $skinManager;
        $this->templateEngine = $templateEngine;
        $this->assetManager = $assetManager;
        $this->skinRegistry = $skinRegistry;
    }

    /**
     * Display the main skin settings page
     */
    public function index(): Response
    {
        $data = [
            'current_skin' => $this->getCurrentSkinInfo(),
            'available_skins' => $this->getAvailableSkinsInfo(),
            'skin_stats' => $this->skinManager->getSkinStats(),
            'customization_options' => $this->getCustomizationOptions(),
            'page_title' => 'Skin Settings',
            'active_tab' => 'overview'
        ];

        return $this->view('safa-skin::settings.index', $data);
    }

    /**
     * Display skin gallery with previews
     */
    public function gallery(): Response
    {
        $data = [
            'skins' => $this->getDetailedSkinInfo(),
            'current_skin' => $this->skinManager->getActiveSkinName(),
            'page_title' => 'Skin Gallery',
            'active_tab' => 'gallery'
        ];

        return $this->view('safa-skin::settings.gallery', $data);
    }

    /**
     * Display skin customization options
     */
    public function customize(): Response
    {
        $currentSkin = $this->skinManager->getActiveSkin();
        if (!$currentSkin) {
            return $this->redirect('/admin/skins');
        }

        $data = [
            'current_skin' => $this->getCurrentSkinInfo(),
            'customization_options' => $this->getCustomizationOptions(),
            'color_schemes' => $this->getColorSchemes(),
            'typography_options' => $this->getTypographyOptions(),
            'layout_options' => $this->getLayoutOptions(),
            'page_title' => 'Customize Skin',
            'active_tab' => 'customize'
        ];

        return $this->view('safa-skin::settings.customize', $data);
    }

    /**
     * Switch to a different skin
     */
    public function switchSkin(Request $request): Response
    {
        $skinName = $request->input('skin_name');
        
        if (empty($skinName)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Skin name is required'
            ], 400);
        }

        $result = $this->skinManager->setActiveSkin($skinName);
        
        if ($result) {
            return $this->jsonResponse([
                'success' => true,
                'message' => "Successfully switched to {$skinName} skin",
                'skin_name' => $skinName
            ]);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'message' => "Failed to switch to {$skinName} skin"
            ], 400);
        }
    }

    /**
     * Get live preview data for a skin
     */
    public function preview(Request $request): Response
    {
        $skinName = $request->input('skin_name');
        
        if (empty($skinName)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Skin name is required'
            ], 400);
        }

        $skinInfo = $this->skinRegistry->getSkinInfo($skinName);
        if (!$skinInfo) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Skin not found'
            ], 404);
        }

        $previewData = [
            'skin' => $skinInfo,
            'assets' => $this->assetManager->getSkinAssets($skinName),
            'templates' => $this->templateEngine->getAvailableTemplates(),
            'preview_html' => $this->generatePreviewHtml($skinName)
        ];

        return $this->jsonResponse([
            'success' => true,
            'preview_data' => $previewData
        ]);
    }

    /**
     * Save skin customization settings
     */
    public function saveCustomization(Request $request): Response
    {
        $settings = $request->input('customization');
        
        if (empty($settings)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Customization settings are required'
            ], 400);
        }

        $result = $this->saveCustomizationSettings($settings);
        
        if ($result) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Customization settings saved successfully'
            ]);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to save customization settings'
            ], 500);
        }
    }

    /**
     * Get skin information for the current active skin
     */
    private function getCurrentSkinInfo(): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return [];
        }

        $skinInfo = $this->skinRegistry->getSkinInfo($activeSkin['name']);
        $assets = $this->assetManager->getSkinAssets($activeSkin['name']);
        
        return array_merge($skinInfo ?? [], [
            'is_active' => true,
            'assets' => $assets,
            'customization' => $this->getCurrentCustomization()
        ]);
    }

    /**
     * Get information for all available skins
     */
    private function getAvailableSkinsInfo(): array
    {
        $skins = $this->skinRegistry->getRegisteredSkins();
        $availableSkins = [];

        foreach ($skins as $name => $skin) {
            $skinInfo = $this->skinRegistry->getSkinInfo($name);
            $isActive = $this->skinManager->getActiveSkinName() === $name;
            
            $availableSkins[] = array_merge($skinInfo ?? [], [
                'is_active' => $isActive,
                'is_compatible' => $this->skinRegistry->isSkinCompatible($name),
                'assets_count' => count($this->assetManager->getSkinAssets($name))
            ]);
        }

        return $availableSkins;
    }

    /**
     * Get detailed information for all skins
     */
    private function getDetailedSkinInfo(): array
    {
        $skins = $this->skinRegistry->getRegisteredSkins();
        $detailedSkins = [];

        foreach ($skins as $name => $skin) {
            $skinInfo = $this->skinRegistry->getSkinInfo($name);
            $assets = $this->assetManager->getSkinAssets($name);
            $templates = $this->templateEngine->getAvailableTemplates();
            
            $detailedSkins[] = array_merge($skinInfo ?? [], [
                'assets' => $assets,
                'templates' => $templates,
                'preview_url' => $this->generatePreviewUrl($name),
                'thumbnail_url' => $this->getThumbnailUrl($name)
            ]);
        }

        return $detailedSkins;
    }

    /**
     * Get customization options for the current skin
     */
    private function getCustomizationOptions(): array
    {
        $currentSkin = $this->skinManager->getActiveSkin();
        if (!$currentSkin) {
            return [];
        }

        return [
            'color_schemes' => $this->getColorSchemes(),
            'typography_options' => $this->getTypographyOptions(),
            'layout_options' => $this->getLayoutOptions(),
            'component_options' => $this->getComponentOptions()
        ];
    }

    /**
     * Get available color schemes
     */
    private function getColorSchemes(): array
    {
        return [
            'traditional' => [
                'name' => 'Traditional Islamic',
                'primary' => '#4F46E5',
                'secondary' => '#7C3AED',
                'accent' => '#A855F7',
                'background' => '#FFFFFF',
                'text' => '#1F2937'
            ],
            'modern' => [
                'name' => 'Modern Islamic',
                'primary' => '#059669',
                'secondary' => '#10B981',
                'accent' => '#34D399',
                'background' => '#F9FAFB',
                'text' => '#111827'
            ],
            'elegant' => [
                'name' => 'Elegant Islamic',
                'primary' => '#DC2626',
                'secondary' => '#EF4444',
                'accent' => '#F87171',
                'background' => '#FEF2F2',
                'text' => '#1F2937'
            ]
        ];
    }

    /**
     * Get typography options
     */
    private function getTypographyOptions(): array
    {
        return [
            'font_family' => [
                'islamic' => 'Amiri, serif',
                'modern' => 'Inter, sans-serif',
                'traditional' => 'Noto Naskh Arabic, serif'
            ],
            'font_size' => [
                'small' => '14px',
                'medium' => '16px',
                'large' => '18px'
            ],
            'line_height' => [
                'tight' => '1.4',
                'normal' => '1.6',
                'relaxed' => '1.8'
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
            'content_width' => ['narrow', 'standard', 'wide'],
            'header_style' => ['minimal', 'standard', 'elaborate'],
            'footer_style' => ['minimal', 'standard', 'comprehensive']
        ];
    }

    /**
     * Get component options
     */
    private function getComponentOptions(): array
    {
        return [
            'show_search' => true,
            'show_navigation' => true,
            'show_breadcrumbs' => true,
            'show_sidebar' => true,
            'show_footer' => true
        ];
    }

    /**
     * Get current customization settings
     */
    private function getCurrentCustomization(): array
    {
        // This would retrieve from user preferences or skin configuration
        return [
            'color_scheme' => 'traditional',
            'font_family' => 'islamic',
            'font_size' => 'medium',
            'line_height' => 'normal',
            'sidebar_position' => 'right',
            'content_width' => 'standard'
        ];
    }

    /**
     * Save customization settings
     */
    private function saveCustomizationSettings(array $settings): bool
    {
        try {
            // Save to user preferences or skin configuration
            // This is a placeholder implementation
            $configPath = $this->getConfigPath();
            $currentConfig = $this->loadConfig($configPath);
            
            $currentConfig['customization'] = array_merge(
                $currentConfig['customization'] ?? [],
                $settings
            );
            
            return $this->saveConfig($configPath, $currentConfig);
        } catch (\Exception $e) {
            error_log('Failed to save customization settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate preview HTML for a skin
     */
    private function generatePreviewHtml(string $skinName): string
    {
        // This would generate a preview of the skin
        return "<div class='skin-preview' data-skin='{$skinName}'>
            <div class='preview-header'>Header Preview</div>
            <div class='preview-content'>Content Preview</div>
            <div class='preview-footer'>Footer Preview</div>
        </div>";
    }

    /**
     * Generate preview URL for a skin
     */
    private function generatePreviewUrl(string $skinName): string
    {
        return "/admin/skins/preview?skin={$skinName}";
    }

    /**
     * Get thumbnail URL for a skin
     */
    private function getThumbnailUrl(string $skinName): string
    {
        $thumbnailPath = "skins/{$skinName}/thumbnail.png";
        if (file_exists($thumbnailPath)) {
            return "/{$thumbnailPath}";
        }
        
        return "/skins/default-thumbnail.png";
    }

    /**
     * Get configuration file path
     */
    private function getConfigPath(): string
    {
        return storage_path('skins/customization.json');
    }

    /**
     * Load configuration from file
     */
    private function loadConfig(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        try {
            $content = file_get_contents($path);
            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Save configuration to file
     */
    private function saveConfig(string $path, array $config): bool
    {
        try {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $content = json_encode($config, JSON_PRETTY_PRINT);
            return file_put_contents($path, $content) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
} 