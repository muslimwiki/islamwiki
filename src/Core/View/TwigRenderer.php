<?php

/**
 * TwigRenderer - Template rendering system for IslamWiki
 *
 * @category  IslamWiki
 * @package   Core\View
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://islam.wiki
 * @since     0.0.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * TwigRenderer handles the rendering of Twig templates.
 *
 * @category  IslamWiki
 * @package   Core\View
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://islam.wiki
 * @since     0.0.1
 */
class TwigRenderer
{
    /** @var Environment */
    private $_twig;

    /** @var string */
    private $_templatePath;

    /** @var string|null */
    private $_activeSkinLayoutPath = null;

    /**
     * Create a new TwigRenderer instance.
     *
     * @param string $templatePath Path to the templates directory
     * @param string $cachePath    Path to the cache directory (or false to disable cache)
     * @param bool   $debug        Whether to enable debug mode
     */
    public function __construct(
        string $templatePath,
        $cachePath = false,
        bool $debug = false
    ) {
        $this->_templatePath = $templatePath;

        $loader = new FilesystemLoader($templatePath);

        // Add the skins directory to the loader so skin templates can be included
        $skinsPath = dirname($templatePath, 2) . '/skins';
        if (is_dir($skinsPath)) {
            $loader->addPath($skinsPath);
        }

        $this->_twig = new Environment($loader, [
            'cache' => $cachePath,
            'debug' => $debug,
            'auto_reload' => $debug,
        ]);

        // Add any global variables or functions here
        $this->addGlobalFunctions();

        // Add global variables
        $this->addGlobals([
            'app' => [
                'debug' => $debug,
                'environment' => $_ENV['APP_ENV'] ?? 'production'
            ],
            'user' => null // Will be updated by middleware
        ]);

        // Always add translation extension to ensure it works
        try {
            $translationService = new \IslamWiki\Core\Language\TranslationService('en');
            $translationExtension = new \IslamWiki\Core\View\TwigTranslationExtension($translationService);
            $this->_twig->addExtension($translationExtension);
            error_log("TwigRenderer: Successfully added TwigTranslationExtension");
        } catch (\Exception $e) {
            error_log("TwigRenderer: Could not add translation extension: " . $e->getMessage());
        }
    }

    /**
     * Set the active skin layout path
     *
     * @param string|null $layoutPath The layout path to set
     *
     * @return void
     */
    public function setActiveSkinLayoutPath(?string $layoutPath): void
    {
        $this->_activeSkinLayoutPath = $layoutPath;

        if ($layoutPath && is_dir($layoutPath)) {
            // Add the skin templates directory to the loader
            $loader = $this->_twig->getLoader();
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
     *
     * @return string|null
     */
    public function getActiveSkinLayoutPath(): ?string
    {
        return $this->_activeSkinLayoutPath;
    }

    /**
     * Render a template with data
     */
    public function render(string $template, array $data = []): string
    {
        try {
            // Check if we need to update the translation language from session
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
                $sessionLanguage = $_SESSION['language'];
                $this->updateTranslationLanguage($sessionLanguage);
            }
            
            return $this->_twig->render($template, $data);
        } catch (\Exception $e) {
            error_log("TwigRenderer::render error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Render a template with skin support.
     *
     * @param string $template The template name
     * @param array  $data     The data to pass to the template
     *
     * @return string
     */
    public function renderWithSkin(string $template, array $data = []): string
    {
        // If we have an active skin layout path, try to render with skin template first
        if ($this->_activeSkinLayoutPath) {
            $skinTemplate = 'skin::' . $template;
            try {
                return $this->_twig->render($skinTemplate, $data);
            } catch (\Twig\Error\LoaderError $e) {
                // If skin template doesn't exist, fall back to default template
                error_log("TwigRenderer::renderWithSkin - Skin template not found: $skinTemplate, falling back to default");
            }
        }

        // Fall back to default template
        return $this->render($template, $data);
    }

    /**
     * Add global functions to Twig.
     *
     * @return void
     */
    private function addGlobalFunctions(): void
    {
        // Add custom functions here
        $this->_twig->addFunction(new TwigFunction('asset', function ($path) {
            return '/assets/' . ltrim($path, '/');
        }));

        $this->_twig->addFunction(new TwigFunction('url', function ($path) {
            return '/' . ltrim($path, '/');
        }));

        $this->_twig->addFunction(new TwigFunction('csrf_token', function () {
            // This would typically get the CSRF token from the session
            return $_SESSION['csrf_token'] ?? '';
        }));

        $this->_twig->addFunction(new TwigFunction('auth_check', function () {
            return isset($_SESSION['user_id']) && isset($_SESSION['username']);
        }));

        $this->_twig->addFunction(new TwigFunction('auth_user', function () {
            if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
                return [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'is_admin' => $_SESSION['is_admin'] ?? false
                ];
            }
            return null;
        }));

        $this->_twig->addFunction(new TwigFunction('config', function ($key, $default = null) {
            // This would typically get config from the application
            return $_ENV[$key] ?? $default;
        }));

        $this->_twig->addFunction(new TwigFunction('format_date', function ($date, $format = 'Y-m-d H:i:s') {
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            return $date->format($format);
        }));

        $this->_twig->addFunction(new TwigFunction('format_number', function ($number, $decimals = 2) {
            return number_format($number, $decimals);
        }));

        $this->_twig->addFunction(new TwigFunction('truncate', function ($text, $length = 100, $suffix = '...') {
            if (strlen($text) <= $length) {
                return $text;
            }
            return substr($text, 0, $length) . $suffix;
        }));

        $this->_twig->addFunction(new TwigFunction('is_current_page', function ($path) {
            $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
            return $currentPath === $path;
        }));

        $this->_twig->addFunction(new TwigFunction('lang_url', function ($path) {
            // Get current language from session or URI
            $currentLanguage = 'en';
            
            // Try to get from session first
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
                $currentLanguage = $_SESSION['language'];
            } else {
                // Try to extract from current URI
                $uri = $_SERVER['REQUEST_URI'] ?? '/';
                $uri = ltrim($uri, '/');
                $segments = explode('/', $uri);
                
                $supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];
                
                if (!empty($segments[0]) && in_array($segments[0], $supportedLanguages, true)) {
                    $currentLanguage = $segments[0];
                }
            }
            
            // Always include language prefix for consistency
            // This makes switching between languages symmetrical
            $path = ltrim($path, '/');
            $langPath = $currentLanguage . '/' . $path;
            
            return url($langPath);
        }));

        $this->_twig->addFunction(new TwigFunction('current_language', function () {
            // Try to get from session first
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
                return $_SESSION['language'];
            }
            
            // Try to extract from current URI
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $uri = ltrim($uri, '/');
            $segments = explode('/', $uri);
            
            $supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];
            
            if (!empty($segments[0]) && in_array($segments[0], $supportedLanguages, true)) {
                return $segments[0];
            }
            
            // Default to English if no language detected
            return 'en';
        }));
    }

    /**
     * Add a Twig extension.
     *
     * @param \Twig\Extension\ExtensionInterface $extension The extension to add
     *
     * @return void
     */
    public function addExtension(\Twig\Extension\ExtensionInterface $extension): void
    {
        $this->_twig->addExtension($extension);
    }

    /**
     * Add global variables to Twig.
     *
     * @param array $globals The global variables to add
     *
     * @return void
     */
    public function addGlobals(array $globals): void
    {
        foreach ($globals as $name => $value) {
            $this->_twig->addGlobal($name, $value);
        }
    }

    /**
     * Get the Twig environment
     */
    public function getTwig(): \Twig\Environment
    {
        return $this->_twig;
    }

    /**
     * Update the translation service language
     */
    public function updateTranslationLanguage(string $language): void
    {
        try {
            // Find the TwigTranslationExtension and update its language
            $extensions = $this->_twig->getExtensions();
            foreach ($extensions as $extension) {
                if ($extension instanceof \IslamWiki\Core\View\TwigTranslationExtension) {
                    $extension->updateLanguage($language);
                    error_log("TwigRenderer: Updated translation language to: $language");
                    break;
                }
            }
        } catch (\Exception $e) {
            error_log("TwigRenderer: Error updating translation language: " . $e->getMessage());
        }
    }
}
