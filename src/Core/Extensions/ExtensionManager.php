<?php

declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

use Container;\Container

/**
 * Extension Manager
 * 
 * Manages the loading, activation, and lifecycle of extensions.
 * 
 * @package IslamWiki\Core\Extensions
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ExtensionManager
{
    private Container $container;
    private array $extensions = [];
    private array $activeExtensions = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register an extension
     */
    public function register(string $name, ExtensionInterface $extension): void
    {
        $this->extensions[$name] = $extension;
    }

    /**
     * Activate an extension
     */
    public function activate(string $name): bool
    {
        if (!isset($this->extensions[$name])) {
            return false;
        }

        $extension = $this->extensions[$name];
        if ($extension->activate()) {
            $this->activeExtensions[$name] = $extension;
            return true;
        }

        return false;
    }

    /**
     * Deactivate an extension
     */
    public function deactivate(string $name): bool
    {
        if (!isset($this->activeExtensions[$name])) {
            return false;
        }

        $extension = $this->activeExtensions[$name];
        if ($extension->deactivate()) {
            unset($this->activeExtensions[$name]);
            return true;
        }

        return false;
    }

    /**
     * Get all extensions
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get active extensions
     */
    public function getActiveExtensions(): array
    {
        return $this->activeExtensions;
    }

    /**
     * Check if extension is active
     */
    public function isActive(string $name): bool
    {
        return isset($this->activeExtensions[$name]);
    }

    /**
     * Get extension by name
     */
    public function getExtension(string $name): ?ExtensionInterface
    {
        return $this->extensions[$name] ?? null;
    }

    /**
     * Boot all registered extensions
     */
    public function bootExtensions(): void
    {
        foreach ($this->extensions as $name => $extension) {
            try {
                if (method_exists($extension, 'boot')) {
                    $extension->boot();
                }
            } catch (\Exception $e) {
                // Log error but continue with other extensions
                error_log("Failed to boot extension {$name}: " . $e->getMessage());
            }
        }
    }
}
