<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use Exception;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Container
{
    /**
     * -----------------------------------------
     * Registered bindings
     * -----------------------------------------
     */
    protected array $bindings = [];

    /**
     * -----------------------------------------
     * Shared singleton instances
     * -----------------------------------------
     */
    protected array $instances = [];

    /**
     * -----------------------------------------
     * Singleton bindings
     * -----------------------------------------
     */
    protected array $singletons = [];

    /**
     * -----------------------------------------
     * Bind abstraction
     * -----------------------------------------
     */
    public function bind(
        string $abstract,
        string|callable|null $concrete = null
    ): void {

        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * -----------------------------------------
     * Register singleton binding
     * -----------------------------------------
     */
    public function singleton(
        string $abstract,
        string|callable|null $concrete = null
    ): void {

        $this->singletons[$abstract] = $concrete ?? $abstract;
    }

    /**
     * -----------------------------------------
     * Register existing instance
     * -----------------------------------------
     */
    public function instance(
        string $abstract,
        object $instance
    ): void {

        $this->instances[$abstract] = $instance;
    }

    /**
     * -----------------------------------------
     * Resolve a single parameter
     * -----------------------------------------
     */
    private function resolveParameter(
        ReflectionParameter $parameter,
        array $overrides = []
    ): mixed {

        /**
         * -----------------------------------------
         * Parameter override
         * -----------------------------------------
         */
        if (
            array_key_exists(
                $parameter->getName(),
                $overrides
            )
        ) {

            return $overrides[
                $parameter->getName()
            ];
        }

        /**
         * -----------------------------------------
         * Resolve class/interface dependency
         * -----------------------------------------
         */
        $type = $parameter->getType();

        if (
            $type instanceof ReflectionNamedType &&
            !$type->isBuiltin()
        ) {

            return $this->get(
                $type->getName()
            );
        }

        /**
         * -----------------------------------------
         * Default value
         * -----------------------------------------
         */
        if (
            $parameter->isDefaultValueAvailable()
        ) {

            return $parameter->getDefaultValue();
        }

        /**
         * -----------------------------------------
         * Unable to resolve
         * -----------------------------------------
         */
        throw new Exception(
            sprintf(
                'Unable to resolve parameter "$%s".',
                $parameter->getName()
            )
        );
    }


    /**
     * -----------------------------------------
     * Get a new instance of a class or callable
     * -----------------------------------------
     */
    public function get(
        string $abstract
    ): object {

        /**
         * -----------------------------------------
         * Existing singleton instance
         * -----------------------------------------
         */
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        /**
         * -----------------------------------------
         * Determine concrete
         * -----------------------------------------
         */
        $concrete =
            $this->singletons[$abstract]
            ?? $this->bindings[$abstract]
            ?? $abstract;

        /**
         * -----------------------------------------
         * Build instance
         * -----------------------------------------
         */
        $instance = $this->make(
            $concrete
        );

        /**
         * -----------------------------------------
         * Store singleton instance
         * -----------------------------------------
         */
        if (isset($this->singletons[$abstract])) {

            $this->instances[$abstract] =
                $instance;
        }

        return $instance;
    }


    /**
     * -----------------------------------------
     * Build a new instance
     * -----------------------------------------
     */
    public function make(
        string|callable $concrete,
        array $parameters = []
    ): object {

        /**
         * -----------------------------------------
         * Factory binding
         * -----------------------------------------
         */
        if (
            $concrete instanceof Closure ||
            is_callable($concrete)
        ) {

            return $concrete($this);
        }

        /**
         * -----------------------------------------
         * Validate class existence
         * -----------------------------------------
         */
        if (!class_exists($concrete)) {

            throw new Exception(
                "Class {$concrete} not found."
            );
        }

        /**
         * -----------------------------------------
         * Reflection
         * -----------------------------------------
         */
        $reflection = new ReflectionClass(
            $concrete
        );

        /**
         * -----------------------------------------
         * Prevent invalid instantiation
         * -----------------------------------------
         */
        if (
            $reflection->isInterface() ||
            $reflection->isAbstract()
        ) {

            throw new Exception(
                "Cannot instantiate {$concrete}."
            );
        }

        /**
         * -----------------------------------------
         * Constructor
         * -----------------------------------------
         */
        $constructor =
            $reflection->getConstructor();

        /**
         * -----------------------------------------
         * No constructor
         * -----------------------------------------
         */
        if (!$constructor) {

            return new $concrete();
        }

        /**
         * -----------------------------------------
         * Resolve constructor dependencies
         * -----------------------------------------
         */
        $dependencies = [];

        foreach (
            $constructor->getParameters()
            as $parameter
        ) {

            $dependencies[] =
                $this->resolveParameter(
                    $parameter,
                    $parameters
                );
        }

        /**
         * -----------------------------------------
         * Instantiate class
         * -----------------------------------------
         */
        return $reflection->newInstanceArgs(
            $dependencies
        );
    }


    /**
     * -----------------------------------------
     * Call a method/function with dependency injection
     * -----------------------------------------
     */
    public function call(
        callable|array $callback,
        array $parameters = []
    ): mixed {

        /**
         * -----------------------------------------
         * Reflection
         * -----------------------------------------
         */
        if (is_array($callback)) {

            $reflection = new ReflectionMethod(
                $callback[0],
                $callback[1]
            );

            $object = $callback[0];

        } else {

            $reflection = new ReflectionFunction(
                $callback
            );

            $object = null;
        }

        /**
         * -----------------------------------------
         * Resolve method dependencies
         * -----------------------------------------
         */
        $dependencies = [];

        foreach (
            $reflection->getParameters()
            as $parameter
        ) {

            $dependencies[] =
                $this->resolveParameter(
                    $parameter,
                    $parameters
                );
        }

        /**
         * -----------------------------------------
         * Invoke callback
         * -----------------------------------------
         */
        return $reflection->invokeArgs(
            $object,
            $dependencies
        );
    }
}