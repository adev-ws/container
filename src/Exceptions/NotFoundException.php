<?php

namespace adevws\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \ErrorException implements NotFoundExceptionInterface
{
    //
}