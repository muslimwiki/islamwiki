<?php
declare(strict_types=1);

namespace IslamWiki\Core\API\Formatters;

use IslamWiki\Core\API\Interfaces\ResponseFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * JSON Response Formatter
 * 
 * Formats API responses as JSON.
 */
class JsonResponseFormatter implements ResponseFormatterInterface
{
    private StreamFactoryInterface $streamFactory;
    
    /**
     * Create a new JSON response formatter.
     */
    public function __construct(StreamFactoryInterface $streamFactory = null)
    {
        $this->streamFactory = $streamFactory ?? new \GuzzleHttp\Psr7\StreamFactory();
    }
    
    /**
     * Format data as JSON response.
     */
    public function format($data, int $statusCode = 200): ResponseInterface
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonData = json_encode(['error' => 'Invalid data format'], JSON_PRETTY_PRINT);
            $statusCode = 500;
        }
        
        $stream = $this->streamFactory->createStream($jsonData);
        
        return new \GuzzleHttp\Psr7\Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            $stream
        );
    }
    
    /**
     * Get supported content type.
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
} 