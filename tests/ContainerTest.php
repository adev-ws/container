<?php

use adevws\Container\Exceptions\ContainerBindingException;
use adevws\Container\Tests\Env\Bar;
use adevws\Container\Tests\Env\Foo;
use adevws\Container\Tests\Env\IFoo;
use adevws\Container\Tests\Env\Message;

beforeEach(function (){
   $this->container = new adevws\Container\Container;
});

test('testa `make` sem `bind`', function () {

    $foo = $this->container->make(Foo::class);

    expect($foo)
        ->toBeInstanceOf(Foo::class);
});

test('testa falha no `make` sem `bind`', function (){

    $this->container->make(IFoo::class);

})->expectException(ContainerBindingException::class);

test('testa `make` com `bind`', function () {

    $this->container->bind(IFoo::class, Foo::class);

    $foo = $this->container->make(IFoo::class);

    expect($foo)
        ->toBeInstanceOf(Foo::class);
});

test('testa `make` com `bind` usando callback', function () {

    $this->container->bind(IFoo::class, function (){
        return new Foo();
    });

    $foo = $this->container->make(IFoo::class);

    expect($foo)
        ->toBeInstanceOf(Foo::class);
});

test('testa `singleton`', function () {

    $this->container->singleton(IFoo::class, Foo::class);

    $foo1 = $this->container->make(IFoo::class);
    $foo2 = $this->container->make(IFoo::class);

    expect($foo1)
        ->toBe($foo2);
});

test('testa `alias`', function () {

    $this->container->alias('foo', Foo::class);

    $foo = $this->container->make('foo');

    expect($foo)
        ->toBeInstanceOf(Foo::class);
});

test('testa múltiplos `alias`', function () {

    $this->container->alias('foo', Foo::class);

    $this->container->alias('bar', 'foo');

    $foo = $this->container->make('bar');

    expect($foo)
        ->toBeInstanceOf(Foo::class);
});

test('testa falha de múltiplos `alias` em círculos', function () {

    $this->container->alias('foo', Foo::class);

    $this->container->alias('bar', 'foo');
    $this->container->alias('foo', 'bar');

    $this->container->make('bar');

})->expectException(ContainerBindingException::class);

test('testa injeção de dependência', function () {
    $this->container->bind(IFoo::class, Foo::class);

    $bar = $this->container->make(Bar::class);

    expect($bar)
        ->toBeInstanceOf(Bar::class);

    expect($bar->getFoo())
        ->toBeInstanceOf(Foo::class);
});

test('testa argumento na injeção de dependência', function () {
    $this->container->bind(IFoo::class, Foo::class);

    $this->container->bind(Message::class, null, [1=>'Hello World']);

    $message = $this->container->make(Message::class);

    expect($message)
        ->toBeInstanceOf(Message::class);

    expect($message->getFoo())
        ->toBeInstanceOf(Foo::class);

    expect($message->getMessage())
        ->toBe('Hello World');
});

test('testa injeção de dependência usando callback', function () {
    $this->container->bind(IFoo::class, Foo::class);

    $this->container->bind(Message::class, function(adevws\Container\Container $container, array $args = []){
        return new Message($container->make(IFoo::class), 'hi');
    });

    $message = $this->container->make(Message::class);

    expect($message)
        ->toBeInstanceOf(Message::class);

    expect($message->getFoo())
        ->toBeInstanceOf(IFoo::class);

    expect($message->getMessage())
        ->toBe('hi');
});

test('testa injeção de dependência com argumentos usando callback', function () {
    $this->container->bind(IFoo::class, Foo::class);

    $this->container->bind(Message::class, function(adevws\Container\Container $container, array $args = []){
        return new Message($container->make(IFoo::class), $args['message']);
    }, ['message' => 'hello']);

    $message = $this->container->make(Message::class);

    expect($message)
        ->toBeInstanceOf(Message::class);

    expect($message->getFoo())
        ->toBeInstanceOf(IFoo::class);

    expect($message->getMessage())
        ->toBe('hello');
});