<?php

namespace adevws\Container\Tests\Env;

interface IFoo {
    public function getValue(): string ;
    public function setValue(string $value);
}