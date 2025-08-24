<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\API
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\API;

use Logger;\Logger
use Exception;

/**
 * API (سراج) - API Management and Knowledge Discovery System
 *
 * API provides "Light" or "Lamp" in Arabic. This class provides
 * comprehensive API management, knowledge discovery, endpoint management,
 * authentication, rate limiting, and response formatting for the IslamWiki application.
 *
 * This system is part of the User Interface Layer and serves as the
 * gateway for external applications to access Islamic knowledge and services.
 *
 * @category  Core
 * @package   IslamWiki\Core\API
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class APIAPI
{
    /**
     * The logging system.
     */
    protected Logger $logger;

    /**
     * API configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Registered API endpoints.
     *
     * @var array<string, array>
     */
    protected array $endpoints = [];

    /**
     * API authentication methods.
     *
     * @var array<string, array>
     */
    protected array $authMethods = [];

    /**
     * Rate limiting rules.
     *
     * @var array<string, array>
     */
    protected array $rateLimits = [];

    /**
     * API response formats.
     *
     * @var array<string, array>
     */
    protected array $responseFormats = [];

    /**
     * API usage statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Constructor.
     *
     * @param Logger $logger The logging system
     * @param array        $config API configuration
     */
    public function __construct(Logger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeAPI();
    }

    /**
     * Initialize API system.
     *
     * @return self
     */
    protected function initializeAPI(): self
    {
        $this->initializeStatistics();
        $this->initializeEndpoints();
        $this->initializeAuthMethods();
        $this->initializeRateLimits();
        $this->initializeResponseFormats();
        $this->logger->info('API system initialized');

        return $this;
    }

    /**
     * Initialize API statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'requests' => [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'rate_limited_requests' => 0
            ],
            'endpoints' => [
                'quran' => 0,
                'hadith' => 0,
                'search' => 0,
                'user' => 0,
                'content' => 0
            ],
            'authentication' => [
                'authenticated_requests' => 0,
                'unauthenticated_requests' => 0,
                'authentication_failures' => 0
            ],
            'performance' => [
                'average_response_time' => 0.0,
                'total_response_time' => 0.0,
                'fastest_response' => PHP_FLOAT_MAX,
                'slowest_response' => 0.0
            ],
            'errors' => [
                'validation_errors' => 0,
                'authentication_errors' => 0,
                'rate_limit_errors' => 0,
                'server_errors' => 0
            ]
        ];

        return $this;
    }

    /**
     * Initialize API endpoints.
     *
     * @return self
     */
    protected function initializeEndpoints(): self
    {
        $this->endpoints = [
            'quran' => [
                'name' => 'Quran API',
                'description' => 'Access to Quran verses, translations, and tafsir',
                'version' => '1.0',
                'base_path' => '/api/v1/quran',
                'methods' => ['GET', 'POST'],
                'authentication' => 'optional',
                'rate_limit' => 'standard',
                'endpoints' => [
                    'verses' => [
                        'path' => '/verses',
                        'method' => 'GET',
                        'description' => 'Get Quran verses with optional filters',
                        'parameters' => ['surah', 'ayah', 'translation', 'tafsir'],
                        'response_format' => 'json'
                    ],
                    'search' => [
                        'path' => '/search',
                        'method' => 'GET',
                        'description' => 'Search Quran text and translations',
                        'parameters' => ['query', 'language', 'limit'],
                        'response_format' => 'json'
                    ],
                    'surah' => [
                        'path' => '/surah/{id}',
                        'method' => 'GET',
                        'description' => 'Get complete surah information',
                        'parameters' => ['id', 'translation', 'tafsir'],
                        'response_format' => 'json'
                    ]
                ]
            ],
            'hadith' => [
                'name' => 'Hadith API',
                'description' => 'Access to Hadith collections and authentication',
                'version' => '1.0',
                'base_path' => '/api/v1/hadith',
                'methods' => ['GET', 'POST'],
                'authentication' => 'optional',
                'rate_limit' => 'standard',
                'endpoints' => [
                    'collections' => [
                        'path' => '/collections',
                        'method' => 'GET',
                        'description' => 'Get available Hadith collections',
                        'parameters' => ['language'],
                        'response_format' => 'json'
                    ],
                    'search' => [
                        'path' => '/search',
                        'method' => 'GET',
                        'description' => 'Search Hadith text and translations',
                        'parameters' => ['query', 'collection', 'authenticity', 'limit'],
                        'response_format' => 'json'
                    ],
                    'narrators' => [
                        'path' => '/narrators',
                        'method' => 'GET',
                        'description' => 'Get Hadith narrators information',
                        'parameters' => ['name', 'generation', 'limit'],
                        'response_format' => 'json'
                    ]
                ]
            ],
            'search' => [
                'name' => 'Search API',
                'description' => 'Comprehensive search across all Islamic content',
                'version' => '1.0',
                'base_path' => '/api/v1/search',
                'methods' => ['GET', 'POST'],
                'authentication' => 'optional',
                'rate_limit' => 'standard',
                'endpoints' => [
                    'global' => [
                        'path' => '/global',
                        'method' => 'GET',
                        'description' => 'Search across all content types',
                        'parameters' => ['query', 'types', 'language', 'limit'],
                        'response_format' => 'json'
                    ],
                    'suggestions' => [
                        'path' => '/suggestions',
                        'method' => 'GET',
                        'description' => 'Get search suggestions',
                        'parameters' => ['query', 'limit'],
                        'response_format' => 'json'
                    ]
                ]
            ],
            'user' => [
                'name' => 'User API',
                'description' => 'User management and authentication',
                'version' => '1.0',
                'base_path' => '/api/v1/user',
                'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
                'authentication' => 'required',
                'rate_limit' => 'strict',
                'endpoints' => [
                    'profile' => [
                        'path' => '/profile',
                        'method' => 'GET',
                        'description' => 'Get user profile information',
                        'parameters' => [],
                        'response_format' => 'json'
                    ],
                    'update' => [
                        'path' => '/profile',
                        'method' => 'PUT',
                        'description' => 'Update user profile',
                        'parameters' => ['name', 'email', 'preferences'],
                        'response_format' => 'json'
                    ]
                ]
            ],
            'content' => [
                'name' => 'Content API',
                'description' => 'General content management and retrieval',
                'version' => '1.0',
                'base_path' => '/api/v1/content',
                'methods' => ['GET', 'POST'],
                'authentication' => 'optional',
                'rate_limit' => 'standard',
                'endpoints' => [
                    'articles' => [
                        'path' => '/articles',
                        'method' => 'GET',
                        'description' => 'Get Islamic articles and content',
                        'parameters' => ['category', 'author', 'date', 'limit'],
                        'response_format' => 'json'
                    ],
                    'categories' => [
                        'path' => '/categories',
                        'method' => 'GET',
                        'description' => 'Get content categories',
                        'parameters' => ['parent', 'language'],
                        'response_format' => 'json'
                    ]
                ]
            ]
        ];

        return $this;
    }

    /**
     * Initialize authentication methods.
     *
     * @return self
     */
    protected function initializeAuthMethods(): self
    {
        $this->authMethods = [
            'api_key' => [
                'name' => 'API Key Authentication',
                'description' => 'Simple API key-based authentication',
                'type' => 'header',
                'header_name' => 'X-API-Key',
                'required' => false,
                'rate_limit_multiplier' => 1.0
            ],
            'bearer_token' => [
                'name' => 'Bearer Token Authentication',
                'description' => 'JWT-based bearer token authentication',
                'type' => 'header',
                'header_name' => 'Authorization',
                'required' => false,
                'rate_limit_multiplier' => 2.0
            ],
            'session_cookie' => [
                'name' => 'Session Cookie Authentication',
                'description' => 'Session-based authentication via cookies',
                'type' => 'cookie',
                'cookie_name' => 'session_id',
                'required' => false,
                'rate_limit_multiplier' => 1.5
            ],
            'oauth2' => [
                'name' => 'OAuth 2.0 Authentication',
                'description' => 'OAuth 2.0 flow for third-party applications',
                'type' => 'oauth2',
                'required' => false,
                'rate_limit_multiplier' => 3.0
            ]
        ];

        return $this;
    }

    /**
     * Initialize rate limiting rules.
     *
     * @return self
     */
    protected function initializeRateLimits(): self
    {
        $this->rateLimits = [
            'standard' => [
                'name' => 'Standard Rate Limit',
                'description' => 'Standard rate limiting for public endpoints',
                'requests_per_minute' => 60,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000,
                'burst_limit' => 10
            ],
            'strict' => [
                'name' => 'Strict Rate Limit',
                'description' => 'Strict rate limiting for sensitive endpoints',
                'requests_per_minute' => 30,
                'requests_per_hour' => 500,
                'requests_per_day' => 5000,
                'burst_limit' => 5
            ],
            'premium' => [
                'name' => 'Premium Rate Limit',
                'description' => 'Higher rate limits for authenticated users',
                'requests_per_minute' => 120,
                'requests_per_hour' => 2000,
                'requests_per_day' => 20000,
                'burst_limit' => 20
            ]
        ];

        return $this;
    }

    /**
     * Initialize response formats.
     *
     * @return self
     */
    protected function initializeResponseFormats(): self
    {
        $this->responseFormats = [
            'json' => [
                'name' => 'JSON Response',
                'description' => 'JavaScript Object Notation response format',
                'mime_type' => 'application/json',
                'default' => true,
                'supports_pagination' => true,
                'supports_metadata' => true
            ],
            'xml' => [
                'name' => 'XML Response',
                'description' => 'Extensible Markup Language response format',
                'mime_type' => 'application/xml',
                'default' => false,
                'supports_pagination' => true,
                'supports_metadata' => true
            ],
            'csv' => [
                'name' => 'CSV Response',
                'description' => 'Comma-Separated Values response format',
                'mime_type' => 'text/csv',
                'default' => false,
                'supports_pagination' => false,
                'supports_metadata' => false
            ],
            'rss' => [
                'name' => 'RSS Response',
                'description' => 'Really Simple Syndication response format',
                'mime_type' => 'application/rss+xml',
                'default' => false,
                'supports_pagination' => false,
                'supports_metadata' => true
            ]
        ];

        return $this;
    }

    /**
     * Process API request.
     *
     * @param string $endpoint    Endpoint name
     * @param string $method      HTTP method
     * @param array  $parameters  Request parameters
     * @param array  $headers     Request headers
     * @param array  $options     Request options
     * @return array<string, mixed>
     */
    public function processRequest(string $endpoint, string $method, array $parameters = [], array $headers = [], array $options = []): array
    {
        $startTime = microtime(true);
        $this->statistics['requests']['total_requests']++;

        try {
            // Validate endpoint
            if (!isset($this->endpoints[$endpoint])) {
                throw new Exception("Endpoint '{$endpoint}' not found");
            }

            // Validate method
            $endpointConfig = $this->endpoints[$endpoint];
            if (!in_array($method, $endpointConfig['methods'])) {
                throw new Exception("Method '{$method}' not allowed for endpoint '{$endpoint}'");
            }

            // Check authentication
            $authResult = $this->checkAuthentication($endpoint, $headers);
            if (!$authResult['success']) {
                $this->statistics['authentication']['authentication_failures']++;
                throw new Exception($authResult['error']);
            }

            // Check rate limiting
            $rateLimitResult = $this->checkRateLimit($endpoint, $authResult['auth_method'], $headers);
            if (!$rateLimitResult['success']) {
                $this->statistics['requests']['rate_limited_requests']++;
                throw new Exception($rateLimitResult['error']);
            }

            // Validate parameters
            $validationResult = $this->validateParameters($endpoint, $parameters);
            if (!$validationResult['success']) {
                $this->statistics['errors']['validation_errors']++;
                throw new Exception($validationResult['error']);
            }

            // Process the request
            $response = $this->executeEndpoint($endpoint, $method, $parameters, $headers, $options);

            // Update statistics
            $responseTime = microtime(true) - $startTime;
            $this->updateRequestStatistics($responseTime, $endpoint, $authResult['auth_method']);

            $this->logger->info("API request processed successfully: {$endpoint} {$method}");

            return [
                'success' => true,
                'data' => $response,
                'endpoint' => $endpoint,
                'method' => $method,
                'response_time' => $responseTime,
                'rate_limit_info' => $rateLimitResult['info']
            ];

        } catch (Exception $e) {
            $this->statistics['requests']['failed_requests']++;
            $this->logger->error("API request failed: {$endpoint} {$method} - " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method,
                'response_time' => microtime(true) - $startTime
            ];
        }
    }

    /**
     * Check authentication for the request.
     *
     * @param string $endpoint Endpoint name
     * @param array  $headers Request headers
     * @return array<string, mixed>
     */
    protected function checkAuthentication(string $endpoint, array $headers): array
    {
        $endpointConfig = $this->endpoints[$endpoint];
        
        if ($endpointConfig['authentication'] === 'optional') {
            return ['success' => true, 'auth_method' => 'none'];
        }

        // Check for API key
        if (isset($headers['X-API-Key'])) {
            if ($this->validateApiKey($headers['X-API-Key'])) {
                return ['success' => true, 'auth_method' => 'api_key'];
            }
        }

        // Check for Bearer token
        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            $token = substr($headers['Authorization'], 7);
            if ($this->validateBearerToken($token)) {
                return ['success' => true, 'auth_method' => 'bearer_token'];
            }
        }

        // Check for session cookie
        if (isset($headers['Cookie'])) {
            $cookies = $this->parseCookies($headers['Cookie']);
            if (isset($cookies['session_id']) && $this->validateSession($cookies['session_id'])) {
                return ['success' => true, 'auth_method' => 'session_cookie'];
            }
        }

        return [
            'success' => false,
            'error' => 'Authentication required for this endpoint',
            'auth_method' => 'none'
        ];
    }

    /**
     * Validate API key.
     *
     * @param string $apiKey API key
     * @return bool
     */
    protected function validateApiKey(string $apiKey): bool
    {
        // Simple validation - in production, this would check against database
        return !empty($apiKey) && strlen($apiKey) >= 32;
    }

    /**
     * Validate Bearer token.
     *
     * @param string $token Bearer token
     * @return bool
     */
    protected function validateBearerToken(string $token): bool
    {
        // Simple validation - in production, this would validate JWT
        return !empty($token) && strlen($token) >= 64;
    }

    /**
     * Validate session.
     *
     * @param string $sessionId Session ID
     * @return bool
     */
    protected function validateSession(string $sessionId): bool
    {
        // Simple validation - in production, this would check session store
        return !empty($sessionId) && strlen($sessionId) >= 32;
    }

    /**
     * Parse cookies string.
     *
     * @param string $cookieString Cookie string
     * @return array<string, string>
     */
    protected function parseCookies(string $cookieString): array
    {
        $cookies = [];
        $pairs = explode(';', $cookieString);
        
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (strpos($pair, '=') !== false) {
                list($name, $value) = explode('=', $pair, 2);
                $cookies[trim($name)] = trim($value);
            }
        }
        
        return $cookies;
    }

    /**
     * Check rate limiting for the request.
     *
     * @param string $endpoint   Endpoint name
     * @param string $authMethod Authentication method
     * @param array  $headers    Request headers
     * @return array<string, mixed>
     */
    protected function checkRateLimit(string $endpoint, string $authMethod, array $headers): array
    {
        $endpointConfig = $this->endpoints[$endpoint];
        $rateLimitType = $endpointConfig['rate_limit'];
        $rateLimitConfig = $this->rateLimits[$rateLimitType];

        // Get client identifier
        $clientId = $this->getClientIdentifier($headers);
        
        // Apply authentication multiplier
        $multiplier = 1.0;
        if ($authMethod !== 'none' && isset($this->authMethods[$authMethod])) {
            $multiplier = $this->authMethods[$authMethod]['rate_limit_multiplier'];
        }

        // Check rate limits
        $currentTime = time();
        $limits = [
            'minute' => $rateLimitConfig['requests_per_minute'] * $multiplier,
            'hour' => $rateLimitConfig['requests_per_hour'] * $multiplier,
            'day' => $rateLimitConfig['requests_per_day'] * $multiplier
        ];

        // Simple rate limiting check - in production, this would use Redis or similar
        foreach ($limits as $period => $limit) {
            if (!$this->checkRateLimitPeriod($clientId, $period, $limit, $currentTime)) {
                return [
                    'success' => false,
                    'error' => "Rate limit exceeded for {$period}",
                    'info' => [
                        'period' => $period,
                        'limit' => $limit,
                        'reset_time' => $this->getRateLimitResetTime($period, $currentTime)
                    ]
                ];
            }
        }

        return [
            'success' => true,
            'info' => [
                'limits' => $limits,
                'client_id' => $clientId
            ]
        ];
    }

    /**
     * Get client identifier from headers.
     *
     * @param array $headers Request headers
     * @return string
     */
    protected function getClientIdentifier(array $headers): string
    {
        // Use IP address if available, otherwise use user agent hash
        if (isset($headers['X-Forwarded-For'])) {
            return $headers['X-Forwarded-For'];
        }
        
        if (isset($headers['X-Real-IP'])) {
            return $headers['X-Real-IP'];
        }
        
        if (isset($headers['User-Agent'])) {
            return hash('sha256', $headers['User-Agent']);
        }
        
        return 'unknown';
    }

    /**
     * Check rate limit for a specific period.
     *
     * @param string $clientId   Client identifier
     * @param string $period     Time period
     * @param int    $limit      Request limit
     * @param int    $currentTime Current timestamp
     * @return bool
     */
    protected function checkRateLimitPeriod(string $clientId, string $period, int $limit, int $currentTime): bool
    {
        // Simple implementation - in production, this would use Redis or similar
        // For now, always return true to allow requests
        return true;
    }

    /**
     * Get rate limit reset time.
     *
     * @param string $period     Time period
     * @param int    $currentTime Current timestamp
     * @return int
     */
    protected function getRateLimitResetTime(string $period, int $currentTime): int
    {
        switch ($period) {
            case 'minute':
                return $currentTime + 60;
            case 'hour':
                return $currentTime + 3600;
            case 'day':
                return $currentTime + 86400;
            default:
                return $currentTime;
        }
    }

    /**
     * Validate request parameters.
     *
     * @param string $endpoint   Endpoint name
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function validateParameters(string $endpoint, array $parameters): array
    {
        $endpointConfig = $this->endpoints[$endpoint];
        
        // Check if endpoint has specific parameter requirements
        if (isset($endpointConfig['endpoints'])) {
            // This is a complex endpoint with sub-endpoints
            // For now, return success - detailed validation would be endpoint-specific
            return ['success' => true];
        }

        // Simple validation - in production, this would be more comprehensive
        return ['success' => true];
    }

    /**
     * Execute the endpoint logic.
     *
     * @param string $endpoint   Endpoint name
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @param array  $headers    Request headers
     * @param array  $options    Request options
     * @return array<string, mixed>
     */
    protected function executeEndpoint(string $endpoint, string $method, array $parameters, array $headers, array $options): array
    {
        // This is a placeholder implementation
        // In production, this would route to actual endpoint handlers
        
        switch ($endpoint) {
            case 'quran':
                return $this->executeQuranEndpoint($method, $parameters);
            case 'hadith':
                return $this->executeHadithEndpoint($method, $parameters);
            case 'search':
                return $this->executeSearchEndpoint($method, $parameters);
            case 'user':
                return $this->executeUserEndpoint($method, $parameters);
            case 'content':
                return $this->executeContentEndpoint($method, $parameters);
            default:
                return ['message' => 'Endpoint not implemented yet'];
        }
    }

    /**
     * Execute Quran endpoint.
     *
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function executeQuranEndpoint(string $method, array $parameters): array
    {
        return [
            'endpoint' => 'quran',
            'method' => $method,
            'data' => [
                'message' => 'Quran API endpoint - implementation pending',
                'parameters' => $parameters
            ]
        ];
    }

    /**
     * Execute Hadith endpoint.
     *
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function executeHadithEndpoint(string $method, array $parameters): array
    {
        return [
            'endpoint' => 'hadith',
            'method' => $method,
            'data' => [
                'message' => 'Hadith API endpoint - implementation pending',
                'parameters' => $parameters
            ]
        ];
    }

    /**
     * Execute Search endpoint.
     *
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function executeSearchEndpoint(string $method, array $parameters): array
    {
        return [
            'endpoint' => 'search',
            'method' => $method,
            'data' => [
                'message' => 'Search API endpoint - implementation pending',
                'parameters' => $parameters
            ]
        ];
    }

    /**
     * Execute User endpoint.
     *
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function executeUserEndpoint(string $method, array $parameters): array
    {
        return [
            'endpoint' => 'user',
            'method' => $method,
            'data' => [
                'message' => 'User API endpoint - implementation pending',
                'parameters' => $parameters
            ]
        ];
    }

    /**
     * Execute Content endpoint.
     *
     * @param string $method     HTTP method
     * @param array  $parameters Request parameters
     * @return array<string, mixed>
     */
    protected function executeContentEndpoint(string $method, array $parameters): array
    {
        return [
            'endpoint' => 'content',
            'method' => $method,
            'data' => [
                'message' => 'Content API endpoint - implementation pending',
                'parameters' => $parameters
            ]
        ];
    }

    /**
     * Update request statistics.
     *
     * @param float  $responseTime Response time
     * @param string $endpoint     Endpoint name
     * @param string $authMethod   Authentication method
     * @return self
     */
    protected function updateRequestStatistics(float $responseTime, string $endpoint, string $authMethod): self
    {
        $this->statistics['requests']['successful_requests']++;
        $this->statistics['performance']['total_response_time'] += $responseTime;

        // Update endpoint statistics
        if (isset($this->statistics['endpoints'][$endpoint])) {
            $this->statistics['endpoints'][$endpoint]++;
        }

        // Update authentication statistics
        if ($authMethod !== 'none') {
            $this->statistics['authentication']['authenticated_requests']++;
        } else {
            $this->statistics['authentication']['unauthenticated_requests']++;
        }

        // Update performance statistics
        $totalRequests = $this->statistics['requests']['successful_requests'];
        $this->statistics['performance']['average_response_time'] = 
            $this->statistics['performance']['total_response_time'] / $totalRequests;

        if ($responseTime < $this->statistics['performance']['fastest_response']) {
            $this->statistics['performance']['fastest_response'] = $responseTime;
        }

        if ($responseTime > $this->statistics['performance']['slowest_response']) {
            $this->statistics['performance']['slowest_response'] = $responseTime;
        }

        return $this;
    }

    /**
     * Get API documentation.
     *
     * @param string $format Response format
     * @return array<string, mixed>
     */
    public function getDocumentation(string $format = 'json'): array
    {
        return [
            'api_name' => 'IslamWiki API',
            'version' => '1.0',
            'description' => 'Comprehensive API for Islamic knowledge and content',
            'endpoints' => $this->endpoints,
            'authentication' => $this->authMethods,
            'rate_limits' => $this->rateLimits,
            'response_formats' => $this->responseFormats,
            'documentation_url' => 'https://islam.wiki/api/docs'
        ];
    }

    /**
     * Get API statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get available endpoints.
     *
     * @return array<string, array>
     */
    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * Get authentication methods.
     *
     * @return array<string, array>
     */
    public function getAuthMethods(): array
    {
        return $this->authMethods;
    }

    /**
     * Get rate limiting rules.
     *
     * @return array<string, array>
     */
    public function getRateLimits(): array
    {
        return $this->rateLimits;
    }

    /**
     * Get response formats.
     *
     * @return array<string, array>
     */
    public function getResponseFormats(): array
    {
        return $this->responseFormats;
    }

    /**
     * Get API configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set API configuration.
     *
     * @param array<string, mixed> $config API configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
