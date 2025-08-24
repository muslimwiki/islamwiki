<?php

declare(strict_types=1);

namespace IslamWiki\Core\API\Authenticators;

use IslamWiki\Core\API\Interfaces\AuthenticatorInterface;
use Container;\Container
use Psr\Http\Message\ServerRequestInterface;

/**
 * Token Authenticator
 *
 * Authenticates API requests using token-based authentication.
 * TODO: Implement token validation logic
 */
class TokenAuthenticator implements AuthenticatorInterface
{
    private Container $container;

    /**
     * Create a new token authenticator.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Authenticate request using token.
     */
    public function authenticate(ServerRequestInterface $request): bool
    {
        // TODO: Implement token validation
        // For now, return false to require implementation
        return false;
    }

    /**
     * Get authentication method name.
     */
    public function getMethod(): string
    {
        return 'token';
    }
}
