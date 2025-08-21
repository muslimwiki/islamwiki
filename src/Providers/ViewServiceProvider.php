<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\View\TwigRenderer;
use IslamWiki\Core\View\TwigTranslationExtension;

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

        // Add translation extension directly to ensure it works
        try {
            $translationService = new \IslamWiki\Core\Language\TranslationService('en');
            $translationExtension = new \IslamWiki\Core\View\TwigTranslationExtension($translationService);
            $twigRenderer->addExtension($translationExtension);
            error_log("ViewServiceProvider: Successfully added TwigTranslationExtension directly");
        } catch (\Exception $e) {
            error_log("ViewServiceProvider: Could not add translation extension directly: " . $e->getMessage());
        }

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
        $container->set('view', $twigRenderer);
        $container->set(TwigRenderer::class, $twigRenderer);
    }

    /**
     * Boot the view services.
     *
     * @param AsasContainer $container The dependency injection container
     */
    public function boot(AsasContainer $container): void
    {
        // Add translation extension if available (after all services are registered)
        try {
            error_log("ViewServiceProvider: Checking for TwigTranslationExtension");
            if ($container->has(TwigTranslationExtension::class)) {
                error_log("ViewServiceProvider: Found TwigTranslationExtension, adding to TwigRenderer");
                $translationExtension = $container->get(TwigTranslationExtension::class);
                $twigRenderer = $container->get(TwigRenderer::class);
                $twigRenderer->addExtension($translationExtension);
                error_log("ViewServiceProvider: Successfully added TwigTranslationExtension to TwigRenderer");
            } else {
                error_log("ViewServiceProvider: TwigTranslationExtension not found in container");
            }
        } catch (\Exception $e) {
            // If translation extension is not available, skip it
            error_log("ViewServiceProvider: Could not add translation extension: " . $e->getMessage());
        }
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
