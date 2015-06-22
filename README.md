Error Handling Middleware
=========================

PSR 7 error handling middleware

Intallation & Requirements
--------------------------

Install using composer

```console
$ composer require jowy/error-handling-middleware
```

This library has following dependencies:

- `zendframework/zend-diactoros`, used for PSR 7 implementation
- `zendframework/zend-stratigility`,  provide abstraction for PSR 7 middleware
- `flip/whoops`, used for error formatting
- `psr/log`, provide abstration for logging

This library has conflict with following library:

- `symfony/http-kernel`, because this library has already used `Symfony\Component\HttpKernel\Exception`, despite require whole `symfony/http-kernel` package it only require the exception class

Usage
-----

Usage on `zendframework/zend-stratigility`

```php
use Zend\Stratigility\MiddlewarePipe;
use Jowy\ExceptionHandler\ExceptionHandler;

$app = new MiddlewarePipe();
$route_middleware = new ExceptionHandler($whoops_output_handler, $psr3_logger, $catch);

$app->pipe($route_middleware);
```

Usage on `relay\relay`

```php
use Pimple\Container;
use Relay\Relay;
use Jowy\ExceptionHandler\ExceptionHandler;

$container = new Container();

$container["middleware"] = [
    ExceptionHandler::class => function() {
        return new ExceptionHandler($whoops_output_handler, $psr3_logger, $catch);
    }
];

$resolver = function ($class) use ($container) {
    return $container[$class];
}

new Relay(array_keys($container["middleware"], $resolver);
```

API
---

```php
use Psr\Log\LoggerInterface;
use Whoops\Handler\HandlerInterface;

class ExceptionHandler
{
    public function __construct(HandlerInterface $error_handler, LoggerInterface $logger, $catch = true);
}
```

License
-------

MIT, see LICENSE.