<?php

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;

class RequestHandler implements RequestHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Start output buffering
            ob_start();
            
            // Let the router handle the request
            $this->router->handle($request);
            
            // Get the output buffer
            $output = ob_get_clean();
            
            // Create a response with the output
            $psr17Factory = new Psr17Factory();
            return $psr17Factory->createResponse(200)
                ->withHeader('Content-Type', 'text/html')
                ->withBody($psr17Factory->createStream($output));
                
        } catch (\Throwable $e) {
            // Log the error
            error_log('Error in RequestHandler: ' . $e->getMessage());
            
            // Return a 500 error response
            $psr17Factory = new Psr17Factory();
            return $psr17Factory->createResponse(500)
                ->withHeader('Content-Type', 'text/plain')
                ->withBody($psr17Factory->createStream('Internal Server Error'));
        }
    }
}
