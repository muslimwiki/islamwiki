<?php
declare(strict_types=1);

/**
 * Settings Controller
 * 
 * Handles user settings and skin management.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Application;
use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Response;
use IslamWiki\Skins\SkinManager;

class SettingsController extends Controller
{
    private SkinManager $skinManager;

    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        $this->skinManager = $container->get('skin.manager');
    }

    /**
     * Display the settings page
     */
    public function index(): Response
    {
        $availableSkins = $this->skinManager->getSkins();
        $activeSkin = $this->skinManager->getActiveSkinName();
        
        $skinOptions = [];
        foreach ($availableSkins as $name => $skin) {
            $skinOptions[$name] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => strtolower($name) === strtolower($activeSkin)
            ];
        }

        return $this->view('settings/index', [
            'title' => 'Settings - IslamWiki',
            'skinOptions' => $skinOptions,
            'activeSkin' => $activeSkin,
            'availableSkins' => $availableSkins
        ]);
    }

    /**
     * Update skin setting
     */
    public function updateSkin(): Response
    {
        $request = $this->container->get('request');
        $skinName = $request->getParsedBody()['skin'] ?? null;

        if (!$skinName) {
            return $this->json(['error' => 'Skin name is required'], 400);
        }

        $availableSkins = $this->skinManager->getSkins();
        
        if (!isset($availableSkins[$skinName])) {
            return $this->json(['error' => 'Invalid skin selected'], 400);
        }

        // Update LocalSettings.php
        $localSettingsPath = dirname(__DIR__, 3) . '/LocalSettings.php';
        $localSettingsContent = file_get_contents($localSettingsPath);
        
        // Replace the active skin setting
        $pattern = '/\$wgActiveSkin\s*=\s*env\(\'ACTIVE_SKIN\',\s*\'[^\']+\'\);/';
        $replacement = "\$wgActiveSkin = env('ACTIVE_SKIN', '$skinName');";
        
        if (preg_match($pattern, $localSettingsContent)) {
            $localSettingsContent = preg_replace($pattern, $replacement, $localSettingsContent);
            file_put_contents($localSettingsPath, $localSettingsContent);
            
            return $this->json([
                'success' => true,
                'message' => "Skin updated to $skinName successfully",
                'activeSkin' => $skinName
            ]);
        } else {
            return $this->json(['error' => 'Could not update skin setting'], 500);
        }
    }

    /**
     * Get skin information
     */
    public function getSkinInfo(string $skinName): Response
    {
        $availableSkins = $this->skinManager->getSkins();
        
        if (!isset($availableSkins[$skinName])) {
            return $this->json(['error' => 'Skin not found'], 404);
        }

        $skin = $availableSkins[$skinName];
        
        return $this->json([
            'name' => $skin->getName(),
            'version' => $skin->getVersion(),
            'author' => $skin->getAuthor(),
            'description' => $skin->getDescription(),
            'config' => $skin->getConfig(),
            'features' => $skin->getFeatures(),
            'dependencies' => $skin->getDependencies(),
            'hasCustomCss' => $skin->hasCustomCss(),
            'hasCustomJs' => $skin->hasCustomJs(),
            'hasCustomLayout' => $skin->hasCustomLayout()
        ]);
    }

    /**
     * Get all available skins
     */
    public function getAvailableSkins(): Response
    {
        $availableSkins = $this->skinManager->getSkins();
        $activeSkin = $this->skinManager->getActiveSkinName();
        
        $skins = [];
        foreach ($availableSkins as $name => $skin) {
            $skins[$name] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => strtolower($name) === strtolower($activeSkin)
            ];
        }
        
        return $this->json($skins);
    }
} 