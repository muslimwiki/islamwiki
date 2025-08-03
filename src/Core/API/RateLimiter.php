<?php
declare(strict_types=1);

namespace IslamWiki\Core\API;

use IslamWiki\Core\Container\Asas;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Rate Limiter
 * 
 * Implements rate limiting for API requests.
 */
class RateLimiter
{
    private Asas $container;
    private array $config;
    private string $storageKey = 'api_rate_limits';
    
    /**
     * Create a new rate limiter instance.
     */
    public function __construct(Asas $container, array $config = [])
    {
        $this->container = $container;
        $this->config = array_merge([
            'requests' => 60,
            'window' => 60, // seconds
            'storage' => 'session', // session, redis, database
        ], $config);
    }
    
    /**
     * Check if request is within rate limits.
     */
    public function check(ServerRequestInterface $request): bool
    {
        $identifier = $this->getIdentifier($request);
        $currentTime = time();
        $windowStart = $currentTime - $this->config['window'];
        
        // Get current requests for this identifier
        $requests = $this->getRequests($identifier);
        
        // Remove old requests outside the window
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp >= $windowStart;
        });
        
        // Check if we're within limits
        if (count($requests) >= $this->config['requests']) {
            return false;
        }
        
        // Add current request
        $requests[] = $currentTime;
        $this->setRequests($identifier, $requests);
        
        return true;
    }
    
    /**
     * Get retry after time in seconds.
     */
    public function getRetryAfterTime(): int
    {
        return $this->config['window'];
    }
    
    /**
     * Get identifier for rate limiting.
     */
    private function getIdentifier(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        $ip = $serverParams['REMOTE_ADDR'] ?? 'unknown';
        
        // Could be enhanced with user ID, API key, etc.
        return "rate_limit:{$ip}";
    }
    
    /**
     * Get requests for an identifier.
     */
    private function getRequests(string $identifier): array
    {
        if ($this->config['storage'] === 'session') {
            $session = $this->container->get('session');
            return $session->get($identifier, []);
        }
        
        // Default to empty array
        return [];
    }
    
    /**
     * Set requests for an identifier.
     */
    private function setRequests(string $identifier, array $requests): void
    {
        if ($this->config['storage'] === 'session') {
            $session = $this->container->get('session');
            $session->put($identifier, $requests);
        }
    }
    
    /**
     * Get current rate limit status.
     */
    public function getStatus(string $identifier): array
    {
        $requests = $this->getRequests($identifier);
        $currentTime = time();
        $windowStart = $currentTime - $this->config['window'];
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp >= $windowStart;
        });
        
        return [
            'current' => count($requests),
            'limit' => $this->config['requests'],
            'remaining' => max(0, $this->config['requests'] - count($requests)),
            'reset_time' => $currentTime + $this->config['window'],
        ];
    }
} 