<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension\Services;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;

/**
 * Asset Manager Service
 * 
 * Manages skin-specific asset loading, optimization, and delivery.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Services
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AssetManager
{
    private AsasContainer $container;
    private SkinManager $skinManager;
    private array $enqueuedAssets = [];
    private array $assetCache = [];

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->skinManager = $container->get('skin.manager');
    }

    /**
     * Enqueue skin-specific assets
     */
    public function enqueueSkinAssets(array $assets = []): void
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return;
        }

        $skinAssets = $this->skinManager->getActiveSkinAssets();
        
        // Enqueue CSS files
        foreach ($skinAssets['css'] as $cssFile) {
            $this->enqueueStyle($cssFile, $activeSkin['path']);
        }

        // Enqueue JavaScript files
        foreach ($skinAssets['js'] as $jsFile) {
            $this->enqueueScript($jsFile, $activeSkin['path']);
        }

        // Enqueue additional assets
        foreach ($assets as $asset) {
            $this->enqueueAsset($asset);
        }
    }

    /**
     * Enqueue a CSS file
     */
    public function enqueueStyle(string $filename, string $skinPath = ''): void
    {
        $assetKey = 'css_' . $filename;
        
        if (empty($skinPath)) {
            $activeSkin = $this->skinManager->getActiveSkin();
            $skinPath = $activeSkin['path'] ?? '';
        }

        if ($skinPath) {
            $assetPath = $skinPath . '/css/' . $filename;
            $webPath = '/skins/' . basename($skinPath) . '/css/' . $filename;
            
            $this->enqueuedAssets[$assetKey] = [
                'type' => 'css',
                'filename' => $filename,
                'path' => $assetPath,
                'web_path' => $webPath,
                'skin_path' => $skinPath,
                'enqueued_at' => microtime(true)
            ];
        }
    }

    /**
     * Enqueue a JavaScript file
     */
    public function enqueueScript(string $filename, string $skinPath = ''): void
    {
        $assetKey = 'js_' . $filename;
        
        if (empty($skinPath)) {
            $activeSkin = $this->skinManager->getActiveSkin();
            $skinPath = $activeSkin['path'] ?? '';
        }

        if ($skinPath) {
            $assetPath = $skinPath . '/js/' . $filename;
            $webPath = '/skins/' . basename($skinPath) . '/js/' . $filename;
            
            $this->enqueuedAssets[$assetKey] = [
                'type' => 'js',
                'filename' => $filename,
                'path' => $assetPath,
                'web_path' => $webPath,
                'skin_path' => $skinPath,
                'enqueued_at' => microtime(true)
            ];
        }
    }

    /**
     * Enqueue a generic asset
     */
    public function enqueueAsset(array $asset): void
    {
        $assetKey = $asset['type'] . '_' . ($asset['filename'] ?? uniqid());
        
        $this->enqueuedAssets[$assetKey] = array_merge($asset, [
            'enqueued_at' => microtime(true)
        ]);
    }

    /**
     * Get all enqueued assets
     */
    public function getEnqueuedAssets(): array
    {
        return $this->enqueuedAssets;
    }

    /**
     * Get enqueued CSS assets
     */
    public function getEnqueuedStyles(): array
    {
        return array_filter($this->enqueuedAssets, function ($asset) {
            return $asset['type'] === 'css';
        });
    }

    /**
     * Get enqueued JavaScript assets
     */
    public function getEnqueuedScripts(): array
    {
        return array_filter($this->enqueuedAssets, function ($asset) {
            return $asset['type'] === 'js';
        });
    }

    /**
     * Filter assets based on skin requirements
     */
    public function filterAssets(array $assets): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return $assets;
        }

        $filteredAssets = [];
        
        foreach ($assets as $asset) {
            // Check if asset is skin-specific
            if ($this->isAssetSkinSpecific($asset, $activeSkin)) {
                $filteredAssets[] = $asset;
            }
        }

        return $filteredAssets;
    }

    /**
     * Check if an asset is skin-specific
     */
    private function isAssetSkinSpecific(array $asset, array $activeSkin): bool
    {
        // Check if asset belongs to active skin
        if (isset($asset['skin']) && $asset['skin'] === $activeSkin['name']) {
            return true;
        }

        // Check if asset path is in skin directory
        if (isset($asset['path']) && strpos($asset['path'], $activeSkin['path']) === 0) {
            return true;
        }

        // Check if asset is global (no skin restriction)
        if (isset($asset['global']) && $asset['global'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Generate HTML for enqueued CSS assets
     */
    public function renderStyles(): string
    {
        $styles = $this->getEnqueuedStyles();
        $html = '';

        foreach ($styles as $style) {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s" type="text/css" media="all">' . "\n",
                htmlspecialchars($style['web_path'])
            );
        }

        return $html;
    }

    /**
     * Generate HTML for enqueued JavaScript assets
     */
    public function renderScripts(): string
    {
        $scripts = $this->getEnqueuedScripts();
        $html = '';

        foreach ($scripts as $script) {
            $html .= sprintf(
                '<script src="%s" type="text/javascript"></script>' . "\n",
                htmlspecialchars($script['web_path'])
            );
        }

        return $html;
    }

    /**
     * Optimize assets for production
     */
    public function optimizeAssets(): array
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return [];
        }

        $optimizedAssets = [];
        $skinAssets = $this->skinManager->getActiveSkinAssets();

        // Optimize CSS
        if (!empty($skinAssets['css'])) {
            $optimizedAssets['css'] = $this->optimizeCSS($skinAssets['css'], $activeSkin['path']);
        }

        // Optimize JavaScript
        if (!empty($skinAssets['js'])) {
            $optimizedAssets['js'] = $this->optimizeJavaScript($skinAssets['js'], $activeSkin['path']);
        }

        return $optimizedAssets;
    }

    /**
     * Optimize CSS files
     */
    private function optimizeCSS(array $cssFiles, string $skinPath): array
    {
        $optimized = [];
        
        foreach ($cssFiles as $cssFile) {
            $filePath = $skinPath . '/css/' . $cssFile;
            
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                
                // Basic CSS optimization (remove comments, whitespace)
                $optimizedContent = $this->minifyCSS($content);
                
                $optimized[] = [
                    'original' => $cssFile,
                    'optimized' => $optimizedContent,
                    'size_reduction' => strlen($content) - strlen($optimizedContent)
                ];
            }
        }

        return $optimized;
    }

    /**
     * Optimize JavaScript files
     */
    private function optimizeJavaScript(array $jsFiles, string $skinPath): array
    {
        $optimized = [];
        
        foreach ($jsFiles as $jsFile) {
            $filePath = $skinPath . '/js/' . $jsFile;
            
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                
                // Basic JS optimization (remove comments, whitespace)
                $optimizedContent = $this->minifyJavaScript($content);
                
                $optimized[] = [
                    'original' => $jsFile,
                    'optimized' => $optimizedContent,
                    'size_reduction' => strlen($content) - strlen($optimizedContent)
                ];
            }
        }

        return $optimized;
    }

    /**
     * Basic CSS minification
     */
    private function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': '], [';', '{', '{', '}', '}', ':'], $css);
        
        return trim($css);
    }

    /**
     * Basic JavaScript minification
     */
    private function minifyJavaScript(string $js): string
    {
        // Remove single-line comments (basic)
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }

    /**
     * Get asset statistics
     */
    public function getAssetStats(): array
    {
        $styles = $this->getEnqueuedStyles();
        $scripts = $this->getEnqueuedScripts();
        
        $totalSize = 0;
        foreach (array_merge($styles, $scripts) as $asset) {
            if (isset($asset['path']) && file_exists($asset['path'])) {
                $totalSize += filesize($asset['path']);
            }
        }

        return [
            'total_assets' => count($this->enqueuedAssets),
            'css_files' => count($styles),
            'js_files' => count($scripts),
            'total_size' => $totalSize,
            'cache_hits' => count($this->assetCache)
        ];
    }

    /**
     * Clear asset cache
     */
    public function clearCache(): void
    {
        $this->assetCache = [];
    }

    /**
     * Preload critical assets
     */
    public function preloadCriticalAssets(): void
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return;
        }

        $skinAssets = $this->skinManager->getActiveSkinAssets();
        
        // Preload main CSS file
        if (!empty($skinAssets['css'])) {
            $mainCSS = $skinAssets['css'][0];
            $this->preloadAsset($mainCSS, 'style');
        }

        // Preload main JavaScript file
        if (!empty($skinAssets['js'])) {
            $mainJS = $skinAssets['js'][0];
            $this->preloadAsset($mainJS, 'script');
        }
    }

    /**
     * Preload a specific asset
     */
    private function preloadAsset(string $filename, string $type): void
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        if (!$activeSkin) {
            return;
        }

        $webPath = '/skins/' . basename($activeSkin['path']) . '/' . $type . '/' . $filename;
        
        // Add preload link to head
        $preloadHtml = sprintf(
            '<link rel="preload" href="%s" as="%s" type="text/%s">',
            htmlspecialchars($webPath),
            $type,
            $type === 'style' ? 'css' : 'javascript'
        );

        // This would be added to the HTML head
        // For now, we'll just log it
        error_log("Preload asset: {$preloadHtml}");
    }
} 