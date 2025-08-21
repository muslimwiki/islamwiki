<?php

declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

/**
 * Extension Interface
 * 
 * Base interface that all extensions must implement.
 * 
 * @package IslamWiki\Core\Extensions
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
interface ExtensionInterface
{
    /**
     * Get extension name
     */
    public function getName(): string;

    /**
     * Get extension version
     */
    public function getVersion(): string;

    /**
     * Get extension description
     */
    public function getDescription(): string;

    /**
     * Install the extension
     */
    public function install(): bool;

    /**
     * Uninstall the extension
     */
    public function uninstall(): bool;

    /**
     * Activate the extension
     */
    public function activate(): bool;

    /**
     * Deactivate the extension
     */
    public function deactivate(): bool;

    /**
     * Initialize the extension
     */
    public function init(): void;
} 