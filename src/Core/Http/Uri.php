<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private $scheme = '';
    private $userInfo = '';
    private $host = '';
    private $port;
    private $path = '';
    private $query = '';
    private $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new \InvalidArgumentException("Unable to parse URI: $uri");
            }
            $this->applyParts($parts);
        }
    }

    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $this->userInfo = $parts['user'] ?? '';
        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = $parts['port'] ?? null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQuery($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }
    public function getHost(): string
    {
        return $this->host;
    }
    public function getPort(): ?int
    {
        return $this->port;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getQuery(): string
    {
        return $this->query;
    }
    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme($scheme): self
    {
        $scheme = $this->filterScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    public function withUserInfo($user, $password = null): self
    {
        $info = $user;
        if ($password !== '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }
        $new = clone $this;
        $new->userInfo = $info;
        return $new;
    }

    public function withHost($host): self
    {
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort($port): self
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    public function withPath($path): self
    {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withQuery($query): self
    {
        $query = $this->filterQuery($query);
        if ($this->query === $query) {
            return $this;
        }
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment($fragment): self
    {
        $fragment = $this->filterQuery($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    public function __toString(): string
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }
        if (($authority = $this->getAuthority()) !== '') {
            $uri .= '//' . $authority;
        }
        if ($this->path !== '') {
            $uri .= $this->path;
        }
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }

    private function filterScheme($scheme): string
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('Scheme must be a string');
        }
        return strtolower($scheme);
    }

    private function filterPort($port): ?int
    {
        if ($port === null) {
            return null;
        }
        $port = (int) $port;
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('Port must be between 1 and 65535');
        }
        return $port;
    }

    private function filterPath($path): string
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]++|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    private function filterQuery($query): string
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException('Query must be a string');
        }
        return $this->filterQueryOrFragment($query);
    }

    private function filterQueryOrFragment($str): string
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Query or fragment must be a string');
        }
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=:@\/\?%]++|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $str
        );
    }
}
