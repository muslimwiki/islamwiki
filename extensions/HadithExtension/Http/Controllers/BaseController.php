<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Http\Controllers;

use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\JsonResponse;
use IslamWiki\Core\Http\RedirectResponse;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\View\View;

/**
 * BaseController
 * 
 * Provides common functionality for all HadithExtension controllers
 */
class BaseController
{
    /**
     * @var Connection Database connection
     */
    protected $db;
    
    /**
     * @var AsasContainer Application container
     */
    protected $container;
    
    /**
     * Constructor
     */
    public function __construct(Connection $db, AsasContainer $container)
    {
        $this->db = $db;
        $this->container = $container;
    }
    
    /**
     * Render a view
     * 
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to the view
     * @return Response
     */
    protected function view(string $view, array $data = []): Response
    {
        // Get the view path from the container or use a default
        $viewPath = $this->container->has('view.path') 
            ? $this->container->get('view.path') 
            : __DIR__ . '/../../../resources/views';
        
        // Create a new view instance
        $view = new View($viewPath);
        
        // Set the layout
        $layout = $data['layout'] ?? 'layouts/main';
        
        // Render the view
        $content = $view->render("hadith/$view", $data);
        
        // If this is an AJAX request, return JSON
        $request = $this->container->get('request');
        if ($request && ($request->ajax() || $request->wantsJson())) {
            return new JsonResponse([
                'success' => true,
                'content' => $content,
                'title' => $data['title'] ?? '',
                'data' => $data
            ]);
        }
        
        // Otherwise, return the full page
        return new Response(
            $view->render($layout, array_merge($data, ['content' => $content]))
        );
    }
    
    /**
     * Return a JSON response
     * 
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @param array $headers Additional headers
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
    
    /**
     * Return a redirect response
     * 
     * @param string $url URL to redirect to
     * @param int $status HTTP status code (default: 302)
     * @return RedirectResponse
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }
    
    /**
     * Return a 404 not found response
     * 
     * @param string $message Error message
     * @param string $title Page title
     * @return Response
     */
    protected function notFound(string $message = 'Page not found', string $title = '404 Not Found'): Response
    {
        return $this->view('errors/404', [
            'title' => $title,
            'message' => $message,
        ])->withStatus(404);
    }
    
    /**
     * Return an error response
     * 
     * @param string $message Error message
     * @param int $status HTTP status code (default: 500)
     * @param array $data Additional data
     * @return Response|JsonResponse
     */
    protected function errorResponse(string $message, int $status = 500, array $data = [])
    {
        $request = $this->container->has('request') ? $this->container->get('request') : null;
        
        // Return JSON for AJAX/API requests
        if ($request && ($request->ajax() || $request->wantsJson())) {
            return $this->json(
                array_merge(['error' => $message], $data),
                $status
            );
        }
        
        // Otherwise return a view
        return $this->view('errors/error', [
            'title' => 'Error',
            'message' => $message,
            'status' => $status,
        ])->withStatus($status);
    }
    
    /**
     * Check if the current user has a specific permission
     * 
     * @param string $permission Permission name
     * @return bool
     */
    protected function can(string $permission): bool
    {
        if (!$this->container->has('auth')) {
            return false;
        }
        
        $auth = $this->container->get('auth');
        return $auth->user() && $auth->user()->can($permission);
    }
    
    /**
     * Get the current authenticated user
     * 
     * @return \stdClass|null
     */
    protected function user()
    {
        if (!$this->container->has('auth')) {
            return null;
        }
        
        return $this->container->get('auth')->user();
    }
    
    /**
     * Get a service from the container
     * 
     * @param string $id Service ID
     * @return mixed
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }
    
    /**
     * Check if a service exists in the container
     * 
     * @param string $id Service ID
     * @return bool
     */
    protected function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
