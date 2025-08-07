<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private const PHRASES = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable'
    ];

    private string $protocolVersion = '1.1';
    private array $headers = [];
    private array $headerNames = [];
    private StreamInterface $body;
    private int $statusCode = 200;
    private string $reasonPhrase = '';

    public function __construct(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        $this->statusCode = $status;
        $this->body = $this->createStream($body);
        $this->protocolVersion = $version;
        $this->reasonPhrase = $reason ?? self::PHRASES[$status] ?? '';
        $this->setHeaders($headers);
    }

    public static function create($body = null, int $status = 200, array $headers = []): self
    {
        return new self($status, $headers, $body);
    }

    /**
     * Sends HTTP headers and the response body to the client.
     *
     * @return $this
     */
    public function send(): self
    {
        // Send status line
        $protocol = $this->getProtocolVersion();
        $status = $this->getStatusCode();
        $reason = $this->getReasonPhrase();
        header("HTTP/{$protocol} {$status} {$reason}");

        // Send headers
        foreach ($this->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $replace = true; // Replace any previous header with the same name

            foreach ($values as $value) {
                header("{$name}: {$value}", $replace);
                $replace = false; // For multiple values, don't replace subsequent headers
            }
        }

        // Send cookies if any (handled by PHP's header() function)

        // Send body
        echo (string) $this->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        }

        return $this;
    }

    public static function json($data, int $status = 200, array $headers = []): self
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new self($status, $headers, $body);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): self
    {
        $headers['Location'] = $url;
        return new self($status, $headers);
    }

    public static function error(string $message, int $status = 500, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/plain; charset=utf-8';
        return new self($status, $headers, $message);
    }

    private function createStream($body = null): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        if (is_string($body)) {
            return Stream::create($body);
        }
        if (is_resource($body)) {
            return new Stream($body);
        }
        return new Stream('php://temp', 'r+');
    }

    private function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $normalized = strtolower($name);
            $value = is_array($value) ? $value : [$value];

            // Remove any existing header with the same normalized name
            if (isset($this->headerNames[$normalized])) {
                $existingName = $this->headerNames[$normalized];
                unset($this->headers[$existingName]);
            }

            $this->headerNames[$normalized] = $name;
            $this->headers[$name] = $value;
        }
    }

    // PSR-7 Interface Methods
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function hasHeader($name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }
    public function getHeader($name): array
    {
        return $this->headers[$this->headerNames[strtolower($name)] ?? ''] ?? [];
    }
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }
    public function getBody(): StreamInterface
    {
        return $this->body;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withProtocolVersion($version): self
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function withHeader($name, $value): self
    {
        $normalized = strtolower($name);
        $value = is_array($value) ? $value : [$value];

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;
        return $new;
    }

    public function withAddedHeader($name, $value): self
    {
        return $this->hasHeader($name)
            ? $this->withHeader($name, array_merge($this->getHeader($name), (array)$value))
            : $this->withHeader($name, $value);
    }

    public function withoutHeader($name): self
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $new = clone $this;
        unset($new->headers[$new->headerNames[$normalized]], $new->headerNames[$normalized]);
        return $new;
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

    public function withStatus($code, $reasonPhrase = ''): self
    {
        if ($code === $this->statusCode && $reasonPhrase === $this->reasonPhrase) {
            return $this;
        }
        $new = clone $this;
        $new->statusCode = (int)$code;
        $new->reasonPhrase = $reasonPhrase ?: self::PHRASES[$code] ?? '';
        return $new;
    }

    /**
     * Add a flash message to the response.
     * This is a simple implementation that stores the message in the session.
     */
    public function with(string $key, string $message): self
    {
        // Store flash message in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash'][$key] = $message;
        }

        return $this;
    }
}
