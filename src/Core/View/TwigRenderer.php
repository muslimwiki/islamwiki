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
        $loader = new FilesystemLoader($templatePath);
        
        $this->twig = new Environment($loader, [
            'cache' => $cachePath,
            'debug' => $debug,
            'auto_reload' => $debug,
        ]);
        
        // Add any global variables or functions here
        $this->addGlobalFunctions();
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
            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
            return strpos($currentUri, $path) === 0;
        }));
        
        // Add function to get current URI
        $this->twig->addFunction(new TwigFunction('current_uri', function () {
            return $_SERVER['REQUEST_URI'] ?? '/';
        }));
    }

    /**
     * Get the underlying Twig environment.
     * 
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
