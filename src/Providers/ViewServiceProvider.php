<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
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
    public function register(AsasContainer $container): void
    {
        // Get the base path from the application if available
        $basePath = null;
        
        try {
            if ($container->has('app')) {
                $app = $container->get('app');
                $basePath = $app->basePath();
            } else {
                // Fallback: try to find the project root
                $basePath = $this->findProjectRoot();
            }
        } catch (\Exception $e) {
            // If app binding fails, use fallback
            $basePath = $this->findProjectRoot();
        }
        
        // Ensure we have an absolute path
        $basePath = realpath($basePath);
        $templatePath = $basePath . '/resources/views';
        $cachePath = $basePath . '/storage/framework/views';
        
        // Create the cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        // In development, disable cache to avoid permission issues
        $isDebug = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production') !== 'production';
        
        $twigRenderer = new TwigRenderer(
            $templatePath,
            false, // Disable cache for now to avoid permission issues
            $isDebug
        );
        
        // Add CSRF token as a global variable
        try {
            if ($container->has('session')) {
                $session = $container->get('session');
                $twigRenderer->addGlobals([
                    'csrf_token' => $session->getCsrfToken()
                ]);
            }
        } catch (\Exception $e) {
            // If session is not available, skip CSRF token
        }
        
        // Register the Twig renderer instance with the container
        $container->instance('view', $twigRenderer);
        $container->instance(TwigRenderer::class, $twigRenderer);
    }
    
    /**
     * Find the project root directory.
     */
    private function findProjectRoot(): string
    {
        // Start from the current file and work backwards
        $currentDir = __DIR__;
        
        // Try different levels up
        $possiblePaths = [
            dirname($currentDir, 2), // src/Providers -> project root
            dirname($currentDir, 3), // src/Providers -> parent -> project root
            dirname($currentDir, 4), // src/Providers -> parent -> parent -> project root
            getcwd(), // Current working directory
        ];
        
        foreach ($possiblePaths as $path) {
            if (is_dir($path . '/resources/views') && is_dir($path . '/src')) {
                return $path;
            }
        }
        
        // If all else fails, use the current directory
        return getcwd();
    }
}
