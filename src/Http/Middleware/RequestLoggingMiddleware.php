<?
declare(strict_types=1);
php\np



namespace IslamWiki\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

/**
 * Request Logging Middleware
 * 
 * This middleware logs HTTP requests and responses for monitoring and debugging.
 * It captures request details, response status, and execution time.
 */
class RequestLoggingMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var float
     */
    private $startTime;
    
    /**
     * @var array List of paths to exclude from logging
     */
    private $excludedPaths = [
        '/health',
        '/ping',
        '/favicon.ico',
    ];
    
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->startTime = microtime(true);
    }
    
    /**
     * Process an incoming server request.
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        // Skip logging for excluded paths
        $path = $request->getUri()->getPath();
        if ($this->shouldExcludePath($path)) {
            return $handler->handle($request);
        }
        
        // Log request
        $this->logRequest($request);
        
        try {
            // Handle the request and get response
            $response = $handler->handle($request);
            
            // Log response
            $this->logResponse($request, $response);
            
            return $response;
        } catch (\Throwable $e) {
            // Log the exception
            $this->logger->error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw the exception to be handled by the error handler
            throw $e;
        }
    }
    
    /**
     * Check if the path should be excluded from logging.
     */
    private function shouldExcludePath(string $path): bool
    {
        foreach ($this->excludedPaths as $excludedPath) {
            if (strpos($path, $excludedPath) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log the request details.
     */
    private function logRequest(Request $request): void
    {
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $request->getHeaderLine('User-Agent');
        $contentType = $request->getHeaderLine('Content-Type');
        
        $context = [
            'method' => $method,
            'uri' => $uri,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'content_type' => $contentType,
            'headers' => $this->getFilteredHeaders($request->getHeaders()),
        ];
        
        // Log request body for non-GET requests
        if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            $body = (string) $request->getBody();
            if (!empty($body)) {
                $context['body'] = $this->filterSensitiveData($body);
            }
        }
        
        $this->logger->info("Request: {$method} {$uri}", $context);
    }
    
    /**
     * Log the response details.
     */
    private function logResponse(Request $request, ResponseInterface $response): void
    {
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');
        $executionTime = round((microtime(true) - $this->startTime) * 1000, 2); // in ms
        
        $context = [
            'method' => $method,
            'uri' => $uri,
            'status_code' => $statusCode,
            'content_type' => $contentType,
            'execution_time_ms' => $executionTime,
            'headers' => $this->getFilteredHeaders($response->getHeaders()),
        ];
        
        // Log response body for error statuses
        if ($statusCode >= 400) {
            $body = (string) $response->getBody();
            if (!empty($body)) {
                $context['body'] = $this->filterSensitiveData($body);
            }
        }
        
        $logLevel = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');
        $this->logger->log($logLevel, "Response: {$statusCode} {$method} {$uri} ({$executionTime}ms)", $context);
    }
    
    /**
     * Filter sensitive data from headers.
     */
    private function getFilteredHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'php-auth-pw',
            'php-auth-user',
            'php-auth-digest',
            'php-auth-',
            'x-csrf-token',
            'x-xsrf-token',
        ];
        
        $filtered = [];
        
        foreach ($headers as $name => $value) {
            $lowerName = strtolower($name);
            $isSensitive = false;
            
            foreach ($sensitiveHeaders as $sensitive) {
                if (strpos($lowerName, $sensitive) !== false) {
                    $isSensitive = true;
                    break;
                }
            }
            
            $filtered[$name] = $isSensitive ? '***FILTERED***' : $value;
        }
        
        return $filtered;
    }
    
    /**
     * Filter sensitive data from request/response bodies.
     */
    private function filterSensitiveData(string $data): string
    {
        // If it's JSON, parse and filter sensitive fields
        if (strpos($data, '{') === 0 || strpos($data, '[') === 0) {
            $json = json_decode($data, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->filterSensitiveArray($json);
                return json_encode($json, JSON_PRETTY_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        
        // If it's form data, filter sensitive fields
        if (strpos($data, '&') !== false) {
            $pairs = explode('&', $data);
            $filtered = [];
            
            foreach ($pairs as $pair) {
                list($key, $value) = array_pad(explode('=', $pair, 2), 2, '');
                $key = urldecode($key);
                
                if ($this->isSensitiveField($key)) {
                    $filtered[] = $key . '=***FILTERED***';
                } else {
                    $filtered[] = $key . '=' . $value;
                }
            }
            
            return implode('&', $filtered);
        }
        
        return $data;
    }
    
    /**
     * Recursively filter sensitive data from an array.
     */
    private function filterSensitiveArray(array &$data): void
    {
        $sensitiveFields = [
            'password',
            'pwd',
            'secret',
            'token',
            'key',
            'api_key',
            'apiKey',
            'access_token',
            'refresh_token',
            'credit_card',
            'cc_number',
            'cvv',
            'ssn',
            'social_security',
        ];
        
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->filterSensitiveArray($value);
            } elseif (is_string($key) && $this->isSensitiveField($key, $sensitiveFields)) {
                $data[$key] = '***FILTERED***';
            }
        }
    }
    
    /**
     * Check if a field name indicates sensitive data.
     */
    private function isSensitiveField(string $field, array $additionalFields = []): bool
    {
        $sensitiveFields = array_merge([
            'password',
            'pwd',
            'secret',
            'token',
            'key',
            'api_key',
            'apiKey',
            'access_token',
            'refresh_token',
            'credit_card',
            'cc_number',
            'cvv',
            'ssn',
            'social_security',
        ], $additionalFields);
        
        $field = strtolower($field);
        
        foreach ($sensitiveFields as $sensitive) {
            if (strpos($field, $sensitive) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
