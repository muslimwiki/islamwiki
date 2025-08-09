<?php

declare(strict_types=1);

namespace IslamWiki\Core\View;

/**
 * Lightweight parameter bag for Twig templates.
 * Provides a get($key, $default) method so templates can call
 * app.request.query.get('q', '') safely.
 */
class ParamsBag
{
    private array $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->params;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }
}
