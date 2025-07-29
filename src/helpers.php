<?php
/**
 * Helper functions for the IslamWiki application.
 *
 * This file contains global helper functions that are used throughout the application.
 * These functions provide common functionality that can be accessed from any part of the application.
 */

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
        }

        return $value;
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
            $config = require ROOT_PATH . '/config/app.php';
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
        $baseUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
        $assetPath = ltrim($path, '/');
        
        return "{$baseUrl}/public/{$assetPath}";
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
        $baseUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
        $path = ltrim($path, '/');
        
        $url = "{$baseUrl}/{$path}";
        
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return $url;
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
     * @throws \IslamWiki\Core\Exception\HttpException
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new \IslamWiki\Core\Exception\HttpException($code, $message, null, $headers);
    }
}

if (!function_exists('abort_if')) {
    /**
     * Throw an HttpException with the given data if the given condition is true.
     *
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     * @throws \IslamWiki\Core\Exception\HttpException
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
     * Throw an HttpException with the given data unless the given condition is true.
     *
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     * @throws \IslamWiki\Core\Exception\HttpException
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
     * Dump the passed variables and end the script.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        
        die(1);
    }
}
