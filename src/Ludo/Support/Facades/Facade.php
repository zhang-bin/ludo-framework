<?php
namespace Ludo\Support\Facades;

use RuntimeException;
use Ludo\Support\ServiceProvider;

class Facade {
    public static function __callStatic($method, $args)
    {
        $instance = ServiceProvider::getInstance()->getRegisteredAbstract(static::getFacadeAccessor());
        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        return $instance->$method(...$args);
    }

    public static function getFacadeAccessor() {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }
}