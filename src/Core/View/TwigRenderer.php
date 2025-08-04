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
        // If we have an active skin layout, try to use it
        if ($this->activeSkinLayoutPath && file_exists($this->activeSkinLayoutPath . '/layout.twig')) {
            // Add the skin layout path to the data
            $data['skin_layout_path'] = 'skin:layout.twig';
        }
        
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
