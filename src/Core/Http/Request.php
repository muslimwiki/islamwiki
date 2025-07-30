<?php
declare(strict_types=1);

namespace IslamWiki\Core\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\RequestInterface;

class Request implements ServerRequestInterface
{
    /**
     * The request method.
     */
    protected string $method;

    /**
     * The request URI.
     */
    protected UriInterface $uri;

    /**
     * The request headers.
     */
    protected array $headers = [];

    /**
     * The request body.
     */
    protected ?StreamInterface $body = null;

    /**
     * The request target.
     */
    protected ?string $requestTarget = null;

    /**
     * The server parameters.
     */
    protected array $serverParams;

    /**
     * The cookie parameters.
     */
    protected array $cookieParams = [];

    /**
     * The query parameters.
     */
    protected array $queryParams = [];

    /**
     * The parsed body parameters.
     */
    protected $parsedBody = null;

    /**
     * The uploaded files.
     */
    protected array $uploadedFiles = [];

    /**
     * The request attributes.
     */
    protected array $attributes = [];

    /**
     * The protocol version.
     */
    protected string $protocolVersion = '1.1';

    /**
     * The request body stream.
     */
    protected $stream;

    /**
     * Create a new HTTP request.
     */
    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        array $serverParams = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->headers = $this->filterHeaders($headers);
        $this->protocolVersion = $version;
        $this->serverParams = $serverParams;

        if ($body !== '' && $body !== null) {
            $this->stream = $body instanceof StreamInterface ? $body : new Stream($body);
        } else {
            $this->stream = new Stream('php://temp', 'r+');
        }
    }

    /**
     * Create a new request from the current PHP globals.
     */
    public static function capture(): self
    {
        // Initialize superglobals if they're not set
        $_GET = $_GET ?? [];
        $_POST = $_POST ?? [];
        $_COOKIE = $_COOKIE ?? [];
        $_FILES = $_FILES ?? [];
        $_SERVER = $_SERVER ?? [];
        
        // Get request method, default to GET if not set
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Get URI from globals
        $uri = self::getUriFromGlobals();
        
        // Get headers
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        
        // Create request body stream
        $body = new Stream('php://input', 'r+');
        
        // Get protocol version
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) 
            ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) 
            : '1.1';

        // Create the request
        $request = new self(
            $method, 
            $uri, 
            is_array($headers) ? $headers : [], 
            $body, 
            $protocol, 
            $_SERVER
        );

        // Add superglobals to the request
        return $request
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(self::normalizeFiles($_FILES));
    }

    /**
     * Get the URI from the global $_SERVER array.
     */
    public static function getUriFromGlobals(): UriInterface
    {
        $uri = new Uri('');

        // Ensure $_SERVER is an array
        $_SERVER = is_array($_SERVER) ? $_SERVER : [];

        // Scheme - default to http if not set
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $uri = $uri->withScheme($isHttps ? 'https' : 'http');

        // Host - try multiple possible server variables
        $host = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = explode(':', $_SERVER['HTTP_HOST'])[0];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } elseif (!empty($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'];
        } else {
            $host = 'localhost';
        }
        $uri = $uri->withHost($host);

        // Port - handle both HTTP_HOST and SERVER_PORT
        $port = null;
        if (!empty($_SERVER['HTTP_HOST'])) {
            $hostParts = explode(':', $_SERVER['HTTP_HOST']);
            if (count($hostParts) > 1) {
                $port = (int) end($hostParts);
            }
        }
        
        if ($port === null && !empty($_SERVER['SERVER_PORT'])) {
            $port = (int) $_SERVER['SERVER_PORT'];
        }
        
        // Only set port if it's not the default for the scheme
        if ($port !== null && $port !== ($isHttps ? 443 : 80)) {
            $uri = $uri->withPort($port);
        }

        // Path and query
        $requestUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $requestUriParts = explode('?', $requestUri, 2);
        
        // Ensure path is not empty and starts with /
        $path = $requestUriParts[0];
        if (empty($path)) {
            $path = '/';
        } elseif ($path[0] !== '/') {
            $path = '/' . $path;
        }
        
        $uri = $uri->withPath($path);
        
        // Set query string if available
        if (isset($requestUriParts[1])) {
            $uri = $uri->withQuery($requestUriParts[1]);
        } elseif (!empty($_SERVER['QUERY_STRING'])) {
            $uri = $uri->withQuery($_SERVER['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * Normalize uploaded files.
     */
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            } else {
                throw new \InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * Create an UploadedFile instance from a $_FILES specification.
     */
    protected static function createUploadedFileFromSpec(array $file): UploadedFile
    {
        if (!isset($file['tmp_name']) || !isset($file['name']) || !isset($file['type']) || 
            !isset($file['size']) || !isset($file['error'])) {
            throw new \InvalidArgumentException('The file must be an array with keys tmp_name, name, type, size, and error');
        }

        return new UploadedFile(
            $file['tmp_name'],
            (int) $file['size'],
            (int) $file['error'],
            $file['name'],
            $file['type']
        );
    }

    /**
     * Filter HTTP headers.
     */
    protected function filterHeaders(array $headers): array
    {
        $result = [];
        
        foreach ($headers as $name => $value) {
            $name = strtolower($name);
            if (is_array($value)) {
                $result[$name] = array_map('strval', array_values($value));
            } else {
                $result[$name] = [strval($value)];
            }
        }
        
        return $result;
    }

    // PSR-7 Message Interface Methods

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        if ($version === $this->protocolVersion) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        if (!is_string($name) || $name === '') {
            throw new \InvalidArgumentException('Header name must be a non-empty string');
        }
        
        $name = strtolower($name);
        
        // Debug: Log the header being accessed
        if (!isset($this->headers[$name])) {
            error_log(sprintf('Header not found: %s', $name));
            return [];
        }
        
        // Ensure we return an array of strings
        $headerValues = $this->headers[$name];
        if (!is_array($headerValues)) {
            error_log(sprintf('Header value is not an array: %s', print_r($headerValues, true)));
            return [];
        }
        
        return array_map('strval', $headerValues);
    }

    public function getHeaderLine($name): string
    {
        try {
            $headerValues = $this->getHeader($name);
            
            // Debug: Log the header values
            error_log(sprintf('Header values for %s: %s', $name, print_r($headerValues, true)));
            
            // Ensure all values are strings before imploding
            $headerValues = array_map('strval', $headerValues);
            
            return implode(', ', $headerValues);
        } catch (\Throwable $e) {
            error_log(sprintf('Error in getHeaderLine(%s): %s', $name, $e->getMessage()));
            return '';
        }
    }

    public function withHeader($name, $value): self
    {
        $normalized = $this->filterHeaders([$name => $value]);
        $new = clone $this;
        $new->headers = array_merge($new->headers, $normalized);
        return $new;
    }

    public function withAddedHeader($name, $value): self
    {
        if (!is_string($name) || $name === '') {
            throw new \InvalidArgumentException('Header name must be a non-empty string');
        }

        $new = clone $this;
        $new->headers[$name] = array_merge(
            $this->getHeader($name),
            is_array($value) ? $value : [$value]
        );
        
        return $new;
    }

    public function withoutHeader($name): self
    {
        $normalized = strtolower($name);
        
        if (!isset($this->headers[$normalized])) {
            return $this;
        }
        
        $new = clone $this;
        unset($new->headers[$normalized]);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    // PSR-7 Request Interface Methods

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        $query = $this->uri->getQuery();
        
        if ($query !== '') {
            $target .= '?' . $query;
        }
        
        return $target ?: '/';
    }

    public function withRequestTarget($requestTarget): self
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        $method = strtoupper($method);
        
        if (!is_string($method) || $method === '') {
            throw new \InvalidArgumentException('Method must be a non-empty string');
        }
        
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            if ($uri->getHost() !== '') {
                $new->headers['host'] = [$uri->getHost()];
                if ($uri->getPort() !== null) {
                    $new->headers['host'][0] .= ':' . $uri->getPort();
                }
            }
        }

        return $new;
    }

    // PSR-7 ServerRequest Interface Methods

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): self
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): self
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value): self
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name): self
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    /**
     * Check if the request is an XMLHttpRequest (AJAX).
     */
    public function isXmlHttpRequest(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }
}
