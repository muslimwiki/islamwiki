<?php

/**
 * Siraj (سراج) - API Management System
 *
 * Comprehensive API management system for IslamWiki.
 * Siraj means "lamp" or "light" in Arabic, representing the
 * system that illuminates and guides API interactions.
 *
 * @package IslamWiki\Core\API
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\API;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Session\WisalSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use IslamWiki\Core\API\RateLimiter;
use IslamWiki\Core\API\Authenticators\SessionAuthenticator;
use IslamWiki\Core\API\Interfaces\AuthenticatorInterface;
use IslamWiki\Core\API\Interfaces\ResponseFormatterInterface;
use IslamWiki\Core\API\Formatters\JsonResponseFormatter;

/**
 * Siraj API Management System
 *
 * Handles API authentication, rate limiting, response formatting,
 * and overall API lifecycle management.
 */
class SirajAPI
{
    private AsasContainer $container;
    private ShahidLogger $logger;
    private WisalSession $session;
    private array $rateLimiters = [];
    private array $authenticators = [];
    private array $responseFormatters = [];

    /**
     * Create a new Siraj API management system.
     */
    public function __construct(AsasContainer $container, ShahidLogger $logger, WisalSession $session)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->session = $session;
        $this->initializeComponents();
    }

    /**
     * Initialize API components.
     */
    private function initializeComponents(): void
    {
        // Initialize rate limiters
        $this->rateLimiters = [
            'default' => new RateLimiter($this->container),
            'strict' => new RateLimiter($this->container, ['requests' => 10, 'window' => 60]),
            'relaxed' => new RateLimiter($this->container, ['requests' => 100, 'window' => 60]),
        ];

        // Initialize authenticators
        $this->authenticators = [
            'session' => new SessionAuthenticator($this->session),
            'token' => new \IslamWiki\Core\API\Authenticators\TokenAuthenticator($this->container),
            'api_key' => new \IslamWiki\Core\API\Authenticators\ApiKeyAuthenticator($this->container),
        ];

        // Initialize response formatters
        $this->responseFormatters = [
            'json' => new JsonResponseFormatter(),
            'xml' => new \IslamWiki\Core\API\Formatters\XmlResponseFormatter(),
            'html' => new \IslamWiki\Core\API\Formatters\HtmlResponseFormatter(),
        ];
    }

    /**
     * Authenticate an API request.
     */
    public function authenticate(ServerRequestInterface $request, string $method = 'session'): bool
    {
        try {
            if (!isset($this->authenticators[$method])) {
                $this->logger->error("Unknown authentication method: {$method}");
                return false;
            }

            $authenticator = $this->authenticators[$method];
            $result = $authenticator->authenticate($request);

            $this->logger->info("API authentication attempt", [
                'method' => $method,
                'success' => $result,
                'ip' => $this->getClientIp($request),
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("API authentication error", [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check rate limiting for a request.
     */
    public function checkRateLimit(ServerRequestInterface $request, string $type = 'default'): bool
    {
        try {
            if (!isset($this->rateLimiters[$type])) {
                $this->logger->error("Unknown rate limiter type: {$type}");
                return false;
            }

            $rateLimiter = $this->rateLimiters[$type];
            $result = $rateLimiter->check($request);

            $this->logger->debug("Rate limit check", [
                'type' => $type,
                'allowed' => $result,
                'ip' => $this->getClientIp($request),
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Rate limiting error", [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format an API response.
     */
    public function formatResponse($data, string $format = 'json', int $statusCode = 200): ResponseInterface
    {
        try {
            if (!isset($this->responseFormatters[$format])) {
                $this->logger->error("Unknown response format: {$format}");
                return $this->formatResponse(['error' => 'Unsupported format'], 'json', 400);
            }

            $formatter = $this->responseFormatters[$format];
            $response = $formatter->format($data, $statusCode);

            $this->logger->debug("API response formatted", [
                'format' => $format,
                'status_code' => $statusCode,
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->error("Response formatting error", [
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            // Fallback to JSON error response
            return $this->formatResponse(['error' => 'Internal server error'], 'json', 500);
        }
    }

    /**
     * Handle an API request with full lifecycle management.
     */
    public function handleRequest(ServerRequestInterface $request, callable $handler, array $options = []): ResponseInterface
    {
        $startTime = microtime(true);

        try {
            // Check rate limiting
            $rateLimitType = $options['rate_limit'] ?? 'default';
            if (!$this->checkRateLimit($request, $rateLimitType)) {
                return $this->formatResponse([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => $this->getRetryAfterTime($rateLimitType)
                ], 'json', 429);
            }

            // Authenticate request
            $authMethod = $options['auth_method'] ?? 'session';
            if (!$this->authenticate($request, $authMethod)) {
                return $this->formatResponse([
                    'error' => 'Authentication required'
                ], 'json', 401);
            }

            // Execute handler
            $response = $handler($request);

            // Log successful request
            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->logger->info("API request completed", [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->getPath(),
                'status_code' => $response->getStatusCode(),
                'execution_time' => round($executionTime, 2),
            ]);

            return $response;
        } catch (\Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->logger->error("API request failed", [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->getPath(),
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2),
            ]);

            return $this->formatResponse([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 'json', 500);
        }
    }

    /**
     * Get client IP address from request.
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get retry after time for rate limited requests.
     */
    private function getRetryAfterTime(string $type): int
    {
        $rateLimiter = $this->rateLimiters[$type] ?? $this->rateLimiters['default'];
        return $rateLimiter->getRetryAfterTime();
    }

    /**
     * Get rate limiter instance.
     */
    public function getRateLimiter(string $type = 'default'): RateLimiter
    {
        return $this->rateLimiters[$type] ?? $this->rateLimiters['default'];
    }

    /**
     * Get authenticator instance.
     */
    public function getAuthenticator(string $method = 'session'): AuthenticatorInterface
    {
        return $this->authenticators[$method] ?? $this->authenticators['session'];
    }

    /**
     * Get response formatter instance.
     */
    public function getResponseFormatter(string $format = 'json'): ResponseFormatterInterface
    {
        return $this->responseFormatters[$format] ?? $this->responseFormatters['json'];
    }
}
