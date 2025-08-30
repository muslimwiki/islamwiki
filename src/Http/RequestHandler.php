<?php

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;
use RuntimeException;

class RequestHandler implements RequestHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->registerRoutes();
    }
    
    protected function registerRoutes(): void
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        
        if (!file_exists($routesFile)) {
            throw new RuntimeException('Routes configuration file not found: ' . $routesFile);
        }
        
        $routes = require $routesFile;
        
        foreach ($routes as $pattern => $handler) {
            if (!is_string($pattern) || !is_array($handler) || count($handler) !== 2) {
                continue;
            }
            
            // Extract HTTP method and path from pattern (e.g., 'GET /path')
            [$method, $path] = explode(' ', $pattern, 2) + ['', ''];
            
            if ($method && $path) {
                $this->router->addRoute($method, $path, $handler);
            }
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        error_log('Handling request: ' . $request->getMethod() . ' ' . $request->getUri()->getPath());
        
        try {
            $result = $this->router->match($request);
            error_log('Router match result: ' . print_r($result, true));
            
            if ($result === null) {
                error_log('No route matched');
                return new Response(404, ['Content-Type' => 'text/plain'], 'Not Found');
            }
            
            $handler = $result['handler'];
            $params = $result['params'] ?? [];
            error_log('Handler: ' . print_r($handler, true));
            error_log('Params: ' . print_r($params, true));
            
            if (is_callable($handler)) {
                error_log('Calling callable handler');
                $response = $handler($request, ...array_values($params));
            } elseif (is_array($handler) && count($handler) === 2) {
                [$controller, $method] = $handler;
                error_log(sprintf('Controller: %s, Method: %s', is_object($controller) ? get_class($controller) : $controller, $method));
                
                if (is_string($controller) && class_exists($controller)) {
                    error_log('Instantiating controller: ' . $controller);
                    $controller = new $controller();
                }
                
                if (is_object($controller) && method_exists($controller, $method)) {
                    error_log(sprintf('Calling %s::%s with params: %s', get_class($controller), $method, print_r($params, true)));
                    
                    // Get method reflection to inspect parameters
                    $reflection = new \ReflectionMethod($controller, $method);
                    $methodParams = $reflection->getParameters();
                    
                    // Log method signature
                    error_log(sprintf('Method %s expects %d parameters', $method, count($methodParams)));
                    foreach ($methodParams as $i => $param) {
                        $type = $param->getType() ? $param->getType()->getName() : 'mixed';
                        error_log(sprintf('  Param %d: $%s (type: %s)', $i, $param->getName(), $type));
                    }
                    
                    // Check if the method expects a second parameter
                    $reflection = new \ReflectionMethod($controller, $method);
                    $parameters = $reflection->getParameters();
                    
                    // If the method has a second parameter, pass the params array
                    if (count($parameters) > 1 && $parameters[1]->getName() === 'args') {
                        $response = $controller->$method($request, $params);
                    } else {
                        // Otherwise, pass the slug directly
                        $response = $controller->$method($request, $params['slug'] ?? null);
                    }
                    
                    if (!($response instanceof \Psr\Http\Message\ResponseInterface)) {
                        throw new \RuntimeException(sprintf(
                            'Controller method %s::%s must return a ResponseInterface',
                            get_class($controller),
                            $method
                        ));
                    }
                } else {
                    error_log('Method does not exist or controller is not an object');
                    $response = new Response(500, ['Content-Type' => 'text/plain'], 'Invalid handler');
                }
            } else {
                error_log('Invalid handler format');
                $response = new Response(500, ['Content-Type' => 'text/plain'], 'Invalid handler');
            }
            
            if (!$response instanceof ResponseInterface) {
                throw new \RuntimeException("Handler must return a ResponseInterface instance");
            }

            return $response;
        } catch (\Throwable $e) {
            error_log('Error handling request: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            return new Response(
                500, 
                ['Content-Type' => 'text/plain'], 
                'Internal Server Error: ' . $e->getMessage()
            );
        }
    }
}
