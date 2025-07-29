<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container;
use IslamWiki\Core\View\TwigRenderer;

/**
 * ViewServiceProvider registers view-related services with the container.
 */
class ViewServiceProvider
{
    /**
     * Register view services with the container.
     *
     * @param Container $container The dependency injection container
     */
    public function register(Container $container): void
    {
        // Use a relative path approach - assume we're in the project root
        $basePath = dirname(__DIR__, 2); // Go up from src/Providers to project root
        $templatePath = $basePath . '/resources/views';
        $cachePath = $basePath . '/storage/framework/views';
        
        // Create the cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        // In development, disable cache to avoid permission issues
        $isDebug = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
        
        $twigRenderer = new TwigRenderer(
            $templatePath,
            false, // Disable cache for now to avoid permission issues
            $isDebug
        );
        
        // Register the Twig renderer instance with the container
        $container->instance('view', $twigRenderer);
        $container->instance(TwigRenderer::class, $twigRenderer);
    }
}
