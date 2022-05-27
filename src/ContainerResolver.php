<?php

namespace adevws\Container;

use adevws\Container\Exceptions\NotFoundException;
use adevws\Container\Exceptions\ContainerBindingException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

abstract class ContainerResolver {

    /**
     * @var array<Bind>
     */
    protected array $bindings = [];

    /**
     * @var array
     */
    protected array $instances = [];

    /**
     * @var array
     */
    protected array $aliases = [];

    /**
     * @var array<ReflectionClass>
     */
    protected array $reflection_class = [];

    /**
     * @var array<ReflectionMethod>
     */
    protected array $reflection_method = [];

    /**
     * @template T
     * @param string|class-string<T> $abstract
     * @return mixed|object|T
     */
    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract]))
            return $this->instances[$abstract];

        $dependency = $this->resolve($abstract);

        $instance = $this->build($dependency->concrete, $dependency->arguments);

        foreach ($dependency->extenders as $callback) {
            $new_instance = $callback($instance, $this);
            if ($new_instance !== null) {
                $instance = $new_instance;
            }
        }

        if ($dependency->single) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Resolve o tipo da abstração
     *
     * @param string $abstract
     * @param array $stack
     * @return Bind
     */
    protected function resolve(string $abstract, array &$stack = []): Bind
    {
        // Verifica se existe um 'alias' para a abstração
        if (isset($this->aliases[$abstract])) {
            $alias = $this->aliases[$abstract];

            if (in_array($alias, $stack)) {

                $stack[] = $alias;
                $error = implode(' -> ', $stack);

                throw new ContainerBindingException("Circular reference is detected: $error");
            } else {

                $stack[] = $alias;
                return $this->resolve($alias, $stack);
            }
        }

        if (!isset($this->bindings[$abstract]))
            $this->bind($abstract);

        return $this->bindings[$abstract];
    }

    /**
     * Builds an instance of a concrete type
     *
     * @param string|callable $concrete
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException|ContainerBindingException
     */
    protected function build(string|callable $concrete, array $arguments = []): mixed
    {
        if (is_callable($concrete))
            return $concrete($this, $arguments);

        if (isset($this->reflection_class[$concrete])) {

            $reflection = $this->reflection_class[$concrete];

        } else try {

            $reflection = $this->reflection_class[$concrete] = new ReflectionClass($concrete);

        } catch (ReflectionException $e) {

            throw new NotFoundException($e->getMessage(), 0, $e);

        }

        if (!$reflection->isInstantiable())
            throw new ContainerBindingException("The '${concrete}' type is not instantiable");

        $constructor = $this->reflection_method[$concrete] ?? ($this->reflection_method[$concrete] = $reflection->getConstructor());

        if (is_null($constructor))
            return new $concrete();

        // Resolve arguments
        $parameters = array_diff_key($constructor->getParameters(), $arguments);
        foreach ($parameters as $key => $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType) {
                $class = $type->getName();

                if (in_array($class, ['bool', 'int', 'float', 'string', 'callable', 'array', 'object', 'self', 'mixed'])) {

                    if ($parameter->isDefaultValueAvailable()) {
                        $arguments[$key] = $parameter->getDefaultValue();
                        continue;
                    }

                    if ($parameter->allowsNull()) {
                        $arguments[$key] = null;
                        continue;
                    }

                    throw new ContainerBindingException("Could not resolve '{$parameter}' for building {$concrete}");
                }

                try {

                    if (isset($this->bindings[$class])) $arguments[$key] = $this->make($class);
                    else $arguments[$key] = $this->build($class);

                } catch (ContainerBindingException $e) {

                    if ($parameter->isDefaultValueAvailable()) {
                        $arguments[$key] = $parameter->getDefaultValue();
                        continue;
                    }

                    if ($parameter->allowsNull()) {
                        $arguments[$key] = null;
                        continue;
                    }

                    throw $e;
                }

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[$key] = $parameter->getDefaultValue();
                continue;
            }

            if (($type instanceof \ReflectionUnionType) && $parameter->allowsNull()) {
                $arguments[$key] = null;
                continue;
            }

            throw new ContainerBindingException("Could not resolve '{$parameter}' for building $concrete");
        }

        ksort($arguments);

        return $reflection->newInstanceArgs($arguments);
    }
}