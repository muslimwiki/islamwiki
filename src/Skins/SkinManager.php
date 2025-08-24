<?php

/**
 * Skin Manager
 *
 * Manages skin loading, registration, and configuration.
 *
 * @package IslamWiki\Skins
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Skins;

use Application;\Application

class SkinManager
{
    private Application $app;
    private array $skins = [];
    private string $activeSkin;
    private ?Skin $currentSkin = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->loadSkins();
        $this->initializeFromLocalSettings();
    }

    private function loadSkins(): void
    {
        global $wgValidSkins;
        $validSkins = $wgValidSkins ?? [];

        if (empty($validSkins)) {
            return;
        }

        $skinsPath = $this->app->basePath('skins');
        if (!is_dir($skinsPath)) {
            return;
        }

        foreach ($validSkins as $skinName => $skinDisplayName) {
            $skinDir = $skinsPath . '/' . $skinName;
            $skinConfigFile = $skinDir . '/skin.json';

            if (!is_dir($skinDir) || !file_exists($skinConfigFile)) {
                continue;
            }

            try {
                $config = json_decode(file_get_contents($skinConfigFile), true);
                if ($config && isset($config['name'])) {
                    $skin = new UserSkin($config, $skinDir);
                    if ($skin->validate()) {
                        $this->skins[$skinName] = $skin;
                        $this->skins[strtolower($skinName)] = $skin;
                    }
                }
            } catch (\Exception $e) {
                // Continue loading other skins
            }
        }
    }

    public function getSkins(): array
    {
        return $this->skins;
    }

    public function getSkin(string $name): ?Skin
    {
        return $this->skins[$name] ?? $this->skins[strtolower($name)] ?? null;
    }

    public function getActiveSkinName(): string
    {
        return $this->activeSkin;
    }

    public function setActiveSkin(string $name): bool
    {
        if (!$this->hasSkin($name)) {
            return false;
        }

        $this->activeSkin = $name;
        $this->currentSkin = null;
        $this->updateContainerCache();
        return true;
    }

    public function getActiveSkin(): ?Skin
    {
        if ($this->currentSkin === null) {
            $this->currentSkin = $this->getSkin($this->activeSkin);
        }
        return $this->currentSkin;
    }

    private function initializeFromLocalSettings(): void
    {
        global $wgActiveSkin;
        $this->activeSkin = $wgActiveSkin ?? 'Bismillah';
    }

    public static function getActiveSkinNameStatic(Application $app): string
    {
        try {
            $container = $app->getContainer();
            $skinManager = $container->get('skin.manager');
            return $skinManager->getActiveSkinName();
        } catch (\Exception $e) {
            global $wgActiveSkin;
            return $wgActiveSkin ?? 'Bismillah';
        }
    }

    public static function setActiveSkinStatic(Application $app, string $skinName): bool
    {
        try {
            $container = $app->getContainer();
            $skinManager = $container->get('skin.manager');
            return $skinManager->setActiveSkin($skinName);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getActiveSkinForUser(int $userId): ?Skin
    {
        try {
            $stmt = $this->app->getContainer()->get('db')->prepare("
                SELECT settings FROM user_settings WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $settings = json_decode($result['settings'], true) ?? [];
                $userSkin = $settings['skin'] ?? null;
                if ($userSkin) {
                    return $this->getSkin($userSkin);
                }
            }
        } catch (\Throwable $e) {
            // Fallback to global default
        }

        return $this->getActiveSkin();
    }

    public function getActiveSkinNameForUser(int $userId): string
    {
        try {
            $db = $this->app->getContainer()->get('db');
            $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $settings = json_decode($result['settings'], true) ?? [];
                $userSkin = $settings['skin'] ?? null;
                if ($userSkin) {
                    $skin = $this->getSkin($userSkin);
                    return $skin ? $skin->getName() : strtolower($userSkin);
                }
            }
        } catch (\Throwable $e) {
            // Fallback to global default
        }

        return $this->getActiveSkinName();
    }

    public function hasSkin(string $name): bool
    {
        return isset($this->skins[$name]) || isset($this->skins[strtolower($name)]);
    }

    public function skinExists(string $name): bool
    {
        return $this->hasSkin($name);
    }

    public function getActiveSkinCss(): string
    {
        $skin = $this->getActiveSkin();
        return $skin ? $skin->getCssContent() : '';
    }

    public function getActiveSkinJs(): string
    {
        $skin = $this->getActiveSkin();
        return $skin ? $skin->getJsContent() : '';
    }

    public function getActiveSkinLayoutPath(): string
    {
        $skin = $this->getActiveSkin();
        return $skin ? $skin->getLayoutPath() : '';
    }

    public function hasActiveSkinCustomLayout(): bool
    {
        $skin = $this->getActiveSkin();
        return $skin ? $skin->hasCustomLayout() : false;
    }

    public function getAvailableSkinNames(): array
    {
        return array_keys($this->skins);
    }

    public function getAllSkinMetadata(): array
    {
        $metadata = [];
        foreach ($this->skins as $name => $skin) {
            $metadata[$name] = $skin->getMetadata();
        }
        return $metadata;
    }

    public function registerSkin(string $name, Skin $skin): void
    {
        $this->skins[strtolower($name)] = $skin;
    }

    public function unregisterSkin(string $name): bool
    {
        $lowerName = strtolower($name);
        $removed = false;

        if (isset($this->skins[$name])) {
            unset($this->skins[$name]);
            $removed = true;
        }

        if (isset($this->skins[$lowerName])) {
            unset($this->skins[$lowerName]);
            $removed = true;
        }

        if ($this->activeSkin === $name || $this->activeSkin === $lowerName) {
            $this->setActiveSkin('Bismillah');
        }

        return $removed;
    }

    private function updateContainerCache(): void
    {
        $container = $this->app->getContainer();
        $activeSkin = $this->getActiveSkin();

        if ($container->has('skin.manager')) {
            $container->instance('skin.manager', $this);
        }

        if ($container->has('skin.active')) {
            $container->instance('skin.active', $activeSkin);
        }

        if ($container->has('skin.data')) {
            $skinData = [
                'css_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/css/' . strtolower($activeSkin->getName()) . '.css' : '',
                'js_url' => $activeSkin ? '/skins/' . $activeSkin->getName() . '/js/' . strtolower($activeSkin->getName()) . '.js' : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.28',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            $container->instance('skin.data', $skinData);
        }
    }

    public function forceReload(): void
    {
        $this->currentSkin = null;
        $this->loadSkins();
        $this->updateContainerCache();
    }
}
