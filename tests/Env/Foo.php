<?php

namespace adevws\Container\Tests\Env;

class Foo implements IFoo
{
    private string $value;

    public function __construct(string $value = 'foo')
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }
}