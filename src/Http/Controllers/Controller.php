<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\View\TwigRenderer;

abstract class Controller
{
    /**
     * The database connection instance.
     */
    protected Connection $db;

    /**
     * The container instance.
     */
    protected AsasContainer $container;

    /**
     * The Twig renderer instance.
     */
    protected ?TwigRenderer $view = null;

    /**
     * Create a new controller instance.
     *
     * @param Connection $db The database connection
     * @param \IslamWiki\Core\Container\AsasContainer $container The dependency injection container
     */
    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        $this->db = $db;
        $this->container = $container;
    }

    /**
     * Send a JSON response.
     */
    protected function json($data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    /**
     * Get the view renderer instance.
     *
     * @return TwigRenderer
     */
    protected function getView(): TwigRenderer
    {
        if ($this->view === null) {
            // Use the 'view' alias that was set up in ViewServiceProvider
            $this->view = $this->container->get('view');
        }

        return $this->view;
    }

    /**
     * Send a view response.
     *
     * @param string $view The template path relative to the views directory (e.g., 'pages/home')
     * @param array $data The data to pass to the view
     * @param int $status The HTTP status code (default: 200)
     * @param array $headers Additional HTTP headers
     * @return Response
     * @throws \Throwable If the view cannot be rendered
     */
    protected function view(string $view, array $data = [], int $status = 200, array $headers = []): Response
    {
        try {
            // Ensure the view has the correct file extension
            $template = str_ends_with($view, '.twig') ? $view : "{$view}.twig";

            // Automatically include user data in all views
            if (!isset($data['user'])) {
                try {
                    $auth = $this->container->get('auth');
                    $data['user'] = $auth->user();
                } catch (\Exception $e) {
                    error_log('Error getting user for view: ' . $e->getMessage());
                    $data['user'] = null;
                }
            }

            // Automatically include current language in all views
            if (!isset($data['current_language'])) {
                try {
                    // First try to get language from session
                    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
                        $data['current_language'] = $_SESSION['language'];
                    } else {
                        // Fallback to LanguageService if session doesn't have language
                        $languageService = $this->container->get(\IslamWiki\Core\Language\LanguageService::class);
                        $data['current_language'] = $languageService->getCurrentLanguage();
                    }
                } catch (\Exception $e) {
                    error_log('Error getting current language for view: ' . $e->getMessage());
                    $data['current_language'] = 'en';
                }
            }

            // Render the template using TwigRenderer with skin support
            $content = $this->getView()->renderWithSkin($template, $data);

            // Create and return the response
            $response = new Response();
            $response->getBody()->write($content);

            $response = $response->withHeader('Content-Type', 'text/html');

            foreach ($headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }

            return $response->withStatus($status);
        } catch (\Throwable $e) {
            // Log the error with more context
            $errorMessage = sprintf(
                'Error rendering view %s: %s in %s:%d\nStack trace:\n%s\n',
                $view,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            error_log($errorMessage);

            // Return a detailed error response for debugging
            $response = new Response();
            $response->getBody()->write('<h1>Error Rendering View</h1>');
            $response->getBody()->write('<pre>' . htmlspecialchars($errorMessage) . '</pre>');

            return $response->withStatus(500);
        }
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())
            ->withHeader('Location', $url)
            ->withStatus($status);
    }

    /**
     * Get the authenticated user.
     */
    protected function user(Request $request): ?array
    {
        // Get Aman from container
        $auth = $this->container->get('auth');
        return $auth->user();
    }

    /**
     * Check if the current user is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        $auth = $this->container->get('auth');
        return $auth->check();
    }

    /**
     * Check if the current user is an admin.
     */
    protected function isAdmin(Request $request): bool
    {
        $auth = $this->container->get('auth');
        return $auth->isAdmin();
    }

    /**
     * Abort the request with an error response.
     *
     * @throws \IslamWiki\Core\Http\Exceptions\HttpException
     */
    protected function abort(int $status, string $message = ''): void
    {
        throw new \IslamWiki\Core\Http\Exceptions\HttpException($status, $message);
    }
}
