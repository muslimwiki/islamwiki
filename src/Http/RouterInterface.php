<?php

namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Handle the incoming request
     */
    public function handle(ServerRequestInterface $request): void;
}
