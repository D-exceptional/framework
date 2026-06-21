<?php

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionNamedType;

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
        string|callable $concrete
    ): void {

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * -----------------------------------------
     * Register singleton binding
     * -----------------------------------------
     */
    public function singleton(
        string $abstract,
        string|callable $concrete
    ): void {

        $this->singletons[$abstract] = $concrete;
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
     * Resolve dependency
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
         * Factory binding
         * -----------------------------------------
         */
        if (
            $concrete instanceof Closure ||
            is_callable($concrete)
        ) {

            $instance = $concrete($this);

            /**
             * -----------------------------------------
             * Store singleton factory result
             * -----------------------------------------
             */
            if (isset($this->singletons[$abstract])) {

                $this->instances[$abstract] = $instance;
            }

            return $instance;
        }

        /**
         * -----------------------------------------
         * Validate class existence
         * -----------------------------------------
         */
        if (!class_exists($concrete)) {

            throw new Exception(
                "Class {$concrete} not found"
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
                "Cannot instantiate {$concrete}"
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

            $instance = new $concrete();

        } else {

            $dependencies = [];

            /**
             * -----------------------------------------
             * Resolve dependencies recursively
             * -----------------------------------------
             */
            foreach (
                $constructor->getParameters()
                as $parameter
            ) {

                $type = $parameter->getType();

                /**
                 * -----------------------------------------
                 * Class dependency
                 * -----------------------------------------
                 */
                if (
                    $type instanceof ReflectionNamedType &&
                    !$type->isBuiltin()
                ) {

                    $dependencies[] =
                        $this->get(
                            $type->getName()
                        );

                } else {

                    /**
                     * -----------------------------------------
                     * Primitive/default values
                     * -----------------------------------------
                     */
                    if (
                        $parameter
                            ->isDefaultValueAvailable()
                    ) {

                        $dependencies[] =
                            $parameter
                                ->getDefaultValue();

                    } else {

                        throw new Exception(
                            "Unable to resolve parameter \${$parameter->getName()} in {$concrete}"
                        );
                    }
                }
            }

            /**
             * -----------------------------------------
             * Instantiate class
             * -----------------------------------------
             */
            $instance =
                $reflection->newInstanceArgs(
                    $dependencies
                );
        }

        /**
         * -----------------------------------------
         * Cache singleton instance
         * -----------------------------------------
         */
        if (isset($this->singletons[$abstract])) {

            $this->instances[$abstract] =
                $instance;
        }

        return $instance;
    }
}