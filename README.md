# Container 

`adevws\container` é um gerenciador de injeção de dependências baseado no `opis/database` com algumas alterações


## Instalação

esse pacote esta disponível no [Packagist] e pode ser instalado usando [Composer]

```shell
composer require adevws/container
```

## Como usar

Com a instância do container, você pode iniciar novos objetos com a função `make`

```php
use adevws\Container;

$container = new Container();

// Nova Instancia de User
$user = $container->make(User::class);
```

### Binds

Para realizar a associação de uma objeto abstrato e um concreto é feita usando método `bind` 


```php
interface MessageInterface 
{
    public function send(): string;
}
```

```php
class Message implements MessageInterface
{
    private $text;
    
    public function __construct(string $text = 'Hello World')
    {
        $this->text = $text;
    }
    
    public function send(): string
    {
        return $this->text;
    }
}
```

```php
$container->bind(MessageInterface::class, Message::class)

echo $container->make(Message::class)->send(); //> Hello World
```

Passando valor do atributo

```php
$container->bind(MessageInterface::class, Message::class, ['Lorem'])

echo $container->make(Message::class)->send(); //> Lorem
```

#### Bind usando callback

```php
$container->bind(MessageInterface::class, function (){
    return new Message('Hello')
})

echo $container->make(Message::class)->send(); //> Hello
```

### Singletons

Container pode criar instancias singletons usando o método `singleton`

```php
class Counter
{
    protected $count = 0;
    
    public function increment(): int
    {
        return $this->count++;
    }
}
```

```php 
$container->singleton(Counter::class);

$container->make(Counter::class)->increment() // $count = 1;
$container->make(Counter::class)->increment() // $count = 2;
$container->make(Counter::class)->increment() // $count = 3;
```

### Injeção de dependência

A injeção de dependência pode ser é feita automaticamente pelo container

```php
class User
{
    private $name;
    private $message;
    
    function __construct(MessageInterface $message, string $name = 'Joe')
    {
        $this->message = $message;
        $this->name = $name;
    }
    
    public function saySomething(): string
    {
        return $this->greeting->send()
    }
}
```

```php
$container->make(User::class)->saySomething(); // > Hello World
```


[Packagist]: https://packagist.org/packages/adevws/container "Packagist"
[composer]: https://getcomposer.org "Composer"