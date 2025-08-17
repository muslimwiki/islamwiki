<?php

/**
 * Helper functions for IslamWiki
 *
 * This file contains utility functions that are used throughout the application.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Define BASE_PATH if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!function_exists('env')) {
    /**
     * Get an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage path.
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return BASE_PATH . '/storage' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $configPath = ROOT_PATH . '/config/app.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
            } else {
                $config = [];
            }
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('app')) {
    /**
     * Get the application instance or a service from the container.
     *
     * @param string|null $service
     * @return mixed
     */
    function app(string $service = null)
    {
        global $app;

        if ($service === null) {
            return $app;
        }

        return $app->getContainer()->get($service);
    }
}

if (!function_exists('view')) {
    /**
     * Get a view instance or render a view.
     *
     * @param string|null $view
     * @param array $data
     * @return mixed
     */
    function view(string $view = null, array $data = [])
    {
        $viewRenderer = app('view');

        if ($view === null) {
            return $viewRenderer;
        }

        return $viewRenderer->render($view, $data);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset(string $path): string
    {
        $baseUrl = rtrim(env('APP_URL', 'https://local.islam.wiki'), '/');
        $assetPath = ltrim($path, '/');

        // Use the new secure asset serving system (not public folder)
        return "{$baseUrl}/assets/{$assetPath}";
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL for the application.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function url(string $path = '', array $parameters = []): string
    {
        $baseUrl = rtrim(env('APP_URL', 'https://local.islam.wiki'), '/');
        $path = ltrim($path, '/');

        $url = "{$baseUrl}/{$path}";

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }
}



if (!function_exists('is_rtl')) {
    /**
     * Check if the current language is RTL.
     *
     * @return bool
     */
    function is_rtl(): bool
    {
        // Try to get from session first
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
            $language = $_SESSION['language'];
        } else {
            // Try to extract from current URI
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $uri = ltrim($uri, '/');
            $segments = explode('/', $uri);
            
            $supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];
            
            if (!empty($segments[0]) && in_array($segments[0], $supportedLanguages, true)) {
                $language = $segments[0];
            } else {
                $language = 'en';
            }
        }
        
        $rtlLanguages = ['ar', 'ur', 'fa', 'he'];
        
        return in_array($language, $rtlLanguages);
    }
}

if (!function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new \IslamWiki\Core\Http\Exceptions\HttpException($code, $message, null, $headers);
    }
}

if (!function_exists('abort_if')) {
    /**
     * Throw an HttpException if the given boolean is true.
     *
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     */
    function abort_if(bool $boolean, int $code, string $message = '', array $headers = []): void
    {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('abort_unless')) {
    /**
     * Throw an HttpException unless the given boolean is true.
     *
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     */
    function abort_unless(bool $boolean, int $code, string $message = '', array $headers = []): void
    {
        if (!$boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit(1);
    }
}
