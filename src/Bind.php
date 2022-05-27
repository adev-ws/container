<?php

namespace adevws\Container;

class Bind {

    /**
     * @var string|callable
     */
    public $concrete;

    /**
     * @var bool
     */
    public bool $single;

    /**
     * @var callable[]
     */
    public array $extenders = [];

    /**
     * @var array
     */
    public array $arguments;

    /**
     * Construtor da bind
     * @param string|callable $concrete
     * @param array $arguments
     * @param bool $single
     */
    public function __construct(string|callable $concrete, array $arguments, bool $single)
    {
        $this->concrete = $concrete;
        $this->arguments = $arguments;
        $this->single = $single;
    }

    /**
     * @param callable $callback
     */
    public function addExtend(callable $callback): void
    {
        $this->extenders[] = $callback;
    }
}