<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database\Schema;

class Fluent
{
    /**
     * All of the attributes set on the fluent instance.
     */
    protected array $attributes = [];

    /**
     * Create a new fluent instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get an attribute from the fluent instance.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Get the attributes from the fluent instance.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to an array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to JSON.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Determine if the given offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     */
    public function __call(string $method, array $parameters): self
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;
        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     */
    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }
}
