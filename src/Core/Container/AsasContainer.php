<?php

namespace IslamWiki\Core\Container;

// PSR-11 ContainerInterface should be available via Composer autoload

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * AsasContainer (أساس) - Foundation Container
 *
 * Dependency injection container for IslamWiki.
 * Asas means "foundation" or "base" in Arabic, representing the
 * foundational layer that holds and manages all application services.
 */
class AsasContainer implements ContainerInterface
{
    /**
     * The container's bindings.
     */
    protected array $bindings = [];

    /**
     * The container's shared instances.
     */
    protected array $instances = [];

    /**
     * The container's aliases.
     */
    protected array $aliases = [];

    /**
     * The container's resolving callbacks.
     */
    protected array $resolvingCallbacks = [];

    /**
     * The container's parameter overrides.
     */
    protected array $with = [];

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?: $abstract,
            'shared' => $shared,
        ];
    }

    /**
     * Register a shared binding in the container.
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Register an alias with the container.
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$abstract] = $alias;
    }

    /**
     * Remove a binding/instance/alias from the container.
     *
     * This allows systems like the skin manager to invalidate cached
     * instances safely (e.g., 'skin.data').
     */
    public function forget(string $abstract): void
    {
        $abstract = $this->getAlias($abstract);
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }
        if (isset($this->bindings[$abstract])) {
            unset($this->bindings[$abstract]);
        }
        // Remove any aliases that point to this abstract
        foreach ($this->aliases as $alias => $target) {
            if ($target === $abstract) {
                unset($this->aliases[$alias]);
            }
        }
    }

    /**
     * Resolve the given type from the container.
     */
    public function get($id)
    {
        try {
            $result = $this->resolve($id);

            // Call any resolving callbacks
            $this->fireResolvingCallbacks($id, $result);

            return $result;
        } catch (\Exception $e) {
            if ($this->has($id)) {
                throw $e;
            }

            throw new \InvalidArgumentException("No binding found for [{$id}].");
        }
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) ||
               isset($this->instances[$id]) ||
               isset($this->aliases[$id]);
    }

    /**
     * Resolve the given type from the container.
     */
    protected function resolve(string $abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // If an instance of the type is currently being managed as a singleton, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation for the given abstract type
        $concrete = $this->getConcrete($abstract);

        // If the concrete is a closure or doesn't match the abstract, we'll build it
        if ($concrete !== $abstract && ! $concrete instanceof \Closure) {
            $concrete = $this->getConcrete($concrete);
        }

        // If the type is a closure, execute it
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        // If the concrete is the same as the abstract, we'll try to resolve it
        if ($concrete === $abstract) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->resolve($concrete, $parameters);
        }

        // If the type is a shared binding, store the instance
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     */
    protected function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Determine if a given type is shared.
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
               (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Get the alias for an abstract if available.
     */
    protected function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Instantiate a concrete instance of the given type.
     */
    public function build(string $concrete, array $parameters = [])
    {
        // If the concrete is a closure, execute it
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new \InvalidArgumentException("Target class [$concrete] does not exist.", 0, $e);
        }

        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface or Abstract Class
        if (! $reflector->isInstantiable()) {
            $this->notInstantiable($concrete);
        }

        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies
        // and we can just resolve the instances of the objects right away.
        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        $instances = $this->resolveDependencies(
            $dependencies,
            $parameters
        );

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     */
    protected function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If the parameter has a type-hint, we will resolve that type from the container.
            // Otherwise, we'll check if the parameter has a default value.
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];
            } elseif ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);
            } elseif ($dependency->getType() && ! $dependency->getType()->isBuiltin()) {
                $results[] = $this->resolveClass($dependency);
            } elseif ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
            } else {
                $declaringClass = $dependency->getDeclaringClass();
                $className = $declaringClass ? $declaringClass->getName() : 'unknown';
                $message = 'Unresolvable dependency resolving [' . $dependency->getName() . '] in class ' . $className;
                throw new \RuntimeException($message);
            }
        }

        return $results;
    }

    /**
     * Determine if the given dependency has a parameter override.
     */
    protected function hasParameterOverride(ReflectionParameter $dependency): bool
    {
        return array_key_exists(
            $dependency->name,
            $this->getLastParameterOverride()
        );
    }

    /**
     * Get a parameter override for a dependency.
     */
    protected function getParameterOverride(ReflectionParameter $dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Get the last parameter override.
     */
    protected function getLastParameterOverride(): array
    {
        return end($this->with) ?: [];
    }

    /**
     * Resolve a class based dependency from the container.
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getType()->getName());
        } catch (\Exception $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Throw an exception that the concrete is not instantiable.
     */
    protected function notInstantiable(string $concrete): void
    {
        throw new \RuntimeException("Target [$concrete] is not instantiable.");
    }

    /**
     * Resolve the given type from the container.
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Register a new resolving callback for a type.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $callback
     * @return void
     */
    public function afterResolving($abstract, $callback = null)
    {
        if (is_string($callback)) {
            $callback = function ($object, $container) use ($callback) {
                return $container->$callback($object);
            };
        }

        if ($abstract instanceof \Closure && $callback === null) {
            $this->resolvingCallbacks['*'][] = $abstract;
        } else {
            $this->resolvingCallbacks[$this->getAlias($abstract)][] = $callback;
        }
    }

    /**
     * Fire all of the resolving callbacks.
     *
     * @param  string  $abstract
     * @param  mixed   $object
     * @return void
     */
    protected function fireResolvingCallbacks($abstract, $object)
    {
        $this->fireCallbackArray(
            $object,
            $this->getCallbacksForType($abstract, $object, $this->resolvingCallbacks)
        );

        $this->fireCallbackArray(
            $object,
            $this->getCallbacksForType('*', $object, $this->resolvingCallbacks)
        );
    }

    /**
     * Get all callbacks for a given type.
     *
     * @param  string  $abstract
     * @param  mixed   $object
     * @param  array   $callbacks
     * @return array
     */
    protected function getCallbacksForType($abstract, $object, array $callbacks)
    {
        $results = [];

        foreach ($callbacks as $type => $typeCallbacks) {
            if ($type === $abstract || $type === get_class($object)) {
                $results = array_merge($results, $typeCallbacks);
            }
        }

        return $results;
    }

    /**
     * Fire an array of callbacks with an object.
     *
     * @param  mixed  $object
     * @param  array  $callbacks
     * @return void
     */
    protected function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($object, $this);
        }
    }
}
