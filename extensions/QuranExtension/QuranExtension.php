<?php

namespace IslamWiki\Extensions\QuranExtension;

use IslamWiki\Core\Extensions\Extension;
use Container;\Container
use QuranExtension\BreadcrumbsExtension;

class QuranExtension extends Extension
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * @var BreadcrumbsExtension
     */
    private $breadcrumbsExtension;

    /**
     * Called when the extension is initialized
     */
    protected function onInitialize(): void
    {
        try {
            // Note: Routes will be registered when registerRoutes() is called externally
            // after the router is available in the container

            // Initialize breadcrumbs extension
            $this->breadcrumbsExtension = new BreadcrumbsExtension();

            // Register views
            $this->registerViews();

            error_log("QuranExtension initialized successfully");
        } catch (\Exception $e) {
            error_log("QuranExtension initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Get the breadcrumbs extension
     * 
     * @return BreadcrumbsExtension
     */
    public function getBreadcrumbsExtension(): BreadcrumbsExtension
    {
        return $this->breadcrumbsExtension;
    }

    /**
     * Called when the extension is disabled
     */
    protected function onDisable(): void
    {
        try {
            // Remove routes
            $this->unregisterRoutes();

            // Remove views
            $this->unregisterViews();

            error_log("QuranExtension disabled successfully");
        } catch (\Exception $e) {
            error_log("QuranExtension disable failed: " . $e->getMessage());
        }
    }

    /**
     * Public method to register routes when router is available
     * Note: Routes are now registered in the main routes/web.php file to prevent
     * conflicts with catch-all wiki routes
     */
    public function registerRoutes(): bool
    {
        try {
            // Routes are now handled in the main routes file to ensure proper precedence
            // over catch-all wiki routes
            error_log("QuranExtension routes are now registered in main routes file");
            return true;
        } catch (\Exception $e) {
            error_log("QuranExtension route registration failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Protected method for internal use
     */
    protected function registerRoutesInternal()
    {
        return $this->registerRoutes();
    }

    protected function unregisterRoutes()
    {
        // Routes will be handled by the main router
        // This is a placeholder for future implementation
    }

    protected function registerViews()
    {
        try {
            // Views will be handled by the main view manager
            // This is a placeholder for future implementation
            error_log("QuranExtension views registered");
        } catch (\Exception $e) {
            error_log("QuranExtension view registration failed: " . $e->getMessage());
        }
    }

    protected function unregisterViews()
    {
        // Views will be handled by the main view manager
        // This is a placeholder for future implementation
    }
}
