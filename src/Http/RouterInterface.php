<?php

namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouterInterface
{
    public function addRoute(string $method, string $path, $handler): void;
    public function match(ServerRequestInterface $request): ?array;
}
