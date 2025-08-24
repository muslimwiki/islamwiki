<?php

/**
 * Core Skin Asset Manager
 *
 * Manages skin CSS, JavaScript, and other asset files.
 *
 * @package IslamWiki\Core\Skin
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Skin;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Core Skin Asset Manager - Asset Management System
 */
class AssetManager
{
    private Container $container;
    private Logger $logger;
    private SkinManager $skinManager;
    private string $assetsPath;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->skinManager = $container->get('skin.manager');
        $this->assetsPath = $container->get('base_path') . '/skins';
    }

    /**
     * Get CSS content for a specific skin
     */
    public function getSkinCss(string $skinName = null): string
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return '';
        }

        $cssPath = $skin['path'] . '/css';
        if (!is_dir($cssPath)) {
            return '';
        }

        $cssFiles = glob($cssPath . '/*.css');
        $cssContent = '';

        foreach ($cssFiles as $cssFile) {
            if (file_exists($cssFile)) {
                $cssContent .= file_get_contents($cssFile) . "\n";
            }
        }

        return $cssContent;
    }

    /**
     * Get JavaScript content for a specific skin
     */
    public function getSkinJs(string $skinName = null): string
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return '';
        }

        $jsPath = $skin['path'] . '/js';
        if (!is_dir($jsPath)) {
            return '';
        }

        $jsFiles = glob($jsPath . '/*.js');
        $jsContent = '';

        foreach ($jsFiles as $jsFile) {
            if (file_exists($jsFile)) {
                $jsContent .= file_get_contents($jsFile) . "\n";
            }
        }

        return $jsContent;
    }

    /**
     * Get skin assets URLs
     */
    public function getSkinAssets(string $skinName = null): array
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return [];
        }

        $assets = [];
        $skinPath = $skin['path'];
        $skinName = $skin['name'];

        // CSS files
        $cssPath = $skinPath . '/css';
        if (is_dir($cssPath)) {
            $cssFiles = glob($cssPath . '/*.css');
            foreach ($cssFiles as $cssFile) {
                $fileName = basename($cssFile);
                $assets['css'][] = "/skins/{$skinName}/css/{$fileName}";
            }
        }

        // JavaScript files
        $jsPath = $skinPath . '/js';
        if (is_dir($jsPath)) {
            $jsFiles = glob($jsPath . '/*.js');
            foreach ($jsFiles as $jsFile) {
                $fileName = basename($jsFile);
                $assets['js'][] = "/skins/{$skinName}/js/{$fileName}";
            }
        }

        // Images
        $imagesPath = $skinPath . '/images';
        if (is_dir($imagesPath)) {
            $imageFiles = glob($imagesPath . '/*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
            foreach ($imageFiles as $imageFile) {
                $fileName = basename($imageFile);
                $assets['images'][] = "/skins/{$skinName}/images/{$fileName}";
            }
        }

        return $assets;
    }

    /**
     * Get specific asset file content
     */
    public function getAssetContent(string $assetPath): ?string
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            $this->logger->warning('Asset file not found', ['path' => $assetPath]);
            return null;
        }

        try {
            $content = file_get_contents($fullPath);
            if ($content === false) {
                $this->logger->error('Failed to read asset file', ['path' => $assetPath]);
                return null;
            }
            return $content;
        } catch (\Exception $e) {
            $this->logger->error('Error reading asset file', ['path' => $assetPath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get asset file metadata
     */
    public function getAssetMetadata(string $assetPath): ?array
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $stat = stat($fullPath);
        if ($stat === false) {
            return null;
        }

        return [
            'size' => $stat['size'],
            'modified' => $stat['mtime'],
            'type' => mime_content_type($fullPath) ?: 'application/octet-stream',
            'path' => $assetPath
        ];
    }

    /**
     * Check if asset exists
     */
    public function assetExists(string $assetPath): bool
    {
        $fullPath = $this->assetsPath . $assetPath;
        return file_exists($fullPath);
    }

    /**
     * Get asset file size
     */
    public function getAssetSize(string $assetPath): ?int
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $size = filesize($fullPath);
        return $size === false ? null : $size;
    }

    /**
     * Get asset file modification time
     */
    public function getAssetModifiedTime(string $assetPath): ?int
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $mtime = filemtime($fullPath);
        return $mtime === false ? null : $mtime;
    }

    /**
     * Get asset file MIME type
     */
    public function getAssetMimeType(string $assetPath): ?string
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $mimeType = mime_content_type($fullPath);
        return $mimeType === false ? null : $mimeType;
    }

    /**
     * Get all assets for a skin
     */
    public function getAllSkinAssets(string $skinName = null): array
    {
        $skin = $skinName ? $this->skinManager->getSkin($skinName) : $this->skinManager->getActiveSkin();
        if (!$skin) {
            return [];
        }

        $assets = [];
        $skinPath = $skin['path'];
        $skinName = $skin['name'];

        // CSS files
        $cssPath = $skinPath . '/css';
        if (is_dir($cssPath)) {
            $cssFiles = glob($cssPath . '/*.css');
            foreach ($cssFiles as $cssFile) {
                $fileName = basename($cssFile);
                $assets['css'][] = [
                    'url' => "/skins/{$skinName}/css/{$fileName}",
                    'path' => $cssFile,
                    'size' => filesize($cssFile),
                    'modified' => filemtime($cssFile)
                ];
            }
        }

        // JavaScript files
        $jsPath = $skinPath . '/js';
        if (is_dir($jsPath)) {
            $jsFiles = glob($jsPath . '/*.js');
            foreach ($jsFiles as $jsFile) {
                $fileName = basename($jsFile);
                $assets['js'][] = [
                    'url' => "/skins/{$skinName}/js/{$fileName}",
                    'path' => $jsFile,
                    'size' => filesize($jsFile),
                    'modified' => filemtime($jsFile)
                ];
            }
        }

        // Images
        $imagesPath = $skinPath . '/images';
        if (is_dir($imagesPath)) {
            $imageFiles = glob($imagesPath . '/*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
            foreach ($imageFiles as $imageFile) {
                $fileName = basename($imageFile);
                $assets['images'][] = [
                    'url' => "/skins/{$skinName}/images/{$fileName}",
                    'path' => $imageFile,
                    'size' => filesize($imageFile),
                    'modified' => filemtime($imageFile)
                ];
            }
        }

        return $assets;
    }

    /**
     * Get asset cache key
     */
    public function getAssetCacheKey(string $assetPath): string
    {
        $modifiedTime = $this->getAssetModifiedTime($assetPath);
        $size = $this->getAssetSize($assetPath);
        
        return md5($assetPath . $modifiedTime . $size);
    }

    /**
     * Validate asset file
     */
    public function validateAsset(string $assetPath): bool
    {
        $fullPath = $this->assetsPath . $assetPath;
        
        if (!file_exists($fullPath)) {
            return false;
        }

        // Check if file is readable
        if (!is_readable($fullPath)) {
            return false;
        }

        // Check file size (prevent extremely large files)
        $size = filesize($fullPath);
        if ($size === false || $size > 10 * 1024 * 1024) { // 10MB limit
            return false;
        }

        return true;
    }
} 