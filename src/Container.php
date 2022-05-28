<?php

namespace adevws\Container;

use adevws\Container\Exceptions\NotFoundException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class Container extends ContainerResolver implements ContainerInterface {

    /**
     * Instância global do container caso ela existir
     *
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $instance = null;

    /**
     * Retorna a instância global de container
     *
     * @return ContainerInterface|null
     */
    public static function getInstance(): ?ContainerInterface
    {
        return static::$instance;
    }

    /**
     * Salva instância global do container
     *
     * @param ContainerInterface $container
     * @return ContainerInterface
     */
    public static function setInstance(ContainerInterface $container): ContainerInterface
    {
        return static::$instance = $container;
    }

    /**
     * @param string $abstract
     * @param null|string|callable $concrete
     * @param array $arguments
     * @return self
     */
    public function singleton(string $abstract, string|callable|null $concrete = null, ...$arguments): self
    {
        return $this->addBind($abstract, $concrete, $arguments, true);
    }

    /**
     * @param string $abstract
     * @param callable $extender
     * @return $this
     * @throws Exceptions\ContainerBindingException
     */
    public function extends(string $abstract, callable $extender): self
    {
        $this->resolve($abstract)->addExtend($extender);
        return $this;
    }

    /**
     * @param string $alias
     * @param string $type
     * @return Container
     */
    public function alias(string $alias, string $type): self
    {
        $this->aliases[$alias] = $type;
        return $this;
    }

    /**
     * @param string $abstract
     * @param string|callable|null $concrete
     * @param array $arguments
     * @return Container
     */
    public function bind(string $abstract, string|callable|null $concrete = null, array $arguments = []) {
        return $this->addBind($abstract, $concrete, $arguments, false);
    }

    /**
     * @param string $abstract
     * @param null|string|callable $concrete
     * @param array $arguments
     * @param bool $single
     * @return self
     */
    protected function addBind(string $abstract, string|callable|null $concrete, array $arguments, bool $single): self
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!is_string($concrete) && !is_callable($concrete))
            throw new InvalidArgumentException('The second argument must be an instantiable class or a callable');

        $dependency = new Bind($concrete, $arguments, $single);

        unset($this->instances[$abstract]);
        unset($this->aliases[$abstract]);

        $this->bindings[$abstract] = $dependency;

        return $this;
    }

    /**
     * @param string $abstract
     * @return $this
     */
    public function unbind(string $abstract): self
    {
        unset($this->bindings[$abstract]);
        unset($this->instances[$abstract]);
        unset($this->aliases[$abstract]);

        return $this;
    }


    /**
     * @param string $alias
     * @return $this
     */
    public function unalias(string $alias): self
    {
        unset($this->aliases[$alias]);
        return $this;
    }

    /**
     * @template T
     * @param string|class-string<T> $id
     * @return mixed|T
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if (!isset($this->aliases[$id])) {
            throw new NotFoundException();
        }

        return $this->make($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->aliases[$id]);
    }
}