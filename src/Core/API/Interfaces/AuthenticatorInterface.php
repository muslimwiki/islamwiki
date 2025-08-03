<?php
declare(strict_types=1);

namespace IslamWiki\Core\API\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator Interface
 * 
 * Defines the contract for API authentication methods.
 */
interface AuthenticatorInterface
{
    /**
     * Authenticate a request.
     * 
     * @param ServerRequestInterface $request The request to authenticate
     * @return bool True if authentication successful, false otherwise
     */
    public function authenticate(ServerRequestInterface $request): bool;
    
    /**
     * Get authentication method name.
     * 
     * @return string The authentication method name
     */
    public function getMethod(): string;
} 