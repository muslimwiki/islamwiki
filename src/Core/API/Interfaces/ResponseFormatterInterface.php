<?php

declare(strict_types=1);

namespace IslamWiki\Core\API\Interfaces;

use Psr\Http\Message\ResponseInterface;

/**
 * Response Formatter Interface
 *
 * Defines the contract for API response formatting.
 */
interface ResponseFormatterInterface
{
    /**
     * Format data into a response.
     *
     * @param mixed $data The data to format
     * @param int $statusCode The HTTP status code
     * @return ResponseInterface The formatted response
     */
    public function format($data, int $statusCode = 200): ResponseInterface;

    /**
     * Get supported content type.
     *
     * @return string The content type
     */
    public function getContentType(): string;
}
