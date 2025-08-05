<?php
declare(strict_types=1);

namespace IslamWiki\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * TwigRenderer handles the rendering of Twig templates.
 */
class TwigRenderer
{
    /** @var Environment */
    private $twig;
    
    /** @var string */
    private $templatePath;
    
    /** @var string|null */
    private $activeSkinLayoutPath = null;

    /**
     * Create a new TwigRenderer instance.
     * 
     * @param string $templatePath Path to the templates directory
     * @param string $cachePath Path to the cache directory (or false to disable cache)
     * @param bool $debug Whether to enable debug mode
     */
    public function __construct(
        string $templatePath,
        $cachePath = false,
        bool $debug = false
    ) {
        $this->templatePath = $templatePath;
        
        $loader = new FilesystemLoader($templatePath);
        
        // Add the skins directory to the loader so skin templates can be included
        $skinsPath = dirname($templatePath, 2) . '/skins';
        if (is_dir($skinsPath)) {
            $loader->addPath($skinsPath);
        }
        
        $this->twig = new Environment($loader, [
            'cache' => $cachePath,
            'debug' => $debug,
            'auto_reload' => $debug,
        ]);
        
        // Add any global variables or functions here
        $this->addGlobalFunctions();
    }
    
    /**
     * Set the active skin layout path
     */
    public function setActiveSkinLayoutPath(?string $layoutPath): void
    {
        $this->activeSkinLayoutPath = $layoutPath;
        
        if ($layoutPath && is_dir($layoutPath)) {
            // Add the skin templates directory to the loader
            $loader = $this->twig->getLoader();
            if ($loader instanceof FilesystemLoader) {
                // Try prependPath instead of addPath
                $loader->prependPath($layoutPath, 'skin');
                
                error_log("TwigRenderer::setActiveSkinLayoutPath - Added skin path: $layoutPath with namespace 'skin'");
                
                // Debug: Check if the path was added
                $paths = $loader->getPaths();
                error_log("TwigRenderer::setActiveSkinLayoutPath - Current paths: " . print_r($paths, true));
            }
        } else {
            error_log("TwigRenderer::setActiveSkinLayoutPath - Invalid path or directory: $layoutPath");
        }
    }
    
    /**
     * Get the active skin layout path
     */
    public function getActiveSkinLayoutPath(): ?string
    {
        return $this->activeSkinLayoutPath;
    }

    /**
     * Render a template with the given data.
     * 
     * @param string $template The template path relative to the template directory
     * @param array $data The data to pass to the template
     * @return string The rendered template
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
    
    /**
     * Render a template with skin layout support.
     * 
     * @param string $template The template path relative to the template directory
     * @param array $data The data to pass to the template
     * @return string The rendered template
     */
    public function renderWithSkin(string $template, array $data = []): string
    {
        // Get skin data from the container if available
        try {
            // Try to get the application instance from the global scope
            if (isset($GLOBALS['app']) && $GLOBALS['app'] instanceof \IslamWiki\Core\Application) {
                $app = $GLOBALS['app'];
                $container = $app->getContainer();
                
                if ($container->has('skin.data')) {
                    $skinData = $container->get('skin.data');
                    
                    // Update the global variables so the template can access them
                    $this->addGlobals([
                        'skin_css_url' => $skinData['css_url'] ?? '',
                        'skin_js_url' => $skinData['js_url'] ?? '',
                        'active_skin' => $skinData['name'] ?? 'default',
                        'skin_version' => $skinData['version'] ?? '0.0.29',
                        'skin_config' => $skinData['config'] ?? [],
                    ]);
                    
                    // Also add to template data for backward compatibility
                    $data['skin_css_url'] = $skinData['css_url'] ?? '';
                    $data['skin_js_url'] = $skinData['js_url'] ?? '';
                    $data['active_skin'] = $skinData['name'] ?? 'default';
                    $data['skin_version'] = $skinData['version'] ?? '0.0.29';
                    $data['skin_config'] = $skinData['config'] ?? [];
                    
                    // Check if we have a skin layout path and use it
                    if ($this->activeSkinLayoutPath && is_file($this->activeSkinLayoutPath . '/layout.twig')) {
                        // Set the skin layout path for the template to use
                        $data['skin_layout_path'] = $this->activeSkinLayoutPath;
                        
                        // Create a wrapper template that extends the skin layout
                        $wrapperTemplate = "{% extends 'skin:layout.twig' %}\n{% block content %}{% include '" . $template . "' %}{% endblock %}";
                        
                        // Create a temporary template name
                        $tempTemplateName = 'temp_' . uniqid();
                        
                        // Store the original loader
                        $originalLoader = $this->twig->getLoader();
                        
                        // Create a new loader that includes both the original paths and our temporary template
                        $arrayLoader = new \Twig\Loader\ArrayLoader([
                            $tempTemplateName => $wrapperTemplate
                        ]);
                        
                        // Create a chain loader to combine both loaders
                        $chainLoader = new \Twig\Loader\ChainLoader([$arrayLoader, $originalLoader]);
                        
                        // Set the chain loader
                        $this->twig->setLoader($chainLoader);
                        
                        // Render the wrapper template
                        $result = $this->twig->render($tempTemplateName, $data);
                        
                        // Restore the original loader
                        $this->twig->setLoader($originalLoader);
                        
                        return $result;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('TwigRenderer::renderWithSkin - Error getting skin data: ' . $e->getMessage());
        }
        
        // Fallback to regular rendering if no skin layout is available
        return $this->render($template, $data);
    }

    /**
     * Add global functions to the Twig environment.
     */
    private function addGlobalFunctions(): void
    {
        // Example of adding a global function
        $this->twig->addFunction(new TwigFunction('asset', function (string $path) {
            // This will be replaced with your actual asset URL logic
            return '/assets/' . ltrim($path, '/');
        }));
        
        // Add function to check if current page matches a path
        $this->twig->addFunction(new TwigFunction('is_current_page', function (string $path) {
            $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
            return $currentPath === $path;
        }));
        
        // Add function to get skin layout path
        $this->twig->addFunction(new TwigFunction('get_skin_layout', function () {
            return $this->activeSkinLayoutPath ? 'skin:layout.twig' : 'layouts/app.twig';
        }));
    }
    
    /**
     * Add global variables to the Twig environment.
     */
    public function addGlobals(array $globals): void
    {
        foreach ($globals as $key => $value) {
            $this->twig->addGlobal($key, $value);
        }
    }
    
    /**
     * Get the Twig environment instance.
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
