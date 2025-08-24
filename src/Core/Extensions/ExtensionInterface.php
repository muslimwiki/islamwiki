<?php

/**
 * Extension Interface
 *
 * Defines the contract for all extensions in IslamWiki.
 *
 * @package IslamWiki\Core\Extensions
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

use IslamWiki\Core\Container\Container;

/**
 * Extension Interface - Contract for all extensions
 */
interface ExtensionInterface
{
    /**
     * Get the extension name.
     *
     * @return string The extension name
     */
    public function getName(): string;

    /**
     * Get the extension version.
     *
     * @return string The extension version
     */
    public function getVersion(): string;

    /**
     * Get the extension description.
     *
     * @return string The extension description
     */
    public function getDescription(): string;

    /**
     * Get the extension author.
     *
     * @return string The extension author
     */
    public function getAuthor(): string;

    /**
     * Get the extension website.
     *
     * @return string The extension website
     */
    public function getWebsite(): string;

    /**
     * Register the extension with the container.
     *
     * @param Container $container The container instance
     */
    public function register(Container $container): void;

    /**
     * Boot the extension.
     *
     * @param Container $container The container instance
     */
    public function boot(Container $container): void;

    /**
     * Check if the extension is enabled.
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool;

    /**
     * Enable the extension.
     */
    public function enable(): void;

    /**
     * Disable the extension.
     */
    public function disable(): void;
} 