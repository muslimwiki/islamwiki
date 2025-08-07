<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    /**
     * The underlying stream resource
     */
    private $stream;

    /**
     * Stream metadata
     */
    private array $metadata;

    /**
     * Create a new Stream instance
     *
     * @param string|resource $stream Stream file pointer or string content
     * @param string $mode Mode with which to open stream
     * @throws \InvalidArgumentException If the stream is not a string or resource
     */
    public function __construct($stream = 'php://temp', string $mode = 'r+')
    {
        if (is_string($stream)) {
            $resource = @fopen($stream, $mode);
            if ($resource === false) {
                throw new \InvalidArgumentException("Unable to open stream: {$stream}");
            }
            $stream = $resource;
        }

        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        $this->stream = $stream;
        $this->metadata = stream_get_meta_data($this->stream);
    }

    /**
     * Create a new Stream from a string
     */
    public static function create(string $content = ''): self
    {
        $stream = new self('php://temp', 'r+');
        if ($content !== '') {
            $stream->write($content);
            $stream->rewind();
        }
        return $stream;
    }

    /**
     * Reads all data from the stream into a string
     */
    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\RuntimeException $e) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * Separates any underlying resources from the stream
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->metadata = [];

        return $result;
    }

    /**
     * Get the size of the stream if known
     */
    public function getSize(): ?int
    {
        if (!isset($this->stream)) {
            return null;
        }

        $stats = fstat($this->stream);
        return $stats['size'] ?? null;
    }

    /**
     * Returns the current position of the file read/write pointer
     */
    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * Returns true if the stream is at the end of the stream
     */
    public function eof(): bool
    {
        return !isset($this->stream) || feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable
     */
    public function isSeekable(): bool
    {
        return isset($this->stream) && $this->getMetadata('seekable');
    }

    /**
     * Seek to a position in the stream
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException(
                'Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true)
            );
        }
    }

    /**
     * Seek to the beginning of the stream
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable
     */
    public function isWritable(): bool
    {
        if (!isset($this->stream)) {
            return false;
        }

        $mode = $this->getMetadata('mode');
        return str_contains($mode, 'w') || str_contains($mode, '+') || str_contains($mode, 'a');
    }

    /**
     * Write data to the stream
     */
    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is readable
     */
    public function isReadable(): bool
    {
        if (!isset($this->stream)) {
            return false;
        }

        $mode = $this->getMetadata('mode');
        return str_contains($mode, 'r') || str_contains($mode, '+');
    }

    /**
     * Read data from the stream
     */
    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    /**
     * Returns the remaining contents in a string
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
