<?php

namespace adevws\Container\Tests\Env;

class Message
{
    private IFoo $foo;
    private string $message;

    public function __construct(IFoo $foo,string $message)
    {
        $this->foo = $foo;
        $this->message = $message;
    }

    public function getFoo(): IFoo
    {
        return $this->foo;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}