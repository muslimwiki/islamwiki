<?php
declare(strict_types=1);

namespace IslamWiki\Core\Http\Exceptions;

use Exception;

class HttpException extends Exception
{
    /**
     * HTTP status code.
     */
    protected int $statusCode;

    /**
     * HTTP headers.
     */
    protected array $headers;

    /**
     * Create a new HTTP exception.
     */
    public function __construct(
        int $statusCode,
        string $message = '',
        ?Exception $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the HTTP headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Create a 404 Not Found exception.
     */
    public static function notFound(string $message = 'Not Found'): self
    {
        return new static(404, $message);
    }

    /**
     * Create a 403 Forbidden exception.
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new static(403, $message);
    }

    /**
     * Create a 401 Unauthorized exception.
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new static(401, $message);
    }

    /**
     * Create a 400 Bad Request exception.
     */
    public static function badRequest(string $message = 'Bad Request'): self
    {
        return new static(400, $message);
    }

    /**
     * Create a 500 Internal Server Error exception.
     */
    public static function serverError(string $message = 'Internal Server Error'): self
    {
        return new static(500, $message);
    }
}
