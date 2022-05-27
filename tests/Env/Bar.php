<?php

namespace adevws\Container\Tests\Env;

class Bar
{
    protected IFoo $foo;

    public function __construct(IFoo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): IFoo
    {
        return $this->foo;
    }
}