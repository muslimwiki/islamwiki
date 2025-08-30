<?php

namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;

class SimpleRouter implements RouterInterface
{
    private array $routes = [];

    public function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function match(ServerRequestInterface $request): ?array
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // Log to both error log and output for visibility
        $log = function($message) {
            error_log($message);
            echo "[DEBUG] $message\n";
        };
        
        $log("\n=== New Request ===");
        $log("Matching request: $method $path");
        $log("Available routes: " . print_r($this->routes, true));

        foreach ($this->routes as $i => $route) {
            $log("\nChecking route #$i: {$route['method']} {$route['path']}");
            
            if ($route['method'] !== $method) {
                $log("  - Method does not match: {$route['method']} !== $method");
                continue;
            }

            $pattern = $this->convertToRegex($route['path']);
            $log("  - Generated pattern: " . $pattern);
            
            $matches = [];
            $result = @preg_match($pattern, $path, $matches);
            
            if ($result === false) {
                $error = error_get_last();
                $log("  - Pattern error: " . ($error['message'] ?? 'Unknown error'));
                $log("  - Pattern: " . $pattern);
                $log("  - Path: " . $path);
                continue;
            }
            
            if ($result === 1) {
                $log("  - Match found!");
                $log("  - Params: " . print_r($matches, true));
                return [
                    'handler' => $route['handler'],
                    'params' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)
                ];
            } else {
                $log("  - No match");
                $log("  - Pattern: $pattern");
                $log("  - Path: $path");
            }
        }

        $log("\nNo matching route found for $method $path");
        return null;
    }

    private function convertToRegex(string $path): string
    {
        // Escape all special regex chars except slashes
        $pattern = preg_quote($path, '~');
        
        // Replace {param} with a named capture group
        $pattern = str_replace(
            ['\{', '\}'],
            ['(?P<', '>[^/]+)'],
            $pattern
        );
        
        // Add start/end anchors and use # as delimiter to avoid escaping slashes
        return '#^' . $pattern . '$#';
    }
}
