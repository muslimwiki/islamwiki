<?php
declare(strict_types=1);

namespace IslamWiki\Core\API\Formatters;

use IslamWiki\Core\API\Interfaces\ResponseFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * XML Response Formatter
 * 
 * Formats API responses as XML.
 * TODO: Implement XML formatting logic
 */
class XmlResponseFormatter implements ResponseFormatterInterface
{
    private StreamFactoryInterface $streamFactory;
    
    /**
     * Create a new XML response formatter.
     */
    public function __construct(StreamFactoryInterface $streamFactory = null)
    {
        $this->streamFactory = $streamFactory ?? new \GuzzleHttp\Psr7\StreamFactory();
    }
    
    /**
     * Format data as XML response.
     */
    public function format($data, int $statusCode = 200): ResponseInterface
    {
        // TODO: Implement XML formatting
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?><response><error>XML format not yet implemented</error></response>';
        
        $stream = $this->streamFactory->createStream($xmlData);
        
        return new \GuzzleHttp\Psr7\Response(
            $statusCode,
            ['Content-Type' => 'application/xml'],
            $stream
        );
    }
    
    /**
     * Get supported content type.
     */
    public function getContentType(): string
    {
        return 'application/xml';
    }
} 