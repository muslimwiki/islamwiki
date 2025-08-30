<?php

namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;

class SimpleRouter implements RouterInterface
{
    public function handle(ServerRequestInterface $request): void
    {
        $path = $request->getUri()->getPath();
        
        // Handle admin routes
        if (str_starts_with($path, '/admin')) {
            $this->handleAdminRoute($path);
            return;
        }
        
        // Handle static files
        if ($this->shouldSkipRouting($path)) {
            $this->handleStaticFile($path);
            return;
        }
        
        // Handle main application routes
        $this->handleMainRoute($request);
    }
    
    private function handleAdminRoute(string $path): void
    {
        // Default to dashboard if no specific admin page is requested
        $adminPage = str_replace('/admin', '', $path) ?: '/dashboard';
        $adminFile = __DIR__ . "/../../admin/pages{$adminPage}.php";
        
        if (file_exists($adminFile)) {
            require $adminFile;
        } else {
            http_response_code(404);
            echo "Admin page not found";
        }
        exit;
    }
    
    private function handleStaticFile(string $path): void
    {
        $basePath = __DIR__ . '/../../public';
        $requestFile = $basePath . $path;
        
        if (file_exists($requestFile) && is_file($requestFile)) {
            $mimeTypes = [
                'css'  => 'text/css',
                'js'   => 'application/javascript',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2'=> 'font/woff2',
                'ttf'  => 'font/ttf',
                'eot'  => 'application/vnd.ms-fontobject',
            ];
            
            $ext = strtolower(pathinfo($requestFile, PATHINFO_EXTENSION));
            if (isset($mimeTypes[$ext])) {
                header('Content-Type: ' . $mimeTypes[$ext]);
            }
            
            readfile($requestFile);
            exit;
        }
        
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }
    
    private function handleMainRoute(ServerRequestInterface $request): void
    {
        // Default route handling
        $path = $request->getUri()->getPath();
        
        if ($path === '/') {
            echo "Welcome to IslamWiki";
            return;
        }
        
        http_response_code(404);
        echo 'Page not found';
    }
    
    private function shouldSkipRouting(string $path): bool
    {
        $extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
        $pathInfo = pathinfo($path);
        
        if (isset($pathInfo['extension']) && in_array(strtolower($pathInfo['extension']), $extensions)) {
            return true;
        }
        
        $fullPath = __DIR__ . '/../../public' . $path;
        return file_exists($fullPath) && (is_file($fullPath) || is_dir($fullPath));
    }
}
