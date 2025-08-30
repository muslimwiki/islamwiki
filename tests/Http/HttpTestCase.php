<?php

namespace Tests\Http;

use BaseTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

abstract class HttpTestCase extends BaseTestCase
{
    protected function createRequest(
        string $method,
        string $path,
        array $serverParams = [],
        $body = null,
        array $headers = [],
        string $version = '1.1'
    ): ServerRequestInterface {
        $uri = new Uri('http://localhost' . $path);
        $request = new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $version,
            $serverParams
        );

        return $request;
    }

    protected function get(ServerRequestInterface $request): ResponseInterface
    {
        // This method will be implemented once we have our router setup
        throw new \RuntimeException('Router not implemented yet');
    }

    protected function getJson(ServerRequestInterface $request): array
    {
        $response = $this->get($request);
        $this->assertJson((string) $response->getBody());
        return json_decode((string) $response->getBody(), true);
    }

    protected function assertResponseStatus(ResponseInterface $response, int $expectedStatus): void
    {
        $this->assertSame($expectedStatus, $response->getStatusCode());
    }

    protected function assertJsonResponse(ResponseInterface $response, array $expectedData): void
    {
        $this->assertResponseStatus($response, 200);
        $data = json_decode((string) $response->getBody(), true);
        $this->assertSame($expectedData, $data);
    }
}
