<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionParameter;

/**
 * Container - DI Container with auto-wiring
 *
 * 1. Explicit bindings: interface/class → factory (e.g. repositories, services).
 * 2. Auto-wiring: for any class not registered, the container instantiates it
 *    by resolving constructor parameters from the container (reflection).
 *
 * So you only register "leaf" dependencies in config; controllers are resolved
 * automatically when their constructor type-hints interfaces already bound.
 *
 * @package App\Core
 * @author BdeLive - Group 8
 * @version 2.0.0 - Auto-wiring
 */
class Container
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, object> Resolved instances for bound ids (singleton per id) */
    private array $instances = [];

    /**
     * Register a binding in the container
     *
     * @param string $id Service identifier (class or interface name)
     * @param callable $builder Factory callable that receives the container and returns the instance
     * @return self For method chaining
     */
    public function set(string $id, callable $builder): self
    {
        $this->bindings[$id] = $builder;
        return $this;
    }

    /**
     * True if the container can resolve this id (binding or auto-wirable class)
     */
    public function has(string $id): bool
    {
        if (isset($this->bindings[$id])) {
            return true;
        }
        return class_exists($id);
    }

    /**
     * Resolve by id: use binding if present, else auto-wire the class
     *
     * @throws \InvalidArgumentException When id is not bound and not auto-wirable
     */
    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $instance = ($this->bindings[$id])($this);
            $this->instances[$id] = $instance;
            return $instance;
        }

        if (class_exists($id)) {
            return $this->resolveClass($id);
        }

        throw new \InvalidArgumentException('Container: cannot resolve ' . $id);
    }

    /**
     * Instantiate a class by resolving its constructor parameters from the container
     *
     * @param class-string $class Fully qualified class name
     */
    private function resolveClass(string $class): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                throw new \InvalidArgumentException(
                    'Container: cannot auto-wire ' . $class . ', parameter $' . $param->getName() . ' has no class/interface type'
                );
            }
            $args[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($args);
    }
}
