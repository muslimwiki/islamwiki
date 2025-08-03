<?php
declare(strict_types=1);

namespace IslamWiki\Core\API\Authenticators;

use IslamWiki\Core\API\Interfaces\AuthenticatorInterface;
use IslamWiki\Core\Session\Wisal;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Session Authenticator
 * 
 * Authenticates API requests using session-based authentication.
 */
class SessionAuthenticator implements AuthenticatorInterface
{
    private Wisal $session;
    
    /**
     * Create a new session authenticator.
     */
    public function __construct(Wisal $session)
    {
        $this->session = $session;
    }
    
    /**
     * Authenticate request using session.
     */
    public function authenticate(ServerRequestInterface $request): bool
    {
        // Check if user is logged in via session
        return $this->session->isLoggedIn();
    }
    
    /**
     * Get authentication method name.
     */
    public function getMethod(): string
    {
        return 'session';
    }
    
    /**
     * Get current user from session.
     */
    public function getUser(): ?array
    {
        if (!$this->session->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->session->getUserId(),
            'username' => $this->session->getUsername(),
            'is_admin' => $this->session->isAdmin(),
        ];
    }
} 