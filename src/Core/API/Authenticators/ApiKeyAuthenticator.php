<?php
declare(strict_types=1);

namespace IslamWiki\Core\API\Authenticators;

use IslamWiki\Core\API\Interfaces\AuthenticatorInterface;
use IslamWiki\Core\Container\Asas;
use Psr\Http\Message\ServerRequestInterface;

/**
 * API Key Authenticator
 * 
 * Authenticates API requests using API key authentication.
 * TODO: Implement API key validation logic
 */
class ApiKeyAuthenticator implements AuthenticatorInterface
{
    private Asas $container;
    
    /**
     * Create a new API key authenticator.
     */
    public function __construct(Asas $container)
    {
        $this->container = $container;
    }
    
    /**
     * Authenticate request using API key.
     */
    public function authenticate(ServerRequestInterface $request): bool
    {
        // TODO: Implement API key validation
        // For now, return false to require implementation
        return false;
    }
    
    /**
     * Get authentication method name.
     */
    public function getMethod(): string
    {
        return 'api_key';
    }
} 