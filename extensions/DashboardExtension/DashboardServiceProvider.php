<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\DashboardExtension;

/**
 * Dashboard Service Provider
 *
 * Registers dashboard-related services with the application container.
 * This is a simplified version that follows the existing extension patterns.
 */
class DashboardServiceProvider
{
    /**
     * Register dashboard services
     *
     * @param mixed $container Application container
     */
    public function register($container): void
    {
        // For now, we'll use a simplified approach
        // Services will be created on-demand when needed
    }

    /**
     * Boot dashboard services
     *
     * @param mixed $container Application container
     */
    public function boot($container): void
    {
        // Initialize dashboard services after registration
        // This will be called by the main extension
    }
} 