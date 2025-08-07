<?php

declare(strict_types=1);

namespace IslamWiki\Core\API\Formatters;

use IslamWiki\Core\API\Interfaces\ResponseFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * HTML Response Formatter
 *
 * Formats API responses as HTML.
 * TODO: Implement HTML formatting logic
 */
class HtmlResponseFormatter implements ResponseFormatterInterface
{
    private StreamFactoryInterface $streamFactory;

    /**
     * Create a new HTML response formatter.
     */
    public function __construct(StreamFactoryInterface $streamFactory = null)
    {
        $this->streamFactory = $streamFactory ?? new \GuzzleHttp\Psr7\StreamFactory();
    }

    /**
     * Format data as HTML response.
     */
    public function format($data, int $statusCode = 200): ResponseInterface
    {
        // TODO: Implement HTML formatting
        $htmlData = '<!DOCTYPE html><html><head><title>API Response</title></head><body><h1>HTML format not yet implemented</h1></body></html>';

        $stream = $this->streamFactory->createStream($htmlData);

        return new \GuzzleHttp\Psr7\Response(
            $statusCode,
            ['Content-Type' => 'text/html'],
            $stream
        );
    }

    /**
     * Get supported content type.
     */
    public function getContentType(): string
    {
        return 'text/html';
    }
}
